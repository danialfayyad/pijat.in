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
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Data profil berhasil diperbarui.</span>
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Kata sandi Anda berhasil diperbarui.</span>
                </div>
            @endif

            <!-- User Identity Card -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5">
                <div class="flex items-center space-x-3.5">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-700 text-white font-black text-xl flex items-center justify-center shrink-0 shadow-xs">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-extrabold text-slate-900 leading-tight truncate">{{ auth()->user()->name }}</h2>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                {{ auth()->user()->role?->label() ?? 'Pelanggan' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                @if (auth()->user()->isPelanggan())
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-1.5 text-slate-600">
                            <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Wilayah: <strong>{{ auth()->user()->region?->name ?? 'Belum ada alamat' }}</strong></span>
                        </div>
                        <a href="{{ route('pelanggan.addresses.index') }}" class="min-h-[44px] inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                            Kelola Alamat
                        </a>
                    </div>
                @endif
            </div>

            @if (auth()->user()->isPelanggan())
                <!-- Quick Navigation: Riwayat, Alamat, Aduan & Sanksi -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('pelanggan.orders.index') }}"
                       class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 transition flex items-center space-x-3 cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Riwayat Pesanan</span>
                            <span class="text-[10px] text-slate-400">Semua pesanan</span>
                        </div>
                    </a>

                    <a href="{{ route('pelanggan.addresses.index') }}"
                       class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 transition flex items-center space-x-3 cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Daftar Alamat</span>
                            <span class="text-[10px] text-slate-400">Kelola lokasi</span>
                        </div>
                    </a>

                    <a href="{{ route('pelanggan.complaints.index') }}"
                       class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 transition flex items-center space-x-3 cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Pusat Aduan</span>
                            <span class="text-[10px] text-slate-400">Riwayat keluhan</span>
                        </div>
                    </a>

                    <a href="{{ route('pelanggan.penalties.index') }}"
                       class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 transition flex items-center space-x-3 cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Status Sanksi</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ auth()->user()->penalty_points }} Poin</span>
                        </div>
                    </a>
                </div>
            @endif

            <!-- Card 1: Informasi Profil -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Card 2: Keamanan Kata Sandi -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Card 3: Keluar dari Akun (Khusus Halaman Profil) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
                <div>
                    <h3 class="text-xs font-bold text-slate-900">Keluar dari Akun</h3>
                    <p class="text-xs text-slate-500">Akhiri sesi aktif Anda pada perangkat ini.</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                        class="w-full min-h-[48px] py-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </div>

            <!-- Card 4: Zona Hapus Akun -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs">
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </x-pwa-layout>
@endif
