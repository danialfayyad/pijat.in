<x-pwa-layout title="Buka Live Chat - Pijat.In" headerTitle="Live Chat Bantuan" :backUrl="route('pelanggan.help.index')">
    <div class="space-y-4 py-2">
        <!-- New Ticket Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Mulai Sesi Chat Bantuan</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Jelaskan kendala atau pertanyaan Anda. Tiket ini akan langsung diteruskan ke Admin Operasional di wilayah Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('pelanggan.help.store') }}" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Topik / Subjek Kendala <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="subject"
                           required
                           maxlength="150"
                           placeholder="Contoh: Kendala pembayaran atau jadwal terapis"
                           value="{{ old('subject') }}"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                    @error('subject')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Pesan Pertama <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="message"
                              rows="4"
                              required
                              minlength="5"
                              maxlength="2000"
                              placeholder="Ceritakan detail pertanyaan atau kendala yang Anda alami..."
                              class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    <span>Kirim & Mulai Percakapan</span>
                </button>
            </form>
        </div>

        <!-- Past Resolved Tickets (If any) -->
        @if ($pastTickets->isNotEmpty())
            <div class="space-y-2 pt-2">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider px-1">Riwayat Tiket Sebelumnya</h4>
                <div class="space-y-2">
                    @foreach ($pastTickets as $past)
                        <a href="{{ route('pelanggan.help.ticket', $past) }}"
                           class="block bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-2xs hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-[11px] font-bold text-slate-500">{{ $past->ticket_code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Selesai</span>
                            </div>
                            <div class="text-xs font-bold text-slate-800 mt-1">{{ $past->subject }}</div>
                            <div class="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                                <span>Wilayah: {{ $past->region?->name ?? '-' }}</span>
                                <span>{{ $past->closed_at?->diffForHumans() ?? $past->updated_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-pwa-layout>

