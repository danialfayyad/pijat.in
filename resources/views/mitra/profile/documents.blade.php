<x-pwa-layout title="Dokumen Kemitraan - Pijat.In" headerTitle="Dokumen Kemitraan" :backUrl="route('mitra.profile')">
    <div class="py-2 space-y-4">
        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-950 flex items-start space-x-3 shadow-2xs">
            <svg class="w-5 h-5 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="leading-relaxed">
                Berkas dokumen resmi yang telah diverifikasi oleh tim Admin Wilayah. Jika memerlukan pembaruan dokumen, silakan hubungi admin melalui Live Chat.
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-800">Berkas Terverifikasi</h3>

            <div class="space-y-3">
                <!-- 1. e-KTP -->
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-slate-200/80 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block">Kartu Tanda Penduduk (e-KTP)</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $user->nik }}</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1 shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Terverifikasi
                    </span>
                </div>

                <!-- 2. Foto Selfie -->
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-slate-200/80 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block">Foto Selfie & e-KTP</span>
                            <span class="text-[11px] text-slate-400">Verifikasi wajah mitra</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1 shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Terverifikasi
                    </span>
                </div>

                <!-- 3. Ijazah / Sertifikat -->
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-slate-200/80 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block">Sertifikat Keahlian / Ijazah</span>
                            <span class="text-[11px] text-slate-400">Kompetensi terapis</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1 shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Valid
                    </span>
                </div>

                <!-- 4. SKCK -->
                <div class="p-3.5 rounded-xl border border-slate-200/90 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-slate-200/80 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block">Surat Bebas Catatan Kriminal (SKCK)</span>
                            <span class="text-[11px] text-slate-400">Kepatuhan kepolisian</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1 shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Valid
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-pwa-layout>

