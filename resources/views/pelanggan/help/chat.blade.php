<x-pwa-layout title="Chat Bantuan #{{ $ticket->ticket_code }}" headerTitle="Live Chat Bantuan" :backUrl="route('pelanggan.help.index')" :fullBleed="true" :hideDock="true">
    <div class="flex-1 flex flex-col h-full min-h-0 bg-slate-100"
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

        <!-- Chat Header Meta Bar -->
        <div class="bg-white border-b border-slate-200/80 px-4 py-2.5 flex items-center justify-between shrink-0 shadow-2xs">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="font-mono text-[11px] font-bold text-slate-500">{{ $ticket->ticket_code }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-[11px] text-slate-500 font-medium truncate">{{ $ticket->region?->name ?? 'Wilayah Layanan' }}</span>
                </div>
                <div class="text-xs font-bold text-slate-800 truncate mt-0.5">{{ $ticket->subject }}</div>
            </div>

            <div class="shrink-0">
                <template x-if="ticketStatus === 'menunggu'">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Menunggu Respon</span>
                    </span>
                </template>
                <template x-if="ticketStatus === 'berlangsung'">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Berlangsung</span>
                    </span>
                </template>
                <template x-if="ticketStatus === 'selesai'">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        <span>Selesai</span>
                    </span>
                </template>
            </div>
        </div>

        <!-- Chat Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0 overscroll-contain" x-ref="chatContainer">
            <!-- Starting notification bubble -->
            <div class="text-center py-2">
                <span class="inline-block px-3 py-1 bg-white/80 border border-slate-200 rounded-full text-[10px] font-medium text-slate-500 shadow-2xs">
                    Sesi chat bantuan dimulai • Terhubung dengan Admin Wilayah
                </span>
            </div>

            <!-- Loop messages -->
            <template x-for="item in messages" :key="item.id">
                <div class="flex flex-col" :class="item.is_me ? 'items-end' : 'items-start'">
                    <!-- Sender Label for Admin -->
                    <template x-if="!item.is_me">
                        <div class="flex items-center gap-1 mb-1 px-1">
                            <span class="text-[10px] font-bold text-slate-700" x-text="item.sender_name"></span>
                            <span class="text-[9px] font-semibold bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded">Admin Wilayah</span>
                        </div>
                    </template>

                    <!-- Bubble Message -->
                    <div class="max-w-[82%] px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed break-words shadow-2xs"
                         :class="item.is_me ? 'bg-emerald-600 text-white rounded-tr-xs' : 'bg-white border border-slate-200/90 text-slate-800 rounded-tl-xs'">
                        <div class="whitespace-pre-wrap" x-text="item.message"></div>
                        <div class="text-[10px] mt-1 text-right"
                             :class="item.is_me ? 'text-emerald-100' : 'text-slate-400'"
                             x-text="item.created_at"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Reopened notice if ticket is finished -->
        <template x-if="ticketStatus === 'selesai'">
            <div class="bg-slate-200/90 border-t border-slate-300 px-3 py-1.5 text-center text-[11px] text-slate-600 shrink-0">
                Sesi ini telah diselesaikan. Ketik pesan di bawah jika ingin membuka kembali percakapan.
            </div>
        </template>

        <!-- Message Input Bar (Menggantikan Navbar Navigasi) -->
        <div class="bg-white border-t border-slate-200 p-3 shrink-0 shadow-lg">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <input type="text"
                       x-model="newMessage"
                       placeholder="Ketik pesan balasan..."
                       autocomplete="off"
                       class="flex-1 text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50 outline-none"
                       :disabled="isSending">
                <button type="submit"
                        class="h-[38px] px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-xs disabled:opacity-50 cursor-pointer flex items-center justify-center space-x-1 shrink-0"
                        :disabled="!newMessage.trim() || isSending">
                    <span x-show="!isSending">Kirim</span>
                    <span x-show="isSending">...</span>
                    <svg x-show="!isSending" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                </button>
            </form>
        </div>
    </div>
</x-pwa-layout>

