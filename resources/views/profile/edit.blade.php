@if (auth()->user()->isAdmin() || auth()->user()->isSuperadmin())
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@else
    <x-pwa-layout title="Akun Saya - Pijat.In" :hideHeader="true">
        @php
            $user = auth()->user();
        @endphp

        <!-- 1. FIXED GREEN HERO CONTAINER (EMERALD LIVIN' STYLE) -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white pt-10 px-5 overflow-hidden">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            <!-- Top Bar: Judul Halaman & Pusat Bantuan -->
            <div class="relative z-10 flex items-center justify-between mb-2">
                <div class="w-8 h-8"></div> <!-- Penyeimbang -->

                <span class="text-xs font-black text-white uppercase tracking-widest drop-shadow-xs">
                    Pengaturan Akun
                </span>

                <a href="{{ route('pelanggan.help.index') }}" 
                   class="w-8 h-8 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                   title="Pusat Bantuan"
                   aria-label="Pusat Bantuan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </div>

            <!-- Avatar & Identitas Pelanggan (Ukuran Besar & Menonjol, Tanpa Titik Hijau) -->
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-slate-900 to-emerald-950 text-white font-black text-2xl flex items-center justify-center shadow-xl border-2 border-white/30">
                    {{ substr($user->name, 0, 1) }}
                </div>

                <h2 class="text-lg font-black text-white mt-2 leading-snug tracking-tight">
                    {{ $user->name }}
                </h2>
                <p class="text-xs text-emerald-100/90 font-mono mt-0.5">
                    {{ $user->email }}
                </p>

                @if ($user->isPelanggan())
                    <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs text-emerald-100 font-semibold shadow-xs">
                        <svg class="w-3.5 h-3.5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $user->region?->name ?? 'Belum ada alamat' }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. FIXED WHITE MENU CONTAINER (BERJARAK AMAN DI BAWAH BADGE WILAYAH HINGGA DOCK) -->
        <div class="fixed top-[272px] bottom-0 left-0 right-0 max-w-md mx-auto z-10 bg-white rounded-t-[32px] px-5 pt-4 pb-20 shadow-2xl flex flex-col justify-between overflow-y-auto">

            <!-- Status Feedback Jika Ada -->
            @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
                <div class="mb-2 p-2.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] font-bold shadow-xs flex items-center space-x-2 shrink-0">
                    <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') === 'profile-updated' ? 'Data profil berhasil diperbarui.' : 'Kata sandi Anda berhasil diperbarui.' }}</span>
                </div>
            @endif

            <!-- Kelompok Menu yang Rapi & Proporsional -->
            <div class="space-y-3.5 flex-1 flex flex-col justify-start">
                <!-- Kelompok A: Akun & Keamanan -->
                <div class="space-y-1">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1">Akun & Keamanan</h3>
                    <div class="bg-slate-50/80 rounded-2xl border border-slate-100 shadow-xs divide-y divide-slate-100 overflow-hidden">
                        <a href="{{ route('profile.personal') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                            <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Informasi Pribadi</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ route('profile.security') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                            <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Keamanan Kata Sandi</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ route('profile.delete') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-rose-50/50 transition group cursor-pointer">
                            <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-rose-600 transition">Hapus Akun</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                @if ($user->isPelanggan())
                    <!-- Kelompok B: Pesanan & Layanan -->
                    <div class="space-y-1">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1">Pesanan & Layanan</h3>
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-100 shadow-xs divide-y divide-slate-100 overflow-hidden">
                            <a href="{{ route('pelanggan.orders.index') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                                <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Riwayat Pesanan</span>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>

                            <a href="{{ route('pelanggan.addresses.index') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                                <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Daftar Alamat</span>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>

                            <a href="{{ route('pelanggan.complaints.index') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                                <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Pusat Aduan</span>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>

                            <a href="{{ route('pelanggan.penalties.index') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                                <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Status Sanksi</span>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Kelompok C: Bantuan & Layanan -->
                    <div class="space-y-1">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1">Bantuan & Layanan</h3>
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-100 shadow-xs divide-y divide-slate-100 overflow-hidden">
                            <a href="{{ route('pelanggan.help.index') }}" class="min-h-[48px] px-4 py-3 sm:py-3.5 flex items-center justify-between hover:bg-emerald-50/50 transition group cursor-pointer">
                                <span class="text-xs sm:text-[13px] font-bold text-slate-800 group-hover:text-emerald-700 transition">Pusat Bantuan & Live Chat</span>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tombol Keluar dari Akun -->
            <div class="pt-4 pb-1 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full min-h-[48px] py-3 rounded-2xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 text-xs font-black uppercase tracking-wider transition flex items-center justify-center space-x-2 cursor-pointer shadow-xs active:scale-95">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </div>

        </div>
    </x-pwa-layout>
@endif
