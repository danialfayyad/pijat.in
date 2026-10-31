@props([
    'order',
    'fetchUrl',
    'sendUrl',
    'isOrderActive' => true,
    'otherPartyName' => 'Terapis / Pelanggan',
])

<div x-data="{
        showChat: false,
        messages: [],
        newMessage: '',
        isSending: false,
        chatPollTimer: null,
        unreadCount: 0,
        hasInitialLoad: false,

        init() {
            // Initial silent message fetch to count existing messages
            this.fetchMessages(true);
            
            // Poll for messages periodically if order is active
            @if ($isOrderActive)
                this.chatPollTimer = setInterval(() => {
                    if (this.showChat) {
                        this.fetchMessages(false);
                    }
                }, 4000);
            @endif
        },

        openChat() {
            this.showChat = true;
            this.unreadCount = 0;
            this.fetchMessages(false).then(() => {
                this.scrollBottom();
            });
        },

        closeChat() {
            this.showChat = false;
        },

        async fetchMessages(isSilent = false) {
            try {
                const res = await fetch('{{ $fetchUrl }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && Array.isArray(data.messages)) {
                        const previousCount = this.messages.length;
                        this.messages = data.messages;
                        if (!isSilent && this.showChat) {
                            if (this.messages.length > previousCount) {
                                this.scrollBottom();
                            }
                        }
                    }
                }
            } catch (err) {}
        },

        async sendMessage() {
            const text = this.newMessage.trim();
            if (!text || this.isSending) return;

            this.isSending = true;
            try {
                const res = await fetch('{{ $sendUrl }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: text })
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data && data.message) {
                        this.messages.push(data.message);
                        this.newMessage = '';
                        this.scrollBottom();
                    }
                } else {
                    const err = await res.json();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim',
                            text: err.error || 'Terjadi kesalahan saat mengirim pesan.',
                            customClass: { popup: 'swal-admin-theme' }
                        });
                    }
                }
            } catch (e) {
            } finally {
                this.isSending = false;
            }
        },

        scrollBottom() {
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        }
    }">

    <!-- Floating Chat Trigger Button (Positioned gracefully above bottom sheet) -->
    <div class="absolute right-4 bottom-24 z-30">
        <button type="button" @click="openChat()"
                class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-xl hover:shadow-2xl active:scale-95 flex items-center justify-center transition border-2 border-white cursor-pointer relative"
                title="Buka Chat Obrolan">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-400 border-2 border-white rounded-full"></span>
        </button>
    </div>

    <!-- Slide-over / Modal Chat Sheet -->
    <div x-show="showChat" style="display: none;"
         class="fixed inset-0 z-50 flex flex-col justify-end bg-slate-900/60 backdrop-blur-xs transition-opacity"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="closeChat()"
             class="w-full max-w-md mx-auto bg-white rounded-t-3xl shadow-2xl flex flex-col h-[75vh] max-h-[600px] border-t border-slate-200 overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">

            <!-- Chat Header -->
            <div class="px-4 py-3 bg-gradient-to-r from-emerald-700 to-teal-700 text-white flex items-center justify-between shrink-0 shadow-xs">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold leading-tight">Obrolan Pesanan</h3>
                        <p class="text-[10px] text-emerald-100 font-medium flex items-center space-x-1">
                            <span>🛡️ Privasi Aman • Nomor HP Terlindungi</span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeChat()"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition cursor-pointer text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Notice Privasi -->
            <div class="px-3.5 py-1.5 bg-emerald-50/90 border-b border-emerald-100/80 text-[10.5px] text-emerald-900 font-medium flex items-center justify-between">
                <span>Hubungi {{ $otherPartyName }} terkait patokan alamat atau kedatangan.</span>
                <span class="text-[9px] uppercase tracking-wider font-extrabold bg-emerald-200/80 text-emerald-900 px-1.5 py-0.5 rounded-sm">In-App Chat</span>
            </div>

            <!-- Chat Message List Body -->
            <div x-ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50 overscroll-contain">
                <template x-if="messages.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400 space-y-2">
                        <div class="w-12 h-12 rounded-full bg-slate-200/70 flex items-center justify-center text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-600">Belum ada percakapan</p>
                        <p class="text-[11px] text-slate-400 max-w-[220px]">
                            Mulai obrolan untuk memberi tahu patokan rumah atau instruksi kedatangan.
                        </p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex flex-col" :class="msg.is_me ? 'items-end' : 'items-start'">
                        <span class="text-[9.5px] font-bold text-slate-400 px-1 mb-0.5" x-text="msg.sender_name"></span>
                        <div class="max-w-[78%] px-3.5 py-2 text-xs leading-relaxed shadow-xs"
                             :class="msg.is_me ? 'bg-emerald-600 text-white rounded-2xl rounded-tr-xs' : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-xs'">
                            <p class="whitespace-pre-wrap break-words" x-text="msg.message"></p>
                        </div>
                        <span class="text-[9px] font-mono text-slate-400 px-1 mt-0.5" x-text="msg.time"></span>
                    </div>
                </template>
            </div>

            <!-- Footer / Input Box -->
            <div class="p-3 bg-white border-t border-slate-200 shrink-0">
                @if ($isOrderActive)
                    <form @submit.prevent="sendMessage()" class="flex items-center space-x-2">
                        <input type="text" x-model="newMessage"
                               placeholder="Tulis pesan..."
                               maxlength="1000"
                               :disabled="isSending"
                               class="flex-1 text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition outline-none">
                        <button type="submit"
                                :disabled="isSending || !newMessage.trim()"
                                class="h-[38px] px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                            <span x-show="!isSending">Kirim</span>
                            <span x-show="isSending">...</span>
                            <svg x-show="!isSending" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                @else
                    <div class="p-2.5 bg-slate-100 rounded-xl text-center text-xs text-slate-500 font-medium">
                        🔒 Sesi obrolan telah ditutup karena pesanan telah selesai atau dibatalkan.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

