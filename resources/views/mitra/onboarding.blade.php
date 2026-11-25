<x-pwa-layout title="Kelengkapan Berkas - Pijat.In" headerTitle="Pendaftaran Mitra">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* SweetAlert2 Theme Matching Admin Confirmation Modal */
        div:where(.swal2-container).swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }
        div:where(.swal2-container) div:where(.swal2-popup).swal-admin-theme {
            width: calc(100% - 2rem) !important;
            max-width: 380px !important;
            padding: 1.5rem !important;
            border-radius: 1.5rem !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            font-family: inherit !important;
            background: #ffffff !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon {
            width: 3.5rem !important;
            height: 3.5rem !important;
            margin: 0.25rem auto 1rem !important;
            border: none !important;
            border-radius: 9999px !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-warning {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
            box-shadow: 0 0 0 8px #fffbeb !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-warning .swal2-icon-content {
            font-size: 1.6rem !important;
            font-weight: 700 !important;
            color: #d97706 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-title {
            font-size: 1rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            line-height: 1.25 !important;
            padding: 0 !important;
            margin: 0 0 0.5rem 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-html-container {
            font-size: 0.75rem !important;
            color: #64748b !important;
            line-height: 1.5 !important;
            margin: 0 0 1.25rem 0 !important;
            padding: 0 0.25rem !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-actions {
            margin: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm {
            width: 100% !important;
            min-height: 42px !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            background-color: #d97706 !important; /* amber-600 */
            color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(217, 119, 6, 0.2) !important;
            transition: all 0.15s ease-in-out !important;
            border: none !important;
            cursor: pointer !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm:hover {
            background-color: #b45309 !important; /* amber-700 */
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm:active {
            background-color: #92400e !important; /* amber-800 */
        }
    </style>

    @php
        $initialStep = 1;
        if ($errors->hasAny(['sop_agreement'])) {
            $initialStep = 4;
        } elseif ($errors->hasAny(['bank_name', 'account_number', 'account_holder_name'])) {
            $initialStep = 3;
        } elseif ($errors->hasAny(['region_id', 'latitude', 'longitude', 'address_text'])) {
            $initialStep = 2;
        } elseif ($errors->hasAny(['nik', 'phone', 'ktp_file', 'selfie_file', 'skck_file'])) {
            $initialStep = 1;
        }
    @endphp

    <div class="py-2 space-y-4"
         x-data="onboardingWizard()"
         x-init="initWizard()">

        <!-- Header Info -->
        <div class="space-y-0.5">
            <h2 class="text-base font-bold text-slate-900 leading-tight">
                Pendaftaran Mitra
            </h2>
            <p class="text-xs text-slate-500">Lengkapi data kependudukan, wilayah operasional, dan rekening pencairan mitra.</p>
        </div>

        @if ($user->isDitolak())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 shadow-xs flex items-start space-x-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-sm">Pendaftaran Sebelumnya Perlu Perbaikan</h3>
                    <p class="text-xs mt-1 leading-relaxed text-rose-800">
                        Alasan penolakan: <strong>{{ $user->rejection_reason ?? 'Berkas tidak memenuhi kriteria.' }}</strong>.
                        Silakan perbarui dokumen dan informasi di bawah ini untuk mengajukan verifikasi ulang.
                    </p>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Stepper Progress Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-3.5 shadow-xs">
            <div class="relative flex items-center justify-between">
                <!-- Line background -->
                <div class="absolute left-6 right-6 top-4 h-0.5 bg-slate-200 -z-0"></div>
                <!-- Line active progress -->
                <div class="absolute left-6 top-4 h-0.5 bg-emerald-600 transition-all duration-300 -z-0"
                     :style="`width: calc(${((currentStep - 1) / 3) * 100}% - 12px)`"></div>

                <template x-for="st in steps" :key="st.num">
                    <button type="button" @click="goToStep(st.num)"
                        class="relative z-10 flex flex-col items-center group focus:outline-hidden"
                        :class="st.num <= currentStep ? 'cursor-pointer' : 'cursor-not-allowed opacity-80'">
                        <!-- Circle Indicator -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-200"
                            :class="{
                                'bg-emerald-700 text-white ring-4 ring-emerald-100 shadow-sm scale-110': currentStep === st.num,
                                'bg-emerald-600 text-white': currentStep > st.num,
                                'bg-white text-slate-400 border-2 border-slate-300': currentStep < st.num
                            }">
                            <template x-if="currentStep > st.num">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </template>
                            <template x-if="currentStep <= st.num">
                                <span x-text="st.num"></span>
                            </template>
                        </div>
                        <!-- Step Label -->
                        <span class="text-[11px] mt-1.5 font-bold transition-colors text-center select-none"
                            :class="currentStep === st.num ? 'text-emerald-800' : (currentStep > st.num ? 'text-slate-800' : 'text-slate-400')"
                            x-text="st.title"></span>
                    </button>
                </template>
            </div>
        </div>

        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.showWarningAlert) {
                        window.showWarningAlert(
                            'Periksa Kembali Formulir',
                            '{{ $errors->first() }}',
                            'Tinjau Formulir'
                        );
                    }
                });
            </script>
        @endif

        <form action="{{ route('mitra.onboarding.store') }}" method="POST" enctype="multipart/form-data" novalidate class="space-y-4" @submit.prevent="submitForm($event)">
            @csrf

            <!-- STEP 1: Identitas Kependudukan & Dokumen KTP -->
            <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/70 flex items-center justify-center font-bold text-xs shrink-0">
                            1
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-xs">Identitas & Dokumen KTP</h3>
                            <p class="text-[11px] text-slate-500">Wajib sesuai dengan e-KTP fisik asli Anda.</p>
                        </div>
                    </div>

                    <!-- Input NIK & Nomor WhatsApp/HP -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                <span>NIK <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="nik" maxlength="16" x-model="nik" @input="nik = (nik || '').replace(/[^0-9]/g, '')" inputmode="numeric" pattern="[0-9]*" required placeholder="16 digit nomor NIK di KTP"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                            @error('nik') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                <span>Nomor WhatsApp/HP <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="tel" name="phone" x-model="phone" @input="phone = (phone || '').replace(/[^0-9]/g, '')" inputmode="numeric" pattern="[0-9]*" required placeholder="Contoh: 081234567890"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                            @error('phone') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Upload Cards: Foto e-KTP & Selfie -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <!-- Foto e-KTP Asli -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-900">
                                    Foto e-KTP Asli <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">e-KTP fisik jelas</span>
                            </div>

                            <div class="relative w-full min-h-[175px] rounded-xl border-2 border-dashed border-slate-300 hover:border-emerald-600 bg-slate-50/70 transition overflow-hidden flex flex-col justify-center items-center p-4">
                                <!-- Empty State -->
                                <div id="ktp_empty_state" 
                                     class="flex flex-col items-center justify-center text-center select-none w-full {{ $partnerProfile?->ktp_path ? 'hidden' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/80 text-emerald-700 flex items-center justify-center mb-2 shadow-2xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">
                                        Unggah Foto e-KTP
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 mb-3">
                                        Foto langsung dengan bingkai atau pilih berkas
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                onclick="openLiveCamera('ktp')" 
                                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>Kamera</span>
                                        </button>
                                        <button type="button" 
                                                onclick="document.getElementById('ktp_file').click()" 
                                                class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span>Pilih File</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Filled State (Image Preview) -->
                                <div id="ktp_filled_state" class="relative w-full h-44 {{ $partnerProfile?->ktp_path ? '' : 'hidden' }}">
                                    <div class="w-full h-full flex items-center justify-center p-2">
                                        <img id="ktp_preview_img" 
                                             src="{{ $partnerProfile?->ktp_path ? Storage::url($partnerProfile->ktp_path) : '' }}" 
                                             class="w-full h-full object-contain rounded-lg" 
                                             alt="Preview e-KTP">
                                    </div>

                                    <!-- Floating Actions Overlay -->
                                    <div class="absolute top-2 right-2 flex items-center gap-1.5">
                                        <button type="button" 
                                                onclick="openLiveCamera('ktp')" 
                                                class="px-2 py-1 bg-white/95 hover:bg-white text-slate-800 text-[11px] font-medium rounded-lg shadow-2xs border border-slate-200 backdrop-blur-xs flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            </svg>
                                            <span>Foto Ulang</span>
                                        </button>
                                        <button type="button" 
                                                onclick="document.getElementById('ktp_file').click()" 
                                                class="px-2 py-1 bg-white/95 hover:bg-white text-slate-800 text-[11px] font-medium rounded-lg shadow-2xs border border-slate-200 backdrop-blur-xs flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span>Ganti File</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Compression & Status Badges -->
                            <div class="flex items-center justify-between min-h-[18px]">
                                <div id="ktp_compress_status" class="hidden"></div>
                                @error('ktp_file') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                                @if ($partnerProfile?->ktp_path)
                                    <p id="ktp_saved_badge" class="text-[11px] text-emerald-700 font-medium">✓ File tersimpan</p>
                                @endif
                            </div>

                            <!-- Hidden Inputs -->
                            <input type="file" id="ktp_file" name="ktp_file" accept=".jpg,.jpeg,.png,.pdf" class="hidden">
                        </div>

                        <!-- Foto Memegang KTP (Selfie) -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-900">
                                    Foto Memegang KTP <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">Wajah & KTP satu bingkai</span>
                            </div>

                            <div class="relative w-full min-h-[175px] rounded-xl border-2 border-dashed border-slate-300 hover:border-emerald-600 bg-slate-50/70 transition overflow-hidden flex flex-col justify-center items-center p-4">
                                <!-- Empty State -->
                                <div id="selfie_empty_state" 
                                     class="flex flex-col items-center justify-center text-center select-none w-full {{ $partnerProfile?->selfie_path ? 'hidden' : '' }}">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/80 text-emerald-700 flex items-center justify-center mb-2 shadow-2xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">
                                        Unggah Foto Selfie KTP
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 mb-3">
                                        Foto langsung dengan bingkai atau pilih berkas
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                onclick="openLiveCamera('selfie')" 
                                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>Kamera</span>
                                        </button>
                                        <button type="button" 
                                                onclick="document.getElementById('selfie_file').click()" 
                                                class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span>Pilih File</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Filled State (Image Preview) -->
                                <div id="selfie_filled_state" class="relative w-full h-44 {{ $partnerProfile?->selfie_path ? '' : 'hidden' }}">
                                    <div class="w-full h-full flex items-center justify-center p-2">
                                        <img id="selfie_preview_img" 
                                             src="{{ $partnerProfile?->selfie_path ? Storage::url($partnerProfile->selfie_path) : '' }}" 
                                             class="w-full h-full object-contain rounded-lg" 
                                             alt="Preview Selfie KTP">
                                    </div>

                                    <!-- Floating Actions Overlay -->
                                    <div class="absolute top-2 right-2 flex items-center gap-1.5">
                                        <button type="button" 
                                                onclick="openLiveCamera('selfie')" 
                                                class="px-2 py-1 bg-white/95 hover:bg-white text-slate-800 text-[11px] font-medium rounded-lg shadow-2xs border border-slate-200 backdrop-blur-xs flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            </svg>
                                            <span>Foto Ulang</span>
                                        </button>
                                        <button type="button" 
                                                onclick="document.getElementById('selfie_file').click()" 
                                                class="px-2 py-1 bg-white/95 hover:bg-white text-slate-800 text-[11px] font-medium rounded-lg shadow-2xs border border-slate-200 backdrop-blur-xs flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span>Ganti File</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Compression & Status Badges -->
                            <div class="flex items-center justify-between min-h-[18px]">
                                <div id="selfie_compress_status" class="hidden"></div>
                                @error('selfie_file') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                                @if ($partnerProfile?->selfie_path)
                                    <p id="selfie_saved_badge" class="text-[11px] text-emerald-700 font-medium">✓ File tersimpan</p>
                                @endif
                            </div>

                            <!-- Hidden Inputs -->
                            <input type="file" id="selfie_file" name="selfie_file" accept=".jpg,.jpeg,.png,.pdf" class="hidden">
                        </div>
                    </div>

                    <!-- Dokumen Pendukung SKCK (Opsional) -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-slate-900">
                                Dokumen SKCK <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <span class="text-[11px] text-slate-500">PDF, JPG, atau PNG (Maks. 5MB)</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-100/60 transition cursor-pointer"
                             onclick="document.getElementById('skck_file').click()">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200/80 text-slate-500 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-slate-800 truncate" x-text="skckFileName || '{{ $partnerProfile?->skck_path ? basename($partnerProfile->skck_path) : 'Belum ada dokumen dipilih' }}'"></p>
                                    @if ($partnerProfile?->skck_path)
                                        <span class="text-[10px] text-emerald-700 font-medium block">✓ Dokumen SKCK tersimpan</span>
                                    @endif
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="event.stopPropagation(); document.getElementById('skck_file').click()" 
                                    class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium rounded-lg transition cursor-pointer shrink-0">
                                <span>Pilih Dokumen</span>
                            </button>
                        </div>
                        @error('skck_file') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror

                        <!-- Hidden Inputs for SKCK -->
                        <input type="file" id="skck_file" name="skck_file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" @change="skckFileName = $event.target.files[0]?.name || ''; persistDraft();">
                    </div>
                </div>

                <!-- Navigation Button Step 1 -->
                <div class="pt-2">
                    <button type="button" @click="nextStep()"
                        class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Lanjut ke Wilayah Layanan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Lokasi Domisili & Wilayah Layanan -->
            <div x-show="currentStep === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/70 flex items-center justify-center font-bold text-xs shrink-0">
                            2
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-xs">Wilayah Layanan & Domisili</h3>
                            <p class="text-[11px] text-slate-500">Pilih wilayah layanan aktif dan tentukan titik koordinat domisili Anda.</p>
                        </div>
                    </div>

                    <!-- Input Hidden region_id & secondary_region_id -->
                    <input type="hidden" name="region_id" id="region_id"
                        x-effect="$el.value = selectedRegionId">
                    <input type="hidden" name="secondary_region_id" id="secondary_region_id" value="">

                    <!-- Pilih & Cari Wilayah Layanan -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <label for="search_city" class="font-semibold text-slate-700">Wilayah Operasional Layanan</label>
                            <button type="button" x-show="selectedRegionId" @click="clearSelectedRegion()" class="text-rose-600 hover:text-rose-700 text-xs font-medium cursor-pointer">
                                Reset Pilihan
                            </button>
                        </div>

                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" id="search_city" x-model="searchQuery" 
                                placeholder="Cari nama kota layanan..."
                                class="w-full min-h-[42px] text-xs rounded-xl border border-slate-200 bg-white focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 placeholder:text-slate-400 pl-10 pr-9 py-2.5 transition">
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center" x-show="searchQuery">
                                <button type="button" @click="searchQuery = ''" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 text-xs rounded-full hover:bg-slate-100 transition cursor-pointer" title="Hapus pencarian">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <!-- Grid Chips Kota -->
                        <div class="max-h-56 overflow-y-auto p-2 bg-slate-50/80 rounded-xl border border-slate-200/90 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="r in filteredRegions" :key="r.id">
                                <div @click="toggleRegion(r)"
                                     class="min-h-[44px] p-2.5 rounded-xl border cursor-pointer select-none transition flex items-center justify-between text-xs"
                                     :class="{
                                         'bg-emerald-700 text-white font-semibold border-emerald-700 shadow-xs': selectedRegionId == r.id,
                                         'bg-white text-slate-800 border-slate-200 hover:border-emerald-300': selectedRegionId != r.id
                                     }">
                                    <div class="truncate mr-2">
                                        <span x-text="r.name" class="block truncate"></span>
                                        <span class="text-[11px] block font-normal" 
                                              :class="selectedRegionId == r.id ? 'text-emerald-100' : 'text-slate-500'"
                                              x-text="r.timezone || 'WIB'"></span>
                                    </div>
                                    <div class="shrink-0 flex items-center">
                                        <template x-if="selectedRegionId == r.id">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] bg-white text-emerald-800 font-bold uppercase tracking-wider shadow-xs">
                                                Terpilih
                                            </span>
                                        </template>
                                        <template x-if="selectedRegionId != r.id">
                                            <span class="text-sm font-bold text-slate-400">+</span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredRegions.length === 0 && regions.length > 0" class="col-span-full py-4 text-center text-xs text-slate-500">
                                Tidak ada kota layanan yang sesuai pencarian "<span x-text="searchQuery"></span>".
                            </div>

                            <div x-show="regions.length === 0" class="col-span-full py-4 text-center text-xs text-amber-900 bg-amber-50 rounded-xl border border-amber-200">
                                Saat ini belum ada kota layanan yang berstatus aktif. Silakan hubungi admin.
                            </div>
                        </div>

                        <!-- Status Pemilihan Kota (Pills) -->
                        <div x-show="selectedRegionId" class="flex flex-wrap items-center gap-2 pt-0.5 text-xs">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200/80 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span class="text-slate-500 text-[11px]">Kota Terpilih:</span>
                                <span class="font-semibold text-slate-800" x-text="getSelectedRegionName()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Peta Titik Domisili -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700">
                                Titik Lokasi Domisili
                            </label>
                            <span class="text-[11px] text-slate-500" x-show="selectedRegionId">
                                Sesuaikan pin dengan alamat tinggal
                            </span>
                        </div>

                        <div id="map" class="w-full h-60 rounded-xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>
                        
                        <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Klik peta atau geser pin untuk menentukan koordinat domisili Anda.</span>
                        </p>
                    </div>

                    <!-- Hidden Coordinates -->
                    <input type="hidden" id="lat" name="latitude" value="{{ old('latitude', '-6.9175') }}">
                    <input type="hidden" id="lng" name="longitude" value="{{ old('longitude', '107.6191') }}">
                    @error('latitude') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap Domisili <span class="text-rose-500">*</span></label>
                            <span id="geo-status" class="text-[11px] text-emerald-700 font-medium">Otomatis terisi saat pin peta dipindahkan</span>
                        </div>
                        <textarea id="address_text" name="address_text" x-model="addressText" rows="3" required placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('address_text', $partnerProfile?->address_text) }}</textarea>
                        @error('address_text') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Navigation Buttons Step 2 -->
                <div class="pt-2 grid grid-cols-2 gap-3">
                    <button type="button" @click="prevStep()"
                        class="w-full min-h-[44px] py-2.5 px-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="nextStep()"
                        class="w-full min-h-[44px] py-2.5 px-3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Lanjut ke Rekening</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Rekening Bank Pencairan -->
            <div x-show="currentStep === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/70 flex items-center justify-center font-bold text-xs shrink-0">
                            3
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-xs">Rekening Bank Pencairan</h3>
                            <p class="text-[11px] text-slate-500">Nama pemilik rekening wajib sesuai dengan identitas e-KTP Anda.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                <span>Nama Bank <span class="text-rose-500">*</span></span>
                            </label>
                            <select name="bank_name" x-model="bankName" required
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:border-emerald-600 focus:ring-emerald-600 transition cursor-pointer">
                                <option value="">-- Pilih Bank --</option>
                                @foreach (\App\Models\Bank::bank()->active()->orderBy('sort_order')->orderBy('name')->get() as $bank)
                                    <option value="{{ $bank->name }}">{{ $bank->name }} ({{ strtoupper($bank->code) }})</option>
                                @endforeach
                            </select>
                            @error('bank_name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                <span>Nomor Rekening <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="account_number" x-model="accountNumber" @input="accountNumber = (accountNumber || '').replace(/[^0-9]/g, '')" inputmode="numeric" pattern="[0-9]*" required placeholder="Contoh: 1234567890"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                            @error('account_number') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                <span>Nama Pemilik <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="account_holder_name" x-model="accountHolderName" required placeholder="Wajib sesuai e-KTP"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                            @error('account_holder_name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons Step 3 -->
                <div class="pt-2 grid grid-cols-2 gap-3">
                    <button type="button" @click="prevStep()"
                        class="w-full min-h-[44px] py-2.5 px-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="nextStep()"
                        class="w-full min-h-[44px] py-2.5 px-3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Lanjut ke Persetujuan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Konfirmasi Data & Persetujuan Kemitraan -->
            <div x-show="currentStep === 4" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <!-- Step Header -->
                    <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs shrink-0">
                            4
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Konfirmasi & Persetujuan</h3>
                            <p class="text-xs text-slate-500">Periksa kembali data pendaftaran Anda sebelum dikirim.</p>
                        </div>
                    </div>

                    <!-- Clean Structured Data Summary -->
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 divide-y divide-slate-100 text-xs">
                        <div class="px-3.5 py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">NIK</span>
                            <span class="font-mono font-semibold text-slate-900" x-text="nik || '-'"></span>
                        </div>
                        <div class="px-3.5 py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">No. WhatsApp</span>
                            <span class="font-mono font-semibold text-slate-900" x-text="phone || '-'"></span>
                        </div>
                        <div class="px-3.5 py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Wilayah Layanan</span>
                            <span class="font-semibold text-emerald-800" x-text="getSelectedRegionName()"></span>
                        </div>
                        <div class="px-3.5 py-2.5 space-y-1">
                            <span class="text-slate-500 block text-[11px]">Alamat Domisili</span>
                            <p class="text-slate-800 leading-relaxed font-normal" x-text="addressText || '-'"></p>
                        </div>
                        <div class="px-3.5 py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Rekening Pencairan</span>
                            <span class="font-medium text-slate-900 text-right">
                                <span x-text="bankName"></span> · <span class="font-mono" x-text="accountNumber"></span><br>
                                <span class="text-slate-500 text-[11px] font-normal">a.n <span x-text="accountHolderName"></span></span>
                            </span>
                        </div>
                    </div>

                    <!-- SOP Document Link & Agreement Checkbox -->
                    <div class="space-y-3 pt-1">
                        <button type="button" @click="openSopModal()"
                            class="w-full h-[64px] min-h-[64px] max-h-[64px] text-left px-3.5 py-3 rounded-xl border transition flex items-center justify-between gap-3 group cursor-pointer"
                            :class="sopAgreed ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50 hover:bg-slate-100/70'">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition"
                                    :class="sopAgreed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200/70 text-slate-600 group-hover:text-slate-800'">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">Ketentuan & SOP Kemitraan</h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5" x-text="sopAgreed ? 'Sudah dibaca dan disetujui' : 'Ketuk untuk membaca dokumen'"></p>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center justify-end text-xs font-semibold min-w-[76px]"
                                :class="sopAgreed ? 'text-emerald-700' : 'text-slate-600 group-hover:text-slate-900'">
                                <span class="text-[11px] whitespace-nowrap" x-text="sopAgreed ? 'Baca Ulang' : 'Buka Dokumen'"></span>
                                <svg class="w-4 h-4 ml-0.5 group-hover:translate-x-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </button>

                        <label class="flex items-start gap-3 cursor-pointer pt-1 min-h-[44px]">
                            <input type="checkbox" name="sop_agreement" id="sop_agreement" value="1" x-model="sopAgreed" required
                                @click="handleCheckboxClick($event)"
                                class="mt-0.5 w-4.5 h-4.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600 cursor-pointer shrink-0">
                            <span class="text-xs text-slate-700 leading-relaxed select-none">
                                Saya menyatakan data di atas sudah benar dan menyetujui seluruh ketentuan serta SOP kemitraan Pijat.In.
                            </span>
                        </label>
                        @error('sop_agreement') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Navigation Buttons Step 4 -->
                <div class="pt-2 grid grid-cols-2 gap-3">
                    <button type="button" @click="prevStep()"
                        class="w-full min-h-[44px] py-2.5 px-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="submit"
                        class="w-full min-h-[44px] py-2.5 px-3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition inline-flex items-center justify-center gap-2 cursor-pointer">
                        <span>Kirim Berkas</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>

        <!-- ==================== MODAL DOKUMEN SOP & KODE ETIK (SCROLL TO UNLOCK) ==================== -->
        <div x-show="showSopModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4" role="dialog" aria-modal="true" style="display: none;" @keydown.escape.window="closeSopModal()">
            <!-- Backdrop Blur -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="closeSopModal()"></div>

            <div class="relative z-10 transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-md border border-slate-200/90 flex flex-col h-[82vh] max-h-[640px]">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-4 py-3.5 border-b border-slate-100 bg-slate-50/70 shrink-0">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Ketentuan & SOP Kemitraan</h3>
                            <p class="text-[11px] text-slate-500">Standar operasional dan kode etik resmi Pijat.In</p>
                        </div>
                    </div>
                    <button type="button" @click="closeSopModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer text-sm">
                        ✕
                    </button>
                </div>

                <!-- Scrollable Document Content -->
                <div id="sop_content_scroll" @scroll="handleSopScroll($event)" class="overflow-y-auto px-5 py-4 text-xs text-slate-700 flex-1 min-h-0 overscroll-contain leading-relaxed divide-y divide-slate-100">
                    <!-- Intro -->
                    <div class="pb-3 text-slate-600 text-xs leading-relaxed">
                        Pijat.In berkomitmen menyediakan layanan pijat kesehatan keluarga yang aman, bersih, dan profesional. Harap baca dan patuhi ketentuan berikut sebelum mulai menerima pesanan:
                    </div>

                    <!-- 1 -->
                    <div class="py-3.5 space-y-1">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">1</span>
                            <span>Layanan Khusus Sesama Jenis</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Terapis pria hanya melayani pelanggan pria, dan terapis wanita hanya melayani pelanggan wanita. Pesanan lintas gender dilarang keras dan berakibat pemutusan kemitraan.
                        </p>
                    </div>

                    <!-- 2 -->
                    <div class="py-3.5 space-y-1">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-rose-50 text-rose-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">2</span>
                            <span>Nol Toleransi Tindakan Asusila</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Layanan murni untuk kesehatan dan relaksasi. Segala bentuk pornografi, asusila, atau pelecehan akan langsung dilaporkan ke pihak kepolisian.
                        </p>
                    </div>

                    <!-- 3 -->
                    <div class="py-3.5 space-y-1">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">3</span>
                            <span>Higienitas & Perlengkapan Kerja</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Wajib berpakaian rapi dan bersih, mencuci tangan sebelum & sesudah sesi, serta membawa minyak pijat resmi dan alas kain yang higienis.
                        </p>
                    </div>

                    <!-- 4 -->
                    <div class="py-3.5 space-y-1">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">4</span>
                            <span>Transparansi Tarif</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Tarif resmi sudah tertera di aplikasi. Dilarang meminta tips secara paksa atau mengarahkan transaksi di luar sistem Pijat.In.
                        </p>
                    </div>

                    <!-- 5 -->
                    <div class="py-3.5 space-y-1">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">5</span>
                            <span>Ketepatan Waktu</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Hadir tepat waktu di lokasi pelanggan. Jika terkendala macet atau hambatan mendesak, segera kabari pelanggan lewat aplikasi.
                        </p>
                    </div>

                    <!-- 6 -->
                    <div class="py-3.5 space-y-1">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">6</span>
                            <span>Kesesuaian Rekening Bank</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Pencairan komisi hanya dapat dikirim ke rekening bank atas nama terapis yang sesuai dengan e-KTP terdaftar.
                        </p>
                    </div>

                    <!-- 7 -->
                    <div class="py-3.5 space-y-1 pb-10">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px] font-bold shrink-0">7</span>
                            <span>Kerahasiaan Data Pelanggan</span>
                        </h4>
                        <p class="text-slate-600 pl-7 leading-relaxed">
                            Alamat rumah, nomor telepon, dan data pelanggan bersifat rahasia. Dilarang keras menyebarkan data pelanggan ke pihak mana pun.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer Controls -->
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/90 shrink-0 space-y-2">
                    <!-- Scroll Status Reminder (Fixed Height to prevent layout shift) -->
                    <div class="h-6 flex items-center justify-center text-[11px]">
                        <span x-show="!hasScrolledToBottom" class="text-amber-800 flex items-center gap-1.5 font-medium">
                            <svg class="w-3.5 h-3.5 animate-bounce text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span>Gulir ke baris paling bawah untuk menyetujui</span>
                        </span>
                        <span x-show="hasScrolledToBottom" class="text-emerald-700 flex items-center gap-1.5 font-medium" style="display: none;">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Dokumen telah selesai dibaca</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" @click="closeSopModal()"
                            class="w-full min-h-[42px] py-2 px-3 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition cursor-pointer">
                            Tutup
                        </button>

                        <button type="button"
                            :disabled="!hasScrolledToBottom"
                            @click="agreeSopFromModal()"
                            class="w-full min-h-[42px] py-2 px-3 text-xs font-bold rounded-xl transition flex items-center justify-center space-x-1.5"
                            :class="hasScrolledToBottom 
                                ? 'bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs cursor-pointer' 
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="hasScrolledToBottom ? 'Saya Pahami & Setujui' : 'Gulir ke Bawah'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>



    </div>

    <!-- Script Leaflet Interactive Marker Pin & Region Auto-Centering & Multi-Step Wizard -->
    <script>
        let map = null;
        let marker = null;
        let regionPolygonLayer = null;

        // Reusable SweetAlert2 notification helper (compact, matches admin modal)
        function showWarningAlert(title, text, confirmText = 'Mengerti', onConfirm = null) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: text,
                    confirmButtonText: confirmText,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-admin-theme',
                        confirmButton: 'swal2-confirm'
                    }
                }).then(() => {
                    if (onConfirm) onConfirm();
                });
            } else {
                alert(title + ':\n' + text);
                if (onConfirm) onConfirm();
            }
        }
        window.showWarningAlert = showWarningAlert;

        // Spatial Boundary Checking Helpers (Point-in-Polygon via Ray Casting)
        function isPointInRing(lng, lat, ring) {
            let inside = false;
            for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                const xi = ring[i][0], yi = ring[i][1];
                const xj = ring[j][0], yj = ring[j][1];
                const intersect = ((yi > lat) !== (yj > lat))
                    && (lng < (xj - xi) * (lat - yi) / (yj - yi) + xi);
                if (intersect) inside = !inside;
            }
            return inside;
        }

        function isPointInPolygonCoords(lng, lat, polyCoords) {
            if (!polyCoords || polyCoords.length === 0) return false;
            if (!isPointInRing(lng, lat, polyCoords[0])) return false;
            for (let i = 1; i < polyCoords.length; i++) {
                if (isPointInRing(lng, lat, polyCoords[i])) return false;
            }
            return true;
        }

        function isCoordinateInsideGeoJSON(lat, lng, geojson) {
            if (!geojson) return true;
            if (geojson.type === 'Feature') geojson = geojson.geometry;
            if (!geojson || !geojson.type || !geojson.coordinates) return true;
            if (geojson.type === 'Polygon') return isPointInPolygonCoords(lng, lat, geojson.coordinates);
            if (geojson.type === 'MultiPolygon') return geojson.coordinates.some(poly => isPointInPolygonCoords(lng, lat, poly));
            return true;
        }
        window.isCoordinateInsideGeoJSON = isCoordinateInsideGeoJSON;

        function showOutsideBoundaryAlert(regionName) {
            showWarningAlert(
                'Di Luar Batas Wilayah',
                `Titik lokasi yang Anda pilih berada di luar batas wilayah operasional ${regionName || 'terpilih'}. Silakan tentukan titik di dalam area hijau.`,
                'Pilih Titik di Dalam Wilayah'
            );
        }
        window.showOutsideBoundaryAlert = showOutsideBoundaryAlert;

        const ONBOARDING_DRAFT_KEY = 'mitra_onboarding_draft_{{ $user->id }}';

        function getOnboardingDraft() {
            try {
                const raw = localStorage.getItem(ONBOARDING_DRAFT_KEY);
                return raw ? JSON.parse(raw) : null;
            } catch (e) {
                return null;
            }
        }
        window.getOnboardingDraft = getOnboardingDraft;

        function saveOnboardingDraft(data) {
            try {
                localStorage.setItem(ONBOARDING_DRAFT_KEY, JSON.stringify(data));
            } catch (e) {
                try {
                    const fallback = { ...data, ktpDataUrl: null, selfieDataUrl: null };
                    localStorage.setItem(ONBOARDING_DRAFT_KEY, JSON.stringify(fallback));
                } catch (err) {}
            }
        }
        window.saveOnboardingDraft = saveOnboardingDraft;

        function clearOnboardingDraft() {
            try {
                localStorage.removeItem(ONBOARDING_DRAFT_KEY);
            } catch (e) {}
        }
        window.clearOnboardingDraft = clearOnboardingDraft;

        function dataURLtoFile(dataurl, filename) {
            if (!dataurl || typeof dataurl !== 'string' || !dataurl.includes(',')) return null;
            try {
                const arr = dataurl.split(',');
                const mimeMatch = arr[0].match(/:(.*?);/);
                const mime = mimeMatch ? mimeMatch[1] : 'image/jpeg';
                const bstr = atob(arr[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename, { type: mime });
            } catch (e) {
                return null;
            }
        }
        window.dataURLtoFile = dataURLtoFile;

        window.savePhotoDraft = function(prefix, dataUrl, fileName) {
            const draft = getOnboardingDraft() || {};
            if (prefix === 'ktp') {
                draft.ktpDataUrl = dataUrl;
                draft.ktpFileName = fileName;
            } else if (prefix === 'selfie') {
                draft.selfieDataUrl = dataUrl;
                draft.selfieFileName = fileName;
            }
            saveOnboardingDraft(draft);
        };

        function onboardingWizard() {
            const hasServerErrors = {{ $errors->any() ? 'true' : 'false' }};
            const draft = !hasServerErrors ? getOnboardingDraft() : null;

            let initStep = {{ $initialStep }};
            if (!hasServerErrors && draft && draft.currentStep && draft.currentStep >= 1 && draft.currentStep <= 4) {
                initStep = draft.currentStep;
            }

            return {
                currentStep: initStep,
                totalSteps: 4,
                steps: [
                    { num: 1, title: 'Identitas' },
                    { num: 2, title: 'Wilayah' },
                    { num: 3, title: 'Rekening' },
                    { num: 4, title: 'Persetujuan' },
                ],
                nik: @js(old('nik', $user->nik ?? '')) || (draft?.nik ?? ''),
                phone: @js(old('phone', $user->phone ?? '')) || (draft?.phone ?? ''),
                searchQuery: '',
                selectedRegionId: @js(old('region_id', $partnerProfile?->region_id ?? '')) || (draft?.selectedRegionId ?? ''),
                secondaryRegionId: @js(old('secondary_region_id', $partnerProfile?->secondary_region_id ?? '')) || (draft?.secondaryRegionId ?? ''),
                addressText: @js(old('address_text', $partnerProfile?->address_text ?? '')) || (draft?.addressText ?? ''),
                regions: @js($activeRegions),
                bankName: @js(old('bank_name', $bankAccount?->bank_name ?? '')) || (draft?.bankName ?? 'BCA'),
                accountNumber: @js(old('account_number', $bankAccount?->account_number ?? '')) || (draft?.accountNumber ?? ''),
                accountHolderName: @js(old('account_holder_name', $bankAccount?->account_holder_name ?? '')) || (draft?.accountHolderName ?? @js($user->name)),
                hasExistingKtp: {{ $partnerProfile?->ktp_path ? 'true' : 'false' }},
                hasExistingSelfie: {{ $partnerProfile?->selfie_path ? 'true' : 'false' }},
                showSopModal: false,
                hasScrolledToBottom: Boolean(draft?.hasScrolledToBottom || draft?.sopAgreed),
                sopAgreed: {{ old('sop_agreement') ? 'true' : 'false' }} || Boolean(draft?.sopAgreed),
                skckFileName: draft?.skckFileName || '',

                persistDraft() {
                    const lat = parseFloat(document.getElementById('lat')?.value) || null;
                    const lng = parseFloat(document.getElementById('lng')?.value) || null;
                    const currentDraft = getOnboardingDraft() || {};

                    const updated = {
                        ...currentDraft,
                        currentStep: this.currentStep,
                        nik: this.nik,
                        phone: this.phone,
                        selectedRegionId: this.selectedRegionId,
                        secondaryRegionId: this.secondaryRegionId,
                        addressText: this.addressText,
                        lat: lat,
                        lng: lng,
                        bankName: this.bankName,
                        accountNumber: this.accountNumber,
                        accountHolderName: this.accountHolderName,
                        sopAgreed: this.sopAgreed,
                        hasScrolledToBottom: this.hasScrolledToBottom,
                        skckFileName: this.skckFileName
                    };
                    saveOnboardingDraft(updated);
                },

                openSopModal() {
                    this.showSopModal = true;
                    history.pushState({ modal: 'sop' }, '');
                    this.$nextTick(() => {
                        const scrollEl = document.getElementById('sop_content_scroll');
                        if (scrollEl) {
                            scrollEl.scrollTop = 0;
                            if (scrollEl.scrollHeight <= scrollEl.clientHeight + 15) {
                                this.hasScrolledToBottom = true;
                                this.persistDraft();
                            }
                        }
                    });
                },

                closeSopModal(fromPopstate = false) {
                    const wasOpen = Boolean(this.showSopModal);
                    this.showSopModal = false;
                    if (wasOpen && !fromPopstate && history.state && history.state.modal === 'sop') {
                        history.back();
                    }
                },

                handleSopScroll(e) {
                    const el = e.target;
                    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 25) {
                        this.hasScrolledToBottom = true;
                        this.persistDraft();
                    }
                },

                agreeSopFromModal() {
                    if (this.hasScrolledToBottom) {
                        this.sopAgreed = true;
                        this.closeSopModal(false);
                        this.persistDraft();
                    }
                },

                handleCheckboxClick(e) {
                    if (!this.hasScrolledToBottom) {
                        e.preventDefault();
                        this.openSopModal();
                    }
                },

                initWizard() {
                    window.mitraWizard = this;

                    this.$watch('currentStep', (val) => {
                        this.onStepSwitched(val);
                        this.persistDraft();
                    });
                    this.$watch('nik', (val) => {
                        if (val && /[^0-9]/.test(val)) {
                            this.nik = val.replace(/[^0-9]/g, '');
                        }
                        this.persistDraft();
                    });
                    this.$watch('phone', (val) => {
                        if (val && /[^0-9]/.test(val)) {
                            this.phone = val.replace(/[^0-9]/g, '');
                        }
                        this.persistDraft();
                    });
                    this.$watch('selectedRegionId', () => { this.persistDraft(); });
                    this.$watch('secondaryRegionId', () => { this.persistDraft(); });
                    this.$watch('addressText', () => { this.persistDraft(); });
                    this.$watch('bankName', () => { this.persistDraft(); });
                    this.$watch('accountNumber', (val) => {
                        if (val && /[^0-9]/.test(val)) {
                            this.accountNumber = val.replace(/[^0-9]/g, '');
                        }
                        this.persistDraft();
                    });
                    this.$watch('accountHolderName', () => { this.persistDraft(); });
                    this.$watch('sopAgreed', () => { this.persistDraft(); });

                    if (this.currentStep === 2) {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (window.map) {
                                    window.map.invalidateSize();
                                }
                            }, 200);
                        });
                    }
                },

                get filteredRegions() {
                    if (!this.searchQuery.trim()) return this.regions;
                    const q = this.searchQuery.toLowerCase();
                    return this.regions.filter(r => r.name.toLowerCase().includes(q));
                },

                toggleRegion(region) {
                    if (this.selectedRegionId == region.id) {
                        this.selectedRegionId = '';
                        if (window.onMitraRegionCleared) {
                            window.onMitraRegionCleared();
                        }
                    } else {
                        this.selectedRegionId = region.id;
                        if (window.onMitraRegionSelected) {
                            window.onMitraRegionSelected(region);
                        }
                    }
                },

                clearSelectedRegion() {
                    this.selectedRegionId = '';
                    if (window.onMitraRegionCleared) {
                        window.onMitraRegionCleared();
                    }
                },

                getSelectedRegionName() {
                    const r = this.regions.find(x => x.id == this.selectedRegionId);
                    return r ? r.name : 'Belum dipilih';
                },

                goToStep(targetStep) {
                    if (targetStep < this.currentStep) {
                        this.currentStep = targetStep;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else if (targetStep > this.currentStep) {
                        if (this.validateStep(this.currentStep)) {
                            this.currentStep = targetStep;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    }
                },

                nextStep() {
                    if (this.validateStep(this.currentStep)) {
                        if (this.currentStep < this.totalSteps) {
                            this.currentStep++;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                onStepSwitched(stepNum) {
                    if (stepNum === 2) {
                        setTimeout(() => {
                            if (window.map) {
                                window.map.invalidateSize();
                            }
                        }, 150);
                    }
                },

                validateStep(stepNum) {
                    if (stepNum === 1) {
                        if (!this.nik || this.nik.trim().length !== 16) {
                            showWarningAlert(
                                'NIK Belum Sesuai',
                                'Mohon isi 16 digit NIK lengkap sesuai dengan e-KTP fisik Anda.',
                                'Perbaiki NIK',
                                () => { document.querySelector('input[name="nik"]')?.focus(); }
                            );
                            return false;
                        }
                        if (!this.phone || this.phone.trim().length < 9) {
                            showWarningAlert(
                                'Nomor WhatsApp/HP Kosong',
                                'Mohon masukkan nomor WhatsApp atau HP aktif untuk keperluan koordinasi kemitraan.',
                                'Isi Nomor HP',
                                () => { document.querySelector('input[name="phone"]')?.focus(); }
                            );
                            return false;
                        }
                        const ktpInput = document.getElementById('ktp_file');
                        const hasKtp = (ktpInput && ktpInput.files && ktpInput.files.length > 0) || this.hasExistingKtp;
                        if (!hasKtp) {
                            showWarningAlert(
                                'Foto e-KTP Wajib Dilampirkan',
                                'Silakan ambil foto e-KTP fisik Anda menggunakan kamera langsung atau pilih dari galeri.',
                                'Ambil Foto e-KTP'
                            );
                            return false;
                        }
                        const selfieInput = document.getElementById('selfie_file');
                        const hasSelfie = (selfieInput && selfieInput.files && selfieInput.files.length > 0) || this.hasExistingSelfie;
                        if (!hasSelfie) {
                            showWarningAlert(
                                'Foto Memegang KTP Wajib Dilampirkan',
                                'Silakan ambil foto selfie memegang e-KTP fisik menggunakan kamera depan atau pilih dari galeri.',
                                'Ambil Foto Selfie'
                            );
                            return false;
                        }
                        return true;
                    }

                    if (stepNum === 2) {
                        if (!this.selectedRegionId) {
                            showWarningAlert(
                                'Kota Layanan Belum Dipilih',
                                'Silakan pilih salah satu Kota / Wilayah Layanan yang tersedia pada daftar sebelum melanjutkan.',
                                'Pilih Kota Layanan'
                            );
                            return false;
                        }

                        const currentRegion = this.regions.find(r => r.id == this.selectedRegionId);
                        const lat = parseFloat(document.getElementById('lat').value);
                        const lng = parseFloat(document.getElementById('lng').value);

                        if (currentRegion && currentRegion.geojson) {
                            if (!isCoordinateInsideGeoJSON(lat, lng, currentRegion.geojson)) {
                                showOutsideBoundaryAlert(currentRegion.name);
                                return false;
                            }
                        }

                        const addr = document.getElementById('address_text')?.value?.trim();
                        if (!addr) {
                            showWarningAlert(
                                'Alamat Domisili Belum Lengkap',
                                'Mohon tentukan titik lokasi Anda di peta atau ketik alamat lengkap tempat tinggal Anda.',
                                'Lengkapi Alamat',
                                () => { document.getElementById('address_text')?.focus(); }
                            );
                            return false;
                        }
                        return true;
                    }

                    if (stepNum === 3) {
                        if (!this.bankName || !this.accountNumber || !this.accountHolderName) {
                            showWarningAlert(
                                'Rincian Rekening Belum Lengkap',
                                'Mohon lengkapi seluruh rincian rekening bank pencairan dana (Nama Bank, Nomor Rekening, dan Nama Pemilik).',
                                'Lengkapi Rekening'
                            );
                            return false;
                        }
                        return true;
                    }

                    return true;
                },

                submitForm(e) {
                    if (!this.sopAgreed) {
                        showWarningAlert(
                            'Persetujuan SOP Diperlukan',
                            'Anda wajib membuka dan membaca dokumen SOP & Kode Etik Kemitraan hingga baris terakhir serta menyetujuinya sebelum mengirim berkas pendaftaran.',
                            'Baca Dokumen SOP',
                            () => { this.openSopModal(); }
                        );
                        return;
                    }
                    clearOnboardingDraft();
                    e.target.submit();
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            const hasServerErrors = {{ $errors->any() ? 'true' : 'false' }};
            const draft = !hasServerErrors && window.getOnboardingDraft ? window.getOnboardingDraft() : null;

            let initialLat = parseFloat(document.getElementById('lat').value) || -6.9175;
            let initialLng = parseFloat(document.getElementById('lng').value) || 107.6191;

            if (!hasServerErrors && draft && draft.lat && draft.lng) {
                initialLat = parseFloat(draft.lat);
                initialLng = parseFloat(draft.lng);
                document.getElementById('lat').value = initialLat.toFixed(6);
                document.getElementById('lng').value = initialLng.toFixed(6);
            }

            map = L.map('map').setView([initialLat, initialLng], 12);
            window.map = map;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            let geocodeTimeout = null;
            function reverseGeocode(lat, lng) {
                const addressInput = document.getElementById('address_text');
                const geoStatus = document.getElementById('geo-status');
                if (!addressInput) return;

                if (geoStatus) {
                    geoStatus.textContent = 'Mendeteksi alamat...';
                }

                clearTimeout(geocodeTimeout);
                geocodeTimeout = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                addressInput.value = data.display_name;
                                addressInput.dispatchEvent(new Event('input'));
                                if (geoStatus) {
                                    geoStatus.textContent = '✓ Alamat diperbarui dari peta';
                                }
                            } else {
                                if (geoStatus) geoStatus.textContent = 'Otomatis terisi saat pin peta dipindahkan';
                            }
                        })
                        .catch(() => {
                            if (geoStatus) geoStatus.textContent = 'Otomatis terisi saat pin peta dipindahkan';
                        });
                }, 400);
            }

            function updateInputs(lat, lng) {
                document.getElementById('lat').value = lat.toFixed(6);
                document.getElementById('lng').value = lng.toFixed(6);
                reverseGeocode(lat, lng);
                if (window.mitraWizard) {
                    window.mitraWizard.persistDraft();
                }
            }

            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                if (window.currentSelectedRegion && window.currentSelectedRegion.geojson) {
                    const isInside = isCoordinateInsideGeoJSON(pos.lat, pos.lng, window.currentSelectedRegion.geojson);
                    if (!isInside) {
                        showOutsideBoundaryAlert(window.currentSelectedRegion.name);
                        if (window.lastValidLatLng) {
                            marker.setLatLng([window.lastValidLatLng.lat, window.lastValidLatLng.lng]);
                        }
                        return;
                    }
                }
                window.lastValidLatLng = { lat: pos.lat, lng: pos.lng };
                updateInputs(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                if (window.currentSelectedRegion && window.currentSelectedRegion.geojson) {
                    const isInside = isCoordinateInsideGeoJSON(e.latlng.lat, e.latlng.lng, window.currentSelectedRegion.geojson);
                    if (!isInside) {
                        showOutsideBoundaryAlert(window.currentSelectedRegion.name);
                        return;
                    }
                }
                window.lastValidLatLng = { lat: e.latlng.lat, lng: e.latlng.lng };
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            let secondaryPolygonLayer = null;

            // When a region is selected from the chips (Wilayah Utama)
            window.onMitraRegionSelected = function(region) {
                if (!region || !region.geojson) return;

                window.currentSelectedRegion = region;

                if (regionPolygonLayer) {
                    map.removeLayer(regionPolygonLayer);
                }

                regionPolygonLayer = L.geoJSON(region.geojson, {
                    style: {
                        color: '#059669',
                        fillColor: '#10b981',
                        fillOpacity: 0.25,
                        weight: 2
                    }
                }).addTo(map);

                const bounds = regionPolygonLayer.getBounds();
                map.fitBounds(bounds, { padding: [20, 20] });

                const center = regionPolygonLayer.getBounds().getCenter();
                window.lastValidLatLng = { lat: center.lat, lng: center.lng };
                marker.setLatLng(center);
                updateInputs(center.lat, center.lng);
            };

            window.onMitraRegionCleared = function() {
                window.currentSelectedRegion = null;
                if (regionPolygonLayer) {
                    map.removeLayer(regionPolygonLayer);
                    regionPolygonLayer = null;
                }
            };

            // Check if a region is pre-selected on load (e.g. after validation error / edit / draft)
            const activeRegions = @js($activeRegions);
            let preselectedId = @js(old('region_id', $partnerProfile?->region_id ?? ''));

            if (!preselectedId && !hasServerErrors && draft && draft.selectedRegionId) {
                preselectedId = draft.selectedRegionId;
            }

            if (preselectedId && activeRegions && activeRegions.length > 0) {
                const preselected = activeRegions.find(r => r.id == preselectedId);
                if (preselected) {
                    window.onMitraRegionSelected(preselected);
                }
            } else if (activeRegions && activeRegions.length === 1) {
                // If there's only 1 active region, auto select it for convenience
                const auto = activeRegions[0];
                document.getElementById('region_id').value = auto.id;
                window.onMitraRegionSelected(auto);
            }

            // Setup Photo Capture & Auto-Compression with Preview
            function setupPhotoUploader(prefix) {
                const mainInput = document.getElementById(prefix + '_file');
                const cameraInput = document.getElementById(prefix + '_camera_input');
                const galleryInput = document.getElementById(prefix + '_gallery_input');
                const emptyState = document.getElementById(prefix + '_empty_state');
                const filledState = document.getElementById(prefix + '_filled_state');
                const previewImg = document.getElementById(prefix + '_preview_img');
                const statusEl = document.getElementById(prefix + '_compress_status');

                // Restore photo from draft if present
                if (!hasServerErrors && draft) {
                    const photoDataUrl = prefix === 'ktp' ? draft.ktpDataUrl : draft.selfieDataUrl;
                    const photoFileName = prefix === 'ktp' ? draft.ktpFileName : draft.selfieFileName;

                    if (photoDataUrl && mainInput && mainInput.files.length === 0) {
                        const restoredFile = window.dataURLtoFile ? window.dataURLtoFile(photoDataUrl, photoFileName || (prefix + '.jpg')) : null;
                        if (restoredFile) {
                            const dt = new DataTransfer();
                            dt.items.add(restoredFile);
                            mainInput.files = dt.files;
                            if (previewImg) previewImg.src = photoDataUrl;
                            if (emptyState) emptyState.classList.add('hidden');
                            if (filledState) filledState.classList.remove('hidden');
                            if (statusEl) {
                                statusEl.innerHTML = `<span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 text-[10px] font-semibold border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>✓ Berkas dipulihkan dari draf</span>
                                </span>`;
                                statusEl.classList.remove('hidden');
                            }
                        }
                    }
                }

                function processFile(file) {
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        mainInput.files = dt.files;
                        if (statusEl) {
                            statusEl.innerHTML = `<span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-medium"><span>File dokumen terpilih: ${file.name}</span></span>`;
                            statusEl.classList.remove('hidden');
                        }
                        return;
                    }

                    if (statusEl) {
                        statusEl.innerHTML = `<span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium"><span>Mengompres foto...</span></span>`;
                        statusEl.classList.remove('hidden');
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        if (previewImg) {
                            previewImg.src = event.target.result;
                        }
                        if (emptyState) emptyState.classList.add('hidden');
                        if (filledState) filledState.classList.remove('hidden');

                        const img = new Image();
                        img.onload = function() {
                            const maxDim = 1280;
                            let width = img.width;
                            let height = img.height;

                            if (width > maxDim || height > maxDim) {
                                if (width >= height) {
                                    height = Math.round((height / width) * maxDim);
                                    width = maxDim;
                                } else {
                                    width = Math.round((width / height) * maxDim);
                                    height = maxDim;
                                }
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.fillStyle = '#FFFFFF';
                            ctx.fillRect(0, 0, width, height);
                            ctx.drawImage(img, 0, 0, width, height);

                            canvas.toBlob(function(blob) {
                                if (!blob) return;

                                const newSizeKb = Math.round(blob.size / 1024);
                                const compressedFile = new File([blob], (file.name || prefix).replace(/\.[^/.]+$/, "") + ".jpg", {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });

                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(compressedFile);
                                mainInput.files = dataTransfer.files;

                                const readerDraft = new FileReader();
                                readerDraft.onload = function(ev) {
                                    if (window.savePhotoDraft) {
                                        window.savePhotoDraft(prefix, ev.target.result, compressedFile.name);
                                    }
                                };
                                readerDraft.readAsDataURL(compressedFile);

                                if (statusEl) {
                                    statusEl.innerHTML = `<span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 text-[10px] font-semibold border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span>✓ Dioptimalkan: ${newSizeKb} KB</span>
                                    </span>`;
                                }
                            }, 'image/jpeg', 0.8);
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }

                if (cameraInput) {
                    cameraInput.addEventListener('change', function(e) {
                        if (e.target.files && e.target.files[0]) {
                            processFile(e.target.files[0]);
                        }
                    });
                }

                if (galleryInput) {
                    galleryInput.addEventListener('change', function(e) {
                        if (e.target.files && e.target.files[0]) {
                            processFile(e.target.files[0]);
                        }
                    });
                }

                if (mainInput) {
                    mainInput.addEventListener('change', function(e) {
                        if (e.target.files && e.target.files[0]) {
                            processFile(e.target.files[0]);
                        }
                    });
                }

                window['processPhoto_' + prefix] = processFile;
            }

            setupPhotoUploader('ktp');
            setupPhotoUploader('selfie');
        });

        // Live WebRTC Camera Stream Controller
        let currentCameraStream = null;
        let activePhotoPrefix = null;
        let capturedBlob = null;
        let isCameraModalOpen = false;

        async function openLiveCamera(prefix) {
            activePhotoPrefix = prefix;
            const modal = document.getElementById('camera_modal');
            const video = document.getElementById('camera_video');
            const loading = document.getElementById('camera_loading');
            const title = document.getElementById('camera_modal_title');
            const ktpOverlay = document.getElementById('ktp_camera_overlay');
            const selfieOverlay = document.getElementById('selfie_camera_overlay');
            const liveControls = document.getElementById('camera_live_controls');
            const reviewControls = document.getElementById('camera_review_controls');
            const snapshotPreview = document.getElementById('camera_snapshot_preview');

            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            liveControls.classList.remove('hidden');
            reviewControls.classList.add('hidden');
            snapshotPreview.classList.add('hidden');

            if (!isCameraModalOpen) {
                isCameraModalOpen = true;
                if (history.state && history.state.modal) {
                    history.replaceState({ modal: 'camera' }, '');
                } else {
                    history.pushState({ modal: 'camera' }, '');
                }
            }

            if (prefix === 'ktp') {
                title.textContent = 'Kamera: Foto e-KTP Asli';
                ktpOverlay.classList.remove('hidden');
                selfieOverlay.classList.add('hidden');
            } else {
                title.textContent = 'Kamera: Foto Selfie Memegang KTP';
                selfieOverlay.classList.remove('hidden');
                ktpOverlay.classList.add('hidden');
            }

            const facingMode = prefix === 'ktp' ? { ideal: 'environment' } : { ideal: 'user' };

            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Akses kamera WebRTC tidak tersedia.');
                }

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: facingMode,
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    },
                    audio: false
                });

                currentCameraStream = stream;
                video.srcObject = stream;
                await video.play();
                loading.classList.add('hidden');
            } catch (err) {
                loading.classList.add('hidden');
                console.warn('Gagal menyalakan kamera langsung:', err);
                closeCameraModal(false);
                showWarningAlert(
                    'Kamera Tidak Dapat Dinyalakan',
                    'Akses kamera langsung tidak dapat dinyalakan pada perangkat/browser ini. Anda dapat memilih berkas foto dari penyimpanan.',
                    'Pilih dari Penyimpanan',
                    () => {
                        const fallbackInput = document.getElementById(prefix + '_gallery_input');
                        if (fallbackInput) fallbackInput.click();
                    }
                );
            }
        }

        function takeSnapshot() {
            const video = document.getElementById('camera_video');
            const snapshotPreview = document.getElementById('camera_snapshot_preview');
            const snapshotImg = document.getElementById('camera_snapshot_img');
            const liveControls = document.getElementById('camera_live_controls');
            const reviewControls = document.getElementById('camera_review_controls');

            if (!video || !video.videoWidth) return;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                if (!blob) return;
                capturedBlob = blob;
                snapshotImg.src = URL.createObjectURL(blob);
                snapshotPreview.classList.remove('hidden');
                liveControls.classList.add('hidden');
                reviewControls.classList.remove('hidden');
            }, 'image/jpeg', 0.92);
        }

        function retakeSnapshot() {
            capturedBlob = null;
            document.getElementById('camera_snapshot_preview').classList.add('hidden');
            document.getElementById('camera_live_controls').classList.remove('hidden');
            document.getElementById('camera_review_controls').classList.add('hidden');
        }

        function confirmSnapshot() {
            if (!capturedBlob || !activePhotoPrefix) return;

            const file = new File([capturedBlob], activePhotoPrefix + '_live_' + Date.now() + '.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            if (window['processPhoto_' + activePhotoPrefix]) {
                window['processPhoto_' + activePhotoPrefix](file);
            }

            closeCameraModal(false);
        }

        function closeCameraModal(fromPopstate = false) {
            if (currentCameraStream) {
                currentCameraStream.getTracks().forEach(track => track.stop());
                currentCameraStream = null;
            }
            const video = document.getElementById('camera_video');
            if (video) video.srcObject = null;
            document.getElementById('camera_modal').classList.add('hidden');

            if (isCameraModalOpen) {
                isCameraModalOpen = false;
                if (!fromPopstate && history.state && history.state.modal === 'camera') {
                    history.back();
                }
            }
        }

        // Reset any leftover modal history state from previous reload
        if (history.state && history.state.modal) {
            history.replaceState(null, '');
        }

        // Intercept Android hardware/gesture back button so it closes open modals instead of navigating away
        window.addEventListener('popstate', function() {
            const cameraModal = document.getElementById('camera_modal');
            if (cameraModal && !cameraModal.classList.contains('hidden')) {
                closeCameraModal(true);
                return;
            }

            if (window.mitraWizard && window.mitraWizard.showSopModal) {
                window.mitraWizard.closeSopModal(true);
                return;
            }
        });
    </script>

    <!-- Live Camera Viewfinder Modal -->
    <div id="camera_modal" class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md hidden flex flex-col items-center justify-between p-4 sm:p-6" style="touch-action: none;">
        <!-- Modal Header -->
        <div class="w-full max-w-md flex items-center justify-between text-white py-1 z-10">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h3 id="camera_modal_title" class="text-xs font-bold uppercase tracking-wider text-emerald-300">Kamera Langsung</h3>
            </div>
            <button type="button" onclick="closeCameraModal()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Live Video Viewport -->
        <div class="relative w-full max-w-md flex-1 my-2 rounded-2xl overflow-hidden bg-black flex items-center justify-center border border-white/10 shadow-2xl">
            <!-- Video Stream -->
            <video id="camera_video" autoplay playsinline muted class="w-full h-full object-cover"></video>

            <!-- Guideline Overlay for KTP -->
            <div id="ktp_camera_overlay" class="absolute inset-0 pointer-events-none hidden flex items-center justify-center p-6">
                <div class="w-full aspect-[1.58/1] max-w-xs border-2 border-dashed border-emerald-400 rounded-2xl shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] relative">
                    <div class="absolute -top-7 left-0 right-0 text-center text-xs font-semibold text-emerald-300 drop-shadow">
                        Posisikan e-KTP di dalam kotak
                    </div>
                </div>
            </div>

            <!-- Guideline Overlay for Selfie -->
            <div id="selfie_camera_overlay" class="absolute inset-0 pointer-events-none hidden flex items-center justify-center p-6">
                <div class="w-60 h-72 border-2 border-dashed border-emerald-400 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.55)] relative">
                    <div class="absolute -top-7 left-0 right-0 text-center text-xs font-semibold text-emerald-300 drop-shadow">
                        Posisikan wajah & KTP di dalam area
                    </div>
                </div>
            </div>

            <!-- Snapshot Image Review -->
            <div id="camera_snapshot_preview" class="absolute inset-0 hidden bg-black flex items-center justify-center">
                <img id="camera_snapshot_img" class="w-full h-full object-contain" alt="Hasil Foto">
            </div>

            <!-- Loading State -->
            <div id="camera_loading" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center text-white space-y-3">
                <svg class="animate-spin w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <p class="text-xs text-slate-300 font-medium">Menyalakan kamera...</p>
            </div>
        </div>

        <!-- Modal Footer Controls -->
        <div class="w-full max-w-md py-3 flex items-center justify-center z-10">
            <!-- Live Shutter -->
            <div id="camera_live_controls" class="flex items-center justify-center w-full">
                <button type="button" onclick="takeSnapshot()" class="w-18 h-18 rounded-full bg-white ring-4 ring-emerald-500 ring-offset-4 ring-offset-slate-950 flex items-center justify-center shadow-lg active:scale-95 transition-transform cursor-pointer">
                    <div class="w-14 h-14 rounded-full border-2 border-slate-900 bg-white"></div>
                </button>
            </div>

            <!-- Review Actions -->
            <div id="camera_review_controls" class="hidden flex items-center justify-between w-full space-x-3">
                <button type="button" onclick="retakeSnapshot()" class="w-1/2 min-h-[44px] py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Foto Ulang</span>
                </button>
                <button type="button" onclick="confirmSnapshot()" class="w-1/2 min-h-[44px] py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Gunakan Foto</span>
                </button>
            </div>
        </div>
    </div>
</x-pwa-layout>
