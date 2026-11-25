<x-pwa-layout title="Ajukan Banding Sanksi - Mitra Pijat.In" headerTitle="Banding Sanksi" :backUrl="route('mitra.penalties.index')">
    <div class="py-2 space-y-4">
        <!-- Panduan & Prosedur Pengajuan Banding -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 text-xs text-slate-700 flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1.5 leading-relaxed">
                <h4 class="font-bold text-slate-900 text-xs">Panduan Pengajuan Banding Poin</h4>
                <p class="text-[11px] text-slate-500">
                    Banding ditujukan untuk mitra yang mengalami kendala darurat di luar kendali (seperti kondisi medis mendadak, kecelakaan lalu lintas, atau musibah force majeure).
                </p>
                <div class="pt-1 text-[11px] text-slate-600 font-medium space-y-0.5">
                    <p>1. Tuliskan kronologi dan alasan permohonan secara jujur dan detail.</p>
                    <p>2. Unggah surat pernyataan resmi / bermaterai sebagai komitmen perbaikan layanan.</p>
                    <p>3. Lampirkan foto bukti pendukung jika ada untuk memperkuat pertimbangan Admin Wilayah.</p>
                </div>
            </div>
        </div>

        <!-- Ringkasan Poin Sanksi -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Poin Sanksi Saat Ini</span>
                <span class="text-2xl font-black text-rose-600 font-mono tracking-tight">{{ $user->penalty_points }} Pts</span>
            </div>
            <div class="text-right text-xs">
                <span class="text-slate-400 font-medium block text-[11px]">Target Pemulihan:</span>
                <span class="font-bold text-emerald-700">Pemulihan {{ $user->penalty_points }} Poin</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-2xs flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Formulir Pengajuan -->
        <form action="{{ route('mitra.penalties.appeal.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Formulir Pengajuan Banding SP</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Lengkapi formulir di bawah ini dengan data yang sebenarnya.</p>
                </div>

                <!-- Alasan Banding -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kronologi & Alasan Permohonan <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="reason" rows="4" required minlength="10" maxlength="1000"
                        placeholder="Ceritakan kronologi kejadian yang menyebabkan penalti, klarifikasi kendala yang dialami, atau komitmen Anda ke depan..."
                        class="w-full p-3.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('reason') }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter, maksimal 1000 karakter.</p>
                </div>

                <!-- Unggah Surat Pernyataan dengan Kompresi Otomatis -->
                <x-image-upload-compressor 
                    name="statement_letter" 
                    label="Dokumen / Surat Pernyataan Banding" 
                    :required="true" 
                    :multiple="false" 
                    :maxFiles="1" 
                    accept=".pdf,image/png,image/jpeg,image/jpg"
                    helpText="Format PDF atau Foto (JPG/PNG). Foto otomatis dikompresi agar ukuran di bawah 2MB." />

                <!-- Foto Bukti Pendukung Opsional dengan Kompresi Otomatis -->
                <x-image-upload-compressor 
                    name="evidence_photos[]" 
                    label="Foto Bukti Pendukung (Opsional)" 
                    :multiple="true" 
                    :maxFiles="3" 
                    helpText="Maksimal 3 foto pendukung (JPG/PNG/WEBP). Contoh: foto kendala kendaraan, bukti medis, atau bukti chat darurat." />
            </div>

            <div class="pt-1">
                <button type="submit"
                    class="w-full min-h-[48px] py-3.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span>Kirim Pengajuan Banding</span>
                </button>
            </div>
        </form>
    </div>
</x-pwa-layout>
