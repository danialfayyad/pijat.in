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
        <div class="mb-1">
            <span class="inline-block text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                Pendaftaran Mitra Terapis
            </span>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight mt-1.5">
                Kelengkapan Berkas & Identitas
            </h2>
            <p class="text-xs text-slate-500">Ikuti 4 langkah mudah di bawah ini untuk memulai kemitraan Anda.</p>
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
                    <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                            1
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Identitas Kependudukan dan Foto Wajah</h3>
                            <p class="text-xs text-slate-500">Wajib sesuai dengan e-KTP fisik asli Anda.</p>
                        </div>
                    </div>

                    <!-- Input NIK & Nomor WhatsApp/HP -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 min-h-[32px] flex items-end mb-1">
                                <span>NIK <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="nik" maxlength="16" x-model="nik" @input="nik = (nik || '').replace(/[^0-9]/g, '')" inputmode="numeric" pattern="[0-9]*" required placeholder="16 digit nomor NIK di KTP"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                            @error('nik') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 min-h-[32px] flex items-end mb-1">
                                <span>Nomor WhatsApp/HP <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="tel" name="phone" x-model="phone" @input="phone = (phone || '').replace(/[^0-9]/g, '')" inputmode="numeric" pattern="[0-9]*" required placeholder="Contoh: 081234567890"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                            @error('phone') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Upload Cards: KYC Frame Gojek/DANA Style -->
                    <div class="space-y-4 pt-1">
                        <!-- Foto e-KTP Asli KYC Frame -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-900">
                                    Foto e-KTP Asli <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">e-KTP fisik jelas & tidak terpotong</span>
                            </div>

                            <!-- KYC Frame Box -->
                            <div class="relative w-full rounded-2xl border-2 border-dashed border-slate-300 hover:border-emerald-600 bg-slate-50/70 hover:bg-emerald-50/20 transition-all overflow-hidden group">
                                <!-- Empty State: Ketuk untuk Buka Kamera Langsung -->
                                <div id="ktp_empty_state" 
                                     onclick="openLiveCamera('ktp')" 
                                     class="py-8 px-4 flex flex-col items-center justify-center text-center cursor-pointer select-none {{ $partnerProfile?->ktp_path ? 'hidden' : '' }}">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-100/70 text-emerald-800 flex items-center justify-center mb-2.5 group-hover:scale-105 group-hover:bg-emerald-700 group-hover:text-white transition-all shadow-2xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-900 group-hover:text-emerald-800 transition">
                                        Buka Kamera Langsung
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Ketuk untuk menyalakan kamera dan foto e-KTP fisik
                                    </p>
                                    
                                    <button type="button" 
                                        onclick="event.stopPropagation(); document.getElementById('ktp_gallery_input').click()" 
                                        class="mt-3 inline-flex items-center space-x-1.5 text-xs text-emerald-700 hover:text-emerald-800 font-semibold cursor-pointer py-1 px-2.5 rounded-lg hover:bg-emerald-50 transition">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="underline underline-offset-2">atau pilih dari galeri / file</span>
                                    </button>
                                </div>

                                <!-- Filled State (Image Preview) -->
                                <div id="ktp_filled_state" class="relative {{ $partnerProfile?->ktp_path ? '' : 'hidden' }}">
                                    <div class="w-full h-44 bg-slate-900/5 flex items-center justify-center p-2">
                                        <img id="ktp_preview_img" 
                                             src="{{ $partnerProfile?->ktp_path ? Storage::url($partnerProfile->ktp_path) : '' }}" 
                                             class="w-full h-full object-contain rounded-xl" 
                                             alt="Preview e-KTP">
                                    </div>

                                    <!-- Floating Actions Overlay -->
                                    <div class="absolute top-2.5 right-2.5 flex items-center space-x-1.5">
                                        <button type="button" 
                                                onclick="openLiveCamera('ktp')" 
                                                class="min-h-[36px] px-3 py-1.5 bg-white/95 hover:bg-white text-slate-800 text-xs font-bold rounded-xl shadow-xs border border-slate-200 backdrop-blur-xs flex items-center space-x-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span>Foto Ulang</span>
                                        </button>
                                        <button type="button" 
                                                onclick="document.getElementById('ktp_gallery_input').click()" 
                                                class="min-h-[36px] px-2.5 py-1.5 bg-white/95 hover:bg-white text-slate-600 text-xs font-medium rounded-xl shadow-xs border border-slate-200 backdrop-blur-xs flex items-center cursor-pointer"
                                                title="Pilih dari galeri">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Compression & Status Badges -->
                            <div class="flex items-center justify-between min-h-[18px]">
                                <div id="ktp_compress_status" class="hidden"></div>
                                @error('ktp_file') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                                @if ($partnerProfile?->ktp_path)
                                    <p id="ktp_saved_badge" class="text-[11px] text-emerald-700 font-medium">✓ File tersimpan di server</p>
                                @endif
                            </div>

                            <!-- Hidden Inputs -->
                            <input type="file" id="ktp_file" name="ktp_file" accept="image/*,.pdf" class="hidden">
                            <input type="file" id="ktp_camera_input" accept="image/*" capture="environment" class="hidden">
                            <input type="file" id="ktp_gallery_input" accept="image/*,.pdf" class="hidden">
                        </div>

                        <!-- Foto Memegang KTP KYC Frame -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-900">
                                    Foto Memegang KTP <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-500">Wajah & KTP satu bingkai</span>
                            </div>

                            <!-- KYC Frame Box -->
                            <div class="relative w-full rounded-2xl border-2 border-dashed border-slate-300 hover:border-emerald-600 bg-slate-50/70 hover:bg-emerald-50/20 transition-all overflow-hidden group">
                                <!-- Empty State -->
                                <div id="selfie_empty_state" 
                                     onclick="openLiveCamera('selfie')" 
                                     class="py-8 px-4 flex flex-col items-center justify-center text-center cursor-pointer select-none {{ $partnerProfile?->selfie_path ? 'hidden' : '' }}">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-100/70 text-emerald-800 flex items-center justify-center mb-2.5 group-hover:scale-105 group-hover:bg-emerald-700 group-hover:text-white transition-all shadow-2xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-900 group-hover:text-emerald-800 transition">
                                        Buka Kamera Selfie
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Ketuk untuk menyalakan kamera depan & foto memegang KTP
                                    </p>
                                    
                                    <button type="button" 
                                        onclick="event.stopPropagation(); document.getElementById('selfie_gallery_input').click()" 
                                        class="mt-3 inline-flex items-center space-x-1.5 text-xs text-emerald-700 hover:text-emerald-800 font-semibold cursor-pointer py-1 px-2.5 rounded-lg hover:bg-emerald-50 transition">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="underline underline-offset-2">atau pilih dari galeri / file</span>
                                    </button>
                                </div>

                                <!-- Filled State (Image Preview) -->
                                <div id="selfie_filled_state" class="relative {{ $partnerProfile?->selfie_path ? '' : 'hidden' }}">
                                    <div class="w-full h-44 bg-slate-900/5 flex items-center justify-center p-2">
                                        <img id="selfie_preview_img" 
                                             src="{{ $partnerProfile?->selfie_path ? Storage::url($partnerProfile->selfie_path) : '' }}" 
                                             class="w-full h-full object-contain rounded-xl" 
                                             alt="Preview Selfie KTP">
                                    </div>

                                    <!-- Floating Actions Overlay -->
                                    <div class="absolute top-2.5 right-2.5 flex items-center space-x-1.5">
                                        <button type="button" 
                                                onclick="openLiveCamera('selfie')" 
                                                class="min-h-[36px] px-3 py-1.5 bg-white/95 hover:bg-white text-slate-800 text-xs font-bold rounded-xl shadow-xs border border-slate-200 backdrop-blur-xs flex items-center space-x-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span>Foto Ulang</span>
                                        </button>
                                        <button type="button" 
                                                onclick="document.getElementById('selfie_gallery_input').click()" 
                                                class="min-h-[36px] px-2.5 py-1.5 bg-white/95 hover:bg-white text-slate-600 text-xs font-medium rounded-xl shadow-xs border border-slate-200 backdrop-blur-xs flex items-center cursor-pointer"
                                                title="Pilih dari galeri">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Compression & Status Badges -->
                            <div class="flex items-center justify-between min-h-[18px]">
                                <div id="selfie_compress_status" class="hidden"></div>
                                @error('selfie_file') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                                @if ($partnerProfile?->selfie_path)
                                    <p id="selfie_saved_badge" class="text-[11px] text-emerald-700 font-medium">✓ File tersimpan di server</p>
                                @endif
                            </div>

                            <!-- Hidden Inputs -->
                            <input type="file" id="selfie_file" name="selfie_file" accept="image/*,.pdf" class="hidden">
                            <input type="file" id="selfie_camera_input" accept="image/*" capture="user" class="hidden">
                            <input type="file" id="selfie_gallery_input" accept="image/*,.pdf" class="hidden">
                        </div>
                    </div>

                    <!-- Dokumen Pendukung SKCK -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dokumen Pendukung SKCK (Opsional)</label>
                        <input type="file" name="skck_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 cursor-pointer">
                    </div>
                </div>

                <!-- Navigation Button Step 1 -->
                <div class="pt-1">
                    <button type="button" @click="nextStep()"
                        class="w-full min-h-[48px] py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Lanjut ke Wilayah Layanan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Lokasi Domisili & Wilayah Layanan -->
            <div x-show="currentStep === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                            2
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Wilayah Layanan dan Alamat Domisili</h3>
                            <p class="text-xs text-slate-500">Pilih kota layanan yang tersedia, lalu tentukan titik domisili Anda.</p>
                        </div>
                    </div>

                    <!-- Input Hidden region_id -->
                    <input type="hidden" name="region_id" id="region_id"
                        x-effect="$el.value = selectedRegionId">

                    <!-- 1. Cari Kota / Wilayah Layanan -->
                    <div>
                        <label for="search_city" class="block text-xs font-semibold text-slate-700 mb-1">
                            1. Cari Kota / Wilayah Layanan
                        </label>
                        <div class="relative">
                            <input type="text" id="search_city" x-model="searchQuery" 
                                placeholder="Ketik nama kota (cth: Surabaya, Malang, Bandung)..."
                                class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 placeholder-slate-400 pl-8 py-2.5">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="min-h-[44px] min-w-[44px] absolute right-0 top-0 flex items-center justify-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- 2. Klik Kota untuk Memilih Sekaligus -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700">2. Klik Kota untuk Memilih Wilayah Operasional:</span>
                            <button type="button" x-show="selectedRegionId" @click="clearRegion()" class="min-h-[44px] text-rose-600 hover:text-rose-800 text-xs font-semibold cursor-pointer">
                                Batal Pilih
                            </button>
                        </div>

                        <!-- Grid Chips Kota -->
                        <div class="max-h-52 overflow-y-auto p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/90 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="r in filteredRegions" :key="r.id">
                                <div @click="selectRegion(r)"
                                     class="min-h-[44px] p-3 rounded-xl border cursor-pointer select-none transition flex items-center justify-between text-xs"
                                     :class="{
                                         'bg-emerald-700 text-white font-bold border-emerald-700 shadow-xs': selectedRegionId == r.id,
                                         'bg-white text-slate-800 border-slate-200 hover:border-emerald-300': selectedRegionId != r.id
                                     }">
                                    <div class="truncate mr-1">
                                        <span x-text="r.name" class="block truncate"></span>
                                        <span class="text-[11px] block font-normal" 
                                              :class="selectedRegionId == r.id ? 'text-emerald-100' : 'text-slate-500'"
                                              x-text="r.timezone || 'WIB'"></span>
                                    </div>
                                    <span class="text-sm font-bold shrink-0" 
                                          :class="selectedRegionId == r.id ? 'text-white' : 'text-slate-400'" 
                                          x-text="selectedRegionId == r.id ? '✓' : '+'"></span>
                                </div>
                            </template>

                            <div x-show="filteredRegions.length === 0 && regions.length > 0" class="col-span-full py-4 text-center text-xs text-slate-500">
                                Tidak ada kota layanan yang sesuai pencarian "<span x-text="searchQuery"></span>".
                            </div>

                            <div x-show="regions.length === 0" class="col-span-full py-4 text-center text-xs text-amber-900 bg-amber-50 rounded-xl border border-amber-200">
                                Saat ini belum ada kota layanan yang berstatus aktif. Silakan hubungi admin.
                            </div>
                        </div>

                        <!-- Footer Status Pemilihan Kota -->
                        <div class="text-xs pt-1 flex items-center justify-between">
                            <div>
                                <span x-show="!selectedRegionId" class="text-slate-500">Belum ada kota yang dipilih.</span>
                                <span x-show="selectedRegionId" class="text-emerald-800 font-semibold flex items-center">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-600 mr-1.5"></span>
                                    <span>Kota Terpilih: <strong x-text="getSelectedRegionName()"></strong></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Peta Interaktif & Penempatan Pin Domisili -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700">
                                3. Titik Lokasi Domisili (Peta Spasial)
                            </label>
                            <span class="text-xs text-emerald-700 font-medium" x-show="selectedRegionId">
                                Peta otomatis diarahkan ke kota terpilih
                            </span>
                        </div>

                        <div id="map" class="w-full h-64 rounded-xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>
                        
                        <p class="text-xs text-slate-500 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            Klik peta atau seret pin untuk menentukan titik alamat tempat tinggal Anda.
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
                        <textarea id="address_text" name="address_text" rows="3" required placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('address_text', $partnerProfile?->address_text) }}</textarea>
                        @error('address_text') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Navigation Buttons Step 2 -->
                <div class="pt-1 flex items-center space-x-3">
                    <button type="button" @click="prevStep()"
                        class="w-1/3 min-h-[48px] py-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="nextStep()"
                        class="w-2/3 min-h-[48px] py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Lanjut ke Rekening Bank</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Rekening Bank Pencairan -->
            <div x-show="currentStep === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                            3
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Rekening Bank Pencairan Dana</h3>
                            <p class="text-xs text-slate-500">Nama pemilik rekening <strong>harus sama</strong> dengan nama di KTP (ADM-03).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 min-h-[32px] flex items-end mb-1">
                                <span>Nama Bank <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="bank_name" x-model="bankName" required placeholder="BCA / Mandiri / BRI / BNI"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                            @error('bank_name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 min-h-[32px] flex items-end mb-1">
                                <span>Nomor Rekening <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="account_number" x-model="accountNumber" @input="accountNumber = (accountNumber || '').replace(/[^0-9]/g, '')" inputmode="numeric" pattern="[0-9]*" required placeholder="Contoh: 1234567890"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                            @error('account_number') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 min-h-[32px] flex items-end mb-1">
                                <span>Nama Pemilik <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="text" name="account_holder_name" x-model="accountHolderName" required placeholder="Wajib sesuai e-KTP"
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                            @error('account_holder_name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons Step 3 -->
                <div class="pt-1 flex items-center space-x-3">
                    <button type="button" @click="prevStep()"
                        class="w-1/3 min-h-[48px] py-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="nextStep()"
                        class="w-2/3 min-h-[48px] py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Lanjut ke Persetujuan SOP</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Ringkasan Berkas, Kode Etik & Persetujuan SOP -->
            <div x-show="currentStep === 4" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                <!-- Ringkasan Data -->
                <div class="bg-emerald-50/70 border border-emerald-200/90 rounded-2xl p-4.5 space-y-3">
                    <div class="flex items-center space-x-2 text-emerald-900 font-bold text-xs">
                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Ringkasan Berkas Pendaftaran Anda</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-white p-2.5 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-slate-500 block">NIK Terdaftar</span>
                            <span class="font-mono font-bold text-slate-900" x-text="nik || '-'"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-slate-500 block">Nomor WhatsApp/HP</span>
                            <span class="font-mono font-bold text-slate-900" x-text="phone || '-'"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-emerald-100 col-span-2">
                            <span class="text-[10px] text-slate-500 block">Kota Layanan & Alamat</span>
                            <span class="font-bold text-slate-900 block" x-text="getSelectedRegionName()"></span>
                            <span class="text-[11px] text-slate-600 block mt-0.5 truncate" x-text="document.getElementById('address_text')?.value || '-'"></span>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-emerald-100 col-span-2">
                            <span class="text-[10px] text-slate-500 block">Rekening Pencairan Dana</span>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900"><span x-text="bankName"></span> - <span class="font-mono" x-text="accountNumber"></span></span>
                                <span class="text-[11px] text-slate-700 font-medium truncate ml-2">a.n <span x-text="accountHolderName"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SOP & Persetujuan -->
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                    <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                            4
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Persetujuan SOP dan Kode Etik Kemitraan</h3>
                            <p class="text-xs text-slate-500">Standar operasional higienitas, etika, dan keselamatan pijat.in.</p>
                        </div>
                    </div>

                    <!-- Clean Link Card Baca Dokumen SOP -->
                    <div class="p-3.5 rounded-xl bg-slate-50 hover:bg-slate-100/90 border border-slate-200/90 transition flex items-center justify-between cursor-pointer group"
                         @click="openSopModal()">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800 flex items-center space-x-1.5">
                                    <span>SOP & Kode Etik Kemitraan Pijat.In</span>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-1.5 py-0.5 rounded">Versi 2.2</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Ketuk untuk membaca dokumen syarat & ketentuan</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 flex items-center space-x-1 group-hover:translate-x-0.5 transition-transform shrink-0">
                            <span>Buka Dokumen</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>

                    <!-- Checkbox Persetujuan -->
                    <div class="pt-1">
                        <label class="flex items-start space-x-3 cursor-pointer min-h-[44px]">
                            <input type="checkbox" name="sop_agreement" id="sop_agreement" value="1" x-model="sopAgreed" required
                                @click="handleCheckboxClick($event)"
                                class="mt-1 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600 cursor-pointer">
                            <span class="text-xs text-slate-800 leading-relaxed select-none">
                                Saya menyatakan bahwa seluruh data di atas adalah benar dan sah, serta saya telah membaca, memahami, dan menyetujui sepenuhnya 
                                <a href="javascript:void(0)" @click.prevent="openSopModal()" class="text-emerald-700 hover:text-emerald-800 font-bold underline underline-offset-2">SOP & Kode Etik Kemitraan Pijat.In Versi 2.2</a>.
                            </span>
                        </label>
                        @error('sop_agreement') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Navigation Buttons Step 4 -->
                <div class="pt-1 flex items-center space-x-3">
                    <button type="button" @click="prevStep()"
                        class="w-1/3 min-h-[48px] py-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="submit"
                        class="w-2/3 min-h-[48px] py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-sm transition inline-flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Kirim Berkas Pendaftaran</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>

        <!-- ==================== MODAL DOKUMEN SOP & KODE ETIK (SCROLL TO UNLOCK) ==================== -->
        <div x-show="showSopModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
            <!-- Backdrop Blur -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showSopModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-3 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all my-auto w-full max-w-sm border border-slate-200/90 flex flex-col max-h-[82vh]">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/70 shrink-0">
                        <div class="flex items-center space-x-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">SOP & Kode Etik Kemitraan</h3>
                                <p class="text-[10px] text-slate-500">Versi 2.2 — Standar Layanan Resmi Pijat.In</p>
                            </div>
                        </div>
                        <button type="button" @click="showSopModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer text-xs">
                            ✕
                        </button>
                    </div>

                    <!-- Scrollable Document Content -->
                    <div id="sop_content_scroll" @scroll="handleSopScroll($event)" class="overflow-y-auto p-4 text-[11px] text-slate-600 space-y-3.5 flex-1 overscroll-contain leading-relaxed divide-y divide-slate-100">
                        <div class="pb-2">
                            <div class="inline-block px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200 mb-2">
                                Prinsip Utama: Layanan Pijat Sehat, Profesional, & Higienis
                            </div>
                            <p class="text-slate-700">
                                Selamat datang di keluarga besar Pijat.In. Seluruh mitra terapis wajib mematuhi standar pelayanan, etika profesi, serta regulasi hukum yang berlaku demi menjaga kehormatan profesi, keselamatan kerja, serta kepercayaan masyarakat.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px]">1</span>
                                <span>Layanan Pijat Khusus Sesama Jenis (Strict Same-Gender)</span>
                            </h4>
                            <p>
                                Terapis pria <strong>hanya melayani pelanggan pria</strong>, dan terapis wanita <strong>hanya melayani pelanggan wanita</strong> tanpa pengecualian. Tindakan menerima atau meminta pesanan lintas gender merupakan pelanggaran berat yang mengakibatkan pemutusan kemitraan seketika dan penonaktifan akun secara permanen.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-rose-50 text-rose-700 inline-flex items-center justify-center text-[11px]">2</span>
                                <span>Nol Toleransi Terhadap Tindakan Asusila & Asusila Terselubung</span>
                            </h4>
                            <p>
                                Platform Pijat.In murni menyediakan layanan pijat kesehatan, kebugaran, dan relaksasi keluarga yang bermartabat. Dilarang keras menawarkan, menyetujui, mendiskusikan, atau melakukan segala bentuk tindakan asusila, pornografi, atau pelecehan seksual. Setiap pelanggaran akan dilaporkan secara resmi kepada pihak Kepolisian RI untuk proses pidana.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px]">3</span>
                                <span>Standar Higienitas, Sanitasi, & Perlengkapan Kerja</span>
                            </h4>
                            <p>
                                Mitra wajib mengenakan seragam resmi atau pakaian kerja rapi, bersih, dan wangi; mencuci tangan sebelum dan sesudah sesi pijat; membawa minyak/lotion pijat berkualitas resmi yang higienis, alas kain bersih, serta masker pelindung.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px]">4</span>
                                <span>Transparansi Tarif & Larangan Pungutan Tambahan</span>
                            </h4>
                            <p>
                                Seluruh rincian biaya telah diatur secara resmi dan transparan di aplikasi. Mitra dilarang keras meminta tips atau biaya tambahan secara paksa, meminta bayaran di luar sistem, atau mengajak pelanggan bertransaksi di luar aplikasi Pijat.In.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px]">5</span>
                                <span>Ketepatan Waktu & Koordinasi Pelaksanaan</span>
                            </h4>
                            <p>
                                Mitra wajib hadir tepat waktu sesuai jadwal dan titik lokasi yang ditentukan. Apabila terjadi kendala lalu lintas atau hambatan mendesak di perjalanan, mitra wajib segera mengabari pelanggan melalui saluran komunikasi resmi aplikasi.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px]">6</span>
                                <span>Rekening Pencairan Dana Pribadi</span>
                            </h4>
                            <p>
                                Pencairan saldo komisi hanya dapat diproses ke rekening bank yang atas namanya sesuai dengan e-KTP fisik terdaftar. Segala bentuk rekayasa data rekening dapat memicu penahanan saldo sementara hingga verifikasi manual oleh tim kepatuhan.
                            </p>
                        </div>

                        <div class="pt-3 space-y-1.5">
                            <h4 class="font-bold text-slate-900 flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 inline-flex items-center justify-center text-[11px]">7</span>
                                <span>Kerahasiaan Data & Privasi Pelanggan</span>
                            </h4>
                            <p>
                                Alamat rumah, nomor telepon, dan data pribadi pelanggan bersifat rahasia. Mitra dilarang keras mendokumentasikan, menyebarluaskan, atau membagikan identitas pelanggan kepada pihak ketiga mana pun.
                            </p>
                        </div>

                        <div class="pt-3 pb-2 text-center text-slate-400 text-[11px]">
                            — Akhir Dokumen SOP & Kode Etik Versi 2.2 —
                        </div>
                    </div>

                    <!-- Modal Footer Controls -->
                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/80 shrink-0">
                        <!-- Scroll Status Reminder -->
                        <div x-show="!hasScrolledToBottom" class="flex items-center justify-center space-x-1.5 text-[10px] text-amber-800 bg-amber-50 py-1.5 px-2.5 rounded-lg border border-amber-200 mb-2">
                            <svg class="w-3 h-3 animate-bounce text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span>Gulir dokumen hingga baris terbawah untuk menyetujui</span>
                        </div>

                        <div x-show="hasScrolledToBottom" class="flex items-center justify-center space-x-1.5 text-[10px] text-emerald-800 bg-emerald-50 py-1.5 px-2.5 rounded-lg border border-emerald-200 mb-2">
                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Dokumen selesai dibaca. Tombol persetujuan aktif.</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showSopModal = false"
                                class="w-1/3 min-h-[42px] py-2 px-3 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                                Tutup
                            </button>

                            <button type="button"
                                :disabled="!hasScrolledToBottom"
                                @click="agreeSopFromModal()"
                                class="w-2/3 min-h-[42px] py-2 px-3 text-xs font-bold rounded-xl transition flex items-center justify-center space-x-1.5"
                                :class="hasScrolledToBottom 
                                    ? 'bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs cursor-pointer' 
                                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="hasScrolledToBottom ? 'Saya Pahami & Setujui' : 'Gulir ke Bawah Dahulu'"></span>
                            </button>
                        </div>
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

        function onboardingWizard() {
            return {
                currentStep: {{ $initialStep }},
                totalSteps: 4,
                steps: [
                    { num: 1, title: 'Identitas' },
                    { num: 2, title: 'Wilayah' },
                    { num: 3, title: 'Rekening' },
                    { num: 4, title: 'Persetujuan' },
                ],
                nik: @js(old('nik', $user->nik ?? '')),
                phone: @js(old('phone', $user->phone ?? '')),
                searchQuery: '',
                selectedRegionId: @js(old('region_id', $partnerProfile?->region_id ?? '')),
                regions: @js($activeRegions),
                bankName: @js(old('bank_name', $bankAccount?->bank_name ?? 'BCA')),
                accountNumber: @js(old('account_number', $bankAccount?->account_number ?? '')),
                accountHolderName: @js(old('account_holder_name', $bankAccount?->account_holder_name ?? $user->name)),
                hasExistingKtp: {{ $partnerProfile?->ktp_path ? 'true' : 'false' }},
                hasExistingSelfie: {{ $partnerProfile?->selfie_path ? 'true' : 'false' }},
                showSopModal: false,
                hasScrolledToBottom: false,
                sopAgreed: {{ old('sop_agreement') ? 'true' : 'false' }},

                openSopModal() {
                    this.showSopModal = true;
                    this.$nextTick(() => {
                        const scrollEl = document.getElementById('sop_content_scroll');
                        if (scrollEl) {
                            if (scrollEl.scrollHeight <= scrollEl.clientHeight + 15) {
                                this.hasScrolledToBottom = true;
                            }
                        }
                    });
                },

                handleSopScroll(e) {
                    const el = e.target;
                    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 25) {
                        this.hasScrolledToBottom = true;
                    }
                },

                agreeSopFromModal() {
                    if (this.hasScrolledToBottom) {
                        this.sopAgreed = true;
                        this.showSopModal = false;
                    }
                },

                handleCheckboxClick(e) {
                    if (!this.hasScrolledToBottom) {
                        e.preventDefault();
                        this.openSopModal();
                    }
                },

                initWizard() {
                    this.$watch('currentStep', (val) => {
                        this.onStepSwitched(val);
                    });
                    this.$watch('nik', (val) => {
                        if (val && /[^0-9]/.test(val)) {
                            this.nik = val.replace(/[^0-9]/g, '');
                        }
                    });
                    this.$watch('phone', (val) => {
                        if (val && /[^0-9]/.test(val)) {
                            this.phone = val.replace(/[^0-9]/g, '');
                        }
                    });
                    this.$watch('accountNumber', (val) => {
                        if (val && /[^0-9]/.test(val)) {
                            this.accountNumber = val.replace(/[^0-9]/g, '');
                        }
                    });
                },

                get filteredRegions() {
                    if (!this.searchQuery.trim()) return this.regions;
                    const q = this.searchQuery.toLowerCase();
                    return this.regions.filter(r => r.name.toLowerCase().includes(q));
                },

                selectRegion(region) {
                    this.selectedRegionId = region.id;
                    if (window.onMitraRegionSelected) {
                        window.onMitraRegionSelected(region);
                    }
                },

                clearRegion() {
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
                    e.target.submit();
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            const initialLat = parseFloat(document.getElementById('lat').value) || -6.9175;
            const initialLng = parseFloat(document.getElementById('lng').value) || 107.6191;

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

            // When a region is selected from the chips
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

                const center = bounds.getCenter();
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

            // Check if a region is pre-selected on load (e.g. after validation error / edit)
            const activeRegions = @js($activeRegions);
            const preselectedId = @js(old('region_id', $partnerProfile?->region_id ?? ''));

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
                closeCameraModal();
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

            closeCameraModal();
        }

        function closeCameraModal() {
            if (currentCameraStream) {
                currentCameraStream.getTracks().forEach(track => track.stop());
                currentCameraStream = null;
            }
            const video = document.getElementById('camera_video');
            if (video) video.srcObject = null;
            document.getElementById('camera_modal').classList.add('hidden');
        }
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
