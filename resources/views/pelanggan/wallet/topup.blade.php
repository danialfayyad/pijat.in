<x-pwa-layout title="Isi Saldo - Pijat.In" :hideHeader="true">
    @php
        $wallet = $user->getOrCreateWallet();
    @endphp

    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen"
         x-data="{
            amount: {{ old('amount', 50000) }},
            presets: [50000, 100000, 200000, 500000],
            minAmount: 10000,
            maxAmount: 10000000,
            get isValid() {
                let num = parseFloat(this.amount) || 0;
                return num >= this.minAmount && num <= this.maxAmount;
            }
         }">

        <!-- Fixed Top Bar Navigasi Mengambang -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ route('pelanggan.wallet.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Kembali ke Dompet"
               aria-label="Kembali ke Dompet">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                Isi Saldo Dompet
            </span>

            <a href="{{ route('pelanggan.help.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Bantuan Top-up"
               aria-label="Pusat Bantuan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </div>

        <!-- 1. FIXED GREEN HERO CONTAINER (KONSISTEN DENGAN DOMPET LIVIN') -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white pt-16 pb-10 px-5 overflow-hidden">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            <!-- Info Saldo di Tengah (Clean & Minimalist, Tanpa Kartu Rekening) -->
            <div class="relative z-10 flex flex-col items-center text-center pt-2 sm:pt-4">

                <!-- Label Saldo Dompet Saat Ini -->
                <div>
                    <h2 class="text-xs font-black text-emerald-100 uppercase tracking-widest drop-shadow-xs">
                        Saldo Dompet Saat Ini
                    </h2>
                </div>

                <!-- Nominal Saldo Besar -->
                <div class="mt-2">
                    <div class="text-3xl sm:text-4xl font-black text-white tracking-tight font-mono drop-shadow-sm">
                        {{ $wallet->formatted_balance }}
                    </div>
                </div>

                <!-- Info Minimal Top-up Badge -->
                <div class="mt-2.5 inline-flex items-center space-x-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] text-emerald-100">
                    <span>Minimal Pengisian:</span>
                    <strong class="text-white font-mono">Rp 10.000</strong>
                </div>
            </div>
        </div>

        <!-- Spacer Tinggi Kartu Hijau -->
        <div class="h-[230px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET (FORMULIR ISI SALDO) -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-3 pb-24 shadow-2xl flex-1 space-y-4">
            <!-- Sheet Drag Handle Bar -->
            <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto"></div>

            @if (! $user->primaryAddress)
                <!-- Enforce DEC-02: Missing Primary Address Warning Card -->
                <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 space-y-3 shadow-xs">
                    <div class="flex items-start space-x-3 text-amber-950">
                        <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h4 class="font-bold text-xs text-amber-950">Wajib Mengatur Alamat Utama</h4>
                            <p class="text-[11px] text-amber-900 leading-relaxed mt-1">
                                Sesuai kebijakan operasional wilayah, Anda wajib menyimpan minimal 1 <strong>Alamat Utama</strong> sebelum dapat melakukan pengisian saldo agar dana teratribusi pada wilayah layanan Anda.
                            </p>
                        </div>
                    </div>

                    <div class="pt-1">
                        <a href="{{ route('pelanggan.addresses.create') }}" 
                            class="w-full min-h-[44px] py-2.5 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-bold text-xs text-center shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Atur Alamat Utama Sekarang</span>
                        </a>
                    </div>
                </div>
            @else
                <!-- Topup Form -->
                <form action="{{ route('pelanggan.wallet.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Section: Nominal Selection Card -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 space-y-3.5 shadow-xs">
                        <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2.5">
                            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <label class="text-xs font-black text-slate-900 uppercase tracking-wider block">Pilih Nominal Isi Saldo</label>
                        </div>

                        <!-- Preset Chips Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="val in presets" :key="val">
                                <button type="button" @click="amount = val"
                                        class="min-h-[46px] p-2.5 rounded-xl border text-xs font-bold text-center transition cursor-pointer flex items-center justify-center active:scale-95"
                                        :class="amount === val ? 'bg-[#064e3b] text-white border-[#064e3b] shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-emerald-300'">
                                    <span class="font-mono text-sm" x-text="'Rp ' + val.toLocaleString('id-ID')"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Custom Amount Input -->
                        <div class="pt-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Atau Masukkan Nominal Lain:
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm font-mono">Rp</span>
                                <input type="number" name="amount" x-model="amount" min="10000" max="10000000" step="1000" required
                                    class="w-full pl-11 pr-3.5 py-3 rounded-xl border border-slate-200 bg-white text-base font-black text-slate-900 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs">
                            </div>
                            @error('amount') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Payment Gateway Info Card -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 space-y-3 shadow-xs">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Metode Pembayaran via Xendit</span>
                                <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">
                                    Bebas pilih metode langsung di Xendit: <strong>QRIS</strong> (BCA, Livin', GoPay, OVO, Dana, ShopeePay), <strong>Virtual Account Bank</strong>, & <strong>E-Wallet</strong>.
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between text-[11px] text-slate-600">
                            <span>Verifikasi Pembayaran:</span>
                            <span class="font-bold text-emerald-700 flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Otomatis Real-time
                            </span>
                        </div>

                        <input type="hidden" name="payment_method" value="XENDIT">
                    </div>

                    <!-- Expiration Notice -->
                    <p class="text-[11px] text-slate-400 text-center">
                        Batas waktu pembayaran adalah <strong>15 menit</strong> sejak tagihan dibuat.
                    </p>

                    <!-- Submit Button -->
                    <div class="pt-1">
                        <button type="submit" 
                            :disabled="!isValid"
                            class="w-full py-3.5 px-4 bg-[#064e3b] hover:bg-emerald-900 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-lg transition flex items-center justify-center space-x-2 active:scale-95 cursor-pointer">
                            <span>Lanjut Bayar via Xendit</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-pwa-layout>
