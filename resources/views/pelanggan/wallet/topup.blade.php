<x-pwa-layout title="Isi Saldo - Pijat.In" headerTitle="Isi Saldo Dompet" :backUrl="route('pelanggan.wallet.index')">
    <div class="py-2 space-y-4">

        <!-- Top Header -->
        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Pengisian Saldo Dompet</h2>
            <p class="text-xs text-slate-500">Pilih nominal isi saldo dan metode pembayaran yang diinginkan.</p>
        </div>

        @if (! $user->primaryAddress)
            <!-- Enforce DEC-02: Missing Primary Address Warning Card -->
            <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 space-y-3">
                <div class="flex items-start space-x-3 text-amber-950">
                    <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h4 class="font-bold text-xs text-amber-950">Wajib Mengatur Alamat Utama</h4>
                        <p class="text-xs text-amber-900 leading-relaxed mt-1">
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
            <!-- Primary Address Region Verified Badge -->
            <div class="bg-emerald-50/90 border border-emerald-200 rounded-2xl p-3.5 flex items-center justify-between text-xs text-emerald-900">
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] text-emerald-800 font-semibold block">Wilayah Domisili Teratribusi</span>
                        <span class="font-extrabold text-xs text-slate-900">{{ $user->region?->name ?? $user->primaryAddress->region?->name }}</span>
                    </div>
                </div>
                <a href="{{ route('pelanggan.addresses.index') }}" class="min-h-[44px] inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                    Ganti
                </a>
            </div>

            <!-- Topup Form -->
            <form action="{{ route('pelanggan.wallet.store') }}" method="POST" class="space-y-4"
                  x-data="{
                    amount: {{ old('amount', 50000) }},
                    presets: [50000, 100000, 200000, 500000]
                  }">
                @csrf

                <!-- Nominal Selection Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-0.5">
                            Pilih Nominal Isi Saldo <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Minimal pengisian saldo Rp 10.000</p>
                    </div>

                    <!-- Preset Chips -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <template x-for="val in presets" :key="val">
                            <button type="button" @click="amount = val"
                                    class="min-h-[44px] p-3 rounded-xl border text-xs font-bold text-center transition cursor-pointer flex items-center justify-center"
                                    :class="amount === val ? 'bg-emerald-700 text-white border-emerald-700 shadow-xs' : 'bg-slate-50/80 text-slate-800 border-slate-200 hover:border-emerald-300'">
                                <span class="font-mono" x-text="'Rp ' + val.toLocaleString('id-ID')"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Custom Amount Input -->
                    <div class="pt-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Atau Masukkan Nominal Lain:
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-3 text-xs font-bold text-slate-500 font-mono">Rp</span>
                            <input type="number" name="amount" x-model="amount" min="10000" max="10000000" step="1000" required
                                class="w-full min-h-[44px] pl-10 pr-4 py-2.5 text-xs font-bold font-mono rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600">
                        </div>
                        @error('amount') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Payment Gateway Info Card -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
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
                <p class="text-xs text-slate-500 text-center">
                    Batas waktu pembayaran adalah <strong>15 menit</strong> sejak tagihan dibuat.
                </p>

                <!-- Submit Button -->
                <div class="pt-1">
                    <button type="submit" 
                        class="w-full min-h-[48px] py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Lanjut Bayar via Xendit</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        @endif

    </div>
</x-pwa-layout>

