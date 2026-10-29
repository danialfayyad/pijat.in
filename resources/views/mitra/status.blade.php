<x-pwa-layout title="Status Akun - Pijat.In" headerTitle="Status Kemitraan">
    <div class="py-4 space-y-4">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($user->isMenungguVerifikasi())
            <!-- Waiting for Admin Verification Card (MIT-03) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 text-center space-y-5">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200 mb-2">
                        Menunggu Verifikasi Admin
                    </span>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Berkas Anda Sedang Ditinjau</h3>
                    <p class="text-xs text-slate-600 mt-2 max-w-md mx-auto leading-relaxed">
                        Terima kasih telah melengkapi data pendaftaran. Tim Admin Wilayah kami sedang memvalidasi keaslian dokumen KTP, foto selfie, dan rekening bank Anda.
                    </p>
                </div>

                <!-- SLA 24 Hours Box -->
                <div class="p-4 rounded-xl bg-emerald-50/80 border border-emerald-200 max-w-md mx-auto flex items-center space-x-3 text-left">
                    <div class="w-9 h-9 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-950">Target SLA Peninjauan: 1 x 24 Jam</div>
                        <div class="text-xs text-emerald-800">Hasil verifikasi akan diperbarui otomatis di halaman ini.</div>
                    </div>
                </div>

                <!-- Application Summary -->
                <div class="bg-slate-50 rounded-xl p-4 text-left text-xs space-y-2.5 max-w-md mx-auto border border-slate-200/80">
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-600">Nama Pemohon:</span>
                        <span class="font-bold text-slate-900">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-600">NIK:</span>
                        <span class="font-mono text-slate-900">{{ substr($user->nik, 0, 6) . '******' . substr($user->nik, -4) }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-600">Wilayah Layanan Domisili:</span>
                        <span class="font-semibold text-emerald-800">{{ $user->partnerProfile?->region?->name ?? 'Terdeteksi' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Rekening Pencairan:</span>
                        <span class="font-mono text-slate-900">{{ $user->primaryBankAccount?->bank_name }} ({{ $user->primaryBankAccount?->account_number }})</span>
                    </div>
                </div>

                <div class="pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-[44px] text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            </div>

        @elseif ($user->isDitolak())
            <!-- Rejected Card with Repair Button (MIT-03) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 text-center space-y-5">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-900 border border-rose-200 mb-2">
                        Pendaftaran Ditolak
                    </span>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Berkas Belum Memenuhi Syarat</h3>
                    <p class="text-xs text-slate-600 mt-2 max-w-md mx-auto leading-relaxed">
                        Admin wilayah menemukan ketidaksesuaian pada dokumen yang Anda kirimkan. Anda dapat memperbaikinya sekarang.
                    </p>
                </div>

                <!-- Rejection Reason Alert Box -->
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 max-w-md mx-auto text-left space-y-1">
                    <div class="text-xs font-bold uppercase tracking-wider text-rose-800">Alasan Penolakan dari Admin:</div>
                    <div class="text-xs font-semibold text-rose-950">
                        {{ $user->rejection_reason ?? 'Dokumen KTP atau rekening tidak terbaca jelas.' }}
                    </div>
                </div>

                <div>
                    <a href="{{ route('mitra.onboarding') }}" 
                        class="min-h-[48px] px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition inline-flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Perbaiki Data & Unggah Ulang Berkas</span>
                    </a>
                </div>

                <div class="pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="min-h-[44px] text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>
</x-pwa-layout>
