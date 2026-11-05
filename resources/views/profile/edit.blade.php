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
    <x-pwa-layout title="Akun Saya - Pijat.In" headerTitle="Pengaturan Akun">
        <div class="py-2 space-y-4">

            <!-- Profile Status Feedback -->
            @if (session('status') === 'profile-updated')
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Data profil berhasil diperbarui.</span>
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Kata sandi Anda berhasil diperbarui.</span>
                </div>
            @endif

            <!-- User Identity Card -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-3.5">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-lg flex items-center justify-center shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-bold text-slate-900 leading-tight truncate">{{ auth()->user()->name }}</h2>
                        <p class="text-xs text-slate-500 mt-1 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                @if (auth()->user()->isPelanggan())
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-1.5 text-slate-600 truncate mr-2">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="truncate">Wilayah: <strong>{{ auth()->user()->region?->name ?? 'Belum ada alamat' }}</strong></span>
                        </div>
                        <a href="{{ route('pelanggan.addresses.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline shrink-0">
                            Kelola Alamat
                        </a>
                    </div>
                @endif
            </div>

            <!-- Kelompok A: Akun & Keamanan -->
            <div class="space-y-1.5">
                <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Akun & Keamanan</h3>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                    <a href="{{ route('profile.personal') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                        <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Informasi Pribadi</span>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <a href="{{ route('profile.security') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                        <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Keamanan Kata Sandi</span>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <a href="{{ route('profile.delete') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                        <span class="text-sm font-semibold text-slate-800 group-hover:text-rose-600 transition">Hapus Akun</span>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            @if (auth()->user()->isPelanggan())
                <!-- Kelompok B: Pesanan & Layanan -->
                <div class="space-y-1.5">
                    <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Pesanan & Layanan</h3>
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                        <a href="{{ route('pelanggan.orders.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Riwayat Pesanan</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ route('pelanggan.addresses.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Daftar Alamat</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ route('pelanggan.complaints.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Pusat Aduan</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ route('pelanggan.penalties.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Status Sanksi</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Kelompok C: Bantuan & Layanan -->
                <div class="space-y-1.5">
                    <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Bantuan & Layanan</h3>
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                        <a href="{{ route('pelanggan.help.index') }}" class="px-4 py-3.5 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-emerald-700 transition">Pusat Bantuan & Live Chat</span>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Tombol Keluar dari Akun -->
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
@endif
