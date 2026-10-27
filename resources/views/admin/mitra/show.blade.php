<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.mitra.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                    ← Kembali ke Antrean
                </a>
                <h2 class="text-xl font-bold text-slate-800 leading-tight mt-1">
                    Inspeksi Dokumen: {{ $mitra->name }}
                </h2>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    Menunggu Keputusan Admin
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{ showRejectModal: false }">

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Left Col: Data Diri & Rekening (1 col) -->
            <div class="space-y-6">
                <!-- Info Pemohon -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">Data Pemohon</h3>
                    
                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Lengkap:</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $mitra->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">NIK KTP:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $mitra->nik }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Jenis Kelamin:</span>
                            <span class="capitalize font-semibold text-slate-700">{{ $mitra->gender }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Kontak WhatsApp:</span>
                            <span class="font-mono text-slate-700">{{ $mitra->phone }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Wilayah Domisili:</span>
                            <span class="font-bold text-emerald-700">{{ $profile?->region?->name ?? 'Belum terpetakan' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Alamat Fisik:</span>
                            <span class="text-slate-700 leading-relaxed">{{ $profile?->address_text }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Rekening (ADM-03) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rekening Bank (ADM-03)</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Pending</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Bank:</span>
                            <span class="font-bold text-slate-800">{{ $bankAccount?->bank_name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Nomor Rekening:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $bankAccount?->account_number ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Nama Pemilik di Rekening:</span>
                            <span class="font-bold {{ strcasecmp($mitra->name, $bankAccount?->account_holder_name ?? '') === 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $bankAccount?->account_holder_name ?? '-' }}
                            </span>
                            @if (strcasecmp($mitra->name, $bankAccount?->account_holder_name ?? '') === 0)
                                <span class="text-[10px] text-emerald-600 block mt-0.5">✓ Cocok dengan nama KTP</span>
                            @else
                                <span class="text-[10px] text-rose-500 block mt-0.5">⚠ Nama rekening berbeda dari identitas KTP</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Col: Foto KTP & Selfie (2 cols) -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                        Perbandingan Dokumen KTP & Foto Wajah
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50">
                            <span class="text-xs font-bold text-slate-700 block mb-2">Foto e-KTP Asli:</span>
                            <div class="h-56 bg-slate-200 rounded-lg overflow-hidden flex items-center justify-center text-slate-400 text-xs">
                                @if ($profile?->ktp_path)
                                    <div class="p-4 text-center">
                                        <svg class="w-12 h-12 mx-auto text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="font-mono text-[11px] text-slate-600 block">{{ basename($profile->ktp_path) }}</span>
                                        <span class="text-[10px] text-emerald-700 font-semibold mt-1 block">Tersimpan di Dokumen Kemitraan</span>
                                    </div>
                                @else
                                    <span>Belum ada foto KTP</span>
                                @endif
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50">
                            <span class="text-xs font-bold text-slate-700 block mb-2">Foto Selfie Bersama KTP:</span>
                            <div class="h-56 bg-slate-200 rounded-lg overflow-hidden flex items-center justify-center text-slate-400 text-xs">
                                @if ($profile?->selfie_path)
                                    <div class="p-4 text-center">
                                        <svg class="w-12 h-12 mx-auto text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-mono text-[11px] text-slate-600 block">{{ basename($profile->selfie_path) }}</span>
                                        <span class="text-[10px] text-emerald-700 font-semibold mt-1 block">Tersimpan di Dokumen Kemitraan</span>
                                    </div>
                                @else
                                    <span>Belum ada foto selfie</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decision Action Bar -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-800">Keputusan Verifikasi Berkas</div>
                        <div class="text-[11px] text-slate-400">Pastikan seluruh data telah sesuai sebelum memberi putusan.</div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button type="button" @click="showRejectModal = true"
                            class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition">
                            Tolak Pendaftaran
                        </button>

                        <form action="{{ route('admin.mitra.approve', $mitra) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menyetujui seluruh berkas dan mengaktifkan akun mitra ini?')">
                            @csrf
                            <button type="submit" 
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Setujui Mitra (Aktifkan)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Alasan Penolakan Baku (ADM-02) -->
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showRejectModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                    <form action="{{ route('admin.mitra.reject', $mitra) }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800" id="modal-title">Tolak Pendaftaran Mitra</h3>
                            <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Alasan Baku Penolakan (ADM-02) <span class="text-rose-500">*</span></label>
                                <select name="rejection_code" required class="w-full text-xs rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500 py-2.5">
                                    <option value="">-- Pilih Alasan Baku --</option>
                                    @foreach ($standardReasons as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan untuk Mitra (Opsional)</label>
                                <textarea name="custom_note" rows="3" placeholder="Contoh: Lampirkan foto KTP dengan pencahayaan terang tanpa pantulan flash..."
                                    class="w-full text-xs rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-2">
                            <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                Konfirmasi Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
