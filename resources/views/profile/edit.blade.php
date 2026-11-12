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
    <x-pwa-layout title="Profil Saya - Pijat.In" :hideHeader="true">
        @php
            $user = auth()->user();
        @endphp

        <div class="-mx-4 -mt-3 -mb-8 min-h-screen bg-[#F8F9FA] pb-24 px-4 pt-4 space-y-4">

            <!-- 1. CLEAN MODERN TOP APP BAR -->
            <div class="flex items-center justify-between pt-1 pb-2">
                <div class="w-9 h-9"></div> <!-- Penyeimbang rata tengah -->

                <h1 class="text-base font-extrabold text-slate-900 tracking-tight">
                    Profil
                </h1>

                <a href="{{ route('pelanggan.help.index') }}" 
                   class="w-9 h-9 rounded-full bg-white border border-slate-200/80 text-slate-600 hover:text-emerald-700 hover:border-emerald-200 flex items-center justify-center shadow-2xs active:scale-95 transition cursor-pointer"
                   title="Pusat Bantuan"
                   aria-label="Pusat Bantuan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </div>

            <!-- Status Feedback Flash Message -->
            @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold shadow-2xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') === 'profile-updated' ? 'Data profil berhasil diperbarui.' : 'Kata sandi Anda berhasil diperbarui.' }}</span>
                </div>
            @endif

            <!-- 2. KARTU IDENTITAS PENGGUNA HORIZONTAL (FLOATING USER CARD) -->
            <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs flex items-center space-x-3.5">
                <!-- Avatar Bulat -->
                <div class="w-14 h-14 rounded-full bg-emerald-800 text-white font-extrabold text-xl flex items-center justify-center ring-4 ring-emerald-50 shrink-0 shadow-2xs">
                    {{ substr($user->name, 0, 1) }}
                </div>

                <!-- Info Nama, Email & Wilayah -->
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900 leading-snug truncate">
                        {{ $user->name }}
                    </h2>
                    <p class="text-xs text-slate-400 font-medium truncate mt-0.5">
                        {{ $user->email }}
                    </p>

                    @if ($user->isPelanggan())
                        <div class="mt-1.5 inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[11px] font-semibold text-emerald-800">
                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="truncate max-w-[180px]">{{ $user->region?->name ?? 'Belum ada wilayah' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. KELOMPOK MENU BERKARTU (GROUPED MENU CARDS) -->
            <div class="space-y-4">

                <!-- KELOMPOK 1: AKUN & KEAMANAN -->
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-1 mb-1.5 block">
                        Akun & Keamanan
                    </span>
                    <div class="bg-white rounded-3xl p-1.5 border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                        <!-- Informasi Pribadi -->
                        <a href="{{ route('profile.personal') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                    Informasi Pribadi
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Keamanan Kata Sandi -->
                        <a href="{{ route('profile.security') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                    Keamanan Kata Sandi
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>


                        <!-- Hapus Akun -->
                        <a href="{{ route('profile.delete') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-rose-50/50 transition group cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:bg-rose-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-rose-600 transition">
                                    Hapus Akun
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>

                        </a>
                    </div>
                </div>

                @if ($user->isPelanggan())
                    <!-- KELOMPOK 2: PESANAN & LAYANAN -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-1 mb-1.5 block">
                            Pesanan & Layanan
                        </span>
                        <div class="bg-white rounded-3xl p-1.5 border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                            <!-- Riwayat Pesanan -->
                            <a href="{{ route('pelanggan.orders.index') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                        Riwayat Pesanan
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <!-- Daftar Alamat -->
                            <a href="{{ route('pelanggan.addresses.index') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                        Daftar Alamat
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <!-- Pusat Aduan -->
                            <a href="{{ route('pelanggan.complaints.index') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                        Pusat Aduan
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <!-- Status Sanksi -->
                            <a href="{{ route('pelanggan.penalties.index') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                        Status Sanksi
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- KELOMPOK 3: BANTUAN & LAYANAN -->
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-1 mb-1.5 block">
                            Bantuan & Layanan
                        </span>
                        <div class="bg-white rounded-3xl p-1.5 border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                            <!-- Pusat Bantuan & Live Chat -->
                            <a href="{{ route('pelanggan.help.index') }}" class="min-h-[50px] px-3.5 py-3 flex items-center justify-between hover:bg-slate-50 transition group cursor-pointer">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs sm:text-[13px] font-semibold text-slate-800 group-hover:text-emerald-800 transition">
                                        Pusat Bantuan & Live Chat
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif

            </div>

            <!-- 4. TOMBOL KELUAR DARI AKUN (LOGOUT) -->
            <div class="pt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full min-h-[48px] py-3 rounded-2xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 text-xs font-bold tracking-wider transition flex items-center justify-center space-x-2 cursor-pointer shadow-2xs active:scale-95">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </div>

        </div>
    </x-pwa-layout>
@endif
