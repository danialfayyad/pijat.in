<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 leading-tight">
                    Portal Mitra Pijat.In
                </h2>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        ● Mitra Terverifikasi
                    </span>
                    <span class="text-xs text-slate-500 font-medium">
                        Wilayah: <strong>{{ $profile?->region?->name ?? 'Semua Wilayah' }}</strong>
                    </span>
                </div>
            </div>
            <div>
                <a href="{{ route('mitra.profile') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition inline-flex items-center space-x-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil Mitra</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Card Toggle Status Kerja Online / Offline (MIT-20) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl {{ $profile?->is_active ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center shadow-md transition-all">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Kesiapan Mitra</div>
                    <div class="text-lg font-black text-slate-900 flex items-center space-x-2">
                        <span>{{ $profile?->is_active ? 'Aktif (Siap Menerima Order)' : 'Sedang Istirahat (Nonaktif)' }}</span>
                        <span class="w-3 h-3 rounded-full {{ $profile?->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300' }}"></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $profile?->is_active ? 'Lokasi Anda terdeteksi radar untuk penawaran order baru sesama gender.' : 'Anda tidak akan menerima tawaran pesanan baru selama mode istirahat aktif.' }}
                    </p>
                </div>
            </div>

            <div>
                <form action="{{ route('mitra.toggle-active') }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $profile?->is_active ? '0' : '1' }}">
                    <button type="submit" 
                        class="w-full md:w-auto px-6 py-3 rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center space-x-2 {{ $profile?->is_active ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        <span>{{ $profile?->is_active ? 'Ubah ke Mode Istirahat' : 'Aktifkan Mode Kerja' }}</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Saldo Tersedia</span>
                <div class="text-2xl font-black text-slate-900 mt-1">Rp 0</div>
                <div class="text-[11px] text-emerald-600 mt-1 font-medium">Rekening: {{ $user->primaryBankAccount?->bank_name ?? '-' }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pesanan Selesai</span>
                <div class="text-2xl font-black text-slate-900 mt-1">0</div>
                <div class="text-[11px] text-slate-400 mt-1">Rating performa: 5.0 ★</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Sanksi SP</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">Bebas SP</div>
                <div class="text-[11px] text-slate-400 mt-1">Rekam jejak bersih</div>
            </div>
        </div>

    </div>
</x-app-layout>
