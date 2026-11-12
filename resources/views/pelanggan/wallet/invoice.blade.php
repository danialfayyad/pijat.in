
<x-pwa-layout title="Tagihan Pembayaran - Pijat.In" :hideHeader="true">
    @php
        $activeBanks = \App\Models\Bank::active()->orderBy('sort_order')->orderBy('name')->get();
        $firstBank = $activeBanks->first();
        $firstBankCode = $firstBank ? strtolower($firstBank->code) : 'bca';
        $digits = preg_replace('/[^0-9]/', '', $invoice->invoice_number) ?: '88991234';
        
        // QRIS Fee calculation (0.7% matching screenshot)
        $qrisFee = (int) round((float) $invoice->amount * 0.007);
        $toBeReceived = max(0, (float) $invoice->amount - $qrisFee);
        
        $initialTab = in_array(strtolower($invoice->payment_method), ['bca', 'mandiri', 'bri', 'bni', 'cimb', 'permata']) ? 'va' : 'qris';
    @endphp

    <div class="-mx-4 -mt-3 -mb-8 min-h-screen bg-[#F8F9FA] flex flex-col justify-between"
         x-data="invoicePayment()">

        <!-- 1. TOP NAVIGATION HEADER (SESUAI GAMBAR 3) -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100/90 px-4 py-3 flex items-center justify-between">
            <a href="{{ $invoice->order ? route('pelanggan.orders.show', $invoice->order) : route('pelanggan.wallet.index') }}" 
               class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-800 hover:bg-slate-100 active:scale-95 transition cursor-pointer"
               title="Kembali"
               aria-label="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="text-center">
                <h1 class="text-sm font-bold text-slate-900 leading-tight">
                    @if ($invoice->isPaid())
                        Status Pembayaran
                    @elseif ($invoice->isExpired())
                        Tagihan Kedaluwarsa
                    @else
                        <span x-text="paymentTab === 'qris' ? 'Top Up via QRIS' : 'Top Up via Virtual Account'">Top Up via QRIS</span>
                    @endif
                </h1>
                @if ($invoice->isPending())
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                        Complete transfer within <span class="font-mono font-semibold text-slate-700" x-text="timeLeft">23:59:59</span>
                    </p>
                @endif
            </div>

            <div class="w-9"></div>
        </header>

        <!-- 2. KONTEN UTAMA -->
        <main class="flex-1 px-4 py-4 max-w-md mx-auto w-full space-y-4">

            <!-- Toast Notifikasi Download QRIS -->
            <div x-show="downloadSuccess" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-2.5 text-xs text-emerald-800 font-semibold shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Kode QRIS berhasil disimpan ke galeri perangkat!</span>
            </div>


            <!-- Top Bar Navigasi -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ $invoice->order ? route('pelanggan.orders.show', $invoice->order) : route('pelanggan.wallet.index') }}" 
                   class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer shadow-sm"
                   title="Kembali"
                   aria-label="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                    @if ($invoice->isPaid())
                        Status Pembayaran
                    @elseif ($invoice->isExpired())
                        Tagihan Kedaluwarsa
                    @else
                        Tagihan Pembayaran
                    @endif
                </span>

                <a href="{{ route('pelanggan.help.index') }}" 
                   class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer shadow-sm"
                   title="Pusat Bantuan"
                   aria-label="Pusat Bantuan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </div>

            <div class="pt-5">

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
        </div>

        <!-- 2. FIXED WHITE CONTENT AREA (TIDAK SLIDE OVER HERO) -->
        <div class="relative z-10 -mt-5 bg-white rounded-t-3xl px-5 pt-5 pb-6 flex-1 min-h-0 overflow-y-auto flex flex-col justify-between space-y-4">

            <!-- Status Alert Messages -->

            <!-- Flash Status Messages -->
            @if (session('status'))
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($invoice->isPaid())
                <!-- STATE: LUNAS / BERHASIL -->
                <div class="bg-white rounded-[28px] border border-slate-200/80 p-6 text-center space-y-4 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold">
                            Pembayaran Berhasil
                        </span>
                        <h2 class="text-2xl font-black text-slate-900 font-mono mt-2">
                            {{ $invoice->formatted_amount }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            @if ($invoice->order)
                                Pesanan #{{ $invoice->order->order_code }} telah lunas dan sedang dicarikan terapis.
                            @else
                                Saldo berhasil ditambahkan ke dompet akun Anda.
                            @endif
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 text-xs space-y-2 text-left border border-slate-100">
                        <div class="flex justify-between text-slate-500">
                            <span>Nomor Tagihan</span>
                            <span class="font-mono font-bold text-slate-800">{{ $invoice->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Waktu Pembayaran</span>
                            <span class="font-medium text-slate-800">{{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d M Y, H:i') : now()->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Metode Pembayaran</span>
                            <span class="font-bold text-slate-800">{{ strtoupper($invoice->payment_method) }}</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        @if ($invoice->order)
                            <a href="{{ route('pelanggan.orders.show', $invoice->order) }}" 
                               class="w-full py-3.5 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center block">
                                Lihat Pesanan Saya
                            </a>
                        @else
                            <a href="{{ route('pelanggan.wallet.index') }}" 
                               class="w-full py-3.5 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center block">
                                Kembali ke Dompet
                            </a>
                        @endif
                    </div>
                </div>

            @elseif ($invoice->isExpired())
                <!-- STATE: KEDALUWARSA -->
                <div class="bg-white rounded-[28px] border border-slate-200/80 p-6 text-center space-y-4 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <div class="w-16 h-16 mx-auto rounded-full bg-rose-50 text-rose-500 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold">
                            Tagihan Kedaluwarsa
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 font-mono mt-2">
                            {{ $invoice->formatted_amount }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Batas waktu pembayaran telah habis. Silakan buat top up baru.
                        </p>
                    </div>

                    <a href="{{ route('pelanggan.wallet.topup') }}" 
                       class="w-full py-3.5 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center block">
                        Top Up Ulang
                    </a>
                </div>

            @else
                <!-- STATE: MENUNGGU PEMBAYARAN -->

                @if ($invoice->order)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-950 flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="leading-relaxed">
                            Pembayaran untuk pesanan <strong class="font-mono text-emerald-900">#{{ $invoice->order->order_code }}</strong>. Setelah pembayaran terverifikasi, sistem otomatis mencarikan terapis.
                        </div>
                    </div>
                @endif

                <!-- TAB SWITCHER (Jika Invoice Bank VA) -->
                @if (in_array(strtolower($invoice->payment_method), ['bca', 'mandiri', 'bri', 'bni', 'cimb', 'permata']))
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/70 rounded-2xl">
                        <button type="button" @click="paymentTab = 'qris'"
                                class="py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer"
                                :class="paymentTab === 'qris' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                            <span>QRIS</span>
                        </button>
                        <button type="button" @click="paymentTab = 'va'"
                                class="py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer"
                                :class="paymentTab === 'va' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                            <span>Virtual Account</span>
                        </button>
                    </div>
                @endif

                <!-- VIEW 1: QRIS INSTAN (100% SESUAI GAMBAR 3) -->
                <div x-show="paymentTab === 'qris'" class="space-y-4">
                    
                    <!-- KARTU UTAMA QRIS -->
                    <div class="bg-white rounded-[28px] border border-slate-200/90 p-6 space-y-4 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)]">
                        
                        <!-- Realistic Dense QR Code -->
                        <div class="flex justify-center pt-1 pb-1">
                            <div class="w-56 h-56 p-1 bg-white rounded-2xl flex items-center justify-center">
                                {!! \App\Support\QrisCode::svg($invoice->invoice_number, 220) !!}
                            </div>
                        </div>

                        <!-- Tombol Save QRIS Code -->
                        <div class="flex justify-center">
                            <button type="button" 
                                    @click="downloadQris()"
                                    :disabled="downloading"
                                    class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-full border border-[#6366F1] text-[#6366F1] hover:bg-indigo-50/70 active:scale-95 font-semibold text-xs tracking-wide transition shadow-2xs cursor-pointer disabled:opacity-50">
                                <template x-if="!downloading">
                                    <span class="flex items-center space-x-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>Save QRIS Code</span>
                                    </span>
                                </template>
                                <template x-if="downloading">
                                    <span class="flex items-center space-x-1.5">
                                        <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span>Menyimpan...</span>
                                    </span>
                                </template>
                            </button>
                        </div>

                        <!-- Rincian Biaya Bersih Sesuai Gambar 3 -->
                        <div class="pt-4 border-t border-slate-100 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="font-normal">Amount to Be Paid</span>
                                <span class="font-bold text-slate-900 tracking-tight">{{ $invoice->formatted_amount }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span class="font-normal">QRIS Fee (0.7%)</span>
                                <span class="font-medium text-slate-600">- Rp{{ number_format($qrisFee, 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200/90 flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-[13px]">To Be Received</span>
                                <span class="font-bold text-slate-900 text-[13px] tracking-tight">Rp{{ number_format($toBeReceived, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Top-Up Instructions Sesuai Gambar 3 -->
                    <div class="pt-2 px-1">
                        <h2 class="text-xs font-bold text-slate-900 mb-3.5 tracking-tight">
                            Detailed Top-Up Instructions
                        </h2>
                        <div class="relative pl-1 space-y-4">
                            <!-- Vertical timeline line -->
                            <div class="absolute left-[11px] top-3 bottom-3 w-[1.5px] bg-slate-200/80 -z-0"></div>

                            <!-- Step 1 -->
                            <div class="relative z-10 flex items-start space-x-3.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 border border-white text-slate-600 font-semibold text-[11px] flex items-center justify-center shrink-0 shadow-2xs">
                                    1
                                </div>
                                <p class="text-xs text-slate-600 leading-snug pt-0.5">
                                    Save the QRIS code shown above.
                                </p>
                            </div>

                            <!-- Step 2 -->
                            <div class="relative z-10 flex items-start space-x-3.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 border border-white text-slate-600 font-semibold text-[11px] flex items-center justify-center shrink-0 shadow-2xs">
                                    2
                                </div>
                                <p class="text-xs text-slate-600 leading-snug pt-0.5">
                                    Open your mobile banking app.
                                </p>
                            </div>

                            <!-- Step 3 -->
                            <div class="relative z-10 flex items-start space-x-3.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 border border-white text-slate-600 font-semibold text-[11px] flex items-center justify-center shrink-0 shadow-2xs">
                                    3
                                </div>
                                <p class="text-xs text-slate-600 leading-snug pt-0.5">
                                    Scan or upload the QRIS code on your mobile banking app.
                                </p>
                            </div>

                            <!-- Step 4 -->
                            <div class="relative z-10 flex items-start space-x-3.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 border border-white text-slate-600 font-semibold text-[11px] flex items-center justify-center shrink-0 shadow-2xs">
                                    4
                                </div>
                                <p class="text-xs text-slate-600 leading-snug pt-0.5">
                                    Enter your PIN to confirm the payment. Your top-up is done!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW 2: VIRTUAL ACCOUNT BANK -->
                <div x-show="paymentTab === 'va'" class="space-y-4" style="display: none;">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeBanks as $b)
                            <button type="button" @click="selectedBank = '{{ strtolower($b->code) }}'"
                                    class="flex-1 min-w-[70px] py-2.5 px-2 rounded-2xl text-center border font-bold text-xs transition cursor-pointer"
                                    :class="selectedBank === '{{ strtolower($b->code) }}' ? 'bg-indigo-50 border-indigo-600 text-indigo-900 ring-2 ring-indigo-500/20' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                                <span>{{ strtoupper($b->code) }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="p-5 bg-white rounded-[28px] border border-slate-200/90 space-y-4 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.04)]">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800" x-text="bankNames[selectedBank]">{{ $firstBank?->name ?? 'Virtual Account' }}</span>
                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">Verifikasi Otomatis</span>
                        </div>

                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">Nomor Virtual Account:</span>
                            <div class="mt-1 flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-base font-black font-mono text-slate-900 tracking-wider" x-text="vaNumbers[selectedBank]">
                                    {{ ($firstBank?->va_prefix ?? '8808') . substr($digits, -8) }}
                                </span>
                                <button type="button" 
                                        @click="copyText(vaNumbers[selectedBank], 'va')"
                                        class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                                    <template x-if="!copiedVa">
                                        <span class="flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Salin</span>
                                        </span>
                                    </template>
                                    <template x-if="copiedVa">
                                        <span class="text-emerald-700 font-extrabold flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Tersalin!</span>
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">Total yang Harus Ditransfer:</span>
                            <div class="mt-1 flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-base font-black font-mono text-slate-900">
                                    {{ $invoice->formatted_amount }}
                                </span>
                                <button type="button" 
                                        @click="copyText('{{ (int)$invoice->amount }}', 'amount')"
                                        class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200 transition cursor-pointer">
                                    <template x-if="!copiedAmount">
                                        <span>Salin Nominal</span>
                                    </template>
                                    <template x-if="copiedAmount">
                                        <span class="text-emerald-700 font-bold">Tersalin!</span>
                                    </template>
                                </button>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500">
                            Atas Nama: <strong class="text-slate-800">Pijat.In - {{ $invoice->user->name }}</strong>
                        </p>
                    </div>
                </div>

                <!-- 3. TOMBOL CANCEL TOP UP (SESUAI GAMBAR 3) -->
                <div class="pt-2 px-1">
                    <a href="{{ $invoice->order ? route('pelanggan.orders.show', $invoice->order) : route('pelanggan.wallet.index') }}"
                       class="w-full py-3.5 rounded-full border border-[#6366F1] text-[#6366F1] hover:bg-indigo-50/70 font-semibold text-xs text-center transition active:scale-[0.99] block cursor-pointer">
                        Cancel Top Up
                    </a>
                </div>

                <!-- Realtime Polling Indicator -->
                <div class="py-1 flex items-center justify-center space-x-2 text-[11px] text-slate-400 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Menunggu verifikasi pembayaran otomatis...</span>
                </div>

                <!-- 4. PENGUJIAN / DEMO SANDBOX (DISCREET ACCORDION) -->
                <div class="pt-2">
                    <button type="button" 
                            @click="openDev = !openDev" 
                            class="w-full py-2 text-center text-[11px] text-slate-400 hover:text-slate-600 transition flex items-center justify-center space-x-1 cursor-pointer">
                        <span>🛠️ Mode Uji Coba (Sandbox Simulator)</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openDev ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="openDev" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-2 p-3 bg-white border border-dashed border-slate-200 rounded-2xl space-y-2">
                        <form action="{{ route('pelanggan.wallet.check-status', $invoice->invoice_number) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-2xs transition cursor-pointer">
                                🔄 Cek Status Gateway Manual
                            </button>
                        </form>
                        <form action="{{ route('pelanggan.wallet.simulate-pay', $invoice->invoice_number) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-2xs transition cursor-pointer">
                                ⚡ Simulasi Bayar Sekarang (Instan Lunas)
                            </button>
                        </form>
                    </div>
                </div>

            @endif

        </main>
    </div>

    <script>
        function invoicePayment() {
            return {
                paymentTab: '{{ $initialTab }}',
                selectedBank: '{{ in_array(strtolower($invoice->payment_method), ['bca', 'mandiri', 'bri', 'bni']) ? strtolower($invoice->payment_method) : $firstBankCode }}',
                copiedVa: false,
                copiedAmount: false,
                downloading: false,
                downloadSuccess: false,
                openDev: false,
                vaNumbers: {
                    @foreach ($activeBanks as $b)
                        '{{ strtolower($b->code) }}': '{{ $b->va_prefix . substr($digits, -8) }}',
                    @endforeach
                },
                bankNames: {
                    @foreach ($activeBanks as $b)
                        '{{ strtolower($b->code) }}': '{{ addslashes($b->name) }}',
                    @endforeach
                },
                copyText(text, type) {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text);
                    }
                    if (type === 'va') {
                        this.copiedVa = true;
                        setTimeout(() => this.copiedVa = false, 2500);
                    } else if (type === 'amount') {
                        this.copiedAmount = true;
                        setTimeout(() => this.copiedAmount = false, 2500);
                    }
                },
                downloadQris() {
                    this.downloading = true;
                    const svg = document.getElementById('qris-svg');
                    if (!svg) {
                        this.downloading = false;
                        return;
                    }

                    try {
                        const serializer = new XMLSerializer();
                        let svgStr = serializer.serializeToString(svg);

                        const canvas = document.createElement('canvas');
                        const scale = 2;
                        canvas.width = 360 * scale;
                        canvas.height = 440 * scale;
                        const ctx = canvas.getContext('2d');
                        ctx.scale(scale, scale);

                        // Background card
                        ctx.fillStyle = '#FFFFFF';
                        if (ctx.roundRect) {
                            ctx.roundRect(0, 0, 360, 440, 24);
                        } else {
                            ctx.rect(0, 0, 360, 440);
                        }
                        ctx.fill();

                        // Header
                        ctx.fillStyle = '#0f172a';
                        ctx.font = 'bold 15px system-ui, -apple-system, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText('QRIS - Pijat.In', 180, 36);

                        ctx.fillStyle = '#64748b';
                        ctx.font = '11px monospace, system-ui';
                        ctx.fillText('{{ $invoice->invoice_number }}', 180, 54);

                        const img = new Image();
                        img.onload = () => {
                            ctx.drawImage(img, 45, 70, 270, 270);

                            // Footer
                            ctx.fillStyle = '#64748b';
                            ctx.font = '11px system-ui, sans-serif';
                            ctx.fillText('Total Pembayaran', 180, 375);

                            ctx.fillStyle = '#0f172a';
                            ctx.font = 'bold 20px monospace, system-ui';
                            ctx.fillText('{{ $invoice->formatted_amount }}', 180, 402);

                            const a = document.createElement('a');
                            a.download = 'QRIS-{{ $invoice->invoice_number }}.png';
                            a.href = canvas.toDataURL('image/png');
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);

                            this.downloading = false;
                            this.downloadSuccess = true;
                            setTimeout(() => this.downloadSuccess = false, 3500);
                        };
                        img.onerror = () => {
                            this.downloading = false;
                        };
                        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgStr);
                    } catch (e) {
                        console.error('Error generating QRIS download:', e);
                        this.downloading = false;
                    }
                },
                expiresAt: new Date('{{ $invoice->expires_at->toIso8601String() }}').getTime(),
                timeLeft: '',
                isExpired: {{ $invoice->isExpired() ? 'true' : 'false' }},
                isPaid: {{ $invoice->isPaid() ? 'true' : 'false' }},
                isCheckingStatus: false,
                updateTimer() {
                    const now = new Date().getTime();
                    const diff = this.expiresAt - now;
                    if (diff <= 0) {
                        this.timeLeft = '00:00:00';
                        this.isExpired = true;
                        return;
                    }
                    const h = Math.floor(diff / (1000 * 60 * 60));
                    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const s = Math.floor((diff % (1000 * 60)) / 1000);
                    this.timeLeft = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                },
                checkStatusAuto() {
                    if (this.isExpired || this.isPaid) return;
                    fetch('{{ route('pelanggan.wallet.check-status', $invoice->invoice_number) }}', {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.paid || data.status === 'PAID') {
                            this.isPaid = true;
                            window.location.reload();
                        }
                    })
                    .catch(() => {});
                },
                init() {
                    this.updateTimer();
                    setInterval(() => this.updateTimer(), 1000);
                    @if ($invoice->isPending())
                        setInterval(() => this.checkStatusAuto(), 5000);
                    @endif
                }
            };
        }
    </script>
</x-pwa-layout>
