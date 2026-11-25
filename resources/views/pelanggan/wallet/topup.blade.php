<x-pwa-layout title="Isi Saldo - Pijat.In" :hideHeader="true" :fullBleed="true">
    @php
        $wallet = $user->getOrCreateWallet();
        $activeBanks = \App\Models\Bank::bank()->active()->orderBy('sort_order')->orderBy('name')->get();
        $qrisChannel = \App\Models\Bank::where('code', 'QRIS')->active()->first();
        $gopayChannel = \App\Models\Bank::where('code', 'GOPAY')->active()->first();
        $danaChannel = \App\Models\Bank::where('code', 'DANA')->active()->first();
        $otherEwallets = \App\Models\Bank::ewallet()->whereNotIn('code', ['GOPAY', 'DANA', 'QRIS'])->active()->orderBy('sort_order')->get();
        $hasEwallets = $qrisChannel || $gopayChannel || $danaChannel || $otherEwallets->isNotEmpty();

        $initialTab = $hasEwallets ? 'ewallet' : 'bank';
        $initialMethod = $qrisChannel ? 'QRIS' : ($activeBanks->isNotEmpty() ? strtoupper($activeBanks->first()->code) : 'XENDIT');
        $initialTitle = $qrisChannel ? 'Top Up via QRIS' : ($activeBanks->isNotEmpty() ? 'Top Up via VA ' . strtoupper($activeBanks->first()->code) : 'Top Up Saldo');
    @endphp

    <div class="flex-1 flex flex-col h-full min-h-0 overflow-hidden bg-slate-50 text-slate-800"
         x-data="{
            step: 'select_method', // 'select_method' | 'input_amount'
            activeTab: '{{ $initialTab }}', // 'bank' | 'ewallet'
            paymentMethod: '{{ $initialMethod }}',
            methodTitle: '{{ $initialTitle }}',
            amountRaw: '{{ request()->input('amount', '100000') }}',
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
                
                setTimeout(() => {
                    this.paymentMethod = method;
                    this.methodTitle = title;
                    this.step = 'input_amount';
                    this.isLoading = false;
                    this.loadingMethod = '';
                }, 400);
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
        <!-- SCREEN 1: PILIH METODE PEMBAYARAN -->
        <!-- ========================================================================= -->
        <div x-show="step === 'select_method'" class="flex-1 flex flex-col h-full min-h-0 bg-slate-50">
            <!-- Top App Bar -->
            <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/70 px-4 py-2.5 min-h-[56px] flex items-center justify-between shrink-0 shadow-2xs">
                <a href="{{ route('pelanggan.wallet.index') }}" 
                   class="w-8 h-8 -ml-1 rounded-full flex items-center justify-center text-slate-700 hover:bg-slate-100 transition shrink-0 cursor-pointer"
                   aria-label="Kembali ke Dompet">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-tight">
                    Pilih Metode Pembayaran
                </h1>

                <div class="w-8"></div>
            </header>

            <!-- Top Animated Progress Bar saat klik metode -->
            <div x-show="isLoading" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="h-1 w-full bg-emerald-100 overflow-hidden sticky top-[49px] z-40">
                <div class="h-full bg-emerald-600 animate-pulse w-full"></div>
            </div>

            @if (! $user->primaryAddress)
                <!-- Enforce DEC-02: Missing Primary Address Warning Card -->
                <div class="p-4 max-w-md mx-auto w-full">
                    <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 space-y-3 shadow-2xs">
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
                <!-- Segmented Tabs: Pill Style Elegan (Shopee / Tokopedia Inspired) -->
                <div class="bg-white border-b border-slate-200/80 px-4 py-2.5 shrink-0 shadow-2xs">
                    <div class="max-w-md mx-auto grid grid-cols-2 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                        <button type="button" 
                                @click="activeTab = 'ewallet'"
                                :class="activeTab === 'ewallet' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                class="py-2 px-3 rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span>QRIS & E-Wallet</span>
                        </button>
                        <button type="button" 
                                @click="activeTab = 'bank'"
                                :class="activeTab === 'bank' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                class="py-2 px-3 rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Virtual Account</span>
                        </button>
                    </div>
                </div>

                <!-- Scrollable Cards Body -->
                <main class="flex-1 min-h-0 overflow-y-auto px-4 py-4 space-y-4 max-w-md mx-auto w-full overscroll-contain">
                    
                    <!-- TAB 1: QRIS & E-WALLET -->
                    <div x-show="activeTab === 'ewallet'" class="space-y-2">
                        <div class="px-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            Saluran Pembayaran Instan
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200/80 divide-y divide-slate-100 overflow-hidden shadow-2xs">
                            @if ($qrisChannel)
                                <!-- 1. QRIS (Pilihan Terpopuler) -->
                                <div @click="selectMethod('QRIS', 'Top Up via QRIS')"
                                     class="group px-4 py-3.5 hover:bg-slate-50/80 active:bg-slate-100 transition cursor-pointer flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <x-payment-logo code="QRIS" name="QRIS" class="w-13 h-9" />
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xs font-bold text-slate-900 truncate">QRIS Standar Nasional</h3>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Populer</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center w-5 h-5 text-slate-400 group-hover:text-emerald-600 transition shrink-0">
                                        <template x-if="isLoading && loadingMethod === 'QRIS'">
                                            <svg class="w-4 h-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isLoading || loadingMethod !== 'QRIS'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            @endif

                            @if ($gopayChannel)
                                <!-- 2. GoPay -->
                                <div @click="selectMethod('GOPAY', 'Top Up via GoPay')"
                                     class="group px-4 py-3.5 hover:bg-slate-50/80 active:bg-slate-100 transition cursor-pointer flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <x-payment-logo code="GOPAY" name="GoPay" class="w-13 h-9" />
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 truncate">GoPay</h3>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center w-5 h-5 text-slate-400 group-hover:text-emerald-600 transition shrink-0">
                                        <template x-if="isLoading && loadingMethod === 'GOPAY'">
                                            <svg class="w-4 h-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isLoading || loadingMethod !== 'GOPAY'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            @endif

                            @if ($danaChannel)
                                <!-- 3. DANA -->
                                <div @click="selectMethod('DANA', 'Top Up via DANA')"
                                     class="group px-4 py-3.5 hover:bg-slate-50/80 active:bg-slate-100 transition cursor-pointer flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <x-payment-logo code="DANA" name="DANA" class="w-13 h-9" />
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 truncate">DANA</h3>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center w-5 h-5 text-slate-400 group-hover:text-emerald-600 transition shrink-0">
                                        <template x-if="isLoading && loadingMethod === 'DANA'">
                                            <svg class="w-4 h-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isLoading || loadingMethod !== 'DANA'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            @endif

                            @foreach ($otherEwallets as $ew)
                                <div @click="selectMethod('{{ strtoupper($ew->code) }}', 'Top Up via {{ $ew->name }}')"
                                     class="group px-4 py-3.5 hover:bg-slate-50/80 active:bg-slate-100 transition cursor-pointer flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <x-payment-logo :code="$ew->code" :name="$ew->name" class="w-13 h-9" />
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 truncate">{{ $ew->name }}</h3>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center w-5 h-5 text-slate-400 group-hover:text-emerald-600 transition shrink-0">
                                        <template x-if="isLoading && loadingMethod === '{{ strtoupper($ew->code) }}'">
                                            <svg class="w-4 h-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isLoading || loadingMethod !== '{{ strtoupper($ew->code) }}'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (! $hasEwallets)
                            <div class="p-8 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-xs font-semibold text-slate-700">Saluran E-Wallet Sedang Nonaktif</p>
                                <p class="text-[11px] text-slate-400 mt-1">Silakan gunakan pembayaran Bank Virtual Account pada tab Virtual Account.</p>
                            </div>
                        @endif
                    </div>

                    <!-- TAB 2: VIRTUAL ACCOUNT BANK -->
                    <div x-show="activeTab === 'bank'" class="space-y-2" style="display: none;">
                        <div class="px-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            Pilihan Bank Nasional (Verifikasi Otomatis)
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200/80 divide-y divide-slate-100 overflow-hidden shadow-2xs">
                            @forelse ($activeBanks as $b)
                                <div @click="selectMethod('{{ strtoupper($b->code) }}', 'Top Up via VA {{ strtoupper($b->code) }}')"
                                     class="group px-4 py-3.5 hover:bg-slate-50/80 active:bg-slate-100 transition cursor-pointer flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <x-payment-logo :code="$b->code" :name="$b->name" class="w-13 h-9" />
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold text-slate-900 truncate">{{ $b->name }}</h3>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center w-5 h-5 text-slate-400 group-hover:text-emerald-600 transition shrink-0">
                                        <template x-if="isLoading && loadingMethod === '{{ strtoupper($b->code) }}'">
                                            <svg class="w-4 h-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isLoading || loadingMethod !== '{{ strtoupper($b->code) }}'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-slate-400 bg-white rounded-2xl border border-slate-200">
                                    Belum ada saluran bank aktif dari Superadmin.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </main>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- SCREEN 2: INPUT NOMINAL DENGAN NUMPAD -->
        <!-- ========================================================================= -->
        <div x-show="step === 'input_amount'" class="flex-1 flex flex-col h-full min-h-0 bg-white justify-between overflow-y-auto" style="display: none;">
            
            <!-- Top App Bar -->
            <header class="bg-white/95 backdrop-blur-md px-4 py-2.5 min-h-[56px] flex items-center justify-between border-b border-slate-200/70 shrink-0">
                <button type="button" 
                        @click="step = 'select_method'"
                        class="w-8 h-8 -ml-1 rounded-full flex items-center justify-center text-slate-700 hover:bg-slate-100 transition shrink-0 cursor-pointer"
                        aria-label="Kembali ke pilihan metode">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-tight" x-text="methodTitle">
                    Top Up Saldo
                </h1>

                <div class="w-8"></div>
            </header>

            <!-- Area Tengah: Nominal Besar & Chip Presets -->
            <div class="flex-1 flex flex-col justify-center items-center px-4 py-6 max-w-md mx-auto w-full">
                <!-- Tampilan Nominal Besar -->
                <div class="flex items-baseline space-x-1.5 my-auto">
                    <span class="text-base font-bold text-slate-900">Rp</span>
                    <span class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight font-mono" x-text="formattedDisplay">
                        100.000
                    </span>
                </div>

                <p x-show="!isValid && numericAmount > 0" class="text-xs text-rose-600 font-semibold mb-3">
                    Minimal pengisian Rp 10.000 & Maksimal Rp 10.000.000
                </p>

                <!-- Chip Pilihan Cepat (Presets) -->
                <div class="w-full grid grid-cols-4 gap-2 mb-4">
                    <button type="button" @click="setPreset(100000)"
                            class="py-2 px-1 text-center rounded-xl border text-xs font-bold transition cursor-pointer"
                            :class="numericAmount === 100000 ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300'">
                        100.000
                    </button>
                    <button type="button" @click="setPreset(200000)"
                            class="py-2 px-1 text-center rounded-xl border text-xs font-bold transition cursor-pointer"
                            :class="numericAmount === 200000 ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300'">
                        200.000
                    </button>
                    <button type="button" @click="setPreset(500000)"
                            class="py-2 px-1 text-center rounded-xl border text-xs font-bold transition cursor-pointer"
                            :class="numericAmount === 500000 ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300'">
                        500.000
                    </button>
                    <button type="button" @click="setPreset(1000000)"
                            class="py-2 px-1 text-center rounded-xl border text-xs font-bold transition cursor-pointer"
                            :class="numericAmount === 1000000 ? 'border-slate-900 bg-slate-900 text-white shadow-xs' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300'">
                        1.000.000
                    </button>
                </div>

                <!-- Form Submit Button: Lanjutkan Top Up -->
                <form action="{{ route('pelanggan.wallet.store') }}" method="POST" class="w-full" @submit="isSubmitting = true">
                    @csrf
                    <input type="hidden" name="amount" :value="numericAmount">
                    <input type="hidden" name="payment_method" :value="paymentMethod">

                    <button type="submit" 
                            :disabled="!isValid || isSubmitting"
                            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm rounded-2xl shadow-sm transition active:scale-[0.98] cursor-pointer flex items-center justify-center space-x-2">
                        <template x-if="!isSubmitting">
                            <span>Lanjutkan Top Up</span>
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

            <!-- Virtual Numeric Keypad -->
            <div class="bg-slate-50/70 border-t border-slate-200/80 px-6 py-4 pb-10 select-none shrink-0">
                <div class="grid grid-cols-3 gap-y-2.5 gap-x-5 max-w-xs mx-auto text-center">
                    <button type="button" @click="pressKey('1')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">1</button>
                    <button type="button" @click="pressKey('2')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">2</button>
                    <button type="button" @click="pressKey('3')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">3</button>

                    <button type="button" @click="pressKey('4')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">4</button>
                    <button type="button" @click="pressKey('5')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">5</button>
                    <button type="button" @click="pressKey('6')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">6</button>

                    <button type="button" @click="pressKey('7')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">7</button>
                    <button type="button" @click="pressKey('8')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">8</button>
                    <button type="button" @click="pressKey('9')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">9</button>

                    <button type="button" @click="pressKey('clear')" class="h-12 flex items-center justify-center text-sm font-bold text-slate-400 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer uppercase">C</button>
                    <button type="button" @click="pressKey('0')" class="h-12 flex items-center justify-center text-2xl font-bold font-mono text-slate-900 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer">0</button>
                    <button type="button" @click="pressKey('backspace')" class="h-12 flex items-center justify-center text-slate-700 bg-white active:bg-slate-100 rounded-xl border border-slate-200/70 shadow-2xs transition cursor-pointer" aria-label="Hapus">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>

    </div>
</x-pwa-layout>
