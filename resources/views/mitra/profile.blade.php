<x-pwa-layout title="Profil Mitra - Pijat.In" headerTitle="Akun & Kemitraan" :backUrl="route('mitra.dashboard')">
    <div class="py-2 space-y-4">

        <!-- Status Feedback -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- 1. Header Identitas Mitra -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-700 to-teal-600 text-white font-black text-xl flex items-center justify-center shrink-0 shadow-xs">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-base font-extrabold text-slate-900 leading-tight truncate">{{ $user->name }}</h2>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                            {{ $user->status?->label() ?? 'Mitra Terverifikasi' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-mono mt-0.5 truncate">{{ $user->phone }}</p>
                </div>
            </div>

            <!-- Performa Mini Bar -->
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 fill-current text-amber-500" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold">Rating Kepuasan</span>
                        <span class="text-xs font-bold text-slate-800">{{ number_format($averageRating, 1) }} / 5.0</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold">Pesanan Selesai</span>
                        <span class="text-xs font-bold text-slate-800 font-mono">{{ $completedOrdersCount }} Order</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelompok A: Pengaturan Akun & Dokumen -->
        <div class="space-y-1.5">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">Akun & Dokumen</h3>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Data Pribadi -->
                <a href="{{ route('mitra.profile.personal') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Data Diri</span>
                            <span class="text-[11px] text-slate-400 block truncate">Nama lengkap & nomor WhatsApp</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Rekening Bank Pencairan -->
                <a href="{{ route('mitra.profile.bank') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 group-hover:bg-teal-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Rekening Pencairan Dana</span>
                            <span class="text-[11px] text-slate-400 block truncate">
                                {{ $user->primaryBankAccount ? ($user->primaryBankAccount->bank_name . ' - ' . $user->primaryBankAccount->account_number) : 'Atur rekening bank' }}
                            </span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Dokumen Kemitraan -->
                <a href="{{ route('mitra.profile.documents') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Dokumen Kemitraan</span>
                            <span class="text-[11px] text-slate-400 block truncate">KTP, Foto Selfie, Ijazah, SKCK</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Keamanan Kata Sandi -->
                <a href="{{ route('mitra.profile.security') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 group-hover:bg-slate-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Keamanan Kata Sandi</span>
                            <span class="text-[11px] text-slate-400 block truncate">Perbarui kata sandi login</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Kelompok B: Operasional & Layanan -->
        <div class="space-y-1.5">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">Operasional & Layanan</h3>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Preferensi Layanan -->
                <a href="{{ route('mitra.profile.services') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Preferensi Jenis Layanan</span>
                            <span class="text-[11px] text-slate-400 block truncate">{{ isset($services) && $services->isNotEmpty() ? $services->pluck('name')->join(', ') : 'Pijat Tradisional, Refleksi Kaki, Aromaterapi Spa' }}</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Wilayah Operasional & Relokasi -->
                <a href="{{ route('mitra.relocation.create') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Wilayah Kerja & Relokasi</span>
                            <span class="text-[11px] text-slate-400 block truncate">
                                {{ $user->partnerProfile?->region?->name ?? 'Belum Ditentukan' }} • Pindah Wilayah
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 shrink-0">
                        @if ($user->hasPendingRelocation())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                        @endif
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
        </div>

        <!-- Kelompok C: Performa & Keuangan -->
        <div class="space-y-1.5">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">Performa & Keuangan</h3>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Dompet & Keuangan -->
                <a href="{{ route('mitra.finance.index') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Dompet & Tarik Saldo</span>
                            <span class="text-[11px] font-mono font-bold text-slate-500 block truncate">
                                Rp {{ number_format($user->wallet?->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <!-- Status Sanksi & Poin SP -->
                <a href="{{ route('mitra.penalties.index') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $user->penalty_points > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Status Sanksi & Banding</span>
                            <span class="text-[11px] text-slate-400 block truncate">
                                {{ $user->penalty_points }} Poin Sanksi / 150 Pts
                            </span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Kelompok D: Bantuan & Sesi -->
        <div class="space-y-1.5">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">Bantuan & Sesi</h3>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- Pusat Bantuan & Live Chat (Disatukan) -->
                <a href="{{ route('mitra.help.index') }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate group-hover:text-emerald-700">Pusat Bantuan & Live Chat</span>
                            <span class="text-[11px] text-slate-400 block truncate">Panduan FAQ & chat bantuan admin wilayah</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Tombol Logout -->
        <div class="pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full py-3 rounded-2xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200/80 font-bold text-xs transition flex items-center justify-center space-x-2 cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>

    </div>
</x-pwa-layout>
