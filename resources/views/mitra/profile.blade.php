<x-pwa-layout title="Profil Mitra - Pijat.In" headerTitle="Akun & Kemitraan">
    <div class="py-2 space-y-4">

        <!-- Status Feedback -->
        @if (session('status'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- 1. Header Identitas Mitra -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-lg flex items-center justify-center shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <h2 class="text-base font-bold text-slate-900 leading-tight truncate">{{ $user->name }}</h2>
                        @if ($user->isTerverifikasi())
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="Terverifikasi">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 truncate">{{ $user->phone }}</p>
                </div>
            </div>

            <!-- Performa Bar Minimalis -->
            <div class="grid grid-cols-2 divide-x divide-slate-100 pt-3 border-t border-slate-100 text-center">
                <div>
                    <div class="text-base font-bold text-slate-900 flex items-center justify-center gap-1">
                        <svg class="w-4 h-4 fill-current text-amber-500" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>{{ number_format($averageRating, 1) }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 font-medium">Rating Kepuasan</div>
                </div>
                <div>
                    <div class="text-base font-bold text-slate-900">
                        {{ $completedOrdersCount }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 font-medium">Pesanan Selesai</div>
                </div>
            </div>
        </div>

        <!-- Kelompok A: Akun & Dokumen -->
        <div class="space-y-1.5">
            <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Akun & Dokumen</h3>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Data Pribadi -->
                <a href="{{ route('mitra.profile.personal') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Data Diri</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Rekening Bank Pencairan -->
                <a href="{{ route('mitra.profile.bank') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Rekening Pencairan Dana</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Dokumen Kemitraan -->
                <a href="{{ route('mitra.profile.documents') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Dokumen Kemitraan</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Keamanan Kata Sandi -->
                <a href="{{ route('mitra.profile.security') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Keamanan Kata Sandi</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Kelompok B: Operasional & Layanan -->
        <div class="space-y-1.5">
            <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Operasional & Layanan</h3>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Preferensi Layanan -->
                <a href="{{ route('mitra.profile.services') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Preferensi Jenis Layanan</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Wilayah Operasional & Relokasi -->
                <a href="{{ route('mitra.relocation.create') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Wilayah Kerja & Relokasi</span>
                    <div class="flex items-center space-x-1.5 shrink-0">
                        @if ($user->hasPendingRelocation())
                            <span class="text-xs font-semibold text-amber-600">Menunggu</span>
                        @endif
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>

        <!-- Kelompok C: Performa & Keuangan -->
        <div class="space-y-1.5">
            <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Performa & Keuangan</h3>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Dompet & Keuangan -->
                <a href="{{ route('mitra.finance.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Dompet & Tarik Saldo</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Status Sanksi & Banding -->
                <a href="{{ route('mitra.penalties.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Status Sanksi & Banding</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Kelompok D: Bantuan & Layanan -->
        <div class="space-y-1.5">
            <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Bantuan & Layanan</h3>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Pusat Bantuan & Live Chat -->
                <a href="{{ route('mitra.help.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Pusat Bantuan & Live Chat</span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Tombol Logout -->
        <div class="pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full py-3 rounded-2xl bg-white hover:bg-rose-50 text-rose-600 border border-slate-200/80 hover:border-rose-200 text-xs font-semibold transition flex items-center justify-center space-x-2 cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>

    </div>
</x-pwa-layout>
