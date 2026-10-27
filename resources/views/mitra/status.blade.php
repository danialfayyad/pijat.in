<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            Status Akun Kemitraan
        </h2>
    </x-slot>

    <div class="py-12 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($user->isMenungguVerifikasi())
            <!-- Waiting for Admin Verification Card (MIT-03) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-8 text-center space-y-6">
                <div class="relative w-20 h-20 mx-auto">
                    <div class="absolute inset-0 rounded-full bg-amber-100 animate-ping opacity-75"></div>
                    <div class="relative w-20 h-20 rounded-full bg-gradient-to-tr from-amber-400 to-amber-500 text-white flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 mb-2">
                        Menunggu Verifikasi Admin
                    </span>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">Berkas Anda Sedang Ditinjau</h3>
                    <p class="text-xs text-slate-500 mt-2 max-w-md mx-auto leading-relaxed">
                        Terima kasih telah melengkapi data pendaftaran. Tim Admin Wilayah kami sedang memvalidasi keaslian dokumen KTP, foto selfie, dan rekening bank Anda.
                    </p>
                </div>

                <!-- SLA 24 Hours Box -->
                <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100 max-w-md mx-auto flex items-center space-x-3 text-left">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-900">Target SLA Peninjauan: 1 x 24 Jam</div>
                        <div class="text-[11px] text-emerald-700">Hasil verifikasi akan diperbarui otomatis di halaman ini.</div>
                    </div>
                </div>

                <!-- Application Summary -->
                <div class="bg-slate-50 rounded-2xl p-5 text-left text-xs space-y-2.5 max-w-md mx-auto border border-slate-100">
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-500">Nama Pemohon:</span>
                        <span class="font-bold text-slate-800">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-500">NIK:</span>
                        <span class="font-mono text-slate-800">{{ substr($user->nik, 0, 6) . '******' . substr($user->nik, -4) }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-500">Wilayah Layanan Domisili:</span>
                        <span class="font-semibold text-emerald-700">{{ $user->partnerProfile?->region?->name ?? 'Terdeteksi' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Rekening Pencairan:</span>
                        <span class="font-mono text-slate-800">{{ $user->primaryBankAccount?->bank_name }} - {{ $user->primaryBankAccount?->account_number }}</span>
                    </div>
                </div>

                <div class="pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            </div>

        @elseif ($user->isDitolak())
            <!-- Rejected Card with Repair Button (MIT-03) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-8 text-center space-y-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-tr from-rose-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 mb-2">
                        Pendaftaran Ditolak
                    </span>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">Berkas Belum Memenuhi Syarat</h3>
                    <p class="text-xs text-slate-500 mt-2 max-w-md mx-auto leading-relaxed">
                        Admin wilayah menemukan ketidaksesuaian pada dokumen yang Anda kirimkan. Anda dapat memperbaikinya sekarang.
                    </p>
                </div>

                <!-- Rejection Reason Alert Box -->
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 max-w-md mx-auto text-left space-y-1">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Alasan Penolakan dari Admin:</div>
                    <div class="text-sm font-semibold text-rose-900">
                        {{ $user->rejection_reason ?? 'Dokumen KTP atau rekening tidak terbaca jelas.' }}
                    </div>
                </div>

                <div>
                    <a href="{{ route('mitra.onboarding') }}" 
                        class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition inline-flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Perbaiki Data & Unggah Ulang Berkas</span>
                    </a>
                </div>

                <div class="pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
