<x-pwa-layout title="Ajukan Banding Sanksi Mitra - Pijat.In" headerTitle="Banding Sanksi" :backUrl="route('mitra.penalties.index')">
    <div class="py-2 space-y-4">
        <!-- Info Card -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-950 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="leading-relaxed">
                <strong>Prosedur Banding Sanksi Mitra (MIT-18):</strong>
                Permohonan banding Anda akan masuk langsung ke antrean verifikasi <strong>Admin Wilayah Akun</strong> Anda saat ini. Anda wajib mengunggah surat pernyataan bermaterai / resmi yang menjelaskan argumen pembelaan atau komitmen perbaikan performa.
            </div>
        </div>

        <!-- Summary Poin -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Poin Sanksi Saat Ini</span>
                <span class="text-2xl font-black text-rose-600 font-mono">{{ $user->penalty_points }} Pts</span>
            </div>
            <div class="text-right text-xs text-slate-500">
                <span class="block font-semibold">Poin Diajukan Pemulihan:</span>
                <span class="font-bold text-emerald-700">-{{ $user->penalty_points }} Pts</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Form Pengajuan -->
        <form action="{{ route('mitra.penalties.appeal.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Formulir Pengajuan Banding SP</h3>

                <!-- Alasan Banding -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Alasan Permohonan Banding <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="reason" rows="4" required minlength="10" maxlength="1000"
                        placeholder="Jelaskan alasan permohonan banding, klarifikasi insiden, atau komitmen perbaikan layanan Anda..."
                        class="w-full p-3.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('reason') }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter, maksimal 1000 karakter.</p>
                </div>

                <!-- Unggah Surat Pernyataan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Dokumen / Surat Pernyataan Banding <span class="text-rose-600">*</span>
                    </label>
                    <input type="file" name="statement_letter" required accept=".pdf,image/png,image/jpeg,image/jpg"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-slate-200 rounded-xl p-2">
                    <p class="text-[11px] text-slate-400 mt-1">Format PDF, JPG, atau PNG. Maksimal 2MB.</p>
                </div>

                <!-- Foto Bukti Pendukung Opsional -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Bukti Pendukung Tambahan (Opsional)
                    </label>
                    <input type="file" name="evidence_photos[]" multiple accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer border border-slate-200 rounded-xl p-2">
                    <p class="text-[11px] text-slate-400 mt-1">Maksimal 3 foto pendukung (JPG/PNG, maks 2MB per file).</p>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full min-h-[48px] py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Kirim Berkas Banding ke Admin Wilayah</span>
                </button>
            </div>
        </form>
    </div>
</x-pwa-layout>

