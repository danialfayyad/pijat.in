<x-pwa-layout title="Top Up Saldo - Pijat.In" :hideHeader="true">
    @php
        $wallet = $user->getOrCreateWallet();
        $activeBanks = \App\Models\Bank::bank()->active()->orderBy('sort_order')->orderBy('name')->get();
        $qrisChannel = \App\Models\Bank::where('code', 'QRIS')->active()->first();
        $gopayChannel = \App\Models\Bank::where('code', 'GOPAY')->active()->first();
        $danaChannel = \App\Models\Bank::where('code', 'DANA')->active()->first();
        $otherEwallets = \App\Models\Bank::ewallet()->whereNotIn('code', ['GOPAY', 'DANA'])->active()->orderBy('sort_order')->get();
        $hasEwallets = $qrisChannel || $gopayChannel || $danaChannel || $otherEwallets->isNotEmpty();

        $initialTab = $hasEwallets ? 'ewallet' : 'bank';
        $initialMethod = $qrisChannel ? 'QRIS' : ($activeBanks->isNotEmpty() ? strtoupper($activeBanks->first()->code) : 'XENDIT');
        $initialTitle = $qrisChannel ? 'Top Up via QRIS' : ($activeBanks->isNotEmpty() ? 'Top Up via VA ' . strtoupper($activeBanks->first()->code) : 'Top Up Saldo');
    @endphp

    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen bg-white relative"
         x-data="{
            step: 'select_method', // 'select_method' | 'input_amount'
            activeTab: '{{ $initialTab }}', // 'bank' | 'ewallet'
            paymentMethod: '{{ $initialMethod }}',
            methodTitle: '{{ $initialTitle }}',
            amountRaw: '100000',
            minAmount: 10000,
            maxAmount: 10000000,
            isLoading: false,
            loadingMethod: '',
            loadingTitle: 'Menyiapkan pembayaran...',
            isSubmitting: false,

            selectMethod(method, title) {
                if (this.isLoading) return;
                this.isLoading = true;
                this.loadingMethod = method;
                this.loadingTitle = 'Menyiapkan ' + (method === 'QRIS' ? 'QRIS' : title) + '...';
                
                // Animasi loading yang halus dan tenang (tidak terlalu cepat: ~850ms)
                setTimeout(() => {
                    this.paymentMethod = method;
                    this.methodTitle = title;
                    this.step = 'input_amount';
                    this.isLoading = false;
                    this.loadingMethod = '';
                }, 850);
            },

            setPreset(val) {
                this.amountRaw = val.toString();
            },

            pressKey(key) {
                if (key === 'backspace') {
                    if (this.amountRaw.length <= 1) {
                        this.amountRaw = '0';
                    } else {
                        this.amountRaw = this.amountRaw.slice(0, -1);
                    }
                    return;
                }

                if (key === 'clear') {
                    this.amountRaw = '0';
                    return;
                }

                if (this.amountRaw === '0') {
                    this.amountRaw = key.toString();
                } else {
                    if (this.amountRaw.length < 9) { // Max 99.999.999
                        this.amountRaw += key.toString();
                    }
                }
            },

            get numericAmount() {
                return parseInt(this.amountRaw, 10) || 0;
            },

            get formattedDisplay() {
                return this.numericAmount.toLocaleString('id-ID');
            },

            get isValid() {
                return this.numericAmount >= this.minAmount && this.numericAmount <= this.maxAmount;
            }
         }">

        <!-- ========================================================================= -->
        <!-- SCREEN 1: PILIH METODE TOP UP (TOP UP FROM) -->
        <!-- ========================================================================= -->
        <div x-show="step === 'select_method'" class="flex flex-col min-h-screen">
            <!-- Top App Bar -->
            <div class="sticky top-0 z-30 bg-white border-b border-slate-100 px-4 py-3 flex items-center justify-between">
                <a href="{{ route('pelanggan.wallet.index') }}" 
                   class="w-9 h-9 rounded-full flex items-center justify-center text-slate-800 hover:bg-slate-100 transition cursor-pointer"
                   aria-label="Kembali ke Dompet">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <h1 class="text-sm font-bold text-slate-900 tracking-tight">
                    Top Up From
                </h1>

                <div class="w-9 h-9 flex items-center justify-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
            </div>

            <!-- Top Animated Progress Bar (Halus & Elegan saat Loading) -->
            <div x-show="isLoading" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="h-1 w-full bg-indigo-50 overflow-hidden sticky top-[49px] z-40">
                <div class="h-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-indigo-700 animate-pulse w-full"></div>
            </div>

            @if (! $user->primaryAddress)
                <!-- Enforce DEC-02: Missing Primary Address Warning Card -->
                <div class="p-4">
                    <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 space-y-3 shadow-xs">
                        <div class="flex items-start space-x-3 text-amber-950">
                            <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Atur Alamat Utama Sekarang</span>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Segmented Tabs: Bank vs E-Wallet or QRIS -->
                <div class="grid grid-cols-2 border-b border-slate-200 bg-white">
                    <button type="button" 
                            @click="activeTab = 'bank'"
                            :class="activeTab === 'bank' ? 'text-slate-900 font-bold border-b-2 border-slate-900' : 'text-slate-400 font-semibold border-b-2 border-transparent'"
                            class="py-3 text-xs text-center transition cursor-pointer">
                        Bank
                    </button>
                    <button type="button" 
                            @click="activeTab = 'ewallet'"
                            :class="activeTab === 'ewallet' ? 'text-slate-900 font-bold border-b-2 border-slate-900' : 'text-slate-400 font-semibold border-b-2 border-transparent'"
                            class="py-3 text-xs text-center transition cursor-pointer">
                        E-Wallet or QRIS
                    </button>
                </div>

                <!-- Tab Content: E-Wallet or QRIS (Dinamis dari Master Saluran Superadmin) -->
                <div x-show="activeTab === 'ewallet'" class="divide-y divide-slate-100">
                    @if ($qrisChannel)
                        <!-- 1. QRIS (Rekomendasi Utama) -->
                        <div @click="selectMethod('QRIS', 'Top Up via QRIS')"
                             class="px-4 py-4 flex items-center justify-between transition cursor-pointer select-none"
                             :class="isLoading && loadingMethod === 'QRIS' ? 'bg-indigo-50/60' : 'hover:bg-slate-50 active:bg-slate-100'">
                            <div class="flex items-center space-x-3.5">
                                <!-- Logo QRIS -->
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 p-1.5 font-black text-[10px] tracking-wider shadow-2xs">
                                    QRIS
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">{{ $qrisChannel->name }}</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Max. Rp10M including the admin fee</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-5 h-5">
                                <template x-if="isLoading && loadingMethod === 'QRIS'">
                                    <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!isLoading || loadingMethod !== 'QRIS'">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    @endif

                    @if ($gopayChannel)
                        <!-- 2. GoPay -->
                        <div @click="selectMethod('GOPAY', 'Top Up via GoPay')"
                             class="px-4 py-4 flex items-center justify-between transition cursor-pointer select-none"
                             :class="isLoading && loadingMethod === 'GOPAY' ? 'bg-indigo-50/60' : 'hover:bg-slate-50 active:bg-slate-100'">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-10 h-10 rounded-full bg-[#00AED6] text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">{{ $gopayChannel->name }}</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Bayar instan via aplikasi Gojek / GoPay</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-5 h-5">
                                <template x-if="isLoading && loadingMethod === 'GOPAY'">
                                    <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!isLoading || loadingMethod !== 'GOPAY'">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    @endif

                    @if ($danaChannel)
                        <!-- 3. DANA -->
                        <div @click="selectMethod('DANA', 'Top Up via DANA')"
                             class="px-4 py-4 flex items-center justify-between transition cursor-pointer select-none"
                             :class="isLoading && loadingMethod === 'DANA' ? 'bg-indigo-50/60' : 'hover:bg-slate-50 active:bg-slate-100'">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-10 h-10 rounded-full bg-[#118EEA] text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">{{ $danaChannel->name }}</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Bayar instan via aplikasi DANA</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-5 h-5">
                                <template x-if="isLoading && loadingMethod === 'DANA'">
                                    <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!isLoading || loadingMethod !== 'DANA'">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    @endif

                    @foreach ($otherEwallets as $ew)
                        <div @click="selectMethod('{{ strtoupper($ew->code) }}', 'Top Up via {{ $ew->name }}')"
                             class="px-4 py-4 flex items-center justify-between transition cursor-pointer select-none"
                             :class="isLoading && loadingMethod === '{{ strtoupper($ew->code) }}' ? 'bg-indigo-50/60' : 'hover:bg-slate-50 active:bg-slate-100'">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-800 font-extrabold text-xs flex items-center justify-center shrink-0 border border-cyan-100">
                                    {{ strtoupper($ew->code) }}
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">{{ $ew->name }}</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $ew->notes ?: 'Saluran pembayaran e-wallet instan' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-5 h-5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    @endforeach

                    @if (! $hasEwallets)
                        <div class="p-8 text-center text-slate-400">
                            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs font-semibold text-slate-600">Saluran E-Wallet / QRIS Sedang Nonaktif</p>
                            <p class="text-[11px] text-slate-400 mt-1">Silakan gunakan pembayaran Bank Virtual Account pada tab Bank.</p>
                        </div>
                    @endif
                </div>

                <!-- Tab Content: Bank (Mengikuti Bank::active() Superadmin) -->
                <div x-show="activeTab === 'bank'" class="divide-y divide-slate-100" style="display: none;">
                    @forelse ($activeBanks as $b)
                        <div @click="selectMethod('{{ strtoupper($b->code) }}', 'Top Up via VA {{ strtoupper($b->code) }}')"
                             class="px-4 py-4 flex items-center justify-between transition cursor-pointer select-none"
                             :class="isLoading && loadingMethod === '{{ strtoupper($b->code) }}' ? 'bg-indigo-50/60' : 'hover:bg-slate-50 active:bg-slate-100'">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 font-extrabold text-xs flex items-center justify-center shrink-0 border border-emerald-100">
                                    {{ strtoupper($b->code) }}
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">{{ $b->name }}</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Virtual Account otomatis 24 jam</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-5 h-5">
                                <template x-if="isLoading && loadingMethod === '{{ strtoupper($b->code) }}'">
                                    <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!isLoading || loadingMethod !== '{{ strtoupper($b->code) }}'">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </template>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400">
                            Belum ada saluran bank aktif dari Superadmin.
                        </div>
                    @endforelse
                </div>

                <!-- Smooth Loading Overlay Modal (Jangan Terlalu Cepat) -->
                <div x-show="isLoading" 
                     x-transition:enter="transition ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/15 backdrop-blur-[2px]"
                     style="display: none;">
                    <div class="bg-white/95 rounded-3xl p-6 shadow-2xl flex flex-col items-center space-y-3.5 min-w-[200px] border border-slate-100 animate-fade-in">
                        <!-- Dual Ring Smooth Spinner -->
                        <div class="relative w-12 h-12 flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full border-3 border-indigo-100"></div>
                            <div class="absolute inset-0 rounded-full border-3 border-indigo-600 border-t-transparent animate-spin" style="animation-duration: 0.9s;"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></div>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-bold text-slate-800" x-text="loadingTitle">Menyiapkan pembayaran...</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Mohon tunggu sebentar</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- SCREEN 2: INPUT NOMINAL DENGAN NUMPAD (TOP UP VIA QRIS) — SESUAI GAMBAR 2 -->
        <!-- ========================================================================= -->
        <div x-show="step === 'input_amount'" class="flex flex-col min-h-screen justify-between" style="display: none;">
            
            <!-- Top App Bar -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-b border-slate-100">
                <button type="button" 
                        @click="step = 'select_method'"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-800 hover:bg-slate-100 transition cursor-pointer"
                        aria-label="Kembali ke pilihan metode">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <h1 class="text-sm font-bold text-slate-900 tracking-tight" x-text="methodTitle">
                    Top Up via QRIS
                </h1>

                <div class="w-9 h-9"></div> <!-- Penyeimbang -->
            </div>

            <!-- Area Tengah: Nominal Besar & Chip Presets -->
            <div class="flex-1 flex flex-col justify-center items-center px-4 py-6">
                <!-- Tampilan Nominal Besar -->
                <div class="flex items-baseline space-x-1.5 my-auto">
                    <span class="text-base font-bold text-slate-900">Rp</span>
                    <span class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight font-sans" x-text="formattedDisplay">
                        100,000
                    </span>
                </div>

                <p x-show="!isValid && numericAmount > 0" class="text-xs text-rose-600 font-semibold mb-3">
                    Minimal pengisian Rp 10.000 & Maksimal Rp 10.000.000
                </p>

                <!-- Chip Pilihan Cepat (Presets) Sesuai Gambar 2 -->
                <div class="w-full flex items-center justify-center gap-2 mb-4">
                    <button type="button" @click="setPreset(100000)"
                            class="flex-1 max-w-[100px] py-2 px-1 text-center rounded-full border border-slate-300 text-xs font-semibold text-slate-800 hover:border-slate-800 transition cursor-pointer"
                            :class="numericAmount === 100000 ? 'border-slate-900 bg-slate-900 text-white' : ''">
                        100,000
                    </button>
                    <button type="button" @click="setPreset(200000)"
                            class="flex-1 max-w-[100px] py-2 px-1 text-center rounded-full border border-slate-300 text-xs font-semibold text-slate-800 hover:border-slate-800 transition cursor-pointer"
                            :class="numericAmount === 200000 ? 'border-slate-900 bg-slate-900 text-white' : ''">
                        200,000
                    </button>
                    <button type="button" @click="setPreset(500000)"
                            class="flex-1 max-w-[100px] py-2 px-1 text-center rounded-full border border-slate-300 text-xs font-semibold text-slate-800 hover:border-slate-800 transition cursor-pointer"
                            :class="numericAmount === 500000 ? 'border-slate-900 bg-slate-900 text-white' : ''">
                        500,000
                    </button>
                    <button type="button" @click="setPreset(1000000)"
                            class="flex-1 max-w-[100px] py-2 px-1 text-center rounded-full border border-slate-300 text-xs font-semibold text-slate-800 hover:border-slate-800 transition cursor-pointer"
                            :class="numericAmount === 1000000 ? 'border-slate-900 bg-slate-900 text-white' : ''">
                        1,000,000
                    </button>
                </div>

                <!-- Form Submit Button: Continue -->
                <form action="{{ route('pelanggan.wallet.store') }}" method="POST" class="w-full" @submit="isSubmitting = true">
                    @csrf
                    <input type="hidden" name="amount" :value="numericAmount">
                    <input type="hidden" name="payment_method" :value="paymentMethod">

                    <button type="submit" 
                            :disabled="!isValid || isSubmitting"
                            class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm rounded-2xl shadow-sm transition active:scale-[0.98] cursor-pointer flex items-center justify-center space-x-2">
                        <template x-if="!isSubmitting">
                            <span>Continue</span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center space-x-2">
                                <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Menyiapkan Tagihan...</span>
                            </span>
                        </template>
                    </button>
                </form>
            </div>

            <!-- Virtual Numeric Keypad (Sesuai Gambar 2) -->
            <div class="bg-white border-t border-slate-100 px-6 py-4 pb-12 select-none">
                <div class="grid grid-cols-3 gap-y-3 gap-x-6 max-w-xs mx-auto text-center">
                    <button type="button" @click="pressKey('1')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">1</button>
                    <button type="button" @click="pressKey('2')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">2</button>
                    <button type="button" @click="pressKey('3')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">3</button>

                    <button type="button" @click="pressKey('4')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">4</button>
                    <button type="button" @click="pressKey('5')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">5</button>
                    <button type="button" @click="pressKey('6')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">6</button>

                    <button type="button" @click="pressKey('7')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">7</button>
                    <button type="button" @click="pressKey('8')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">8</button>
                    <button type="button" @click="pressKey('9')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">9</button>

                    <button type="button" @click="pressKey('clear')" class="py-3 text-sm font-bold text-slate-400 active:bg-slate-100 rounded-xl transition cursor-pointer uppercase">C</button>
                    <button type="button" @click="pressKey('0')" class="py-3 text-2xl font-semibold text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">0</button>
                    <button type="button" @click="pressKey('backspace')" class="py-3 flex items-center justify-center text-slate-900 active:bg-slate-100 rounded-xl transition cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>

    </div>
</x-pwa-layout>
