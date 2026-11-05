<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.support-chats.index') }}" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition" title="Kembali ke Antrean">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-slate-400">{{ $ticket->ticket_code }}</span>
                        @if ($ticket->status === 'berlangsung')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                <span>Sedang Berlangsung</span>
                            </span>
                        @elseif ($ticket->status === 'menunggu')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Menunggu Respon</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Selesai</span>
                            </span>
                        @endif
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">
                        {{ $ticket->subject }}
                    </h2>
                </div>
            </div>

            <!-- Actions Header -->
            <div class="flex items-center gap-2">
                @if ($ticket->status !== 'selesai')
                    <form method="POST" action="{{ route('admin.support-chats.close', $ticket) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan dan menutup sesi chat bantuan ini?')">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 border border-slate-200">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Tandai Selesai</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.support-chats.reopen', $ticket) }}">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 border border-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span>Buka Kembali Tiket</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left / Center: Live Chat Stream (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col h-[650px] overflow-hidden"
                 x-data="supportChatDesk({
                     ticketId: {{ $ticket->id }},
                     currentAdminId: {{ Auth::id() }},
                     fetchUrl: '{{ route('admin.support-chats.messages', $ticket) }}',
                     sendUrl: '{{ route('admin.support-chats.reply', $ticket) }}',
                     csrfToken: '{{ csrf_token() }}',
                     initialMessages: {{ json_encode($ticket->messages->map(fn($m) => [
                         'id' => $m->id,
                         'sender_id' => $m->sender_id,
                         'sender_name' => $m->sender?->name ?? 'User',
                         'sender_role' => $m->sender_role,
                         'is_admin' => $m->sender_role === 'admin',
                         'message' => $m->message,
                         'created_at' => $m->created_at->format('H:i')
                     ])) }}
                 })">
                
                <!-- Chat Stream Area -->
                <div class="flex-1 p-5 overflow-y-auto space-y-4 bg-slate-50/50" x-ref="chatBox">
                    <div class="text-center my-2">
                        <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-white text-slate-400 border border-slate-200/60 shadow-2xs">
                            Sesi Bantuan Wilayah {{ $ticket->region?->name }} dimulai {{ $ticket->created_at->format('d M Y, H:i') }} WIB
                        </span>
                    </div>

                    <template x-for="item in messages" :key="item.id">
                        <div class="flex flex-col" :class="item.is_admin ? 'items-end' : 'items-start'">
                            <div class="text-[10px] text-slate-400 mb-1 px-1 font-medium"
                                 x-text="item.is_admin ? 'Admin: ' + item.sender_name : item.sender_name + ' (' + item.sender_role + ')'">
                            </div>
                            <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-xs shadow-2xs leading-relaxed"
                                 :class="item.is_admin ? 'bg-emerald-600 text-white rounded-br-xs' : 'bg-white text-slate-800 border border-slate-200/80 rounded-bl-xs'">
                                <p class="whitespace-pre-wrap" x-text="item.message"></p>
                                <div class="text-[10px] mt-1 text-right"
                                     :class="item.is_admin ? 'text-emerald-100' : 'text-slate-400'"
                                     x-text="item.created_at">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Input Message Bar -->
                @if ($ticket->status !== 'selesai')
                    <div class="p-3.5 bg-white border-t border-slate-100">
                        <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                            <textarea x-model="newMessage"
                                      @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                                      rows="1" 
                                      placeholder="Ketik balasan untuk pengguna (Tekan Enter untuk kirim)..."
                                      required
                                      class="flex-1 text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 resize-none"></textarea>
                            
                            <button type="submit" 
                                    :disabled="isSending || !newMessage.trim()"
                                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Kirim</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-4 bg-slate-100 text-center text-xs text-slate-500 font-medium border-t border-slate-200">
                        Tiket ini telah ditutup pada {{ $ticket->closed_at?->format('d M Y, H:i') }}. Klik "Buka Kembali Tiket" di atas untuk mengirim pesan baru.
                    </div>
                @endif
            </div>

            <!-- Right: User & Regional Context Sidebar (1 Col) -->
            <div class="space-y-4">
                <!-- User Profile Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400">Informasi Pengguna</h4>
                        @if ($ticket->user_role === 'mitra')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Mitra Terapis
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Pelanggan
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-base border border-slate-200">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="font-bold text-sm text-slate-800 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $user->phone ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Wilayah Tugas:</span>
                            <span class="font-semibold text-slate-700">{{ $ticket->region?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status Akun:</span>
                            <span class="font-semibold text-slate-700">{{ ucfirst($user->status?->value ?? (string) $user->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Poin Sanksi:</span>
                            <span class="font-bold {{ $user->penalty_points > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $user->penalty_points }} Poin</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        @if ($ticket->user_role === 'mitra')
                            <a href="{{ route('admin.users.partners.show', $user) }}" 
                               target="_blank"
                               class="w-full block text-center py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition border border-slate-200">
                                Lihat Profil Lengkap Mitra &rarr;
                            </a>
                        @else
                            <a href="{{ route('admin.users.customers.show', $user) }}" 
                               target="_blank"
                               class="w-full block text-center py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition border border-slate-200">
                                Lihat Profil Pelanggan &rarr;
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Admin Handling Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400">Penanggung Jawab</h4>
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center justify-center">
                            {{ strtoupper(substr($ticket->admin?->name ?? Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">{{ $ticket->admin?->name ?? Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-400">Admin Wilayah Aktif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine Chat Desk Handler Script -->
    <script>
        function supportChatDesk(config) {
            return {
                ticketId: config.ticketId,
                currentAdminId: config.currentAdminId,
                fetchUrl: config.fetchUrl,
                sendUrl: config.sendUrl,
                csrfToken: config.csrfToken,
                messages: config.initialMessages || [],
                newMessage: '',
                isSending: false,
                pollingInterval: null,

                init() {
                    this.scrollToBottom();
                    this.pollingInterval = setInterval(() => {
                        this.pollNewMessages();
                    }, 4000);
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const box = this.$refs.chatBox;
                        if (box) {
                            box.scrollTop = box.scrollHeight;
                        }
                    });
                },

                async pollNewMessages() {
                    const latestId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;
                    try {
                        const res = await fetch(`${this.fetchUrl}?after_id=${latestId}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data.messages && data.messages.length > 0) {
                            this.messages.push(...data.messages);
                            this.scrollToBottom();
                        }
                    } catch (e) {}
                },

                async sendMessage() {
                    const text = this.newMessage.trim();
                    if (!text || this.isSending) return;

                    this.isSending = true;
                    try {
                        const res = await fetch(this.sendUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({ message: text })
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data.message) {
                                this.messages.push(data.message);
                                this.newMessage = '';
                                this.scrollToBottom();
                            }
                        }
                    } catch (e) {
                        alert('Gagal mengirim pesan balasan. Silakan periksa koneksi.');
                    } finally {
                        this.isSending = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>

