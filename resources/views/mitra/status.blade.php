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
                        Hasil verifikasi akan diperbarui otomatis di halaman ini dalam 1 x 24 Jam.
                    </p>
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
            <!-- Rejected Card with Clean Human Feedback -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-6 space-y-5">
                <!-- Status Icon & Title -->
                <div class="text-center">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-rose-50 text-rose-600 ring-8 ring-rose-50/70 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 mb-2">
                        Pendaftaran Perlu Perbaikan
                    </span>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Berkas Belum Memenuhi Syarat</h3>
                    <p class="text-xs text-slate-600 mt-2 max-w-md mx-auto leading-relaxed">
                        {{ $user->rejection_reason ?? 'Dokumen KTP atau rekening tidak terbaca jelas.' }}
                    </p>
                </div>

                <!-- Ringkasan Data Terdaftar -->
                <div class="bg-slate-50 rounded-xl p-4 text-left text-xs space-y-2.5 border border-slate-200/80">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                        Data Pendaftaran Terakhir:
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-600">Nama Pemohon:</span>
                        <span class="font-bold text-slate-900">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-600">NIK:</span>
                        <span class="font-mono text-slate-900">{{ substr($user->nik, 0, 6) . '******' . substr($user->nik, -4) }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-slate-200/60">
                        <span class="text-slate-600">Wilayah Layanan:</span>
                        <span class="font-semibold text-emerald-800">{{ $user->partnerProfile?->region?->name ?? 'Terdeteksi' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Rekening Pencairan:</span>
                        <span class="font-mono text-slate-900">{{ $user->primaryBankAccount?->bank_name }} ({{ $user->primaryBankAccount?->account_number }})</span>
                    </div>
                </div>

                <!-- Tombol Aksi: Kiri Keluar, Kanan Unggah Ulang -->
                <div class="pt-1 flex items-center space-x-3">
                    <form method="POST" action="{{ route('logout') }}" class="w-1/2">
                        @csrf
                        <button type="submit" class="w-full min-h-[46px] py-2.5 px-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl shadow-2xs transition flex items-center justify-center cursor-pointer">
                            Keluar dari Akun
                        </button>
                    </form>

                    <a href="{{ route('mitra.onboarding') }}" 
                        class="w-1/2 min-h-[46px] py-2.5 px-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>Unggah Ulang</span>
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-pwa-layout>
