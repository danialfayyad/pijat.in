<x-pwa-layout title="Chat Bantuan Pelanggan #{{ $ticket->ticket_code }}"
              :headerTitle="$ticket->region?->name ? 'Admin ' . $ticket->region->name : 'Bantuan Operasional'"
              :headerSubtitle="'#' . $ticket->ticket_code . ' • ' . $ticket->subject"
              :backUrl="route('pelanggan.help.index')"
              :fullBleed="true"
              :hideDock="true"
              :hideNotification="true">
    <x-slot:headerAction>
        <div class="flex items-center gap-2.5">
            <template x-if="ticketStatus === 'menunggu'">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Menunggu</span>
                </span>
            </template>
            <template x-if="ticketStatus === 'berlangsung'">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Aktif</span>
                </span>
            </template>
            <template x-if="ticketStatus === 'selesai'">
                <span class="text-xs font-medium text-slate-400">
                    Selesai
                </span>
            </template>

            <template x-if="ticketStatus !== 'selesai'">
                <form method="POST" action="{{ route('pelanggan.help.close', $ticket) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri sesi chat ini? Riwayat percakapan akan dihapus.');" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline px-1 py-0.5 transition cursor-pointer">
                        Akhiri
                    </button>
                </form>
            </template>
        </div>
    </x-slot:headerAction>

    <div class="flex-1 flex flex-col h-full min-h-0 bg-slate-50"
         x-data="{
             ticketId: {{ $ticket->id }},
             ticketStatus: '{{ $ticket->status }}',
             messages: @js($ticket->messages->map(fn($m) => [
                 'id' => $m->id,
                 'message' => $m->message,
                 'sender_id' => $m->sender_id,
                 'sender_name' => $m->sender_role === 'admin' ? ($ticket->admin?->name ?? 'Admin Wilayah') : $m->sender?->name,
                 'sender_role' => $m->sender_role,
                 'created_at' => $m->created_at->format('H:i'),
                 'is_me' => $m->sender_id === auth()->id(),
             ])),
             newMessage: '',
             isSending: false,
             pollTimer: null,

             init() {
                 this.$nextTick(() => this.scrollToBottom());
                 this.pollTimer = setInterval(() => {
                     this.pollMessages();
                 }, 4000);
             },

             destroy() {
                 if (this.pollTimer) clearInterval(this.pollTimer);
             },

             scrollToBottom() {
                 const container = this.$refs.chatContainer;
                 if (container) {
                     container.scrollTop = container.scrollHeight;
                 }
             },

             async pollMessages() {
                 const latestId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;
                 try {
                     const res = await fetch('{{ route('pelanggan.help.messages', $ticket) }}?after_id=' + latestId, {
                         headers: { 'Accept': 'application/json' }
                     });
                     if (res.ok) {
                         const data = await res.json();
                         this.ticketStatus = data.ticket_status;
                         if (Array.isArray(data.messages) && data.messages.length > 0) {
                             this.messages.push(...data.messages);
                             this.$nextTick(() => this.scrollToBottom());
                         }
                     }
                 } catch (e) {}
             },

             async sendMessage() {
                 const text = this.newMessage.trim();
                 if (!text || this.isSending) return;

                 this.isSending = true;
                 try {
                     const res = await fetch('{{ route('pelanggan.help.reply', $ticket) }}', {
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
                         if (data.success && data.message) {
                             data.message.is_me = true;
                             this.messages.push(data.message);
                             this.newMessage = '';
                             this.ticketStatus = 'menunggu';
                             this.$nextTick(() => this.scrollToBottom());
                         }
                     }
                 } catch (e) {
                 } finally {
                     this.isSending = false;
                 }
             }
         }">

        <!-- Chat Messages Container -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3 min-h-0 overscroll-contain" x-ref="chatContainer">
            <!-- Starting system notice -->
            <div class="text-center py-1">
                <span class="inline-block px-3 py-1 bg-white border border-slate-200/80 rounded-full text-[11px] font-medium text-slate-500 shadow-2xs">
                    Sesi chat bantuan dimulai • Terhubung dengan Admin Wilayah
                </span>
            </div>

            <!-- Loop messages -->
            <template x-for="item in messages" :key="item.id">
                <div class="flex flex-col" :class="item.is_me ? 'items-end' : 'items-start'">
                    <!-- Sender Label for Admin -->
                    <template x-if="!item.is_me">
                        <div class="flex items-center gap-1.5 mb-1 px-1">
                            <span class="text-[11px] font-semibold text-slate-700" x-text="item.sender_name"></span>
                            <span class="text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-1.5 py-0.2 rounded">Admin</span>
                        </div>
                    </template>

                    <!-- Bubble Message -->
                    <div class="max-w-[85%] px-3.5 py-2.5 text-sm leading-relaxed break-words shadow-2xs"
                         :class="item.is_me ? 'bg-emerald-600 text-white rounded-2xl rounded-br-xs' : 'bg-white border border-slate-200/80 text-slate-800 rounded-2xl rounded-bl-xs'">
                        <div class="whitespace-pre-wrap" x-text="item.message"></div>
                        <div class="text-[10px] mt-1 text-right"
                             :class="item.is_me ? 'text-emerald-100' : 'text-slate-400'"
                             x-text="item.created_at"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sesi Selesai Banner & Tombol Mulai Chat Baru -->
        <template x-if="ticketStatus === 'selesai'">
            <div class="bg-white border-t border-slate-200 p-3 shrink-0 shadow-lg space-y-2">
                <div class="text-center text-xs text-slate-500">
                    Sesi obrolan ini telah berakhir. Riwayat sesi telah ditutup.
                </div>
                <a href="{{ route('pelanggan.help.chat') }}"
                   class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <span>Mulai Percakapan Baru</span>
                </a>
            </div>
        </template>

        <!-- Message Input Bar (Hanya tampil jika tiket masih aktif) -->
        <template x-if="ticketStatus !== 'selesai'">
            <div class="bg-white border-t border-slate-200/80 px-3 py-2.5 shrink-0 shadow-lg">
                <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                    <input type="text"
                           x-model="newMessage"
                           placeholder="Ketik pesan..."
                           autocomplete="off"
                           class="flex-1 text-sm bg-slate-100 hover:bg-slate-100/90 focus:bg-white border border-slate-200/70 focus:border-emerald-500 rounded-full px-4 py-2.5 outline-none transition"
                           :disabled="isSending">
                    <button type="submit"
                            aria-label="Kirim Pesan"
                            class="w-10 h-10 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full transition shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center shrink-0 active:scale-95"
                            :disabled="!newMessage.trim() || isSending">
                        <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </template>
    </div>
</x-pwa-layout>
