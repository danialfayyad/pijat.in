<x-pwa-layout title="Tagihan Pembayaran - Pijat.In" :hideHeader="true">
    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen"
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

        <style>
            @keyframes checkmark-draw {
                0% { stroke-dashoffset: 48; }
                100% { stroke-dashoffset: 0; }
            }
            @keyframes pop-bounce {
                0% { transform: scale(0); opacity: 0; }
                70% { transform: scale(1.15); opacity: 1; }
                100% { transform: scale(1); opacity: 1; }
            }
            @keyframes pulse-ring {
                0% { transform: scale(0.85); opacity: 0.8; }
                50% { transform: scale(1.25); opacity: 0.2; }
                100% { transform: scale(0.85); opacity: 0.8; }
            }
            @keyframes confetti-pop {
                0% { transform: translateY(10px) scale(0); opacity: 0; }
                50% { opacity: 1; }
                100% { transform: translateY(-30px) scale(1); opacity: 0; }
            }
            .animate-pop-bounce {
                animation: pop-bounce 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            }
            .animate-draw-check {
                stroke-dasharray: 48;
                stroke-dashoffset: 48;
                animation: checkmark-draw 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards 0.25s;
            }
            .animate-pulse-ring {
                animation: pulse-ring 2.2s infinite ease-in-out;
            }
        </style>

        <!-- 1. TOP BAR NAVIGASI MENGAMBANG DI ATAS HERO -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ $invoice->order ? route('pelanggan.orders.show', $invoice->order) : route('pelanggan.wallet.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto shadow-sm"
               title="Kembali"
               aria-label="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-black text-white uppercase tracking-widest drop-shadow-xs">
                @if ($invoice->isPaid())
                    Status Pembayaran
                @elseif ($invoice->isExpired())
                    Tagihan Kedaluwarsa
                @else
                    Tagihan Pembayaran
                @endif
            </span>

            <a href="{{ route('pelanggan.help.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto shadow-sm"
               title="Pusat Bantuan"
               aria-label="Pusat Bantuan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </div>

        <!-- 2. FIXED GREEN HERO CONTAINER (EMERALD LIVIN' STYLE) -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white pt-28 pb-8 px-5 overflow-hidden">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            @if ($invoice->isPaid())
                <!-- STATE: SUDAH BAYAR / LUNAS DENGAN ANIMASI CENTANG -->
                <div class="relative z-10 flex flex-col items-center text-center">
                    
                    <!-- Lingkaran Animasi Sukses Lebih Besar & Elegan -->
                    <div class="relative flex items-center justify-center my-1.5">
                        <!-- Pulsing Glow Ring -->
                        <div class="absolute w-28 h-28 rounded-full bg-white/20 animate-pulse-ring pointer-events-none"></div>
                        
                        <!-- Checkmark Circle Badge -->
                        <div class="relative w-22 h-22 rounded-full bg-white text-emerald-600 flex items-center justify-center shadow-2xl animate-pop-bounce ring-4 ring-white/30">
                            <svg class="w-11 h-11 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path class="animate-draw-check" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Status Pill (Spasi diperlebar mt-4) -->
                    <div class="mt-4">
                        <span class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-black uppercase tracking-wider text-emerald-100 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-ping"></span>
                            <span>Pembayaran Berhasil</span>
                        </span>
                    </div>

                    <!-- Nominal Saldo Besar (Spasi diperlebar mt-3) -->
                    <div class="mt-3 text-3xl sm:text-4xl font-black text-white tracking-tight font-mono">
                        {{ $invoice->formatted_amount }}
                    </div>

                    <!-- Penjelasan Ringkas (Spasi diperlebar mt-2.5) -->
                    <p class="text-xs text-emerald-100/90 mt-2.5 max-w-xs leading-relaxed">
                        @if ($invoice->order)
                            Pembayaran pesanan #{{ $invoice->order->order_code }} telah lunas dan sedang mencarikan mitra terapis.
                        @else
                            Saldo telah berhasil ditambahkan ke dompet Pijat.In Anda.
                        @endif
                    </p>
                </div>

            @elseif ($invoice->isExpired())
                <!-- STATE: KEDALUWARSA -->
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="w-20 h-20 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-2xl ring-4 ring-rose-300/30">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-900/40 backdrop-blur-md border border-rose-400/30 text-[11px] font-black uppercase tracking-wider text-rose-100 shadow-xs">
                            Kedaluwarsa
                        </span>
                    </div>

                    <div class="mt-2 text-2xl sm:text-3xl font-black text-white tracking-tight font-mono">
                        {{ $invoice->formatted_amount }}
                    </div>

                    <p class="text-xs text-emerald-100/90 mt-1 max-w-xs leading-relaxed">
                        Batas waktu pembayaran 15 menit telah habis. Silakan buat tagihan baru.
                    </p>
                </div>

            @else
                <!-- STATE: MENUNGGU PEMBAYARAN (PENDING) -->
                <div class="relative z-10 flex flex-col items-center text-center">
                    
                    <!-- Countdown Timer Pill Mengambang -->
                    <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs text-white shadow-xs">
                        <svg class="w-4 h-4 text-amber-300 animate-spin" style="animation-duration: 4s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium text-emerald-100">Selesaikan Dalam:</span>
                        <span class="font-black font-mono text-amber-300 text-sm" x-text="timeLeft">15:00</span>
                    </div>

                    <div class="mt-3">
                        <h2 class="text-xs font-extrabold text-emerald-100 uppercase tracking-wider">
                            Total Tagihan Anda
                        </h2>
                    </div>

                    <!-- Nominal Besar -->
                    <div class="mt-1 text-3xl sm:text-4xl font-black text-white tracking-tight font-mono">
                        {{ $invoice->formatted_amount }}
                    </div>

                    <p class="text-[11px] text-emerald-100/90 font-mono mt-1">
                        No. Tagihan: {{ $invoice->invoice_number }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Spacer Tinggi Hero (Tepat dan rapi agar jarak ke container putih proporsional) -->
        <div class="{{ $invoice->isPaid() ? 'h-[385px]' : 'h-[275px]' }} shrink-0 pointer-events-none"></div>

        <!-- 3. OVERLAPPING WHITE BOTTOM SHEET (LEMBAR STRUK & AKSI PEMBAYARAN) -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-4 pb-12 shadow-2xl flex-1 flex flex-col justify-between space-y-4 min-h-[calc(100vh-{{ $invoice->isPaid() ? '385px' : '275px' }})]">

            <!-- Status Alert Messages -->
            @if (session('status'))
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($invoice->isPaid())
                <!-- STRUK DETAIL TRANSAKSI SUKSES -->
                <div class="space-y-4 flex-1 flex flex-col justify-between">
                    <div class="bg-slate-50/90 rounded-2xl border border-slate-100 p-4 sm:p-5 space-y-3.5 shadow-xs">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Nomor Tagihan</span>
                            <span class="font-mono font-bold text-slate-800">{{ $invoice->invoice_number }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Metode Pembayaran</span>
                            <span class="font-bold text-slate-800">
                                {{ $invoice->payment_url ? 'Xendit Gateway (QRIS/VA)' : $invoice->payment_method }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Waktu Selesai</span>
                            <span class="font-medium text-slate-800">
                                {{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Status</span>
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Lunas (Berhasil)</span>
                            </span>
                        </div>

                        <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600">Total Pembayaran</span>
                            <span class="text-base font-black text-slate-900 font-mono">{{ $invoice->formatted_amount }}</span>
                        </div>
                    </div>

                    <!-- Tombol Aksi Kembali -->
                    <div class="pt-2 shrink-0">
                        @if ($invoice->order)
                            <a href="{{ route('pelanggan.orders.show', $invoice->order) }}" 
                                class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2 cursor-pointer active:scale-95">
                                <span>Kembali ke Pelacakan Pesanan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('pelanggan.wallet.index') }}" 
                                class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2 cursor-pointer active:scale-95">
                                <span>Kembali ke Dompet</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

            @elseif ($invoice->isExpired())
                <!-- TAMPILAN KEDALUWARSA -->
                <div class="space-y-4">
                    <div class="bg-slate-50/80 rounded-2xl border border-slate-100 p-4 space-y-2.5 text-xs text-slate-600">
                        <div class="flex items-center justify-between">
                            <span>Nomor Tagihan:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $invoice->invoice_number }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Status:</span>
                            <span class="text-rose-600 font-bold">Kedaluwarsa</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('pelanggan.wallet.topup') }}" 
                            class="w-full py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center cursor-pointer active:scale-95">
                            Buat Tagihan Baru
                        </a>
                    </div>
                </div>

            @else
                <!-- TAMPILAN PENDING (INSTRUKSI BAYAR) -->
                <div class="space-y-4">
                    @if ($invoice->order)
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-950 flex items-start space-x-2.5">
                            <svg class="w-4 h-4 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="leading-relaxed">
                                Tagihan pembayaran untuk <strong>Pesanan #{{ $invoice->order->order_code }}</strong>. Setelah pembayaran lunas, sistem otomatis memproses pencarian mitra terapis.
                            </div>
                        </div>
                    @endif

                    <!-- Card Metode Pembayaran -->
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Metode Pembayaran</span>
                            <span class="font-bold text-slate-900">{{ $invoice->payment_url ? 'Xendit Gateway (QRIS, VA, E-Wallet)' : $invoice->payment_method }}</span>
                        </div>

                        @if ($invoice->payment_url)
                            <div class="p-4 bg-white rounded-2xl border border-blue-200/80 shadow-xs space-y-3 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 text-blue-700 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Tagihan Siap Dibayar di Xendit</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                        Selesaikan pembayaran di halaman aman Xendit. Tersedia QRIS, Transfer Virtual Account, atau E-Wallet.
                                    </p>
                                </div>

                                <a href="{{ $invoice->payment_url }}" target="_blank"
                                    class="w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2 cursor-pointer active:scale-95">
                                    <span>Buka Halaman Checkout Xendit &rarr;</span>
                                </a>
                            </div>
                        @elseif ($invoice->payment_method === 'QRIS')
                            <div class="py-3 text-center space-y-2">
                                <div class="w-44 h-44 mx-auto p-3 bg-white rounded-2xl border border-slate-200 shadow-xs flex items-center justify-center">
                                    <svg class="w-36 h-36 text-slate-900" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm8-2h2v2h-2v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-8 4h2v2h-2v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-8 4h2v2h-2v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z"/>
                                    </svg>
                                </div>
                                <span class="text-xs text-slate-500 block">Pindai QRIS dengan aplikasi dompet digital atau mobile banking Anda</span>
                            </div>
                        @else
                            <div class="p-3 bg-white rounded-2xl border border-slate-200 text-center">
                                <span class="text-xs text-slate-500 block font-medium">Nomor Virtual Account:</span>
                                <div class="text-base font-black font-mono text-slate-900 tracking-wider mt-0.5">
                                    8808{{ substr($invoice->invoice_number, -8) }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Tombol Cek Status Xendit -->
                    <form action="{{ route('pelanggan.wallet.check-status', $invoice->invoice_number) }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="w-full py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2 cursor-pointer active:scale-95">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Cek Status Pembayaran ke Xendit</span>
                        </button>
                    </form>

                    <!-- Tombol Simulasi Bayar Instan (Untuk Pengujian) -->
                    <form action="{{ route('pelanggan.wallet.simulate-pay', $invoice->invoice_number) }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="w-full py-3.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2 cursor-pointer active:scale-95">
                            <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Simulasi Bayar Sekarang (Uji Coba)</span>
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-pwa-layout>
