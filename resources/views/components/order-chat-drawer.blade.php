@props([
    'order',
    'fetchUrl',
    'sendUrl',
    'isOrderActive' => true,
    'otherPartyName' => 'Terapis / Pelanggan',
    'positionClass' => null,
])

@php
    $isFinished = in_array($order->status, [\App\Enums\OrderStatus::Selesai, \App\Enums\OrderStatus::Dibatalkan])
        || in_array($order->status?->value ?? '', ['selesai', 'dibatalkan'])
        || ! $isOrderActive;

    $fabPosition = $positionClass ?? ($isFinished ? 'right-4 bottom-4' : 'right-4 bottom-24');
@endphp

<div @open-order-chat.window="openChat()"
     x-data="{
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

    <!-- Floating Chat Trigger Button -->
    <div class="absolute {{ $fabPosition }} z-30">
        <button type="button" @click="openChat()"
                class="w-12 h-12 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg active:scale-95 flex items-center justify-center transition cursor-pointer"
                title="Buka Chat Obrolan"
                aria-label="Buka Chat Obrolan">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>
    </div>

    <!-- Slide-over / Modal Chat Sheet -->
    <div x-show="showChat" style="display: none;"
         class="fixed inset-0 z-50 flex flex-col justify-end bg-slate-900/40 transition-opacity"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="closeChat()"
             class="w-full max-w-md mx-auto bg-white rounded-t-2xl shadow-xl flex flex-col h-[78vh] max-h-[620px] border-t border-slate-200/80 overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">

            <!-- Chat Header -->
            <div class="px-4 py-3 bg-white border-b border-slate-200/80 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-700 font-bold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(mb_substr($otherPartyName, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 leading-tight truncate">{{ $otherPartyName }}</h3>
                        <p class="text-[11px] text-slate-500 leading-tight mt-0.5">Obrolan pesanan</p>
                    </div>
                </div>
                <button type="button" @click="closeChat()"
                        aria-label="Tutup Obrolan"
                        class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Chat Message List Body -->
            <div x-ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-3 space-y-2.5 bg-slate-50 overscroll-contain">
                <template x-if="messages.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center px-6 py-8">
                        <p class="text-xs font-medium text-slate-500">Belum ada pesan</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-[220px]">
                            Kirim pesan untuk koordinasi alamat atau kedatangan.
                        </p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex flex-col" :class="msg.is_me ? 'items-end' : 'items-start'">
                        <template x-if="!msg.is_me">
                            <span class="text-[11px] font-semibold text-slate-600 mb-1 px-1" x-text="msg.sender_name"></span>
                        </template>
                        <div class="max-w-[82%] px-3.5 py-2 text-sm leading-relaxed break-words"
                             :class="msg.is_me ? 'bg-emerald-600 text-white rounded-2xl rounded-br-xs' : 'bg-white border border-slate-200/80 text-slate-800 rounded-2xl rounded-bl-xs'">
                            <div class="whitespace-pre-wrap" x-text="msg.message"></div>
                            <div class="text-[10px] mt-1 text-right"
                                 :class="msg.is_me ? 'text-emerald-100' : 'text-slate-400'"
                                 x-text="msg.time"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer / Input Box -->
            @if ($isOrderActive)
                <div class="bg-white border-t border-slate-200/80 px-3 py-2.5 shrink-0">
                    <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                        <input type="text" x-model="newMessage"
                               placeholder="Ketik pesan..."
                               maxlength="1000"
                               autocomplete="off"
                               :disabled="isSending"
                               class="flex-1 text-sm bg-slate-100 focus:bg-white border border-slate-200/70 focus:border-emerald-500 rounded-full px-4 py-2.5 outline-none transition">
                        <button type="submit"
                                aria-label="Kirim Pesan"
                                :disabled="isSending || !newMessage.trim()"
                                class="w-10 h-10 rounded-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white transition flex items-center justify-center shrink-0 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-slate-50 border-t border-slate-200/80 px-4 py-3 text-center text-xs text-slate-500 shrink-0">
                    Sesi obrolan telah ditutup karena pesanan selesai atau dibatalkan.
                </div>
            @endif

        </div>
    </div>
</div>

