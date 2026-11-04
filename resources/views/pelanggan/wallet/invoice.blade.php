<x-pwa-layout title="Tagihan Pembayaran - Pijat.In" headerTitle="Tagihan Pembayaran" :backUrl="$invoice->order ? route('pelanggan.orders.show', $invoice->order) : route('pelanggan.wallet.index')">
    <div class="py-2 space-y-4"
         x-data="{
            expiresAt: new Date('{{ $invoice->expires_at->toIso8601String() }}').getTime(),
            timeLeft: '',
            isExpired: {{ $invoice->isExpired() ? 'true' : 'false' }},
            updateTimer() {
                const now = new Date().getTime();
                const diff = this.expiresAt - now;
                if (diff <= 0) {
                    this.timeLeft = '00:00';
                    this.isExpired = true;
                    return;
                }
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);
                this.timeLeft = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }
         }"
         x-init="updateTimer(); setInterval(() => updateTimer(), 1000);">

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($invoice->isPaid())
            <!-- Paid Success Card -->
            <div class="bg-white rounded-2xl p-6 border border-emerald-300 shadow-sm text-center space-y-3.5">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 mx-auto flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <span class="inline-block text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                        Pembayaran Berhasil
                    </span>
                    <h3 class="text-xl font-extrabold text-slate-900 mt-2 font-mono">{{ $invoice->formatted_amount }}</h3>
                    <p class="text-xs text-slate-600 mt-1">
                        @if ($invoice->order)
                            Pembayaran pesanan #{{ $invoice->order->order_code }} telah lunas dan sedang mencarikan mitra.
                        @else
                            Saldo telah berhasil ditambahkan ke dompet Pijat.In Anda.
                        @endif
                    </p>
                </div>
                <div class="pt-2">
                    @if ($invoice->order)
                        <a href="{{ route('pelanggan.orders.show', $invoice->order) }}" 
                            class="w-full min-h-[44px] py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center cursor-pointer">
                            Kembali ke Pelacakan Pesanan
                        </a>
                    @else
                        <a href="{{ route('pelanggan.wallet.index') }}" 
                            class="w-full min-h-[44px] py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center cursor-pointer">
                            Kembali ke Dompet
                        </a>
                    @endif
                </div>
            </div>
        @elseif ($invoice->isExpired())
            <!-- Expired Card -->
            <div class="bg-white rounded-2xl p-6 border border-rose-200 shadow-sm text-center space-y-3.5">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-700 mx-auto flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Tagihan Telah Kedaluwarsa</h3>
                    <p class="text-xs text-slate-600 mt-1">Waktu pembayaran 15 menit telah habis. Silakan buat tagihan baru.</p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('pelanggan.wallet.topup') }}" 
                        class="w-full min-h-[44px] py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center cursor-pointer">
                        Buat Tagihan Baru
                    </a>
                </div>
            </div>
        @else
            <!-- Pending Payment Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs space-y-4">
                
                @if ($invoice->order)
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-950 flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="leading-relaxed">
                            Tagihan kekurangan pembayaran untuk <strong>Pesanan #{{ $invoice->order->order_code }}</strong>. Setelah Anda menyelesaikan pembayaran, sistem otomatis memproses pencarian mitra terapis.
                        </div>
                    </div>
                @endif

                <!-- Countdown Banner -->
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between text-xs">
                    <span class="text-amber-950 font-semibold flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Selesaikan Pembayaran Dalam:</span>
                    </span>
                    <span class="text-sm font-black font-mono text-amber-950" x-text="timeLeft">15:00</span>
                </div>

                <!-- Invoice Header Details -->
                <div class="text-center pt-2">
                    <span class="text-xs font-medium text-slate-500 block">Total Tagihan</span>
                    <div class="text-2xl font-black text-slate-900 font-mono mt-0.5">{{ $invoice->formatted_amount }}</div>
                    <span class="text-xs text-slate-500 font-mono mt-1 block">Nomor Tagihan: {{ $invoice->invoice_number }}</span>
                </div>

                <!-- Payment Details Box -->
                <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/90 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">Metode:</span>
                        <span class="font-bold text-slate-900">{{ $invoice->payment_method }}</span>
                    </div>

                    @if ($invoice->payment_method === 'QRIS')
                        <!-- QRIS Visual Mockup -->
                        <div class="py-3 text-center space-y-2">
                            <div class="w-44 h-44 mx-auto p-3 bg-white rounded-xl border border-slate-200 shadow-xs flex items-center justify-center">
                                <!-- QR Mockup SVG -->
                                <svg class="w-36 h-36 text-slate-900" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm8-2h2v2h-2v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-8 4h2v2h-2v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-8 4h2v2h-2v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-slate-500 block">Pindai QRIS dengan aplikasi dompet digital atau mobile banking Anda</span>
                        </div>
                    @else
                        <!-- Virtual Account Number -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 text-center">
                            <span class="text-xs text-slate-500 block font-medium">Nomor Virtual Account:</span>
                            <div class="text-base font-black font-mono text-slate-900 tracking-wider mt-0.5">
                                8808{{ substr($invoice->invoice_number, -8) }}
                            </div>
                        </div>
                    @endif
                </div>

                @if ($invoice->payment_url)
                    <div class="pt-2">
                        <a href="{{ $invoice->payment_url }}" target="_blank"
                            class="w-full min-h-[48px] py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                            <span>Buka Halaman Checkout Xendit Resmi &rarr;</span>
                        </a>
                    </div>
                @endif

                <!-- Button Check Status to Xendit API (No Ngrok needed on localhost!) -->
                <form action="{{ route('pelanggan.wallet.check-status', $invoice->invoice_number) }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" 
                        class="w-full min-h-[48px] py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Cek Status Pembayaran ke Xendit</span>
                    </button>
                    <p class="text-[11px] text-slate-500 text-center mt-1.5">
                        Setelah Anda selesai transfer/bayar di Xendit, klik tombol di atas untuk sinkronisasi seketika.
                    </p>
                </form>

                <!-- Simulation Action Button (For Testing/Verification) -->
                <form action="{{ route('pelanggan.wallet.simulate-pay', $invoice->invoice_number) }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" 
                        class="w-full min-h-[48px] py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simulasi Bayar Sekarang (Uji Coba)</span>
                    </button>
                    <p class="text-[11px] text-slate-500 text-center mt-1.5">
                        Tekan tombol di atas untuk mensimulasikan pelunasan saldo instan.
                    </p>
                </form>

            </div>
        @endif

    </div>
</x-pwa-layout>

