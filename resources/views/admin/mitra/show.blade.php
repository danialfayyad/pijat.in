<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mitra.index') }}" 
               class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Antrean Verifikasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-lg text-slate-900 leading-tight">
                    Inspeksi Dokumen: {{ $mitra->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" 
         x-data="{ 
            showRejectModal: false, 
            previewPhoto: null, 
            previewTitle: '' 
         }">

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Col: Data Diri & Rekening (5 cols on lg) -->
            <div class="lg:col-span-5 space-y-5">
                <!-- Info Pemohon -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-xs font-semibold text-slate-900">Data Pemohon</h3>
                    </div>
                    
                    <div class="space-y-3 text-xs">
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Status Pendaftaran</span>
                            <span class="font-semibold text-amber-700">Menunggu Verifikasi</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Nama Lengkap</span>
                            <span class="font-semibold text-slate-900 text-right">{{ $mitra->name }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">NIK KTP</span>
                            <span class="font-medium text-slate-900 text-right">{{ $mitra->nik ?? '-' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Jenis Kelamin</span>
                            <span class="capitalize text-slate-800 text-right">{{ $mitra->gender ?? '-' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">WhatsApp</span>
                            @if ($mitra->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $mitra->phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" 
                                   class="text-emerald-700 hover:text-emerald-800 font-medium text-right hover:underline flex items-center gap-1">
                                    <span>{{ $mitra->phone }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @else
                                <span class="text-slate-400 text-right">-</span>
                            @endif
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Wilayah Utama</span>
                            <span class="font-medium text-emerald-800 text-right">{{ $profile?->region?->name ?? 'Belum terpetakan' }}</span>
                        </div>
                        @if ($profile?->secondaryRegion)
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-slate-500 shrink-0">Wilayah Tambahan</span>
                                <span class="font-medium text-slate-800 text-right">{{ $profile->secondaryRegion->name }}</span>
                            </div>
                        @endif
                        <div class="pt-2 border-t border-slate-100 space-y-1">
                            <span class="text-slate-500 block">Alamat Domisili Fisik</span>
                            <p class="text-slate-800 leading-relaxed font-medium">{{ $profile?->address_text ?? 'Belum ada alamat' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rekening Bank (Pencairan) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-xs font-semibold text-slate-900">Rekening Bank (Pencairan)</h3>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Nama Bank</span>
                            <span class="font-semibold text-slate-900 text-right">{{ $bankAccount?->bank_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Nomor Rekening</span>
                            <span class="font-medium text-slate-900 text-right">{{ $bankAccount?->account_number ?? '-' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-slate-500 shrink-0">Atas Nama</span>
                            <span class="font-semibold text-slate-900 text-right">{{ $bankAccount?->account_holder_name ?? '-' }}</span>
                        </div>

                        @if ($bankAccount)
                            <div class="pt-2 border-t border-slate-100">
                                @if (strcasecmp($mitra->name, $bankAccount->account_holder_name ?? '') === 0)
                                    <div class="flex items-center gap-2 text-xs text-emerald-800 bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-200/70">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Nama rekening cocok dengan identitas KTP</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-xs text-rose-800 bg-rose-50/70 p-2.5 rounded-xl border border-rose-200/70">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Nama pemilik rekening berbeda dari KTP</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Col: Foto KTP & Selfie + Decision (7 cols on lg) -->
            <div class="lg:col-span-7 space-y-5">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-xs font-semibold text-slate-900">Perbandingan Dokumen KTP & Foto Wajah</h3>
                    </div>

                    @php
                        $isKtpImage = $profile?->ktp_path && preg_match('/\.(jpg|jpeg|png|webp|svg)$/i', $profile->ktp_path);
                        $isSelfieImage = $profile?->selfie_path && preg_match('/\.(jpg|jpeg|png|webp|svg)$/i', $profile->selfie_path);
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Foto KTP -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-900">Foto e-KTP Asli</h4>
                                    <span class="text-[11px] text-slate-500">KTP fisik pemohon</span>
                                </div>
                                @if ($profile?->ktp_path)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ strtoupper(pathinfo($profile->ktp_path, PATHINFO_EXTENSION)) }}
                                    </span>
                                @endif
                            </div>

                            <div class="h-64 rounded-xl border border-slate-200 bg-slate-50/80 overflow-hidden flex items-center justify-center relative group">
                                @if ($isKtpImage)
                                    <div class="w-full h-full cursor-pointer flex items-center justify-center p-2"
                                         @click="previewPhoto = '/storage/{{ ltrim($profile->ktp_path, '/') }}'; previewTitle = 'Foto e-KTP: {{ $mitra->name }}'">
                                        <img src="/storage/{{ ltrim($profile->ktp_path, '/') }}" 
                                             alt="Foto e-KTP" 
                                             class="max-h-full max-w-full object-contain rounded-lg transition duration-200 group-hover:scale-[1.02]">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-medium transition duration-200 backdrop-blur-2xs gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            <span>Perbesar</span>
                                        </div>
                                    </div>
                                @elseif ($profile?->ktp_path)
                                    <div class="p-4 text-center">
                                        <svg class="w-10 h-10 mx-auto text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-xs text-slate-700 block font-medium">Dokumen Tersimpan</span>
                                        <a href="/storage/{{ ltrim($profile->ktp_path, '/') }}" target="_blank" class="text-[11px] text-emerald-700 hover:underline mt-1 inline-block">
                                            Buka Berkas
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada foto KTP</span>
                                @endif
                            </div>
                        </div>

                        <!-- Foto Selfie -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-900">Foto Selfie Bersama KTP</h4>
                                    <span class="text-[11px] text-slate-500">Pegang KTP dekat wajah</span>
                                </div>
                                @if ($profile?->selfie_path)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ strtoupper(pathinfo($profile->selfie_path, PATHINFO_EXTENSION)) }}
                                    </span>
                                @endif
                            </div>

                            <div class="h-64 rounded-xl border border-slate-200 bg-slate-50/80 overflow-hidden flex items-center justify-center relative group">
                                @if ($isSelfieImage)
                                    <div class="w-full h-full cursor-pointer flex items-center justify-center p-2"
                                         @click="previewPhoto = '/storage/{{ ltrim($profile->selfie_path, '/') }}'; previewTitle = 'Foto Selfie Bersama KTP: {{ $mitra->name }}'">
                                        <img src="/storage/{{ ltrim($profile->selfie_path, '/') }}" 
                                             alt="Foto Selfie Bersama KTP" 
                                             class="max-h-full max-w-full object-contain rounded-lg transition duration-200 group-hover:scale-[1.02]">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-medium transition duration-200 backdrop-blur-2xs gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            <span>Perbesar</span>
                                        </div>
                                    </div>
                                @elseif ($profile?->selfie_path)
                                    <div class="p-4 text-center">
                                        <svg class="w-10 h-10 mx-auto text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-xs text-slate-700 block font-medium">Dokumen Tersimpan</span>
                                        <a href="/storage/{{ ltrim($profile->selfie_path, '/') }}" target="_blank" class="text-[11px] text-emerald-700 hover:underline mt-1 inline-block">
                                            Buka Berkas
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada foto selfie</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decision Action Bar -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xs font-semibold text-slate-900">Keputusan Verifikasi Berkas</h4>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <button type="button" @click="showRejectModal = true"
                            class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 text-xs font-semibold rounded-xl transition cursor-pointer">
                            Tolak Pendaftaran
                        </button>

                        <form id="approve-mitra-form" action="{{ route('admin.mitra.approve', $mitra) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="button" 
                                onclick="confirmApproveMitra(this)"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Setujui & Aktifkan</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Alasan Penolakan Baku (ADM-02) -->
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-2xs transition-opacity" @click="showRejectModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 relative z-10">
                    <form action="{{ route('admin.mitra.reject', $mitra) }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900" id="modal-title">Tolak Pendaftaran Mitra</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pemohon akan menerima alasan penolakan dan dapat mengajukan ulang.</p>
                            </div>
                            <button type="button" @click="showRejectModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Alasan Penolakan Berkas <span class="text-rose-500">*</span></label>
                                <select name="rejection_code" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500 py-2.5">
                                    <option value="">Pilih Alasan Penolakan</option>
                                    @foreach ($standardReasons as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Tambahan untuk Mitra (Opsional)</label>
                                <textarea name="custom_note" rows="3" placeholder="Contoh: Lampirkan foto KTP dengan pencahayaan terang tanpa pantulan flash..."
                                    class="w-full text-xs rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end items-center gap-2">
                            <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-800 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs rounded-xl shadow-xs transition cursor-pointer">
                                Tolak Berkas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lightbox Pratinjau Foto Penuh -->
        <div x-show="previewPhoto" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs transition" 
             style="display: none;"
             @keydown.escape.window="previewPhoto = null">
            <div class="fixed inset-0" @click="previewPhoto = null"></div>

            <div class="relative max-w-4xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl z-10" @click.stop>
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm" x-text="previewTitle || 'Pratinjau Foto'"></div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Tekan ESC atau klik di luar untuk menutup</div>
                    </div>
                    <button type="button" @click="previewPhoto = null" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 bg-slate-950 flex items-center justify-center max-h-[75vh] overflow-auto">
                    <img :src="previewPhoto" alt="Preview" class="max-h-[70vh] w-auto object-contain rounded-lg">
                </div>
                <div class="p-3 bg-white border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Pratinjau Dokumen Kemitraan</span>
                    <a :href="previewPhoto" target="_blank" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                        Buka di Tab Baru
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script>
        function confirmApproveMitra(button) {
            const form = button ? button.closest('form') : document.getElementById('approve-mitra-form');
            if (typeof window.confirmAction === 'function') {
                window.confirmAction({
                    title: 'Setujui Pendaftaran Mitra?',
                    message: 'Seluruh berkas dokumen identitas dan rekening akan diverifikasi. Akun mitra akan langsung aktif dan siap beroperasi.',
                    type: 'success',
                    confirmText: 'Setujui & Aktifkan',
                    cancelText: 'Batal',
                    form: form,
                    onConfirm: function() {
                        if (form) form.submit();
                    }
                });
            } else if (confirm('Apakah Anda yakin ingin menyetujui dan mengaktifkan pendaftaran mitra ini?')) {
                if (form) form.submit();
            }
        }
    </script>
</x-app-layout>
