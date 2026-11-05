<div x-data="{
        open: false,
        loading: false,
        filter: 'all',
        notifications: [],
        unreadCount: {{ Auth::user()?->systemNotifications()->unread()->count() ?? 0 }},
        async fetchNotifications() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('notifications.index') }}?filter=' + this.filter, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                }
            } catch (e) {
                console.error('Failed to load notifications', e);
            } finally {
                this.loading = false;
            }
        },
        async markAsRead(id, actionUrl) {
            try {
                const res = await fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.unreadCount = data.unread_count;
                    const item = this.notifications.find(n => n.id === id);
                    if (item) item.read = true;
                    window.dispatchEvent(new CustomEvent('notification-read'));
                    if (actionUrl) {
                        window.location.href = actionUrl;
                    }
                }
            } catch (e) {
                console.error('Failed to mark notification as read', e);
            }
        },
        async markAllAsRead() {
            try {
                const res = await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });
                if (res.ok) {
                    this.unreadCount = 0;
                    this.notifications.forEach(n => n.read = true);
                    window.dispatchEvent(new CustomEvent('notifications-read-all'));
                }
            } catch (e) {
                console.error('Failed to mark all as read', e);
            }
        }
    }"
    @open-notification-modal.window="open = true; fetchNotifications()"
    @keydown.escape.window="open = false"
    class="relative z-50">

    <!-- Backdrop (Full Screen Overlay to dim background) -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity z-[65]"
         style="display: none;"></div>

    <!-- Centered PWA Frame Overlay Container: Confined strictly within the PWA width (max-w-md mx-auto) -->
    <div x-show="open" 
         class="fixed inset-y-0 left-0 right-0 max-w-md mx-auto z-[70] overflow-hidden pointer-events-none"
         style="display: none;">

        <!-- Drawer Panel (Sidebar Slide from Right to Left INSIDE PWA, covering 3/4 PWA width) -->
        <div x-show="open" 
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-250"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 w-3/4 bg-white shadow-2xl flex flex-col h-full border-l border-slate-200 rounded-l-2xl sm:rounded-l-3xl overflow-hidden pointer-events-auto"
             @click.stop>
            
            <!-- Header -->
            <div class="p-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/90">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900 leading-none">Pusat Notifikasi</h3>
                        <span class="text-[10px] text-slate-500 font-medium block mt-0.5">
                            <span x-text="unreadCount">0</span> belum dibaca
                        </span>
                    </div>
                </div>

                <!-- Close Button -->
                <button type="button" 
                        @click="open = false"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer"
                        aria-label="Tutup Notifikasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Filter Tabs & Dedicated Action Toolbar -->
            <div class="px-3 py-2 border-b border-slate-100 bg-white flex items-center justify-between gap-1.5 flex-wrap">
                <div class="flex items-center space-x-1.5">
                    <button type="button" 
                            @click="filter = 'all'; fetchNotifications()"
                            :class="filter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-2 py-1 rounded-lg text-[11px] transition cursor-pointer">
                        Semua
                    </button>
                    <button type="button" 
                            @click="filter = 'unread'; fetchNotifications()"
                            :class="filter === 'unread' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-2 py-1 rounded-lg text-[11px] transition cursor-pointer flex items-center space-x-1">
                        <span>Belum Dibaca</span>
                        <span x-show="unreadCount > 0" 
                              class="px-1.5 py-0.2 rounded-full text-[9px] font-mono font-bold bg-emerald-600 text-white" 
                              x-text="unreadCount"></span>
                    </button>
                </div>

                <!-- Tombol Tandai Baca Semua -->
                <button type="button" 
                        @click="markAllAsRead()"
                        :disabled="unreadCount === 0"
                        :class="unreadCount > 0 ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-300 cursor-pointer shadow-2xs' : 'text-slate-400 bg-slate-50 border-slate-200 cursor-not-allowed opacity-60'"
                        class="inline-flex items-center space-x-1 px-2 py-1 rounded-lg text-[11px] font-bold border transition shrink-0"
                        title="Tandai semua notifikasi telah dibaca">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="whitespace-nowrap">Tandai Baca Semua</span>
                </button>
            </div>

        <!-- Notification List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2 space-y-2">
            <!-- Loading Spinner -->
            <div x-show="loading" class="py-12 text-center text-slate-400 space-y-2">
                <svg class="w-7 h-7 mx-auto animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-medium block">Memuat notifikasi...</span>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && notifications.length === 0" class="py-16 text-center space-y-3 px-6">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Belum Ada Notifikasi</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Pemberitahuan aktivitas pesanan, transaksi dompet, dan status akun Anda akan tampil di sini.
                    </p>
                </div>
            </div>

            <!-- Notification Items -->
            <template x-for="item in notifications" :key="item.id">
                <div class="p-3.5 rounded-xl transition border"
                     :class="item.read ? 'bg-white border-slate-100 text-slate-700' : 'bg-emerald-50/40 border-emerald-200/70 text-slate-900 shadow-2xs'">
                    <div class="flex items-start justify-between space-x-2.5">
                        <div class="flex items-start space-x-2.5 flex-1 min-w-0">
                            <!-- Unread indicator dot -->
                            <div class="mt-1 shrink-0">
                                <span class="w-2 h-2 rounded-full block"
                                      :class="item.read ? 'bg-transparent' : 'bg-emerald-600 ring-2 ring-emerald-200'"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h5 class="text-xs font-bold truncate leading-tight" 
                                        :class="item.read ? 'text-slate-800' : 'text-slate-950'"
                                        x-text="item.title"></h5>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1 leading-relaxed" x-text="item.body"></p>

                                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                                    <span class="font-medium" x-text="item.created_at_human"></span>

                                    <div class="flex items-center space-x-2">
                                        <!-- Action Link if present -->
                                        <template x-if="item.action_url">
                                            <button type="button" 
                                                    @click="markAsRead(item.id, item.action_url)"
                                                    class="text-emerald-700 hover:text-emerald-800 font-bold hover:underline cursor-pointer">
                                                <span x-text="item.action_label || 'Buka Rincian &rarr;'"></span>
                                            </button>
                                        </template>

                                        <!-- Mark Read Button if Unread -->
                                        <template x-if="!item.read">
                                            <button type="button" 
                                                    @click="markAsRead(item.id, null)"
                                                    title="Tandai sudah dibaca"
                                                    class="text-slate-400 hover:text-emerald-600 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Link to full page if needed -->
        <div class="p-3 bg-slate-50 border-t border-slate-200 text-center text-[11px] text-slate-500">
            <span>Notifikasi otomatis dibersihkan setiap 30 hari.</span>
        </div>
    </div>
</div>
</div>

