<x-pwa-layout title="Instruksi Pembayaran - Pijat.In" :hideHeader="true" :fullBleed="true">
    @php
        $activeBanks = \App\Models\Bank::bank()->active()->orderBy('sort_order')->orderBy('name')->get();
        if ($activeBanks->isEmpty()) {
            $activeBanks = \App\Models\Bank::active()->orderBy('sort_order')->orderBy('name')->get();
        }
        $firstBank = $activeBanks->first();
        $firstBankCode = $firstBank ? strtolower($firstBank->code) : 'bca';
        $digits = preg_replace('/[^0-9]/', '', $invoice->invoice_number) ?: '88991234';

        $isVaMethod = in_array(strtolower($invoice->payment_method), ['bca', 'mandiri', 'bri', 'bni', 'cimb', 'permata', 'bsi', 'danamon', 'va']);
        $methodCode = strtolower($isVaMethod ? ($invoice->va_bank_code ?? $invoice->payment_method) : $invoice->payment_method);
        $initialTab = $isVaMethod ? 'va' : 'qris';

        $methodDisplayName = match (strtoupper($invoice->payment_method ?? '')) {
            'QRIS' => 'QRIS Standar Nasional',
            'BCA' => 'Virtual Account BCA',
            'MANDIRI' => 'Virtual Account Mandiri',
            'BRI' => 'Virtual Account BRI (BRIVA)',
            'BNI' => 'Virtual Account BNI',
            'CIMB' => 'Virtual Account CIMB Niaga',
            'PERMATA' => 'Virtual Account Permata',
            'BSI' => 'Virtual Account BSI',
            'DANAMON' => 'Virtual Account Danamon',
            'GOPAY' => 'GoPay',
            'DANA' => 'DANA',
            default => $isVaMethod ? 'Virtual Account ' . strtoupper($invoice->payment_method) : 'QRIS / E-Wallet',
        };

        $changeMethodUrl = $invoice->order 
            ? route('pelanggan.orders.show', $invoice->order) 
            : route('pelanggan.wallet.topup', ['amount' => (int) $invoice->amount, 'change' => 1]);
    @endphp

    <style>
        /* 1. Modal Spring Pop-in */
        @keyframes modal-spring-in {
            0% {
                opacity: 0;
                transform: scale(0.82) translateY(28px);
            }
            60% {
                opacity: 1;
                transform: scale(1.03) translateY(-4px);
            }
            85% {
                transform: scale(0.99) translateY(1px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .animate-modal-spring {
            animation: modal-spring-in 0.46s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* 2. Checkmark Draw & Pop */
        @keyframes checkmark-draw {
            0% {
                stroke-dashoffset: 28;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        @keyframes icon-pop-bounce {
            0% {
                transform: scale(0.2);
                opacity: 0;
            }
            60% {
                transform: scale(1.12);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        .animate-icon-pop {
            animation: icon-pop-bounce 0.5s 0.1s cubic-bezier(0.34, 1.45, 0.64, 1) both;
        }
        .animate-check-stroke {
            stroke-dasharray: 28;
            stroke-dashoffset: 28;
            animation: checkmark-draw 0.75s 0.28s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        /* 3. Soft Ripple Ring */
        @keyframes ripple-ring {
            0% {
                transform: scale(0.75);
                opacity: 0.9;
            }
            100% {
                transform: scale(1.85);
                opacity: 0;
            }
        }
        .animate-ripple {
            animation: ripple-ring 0.9s 0.2s cubic-bezier(0, 0, 0.2, 1) forwards;
        }

        /* 4. Celebratory Sparkle Burst */
        @keyframes sparkle-burst {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }
            30% {
                opacity: 1;
            }
            70% {
                transform: scale(1.15) rotate(25deg);
                opacity: 0.9;
            }
            100% {
                transform: scale(1.4) rotate(45deg);
                opacity: 0;
            }
        }
        .animate-sparkles {
            animation: sparkle-burst 0.85s 0.18s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* 5. Staggered Content Reveal */
        @keyframes content-fade-up {
            0% {
                opacity: 0;
                transform: translateY(12px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .stagger-1 { animation: content-fade-up 0.45s 0.2s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .stagger-2 { animation: content-fade-up 0.45s 0.32s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .stagger-3 { animation: content-fade-up 0.45s 0.44s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .stagger-4 { animation: content-fade-up 0.45s 0.56s cubic-bezier(0.16, 1, 0.3, 1) both; }
    </style>

    <div class="flex-1 flex flex-col h-full min-h-0 bg-slate-50 text-slate-800"
         x-data="invoicePayment()">

        <!-- 1. TOP APP BAR -->
        <header class="sticky top-0 z-30 bg-white border-b border-slate-200/80 px-4 py-3 flex items-center justify-between shrink-0 shadow-2xs">
            <a href="{{ $invoice->order ? route('pelanggan.orders.show', $invoice->order) : route('pelanggan.wallet.index') }}" 
               class="w-9 h-9 rounded-full flex items-center justify-center text-slate-700 hover:bg-slate-100 active:scale-95 transition cursor-pointer"
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
                        Instruksi Pembayaran
                    @endif
                </h1>
                <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                    {{ $invoice->invoice_number }}
                </p>
            </div>

            <!-- Tombol Bagikan Tagihan (Native Share API) -->
            <button type="button"
                    @click="shareInvoice()"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:scale-95 transition cursor-pointer"
                    aria-label="Bagikan Tagihan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </button>
        </header>

        <!-- Toast Salin / Unduh -->
        <div x-show="toastMessage"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed top-16 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-slate-900/90 text-white text-xs font-semibold rounded-full shadow-lg pointer-events-none flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <span x-text="toastMessage"></span>
        </div>

        <!-- 2. SCROLLABLE CONTENT BODY -->
        <main class="flex-1 min-h-0 overflow-y-auto px-4 py-4 space-y-3.5 max-w-md mx-auto w-full overscroll-contain">

            @if (session('info'))
                <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-semibold flex items-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if ($invoice->isExpired())
                <!-- STATE: KEDALUWARSA -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 text-center space-y-4 shadow-2xs">
                    <div class="w-14 h-14 mx-auto rounded-full bg-rose-50 text-rose-500 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200/60">
                            Tagihan Kedaluwarsa
                        </span>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-2">
                            {{ $invoice->formatted_amount }}
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Batas waktu pembayaran telah habis. Silakan buat pengisian saldo baru.
                        </p>
                    </div>

                    <a href="{{ route('pelanggan.wallet.topup') }}" 
                       class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center shadow-xs">
                        Ulangi Top Up
                    </a>
                </div>

            @else
                <!-- CONTAINER 1: KARTU RINCIAN TAGIHAN & METODE PEMBAYARAN (Container BG Putih Rapi) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-5 space-y-3.5">
                    
                    <!-- Baris Atas: Nominal Pembayaran & Status Waktu -->
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-medium text-slate-500 block">Total Pembayaran</span>
                            <div class="text-2xl sm:text-3xl font-black font-mono text-slate-900 tracking-tight mt-0.5">
                                {{ $invoice->formatted_amount }}
                            </div>
                        </div>

                        <div>
                            @if ($invoice->isPaid())
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60 shrink-0">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Lunas</span>
                                </span>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-800 text-xs font-semibold border border-amber-200/60 shrink-0">
                                    <svg class="w-3.5 h-3.5 text-amber-600 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="font-mono font-bold" x-text="timeLeft">15:00</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Garis Pemisah -->
                    <div class="border-t border-slate-100"></div>

                    <!-- Baris Bawah: Metode Pembayaran Terpilih & Aksi -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-payment-logo :code="$isVaMethod ? ($invoice->va_bank_code ?? $invoice->payment_method) : 'QRIS'" class="w-12 h-8 shrink-0" />
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-900 truncate block">
                                    {{ $methodDisplayName }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono block">
                                    No. {{ $invoice->invoice_number }}
                                </span>
                            </div>
                        </div>

                        @if (! $invoice->isPaid())
                            <a href="{{ $changeMethodUrl }}"
                               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline shrink-0">
                                Ubah Metode
                            </a>
                        @else
                            <button type="button" 
                                    @click="openSuccessModal()"
                                    class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 cursor-pointer shrink-0">
                                <span>Lihat Bukti</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if ($invoice->order)
                        <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400">
                            Pesanan: <strong class="font-mono text-slate-700">#{{ $invoice->order->order_code }}</strong>
                        </div>
                    @endif
                </div>

                <!-- CONTAINER 2: AREA BAYAR (QRIS ATAU VIRTUAL ACCOUNT) -->
                @if (strtoupper($invoice->payment_method ?? '') === 'XENDIT')
                    <!-- Switcher Jika Pembayaran Multi-Metode Xendit Generik -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/60 rounded-xl text-xs font-semibold">
                        <button type="button" @click="paymentTab = 'qris'"
                                class="py-2 px-3 rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5"
                                :class="paymentTab === 'qris' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'">
                            <span>QRIS</span>
                        </button>
                        <button type="button" @click="paymentTab = 'va'"
                                class="py-2 px-3 rounded-lg transition cursor-pointer flex items-center justify-center gap-1.5"
                                :class="paymentTab === 'va' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'">
                            <span>Virtual Account</span>
                        </button>
                    </div>
                @endif

                <!-- KARTU 2A: TAMPILAN QRIS -->
                <div x-show="paymentTab === 'qris'" class="bg-white rounded-2xl border border-slate-200/80 p-5 text-center shadow-2xs">
                    <div class="mb-2">
                        <span class="text-xs font-bold text-slate-900 block">Pindai Kode QRIS</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5">Bisa bayar dari m-Banking atau E-Wallet mana pun</span>
                    </div>

                    <!-- Kotak QRIS (Klik untuk Zoom) -->
                    <div class="flex flex-col items-center justify-center my-3">
                        <div @click="openQrModal = true"
                             class="relative inline-block p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs cursor-pointer group hover:border-emerald-400 transition"
                             title="Ketuk untuk memperbesar kode QR">
                            {!! \App\Support\QrisCode::svg($invoice->qris_string ?: $invoice->invoice_number, 190) !!}
                            
                            @if ($invoice->isPaid())
                                <div class="absolute inset-0 bg-white/90 backdrop-blur-[2px] flex flex-col items-center justify-center p-3 text-center rounded-2xl">
                                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center mb-1 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-900">Pembayaran Diterima</span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d M Y, H:i') : 'Lunas' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if (! $invoice->isPaid())
                            <button type="button" 
                                    @click="openQrModal = true"
                                    class="text-[10px] text-slate-400 hover:text-emerald-700 flex items-center gap-1 mt-1.5 transition cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                </svg>
                                <span>Ketuk QR untuk memperbesar</span>
                            </button>
                        @endif
                    </div>

                    @if (! $invoice->isPaid())
                        <button type="button" 
                                @click="downloadQris()"
                                :disabled="downloading"
                                class="w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition cursor-pointer flex items-center justify-center gap-2 mt-1">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span x-text="downloading ? 'Menyiapkan gambar...' : 'Simpan Gambar QR'"></span>
                        </button>
                    @endif
                </div>

                <!-- KARTU 2B: TAMPILAN VIRTUAL ACCOUNT -->
                <div x-show="paymentTab === 'va'" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-4" style="display: none;">
                    @if (! empty($invoice->va_number))
                        <div>
                            <span class="text-[11px] text-slate-400 font-medium block">Nomor Virtual Account</span>
                            <div class="mt-1 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3">
                                <span class="text-lg sm:text-xl font-bold font-mono text-slate-900 tracking-wider">
                                    {{ $invoice->va_number }}
                                </span>
                                @if ($invoice->isPaid())
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60">
                                        Lunas
                                    </span>
                                @else
                                    <button type="button" 
                                            @click="copyText('{{ $invoice->va_number }}', 'Nomor VA disalin', 'va')"
                                            class="px-3 py-1.5 rounded-lg text-white font-bold text-xs transition cursor-pointer shrink-0 shadow-2xs flex items-center gap-1.5"
                                            :class="copiedKey === 'va' ? 'bg-emerald-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                                        <template x-if="copiedKey === 'va'">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Tersalin</span>
                                            </span>
                                        </template>
                                        <template x-if="copiedKey !== 'va'">
                                            <span>Salin</span>
                                        </template>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="text-xs font-bold text-slate-800 block mb-1.5">Pilih Bank Pembayaran:</label>
                            <select x-model="selectedBank"
                                    :disabled="{{ $invoice->isPaid() ? 'true' : 'false' }}"
                                    class="w-full text-xs font-semibold py-2.5 px-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-slate-800 cursor-pointer disabled:bg-slate-100 disabled:cursor-not-allowed">
                                @foreach ($activeBanks as $b)
                                    <option value="{{ strtolower($b->code) }}">{{ $b->name }} ({{ strtoupper($b->code) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <span class="text-[11px] text-slate-400 font-medium block">Nomor Virtual Account</span>
                            <div class="mt-1 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3">
                                <span class="text-lg sm:text-xl font-bold font-mono text-slate-900 tracking-wider" x-text="vaNumbers[selectedBank]">
                                    {{ ($firstBank?->va_prefix ?? '8808') . substr($digits, -8) }}
                                </span>
                                @if ($invoice->isPaid())
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60">
                                        Lunas
                                    </span>
                                @else
                                    <button type="button" 
                                            @click="copyText(vaNumbers[selectedBank], 'Nomor VA disalin', 'va')"
                                            class="px-3 py-1.5 rounded-lg text-white font-bold text-xs transition cursor-pointer shrink-0 shadow-2xs flex items-center gap-1.5"
                                            :class="copiedKey === 'va' ? 'bg-emerald-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                                        <template x-if="copiedKey === 'va'">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Tersalin</span>
                                            </span>
                                        </template>
                                        <template x-if="copiedKey !== 'va'">
                                            <span>Salin</span>
                                        </template>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="text-xs space-y-1.5 pt-1 border-t border-slate-100">
                        <div class="flex justify-between text-slate-500">
                            <span>Nama Rekening</span>
                            <span class="font-semibold text-slate-800">Pijat.In - {{ $invoice->user->name }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Bank Penerima</span>
                            <span class="font-semibold text-slate-800">{{ strtoupper($invoice->va_bank_code ?? $invoice->payment_method) }}</span>
                        </div>
                    </div>
                </div>

                <!-- CONTAINER 3: PETUNJUK PEMBAYARAN KHUSUS METODE TERPILIH (Context-Aware, Tanpa Tab) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 space-y-3">
                    @if ($isVaMethod)
                        @if ($methodCode === 'bca')
                            <!-- KHUSUS BCA -->
                            <h3 class="text-xs font-bold text-slate-900">Cara Pembayaran Virtual Account BCA</h3>
                            <div class="space-y-3 text-xs text-slate-600">
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via BCA Mobile (m-BCA):</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Buka aplikasi BCA Mobile, pilih <strong>m-BCA</strong> lalu masukkan kode akses.</li>
                                        <li>Pilih menu <strong>m-Transfer > BCA Virtual Account</strong>.</li>
                                        <li>Masukkan nomor Virtual Account di atas lalu tekan <strong>Send</strong>.</li>
                                        <li>Periksa nama penerima <strong>Pijat.In</strong> dan nominal tagihan, lalu masukkan PIN m-BCA.</li>
                                    </ol>
                                </div>
                                <div class="pt-2 border-t border-slate-100 space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via ATM BCA:</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Masukkan kartu ATM BCA dan 6 digit PIN Anda.</li>
                                        <li>Pilih menu <strong>Transaksi Lainnya > Transfer > Ke Rek BCA Virtual Account</strong>.</li>
                                        <li>Ketikkan nomor Virtual Account di atas lalu tekan <strong>Benar</strong>.</li>
                                        <li>Periksa konfirmasi pada layar ATM lalu selesaikan pembayaran.</li>
                                    </ol>
                                </div>
                            </div>

                        @elseif ($methodCode === 'mandiri')
                            <!-- KHUSUS MANDIRI -->
                            <h3 class="text-xs font-bold text-slate-900">Cara Pembayaran Mandiri Virtual Account</h3>
                            <div class="space-y-3 text-xs text-slate-600">
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via Livin' by Mandiri (Kuning):</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Buka dan login aplikasi Livin' by Mandiri.</li>
                                        <li>Pilih menu <strong>Bayar > Pembayaran Baru</strong> lalu ketik <strong>Virtual Account</strong>.</li>
                                        <li>Masukkan nomor Virtual Account di atas lalu tekan <strong>Lanjutkan</strong>.</li>
                                        <li>Pastikan nama penerima <strong>Pijat.In</strong> dan total sesuai, lalu selesaikan dengan PIN Livin'.</li>
                                    </ol>
                                </div>
                                <div class="pt-2 border-t border-slate-100 space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via ATM Mandiri:</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Masukkan kartu ATM Mandiri dan PIN Anda.</li>
                                        <li>Pilih menu <strong>Bayar/Beli > Lainnya > Multi Payment</strong>.</li>
                                        <li>Masukkan nomor Virtual Account di atas lalu tekan <strong>Benar</strong>.</li>
                                        <li>Konfirmasi pembayaran pada layar lalu selesaikan transaksi.</li>
                                    </ol>
                                </div>
                            </div>

                        @elseif ($methodCode === 'bri')
                            <!-- KHUSUS BRI (BRIVA) -->
                            <h3 class="text-xs font-bold text-slate-900">Cara Pembayaran BRIVA (BRI)</h3>
                            <div class="space-y-3 text-xs text-slate-600">
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via BRImo:</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Buka aplikasi BRImo dan login akun Anda.</li>
                                        <li>Pilih menu <strong>Tagihan > BRIVA</strong> lalu pilih <strong>Tambah Pembayaran Baru</strong>.</li>
                                        <li>Masukkan nomor Virtual Account di atas lalu tekan <strong>Lanjutkan</strong>.</li>
                                        <li>Periksa nama penerima <strong>Pijat.In</strong> dan masukkan PIN BRImo untuk membayar.</li>
                                    </ol>
                                </div>
                                <div class="pt-2 border-t border-slate-100 space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via ATM BRI:</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Masukkan kartu ATM dan PIN Anda di mesin ATM BRI.</li>
                                        <li>Pilih menu <strong>Transaksi Lainnya > Pembayaran > Lainnya > BRIVA</strong>.</li>
                                        <li>Masukkan nomor Virtual Account di atas lalu tekan <strong>Benar</strong>.</li>
                                        <li>Periksa konfirmasi pada layar lalu selesaikan pembayaran.</li>
                                    </ol>
                                </div>
                            </div>

                        @elseif ($methodCode === 'bni')
                            <!-- KHUSUS BNI -->
                            <h3 class="text-xs font-bold text-slate-900">Cara Pembayaran BNI Virtual Account</h3>
                            <div class="space-y-3 text-xs text-slate-600">
                                <div class="space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via BNI Mobile Banking:</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Buka aplikasi BNI Mobile Banking dan login.</li>
                                        <li>Pilih menu <strong>Pembayaran > Virtual Account Billing</strong>.</li>
                                        <li>Pilih tab <strong>Input Baru</strong> dan ketikkan nomor Virtual Account di atas.</li>
                                        <li>Periksa informasi tagihan, masukkan password transaksi Anda lalu konfirmasi.</li>
                                    </ol>
                                </div>
                                <div class="pt-2 border-t border-slate-100 space-y-1">
                                    <span class="font-bold text-slate-800 text-[11px] block">Via ATM BNI:</span>
                                    <ol class="list-decimal list-inside space-y-1 text-slate-500 text-[11px] leading-relaxed">
                                        <li>Masukkan kartu ATM BNI dan PIN Anda.</li>
                                        <li>Pilih <strong>Menu Lain > Pembayaran > Menu Berikutnya > Virtual Account Billing</strong>.</li>
                                        <li>Masukkan nomor Virtual Account di atas lalu selesaikan pembayaran.</li>
                                    </ol>
                                </div>
                            </div>

                        @else
                            <!-- BANK LAINNYA / FALLBACK -->
                            <h3 class="text-xs font-bold text-slate-900">Cara Pembayaran Virtual Account {{ strtoupper($invoice->va_bank_code ?? $invoice->payment_method) }}</h3>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-500 text-[11px] leading-relaxed">
                                <li>Buka aplikasi m-Banking atau kunjungi mesin ATM bank Anda.</li>
                                <li>Pilih menu <strong>Transfer > Virtual Account</strong> (atau Bayar Tagihan).</li>
                                <li>Masukkan nomor Virtual Account yang tertera di atas.</li>
                                <li>Pastikan nama penerima tertera <strong>Pijat.In</strong> dan nominal sesuai.</li>
                                <li>Masukkan PIN transaksi Anda untuk menyelesaikan pembayaran.</li>
                            </ol>
                        @endif

                    @else
                        <!-- KHUSUS QRIS & E-WALLET -->
                        <h3 class="text-xs font-bold text-slate-900">Cara Pembayaran QRIS</h3>
                        <ol class="list-decimal list-inside space-y-1.5 text-slate-500 text-[11px] leading-relaxed">
                            <li>Buka aplikasi m-Banking atau E-Wallet pilihan Anda (BCA, Livin', BRImo, GoPay, OVO, DANA, ShopeePay, dll).</li>
                            <li>Pilih menu <strong>Scan / Bayar QRIS</strong>.</li>
                            <li>Arahkan kamera ke kode QR di atas atau unggah gambar yang telah disimpan.</li>
                            <li>Pastikan nama penerima <strong>Pijat.In</strong> dan nominal tagihan <strong>{{ $invoice->formatted_amount }}</strong>.</li>
                            <li>Masukkan PIN transaksi Anda untuk mengonfirmasi pembayaran.</li>
                        </ol>
                    @endif
                </div>

                @if ($invoice->isPaid())
                    <!-- BOTTOM ACTION (Saat Lunas) -->
                    <div class="space-y-2 pt-1">
                        @if ($invoice->order)
                            <a href="{{ route('pelanggan.orders.show', $invoice->order) }}" 
                               class="w-full h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                                <span>Lihat Pesanan Saya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('pelanggan.wallet.index') }}" 
                               class="w-full h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                                <span>Buka Dompet Saya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @else
                    <!-- FOOTER STATUS & SIMULATOR PANEL -->
                    <div class="pt-1 space-y-3">
                        <div class="flex items-center justify-center gap-2 text-xs text-slate-500">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Menunggu verifikasi pembayaran otomatis...</span>
                        </div>

                        <!-- Simulator Sandbox (Discreet Collapsible Panel, Developer / Testing Tool) -->
                        <details class="group">
                            <summary class="text-[11px] font-medium text-slate-400 hover:text-slate-600 text-center cursor-pointer list-none py-1 flex items-center justify-center gap-1.5 select-none transition">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Panel Pengujian Sandbox</span>
                                <svg class="w-3 h-3 text-slate-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="p-3 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-2 space-y-2.5">
                                <div class="grid grid-cols-2 gap-2">
                                    <form action="{{ route('pelanggan.wallet.check-status', $invoice->invoice_number) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full py-2 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer text-center">
                                            Cek Status
                                        </button>
                                    </form>
                                    <form action="{{ route('pelanggan.wallet.simulate-pay', $invoice->invoice_number) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full py-2 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition cursor-pointer shadow-xs text-center">
                                            Simulasi Bayar
                                        </button>
                                    </form>
                                </div>
                                @if (! empty($invoice->payment_url))
                                    <div class="text-center pt-0.5">
                                        <a href="{{ $invoice->payment_url }}" target="_blank" rel="noopener noreferrer"
                                           class="text-[11px] font-semibold text-emerald-700 hover:underline inline-flex items-center gap-1">
                                            <span>Buka Halaman Resmi Simulator Xendit</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </details>
                    </div>
                @endif

            @endif

        </main>

        <!-- 3. OVERLAY LOADING VERIFIKASI PEMBAYARAN (Fintech Style) -->
        <div x-show="isVerifying"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-250"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center select-none">
            
            <div class="relative w-16 h-16 mb-4 flex items-center justify-center">
                <!-- Circular spinner -->
                <svg class="w-14 h-14 animate-spin text-emerald-500" viewBox="0 0 48 48" fill="none">
                    <circle class="opacity-20 text-white" cx="24" cy="24" r="20" stroke="currentColor" stroke-width="3.5"></circle>
                    <path class="text-emerald-400" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" d="M24 4 A 20 20 0 0 1 44 24"></path>
                </svg>
            </div>

            <span class="text-white text-sm font-bold tracking-tight">Memverifikasi Pembayaran</span>
            <span class="text-slate-300 text-xs mt-1">Sinkronisasi status transaksi...</span>
        </div>

        <!-- 4. MODAL FULLSCREEN ZOOM QRIS LIGHTBOX -->
        <div x-show="openQrModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-xs transition-opacity" @click="openQrModal = false"></div>
            <div class="fixed inset-0 z-10 flex min-h-full items-center justify-center p-4">
                <div @click.outside="openQrModal = false"
                     class="relative w-full max-w-xs rounded-3xl bg-white p-6 text-center shadow-2xl border border-slate-100 animate-modal-spring">
                    <button type="button" 
                            @click="openQrModal = false" 
                            class="absolute top-4 right-4 w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    
                    <div class="mb-3">
                        <h3 class="text-sm font-bold text-slate-900">QRIS Standar Nasional</h3>
                        <p class="text-xs text-emerald-700 font-mono font-bold mt-0.5">{{ $invoice->formatted_amount }}</p>
                    </div>

                    <div class="p-3 bg-white border border-slate-200/90 rounded-2xl inline-block shadow-xs mx-auto">
                        {!! \App\Support\QrisCode::svg($invoice->qris_string ?: $invoice->invoice_number, 230) !!}
                    </div>

                    <p class="text-[11px] text-slate-400 mt-3 leading-tight">Arahkan kamera aplikasi m-Banking atau E-Wallet untuk memindai</p>

                    <button type="button" 
                            @click="downloadQris(); openQrModal = false;" 
                            class="w-full mt-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        Simpan Gambar QR
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. MODAL POPUP BERHASIL PEMBAYARAN / TOP UP (Spring Pop + Ripple + Sparkles + Cascade) -->
        <div x-show="showSuccessModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="modal-success-title">
            
            <!-- Backdrop Blur Overlay -->
            <div x-show="showSuccessModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showSuccessModal = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            </div>

            <!-- Modal Content Alignment -->
            <div class="fixed inset-0 z-10 flex min-h-full items-center justify-center p-4">
                <div x-show="showSuccessModal"
                     x-transition:enter="animate-modal-spring"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                     @click.outside="showSuccessModal = false"
                     class="relative w-full max-w-sm rounded-3xl bg-white p-6 sm:p-7 text-center shadow-2xl border border-slate-100 overflow-hidden">
                    
                    <!-- Close Icon Button -->
                    <button type="button" 
                            @click="showSuccessModal = false"
                            class="absolute top-4 right-4 w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer z-20"
                            aria-label="Tutup modal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Animated Celebration Center: Ripple + Sparkles + Pop Icon + Drawing Checkmark -->
                    <template x-if="showSuccessModal">
                        <div class="relative mx-auto flex items-center justify-center w-20 h-20 mb-2">
                            <!-- Soft Expanding Ripple Wave -->
                            <div class="absolute inset-2 rounded-full bg-emerald-400/25 animate-ripple pointer-events-none"></div>

                            <!-- Celebratory Sparkle Burst -->
                            <svg class="absolute inset-0 w-20 h-20 animate-sparkles pointer-events-none" viewBox="0 0 80 80" fill="none">
                                <circle cx="40" cy="8" r="2.5" fill="#10b981" />
                                <circle cx="70" cy="22" r="2" fill="#34d399" />
                                <circle cx="72" cy="56" r="2.5" fill="#059669" />
                                <circle cx="48" cy="74" r="2" fill="#10b981" />
                                <circle cx="12" cy="58" r="2.5" fill="#34d399" />
                                <circle cx="10" cy="24" r="2" fill="#059669" />
                                <polygon points="62,10 63.5,14 67.5,14 64.5,17 65.5,21 62,18 58.5,21 59.5,17 56.5,14 60.5,14" fill="#fbbf24" />
                                <polygon points="20,62 21.5,66 25.5,66 22.5,69 23.5,73 20,70 16.5,73 17.5,69 14.5,66 18.5,66" fill="#fbbf24" />
                            </svg>

                            <!-- Main Emerald Badge with Spring Pop -->
                            <div class="relative flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md animate-icon-pop z-10">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path class="animate-check-stroke" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </template>

                    <!-- Title & Subtitle (Stagger 1) -->
                    <div class="stagger-1">
                        <h3 id="modal-success-title" class="text-lg font-bold text-slate-900 tracking-tight">
                            @if ($invoice->order)
                                Pembayaran Berhasil
                            @else
                                Top Up Berhasil
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            @if ($invoice->order)
                                Pembayaran pesanan Anda telah diverifikasi oleh sistem.
                            @else
                                Saldo berhasil ditambahkan ke dompet akun Anda.
                            @endif
                        </p>
                    </div>

                    <!-- Amount Highlight (Stagger 2) -->
                    <div class="my-3 stagger-2">
                        <span class="text-3xl font-extrabold font-mono text-slate-900 tracking-tight">
                            {{ $invoice->formatted_amount }}
                        </span>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100 my-4"></div>

                    <!-- Transaction Details (Stagger 3) -->
                    <div class="space-y-2.5 text-xs text-left stagger-3">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Nomor Tagihan</span>
                            <div class="flex items-center gap-1.5 font-mono font-semibold text-slate-700">
                                <span>{{ $invoice->invoice_number }}</span>
                                <button type="button" 
                                        @click="copyText('{{ $invoice->invoice_number }}', 'No. Tagihan disalin', 'inv')"
                                        class="text-slate-400 hover:text-emerald-600 transition cursor-pointer"
                                        aria-label="Salin Nomor Tagihan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Metode</span>
                            <span class="font-semibold text-slate-800">{{ strtoupper($invoice->payment_method) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Waktu Pembayaran</span>
                            <span class="font-medium text-slate-700">
                                {{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d M Y, H:i') : now()->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                        </div>

                        @if ($invoice->order)
                            <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                                <span class="text-slate-400">Kode Pesanan</span>
                                <span class="font-mono font-bold text-slate-900">#{{ $invoice->order->order_code }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100 my-4"></div>

                    <!-- Action CTAs (Stagger 4) -->
                    <div class="space-y-2 pt-1 stagger-4">
                        @if ($invoice->order)
                            <a href="{{ route('pelanggan.orders.show', $invoice->order) }}" 
                               class="w-full h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Lihat Pesanan Saya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('pelanggan.wallet.index') }}" 
                               class="w-full h-11 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Buka Dompet Saya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif

                        <button type="button" 
                                @click="showSuccessModal = false"
                                class="w-full h-10 rounded-xl text-slate-500 hover:text-slate-800 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                            Tutup & Lihat Instruksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoicePayment() {
            return {
                isVerifying: false,
                showSuccessModal: false,
                openQrModal: false,
                copiedKey: null,
                paymentTab: '{{ $initialTab }}',
                selectedBank: '{{ in_array(strtolower($invoice->payment_method), ['bca', 'mandiri', 'bri', 'bni', 'cimb', 'permata', 'bsi']) ? strtolower($invoice->payment_method) : $firstBankCode }}',
                downloading: false,
                toastMessage: '',
                openDev: false,
                expiresAt: new Date('{{ $invoice->expires_at->toIso8601String() }}').getTime(),
                timeLeft: '',
                isExpired: {{ $invoice->isExpired() ? 'true' : 'false' }},
                isPaid: {{ $invoice->isPaid() ? 'true' : 'false' }},
                vaNumbers: {
                    @foreach ($activeBanks as $b)
                        '{{ strtolower($b->code) }}': '{{ ($invoice->available_banks[strtoupper($b->code)] ?? null) ?: (($b->va_prefix ?? '8808') . substr($digits, -8)) }}',
                    @endforeach
                },
                showToast(msg) {
                    this.toastMessage = msg;
                    setTimeout(() => { this.toastMessage = ''; }, 2500);
                },
                copyText(text, label = 'Tersalin', key = null) {
                    if (!navigator.clipboard) {
                        const t = document.createElement('textarea');
                        t.value = text;
                        document.body.appendChild(t);
                        t.select();
                        document.execCommand('copy');
                        document.body.removeChild(t);
                    } else {
                        navigator.clipboard.writeText(text);
                    }
                    if (key) {
                        this.copiedKey = key;
                        setTimeout(() => { if (this.copiedKey === key) this.copiedKey = null; }, 1800);
                    }
                    this.showToast(label);
                },
                shareInvoice() {
                    const shareText = `Instruksi Pembayaran Pijat.In\nNo. Tagihan: {{ $invoice->invoice_number }}\nNominal: {{ $invoice->formatted_amount }}\nMetode: {{ $methodDisplayName }}\nBatas Pembayaran: {{ $invoice->expires_at->translatedFormat('d M Y, H:i') }} WIB\n\nDetail pembayaran: ${window.location.href}`;
                    if (navigator.share) {
                        navigator.share({
                            title: 'Tagihan Pembayaran Pijat.In - {{ $invoice->invoice_number }}',
                            text: shareText,
                            url: window.location.href,
                        }).catch(() => {});
                    } else {
                        this.copyText(shareText, 'Rincian tagihan berhasil disalin');
                    }
                },
                openSuccessModal() {
                    this.showSuccessModal = true;
                    if (window.navigator && window.navigator.vibrate) {
                        try {
                            window.navigator.vibrate(40);
                        } catch (e) {}
                    }
                },
                triggerSuccessSequence() {
                    this.isVerifying = true;
                    setTimeout(() => {
                        this.isVerifying = false;
                        this.showSuccessModal = true;
                        if (window.navigator && window.navigator.vibrate) {
                            try {
                                window.navigator.vibrate([35, 45, 50]);
                            } catch (e) {}
                        }
                    }, 1200);
                },
                downloadQris() {
                    this.downloading = true;
                    try {
                        const svgElement = document.querySelector('svg[data-qris]');
                        if (!svgElement) {
                            this.downloading = false;
                            return;
                        }
                        const svgData = new XMLSerializer().serializeToString(svgElement);
                        const canvas = document.createElement('canvas');
                        canvas.width = 400;
                        canvas.height = 480;
                        const ctx = canvas.getContext('2d');

                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, 400, 480);

                        ctx.fillStyle = '#0f172a';
                        ctx.font = 'bold 16px system-ui, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText('QRIS - Pijat.In', 200, 36);

                        ctx.fillStyle = '#64748b';
                        ctx.font = '11px monospace, system-ui';
                        ctx.fillText('{{ $invoice->invoice_number }}', 200, 54);

                        const img = new Image();
                        img.onload = () => {
                            ctx.drawImage(img, 50, 70, 300, 300);

                            ctx.fillStyle = '#64748b';
                            ctx.font = '11px system-ui, sans-serif';
                            ctx.fillText('Total Pembayaran', 200, 395);

                            ctx.fillStyle = '#0f172a';
                            ctx.font = 'bold 20px monospace, system-ui';
                            ctx.fillText('{{ $invoice->formatted_amount }}', 200, 422);

                            const a = document.createElement('a');
                            a.download = 'QRIS-{{ $invoice->invoice_number }}.png';
                            a.href = canvas.toDataURL('image/png');
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);

                            this.downloading = false;
                            this.showToast('Kode QRIS berhasil disimpan');
                        };
                        img.onerror = () => { this.downloading = false; };
                        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgData);
                    } catch (e) {
                        this.downloading = false;
                    }
                },
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
                },
                checkStatusAuto() {
                    if (this.isExpired || this.isPaid || this.isVerifying) return;
                    fetch('{{ route('pelanggan.wallet.check-status', $invoice->invoice_number) }}', {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.paid || data.status === 'PAID') {
                            this.isPaid = true;
                            this.triggerSuccessSequence();
                        }
                    })
                    .catch(() => {});
                },
                init() {
                    this.updateTimer();
                    if (!this.isPaid) {
                        setInterval(() => this.updateTimer(), 1000);
                    }
                    @if ($invoice->isPending())
                        setInterval(() => this.checkStatusAuto(), 5000);
                    @endif

                    @if ($invoice->isPaid() && session('status'))
                        this.triggerSuccessSequence();
                    @elseif ($invoice->isPaid())
                        this.showSuccessModal = true;
                    @endif
                }
            };
        }
    </script>
</x-pwa-layout>
