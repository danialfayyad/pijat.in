<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $appFavicon = \App\Models\Parameter::get('app_favicon');
            $appName = \App\Models\Parameter::get('app_name', config('app.name', 'Pijat.In'));
        @endphp
        @if ($appFavicon)
            <link rel="icon" href="{{ asset('storage/' . $appFavicon) }}">
        @endif
        <title>{{ $title ?? $appName }}</title>

        <!-- Fonts: Maison Neue & Fallbacks -->
        <link rel="preconnect" href="https://db.onlinewebfonts.com">
        <link href="https://db.onlinewebfonts.com/c/2173dceabf0710a335c0bca178efc162?family=Maison+Neue" rel="stylesheet" type="text/css"/>
        <link href="https://db.onlinewebfonts.com/c/b596fe7f763c7633f489a1acdbb13c70?family=Maison+Neue+Demi" rel="stylesheet" type="text/css"/>
        <link href="https://db.onlinewebfonts.com/c/4a373e70cd01dec089c1dd1185ceba51?family=Maison+Neue+Bold" rel="stylesheet" type="text/css"/>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="desktopNotificationCenter()">
        <div class="h-screen flex overflow-hidden bg-slate-50">
            <!-- Left Sidebar (Desktop) -->
            @include('layouts.sidebar')

            <!-- Mobile Sidebar Drawer (Slide-over) -->
            <div x-show="sidebarOpen" 
                 x-cloak
                 class="fixed inset-0 z-50 lg:hidden flex" 
                 role="dialog" 
                 aria-modal="true">
                
                <!-- Backdrop -->
                <div x-show="sidebarOpen" 
                     x-transition:enter="transition-opacity ease-linear duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="sidebarOpen = false" 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

                <!-- Drawer Content -->
                <div x-show="sidebarOpen" 
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative flex-1 flex flex-col max-w-xs w-full bg-gradient-to-b from-emerald-600 via-emerald-700 to-teal-800 text-white z-10 shadow-2xl">
                    
                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 -mr-12 pt-4">
                        <button type="button" @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full text-white hover:bg-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Brand -->
                    <div class="h-20 flex items-center px-6 border-b border-white/15 bg-white/10 backdrop-blur-xs">
                        <span class="text-2xl font-black text-white truncate">{{ $appName }}</span>
                    </div>

                    <!-- Mobile Navigation Links -->
                    <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        @if (Auth::user()->isSuperadmin())
                            <!-- Grup 1: Operasional Platform -->
                            <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Operasional Platform
                            </div>

                            <a href="{{ route('superadmin.dashboard') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.dashboard') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dashboard Platform
                            </a>
                            <a href="{{ route('superadmin.regions.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.regions.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Master Wilayah
                            </a>
                            <a href="{{ route('superadmin.admins.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.admins.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Penugasan Admin
                            </a>
                            <a href="{{ route('admin.orders.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Monitoring Pesanan
                            </a>

                            <!-- Grup 2: Bisnis & Finansial -->
                            <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Bisnis & Finansial
                            </div>

                            <a href="{{ route('superadmin.finance.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.finance.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Keuangan Nasional
                            </a>
                            <a href="{{ route('admin.catalog.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.catalog.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Katalog Layanan
                            </a>
                            <a href="{{ route('superadmin.vouchers.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.vouchers.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Voucher Diskon
                            </a>
                            <a href="{{ route('superadmin.banks.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.banks.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Master Rekening Bank
                            </a>

                            <!-- Grup 3: Konten & Bantuan -->
                            <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Konten & Bantuan
                            </div>

                            <a href="{{ route('superadmin.banners.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.banners.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Banner Promosi
                            </a>
                            <a href="{{ route('superadmin.complaint-categories.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.complaint-categories.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Kategori Aduan
                            </a>
                            <a href="{{ route('superadmin.faqs.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.faqs.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Manajemen FAQ
                            </a>
                            <a href="{{ route('superadmin.legal-documents.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.legal-documents.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dokumen Legal & SOP
                            </a>

                            <!-- Grup 4: Tata Kelola & Sistem -->
                            <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Tata Kelola & Sistem
                            </div>

                            <a href="{{ route('superadmin.parameters.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.parameters.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Parameter Sistem
                            </a>
                            <a href="{{ route('superadmin.branding.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.branding.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Identitas Aplikasi
                            </a>
                            <a href="{{ route('superadmin.audit-logs.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.audit-logs.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Log Audit Terpusat
                            </a>
                            <a href="{{ route('admin.notifications.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.notifications.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Notifikasi & Antrean
                            </a>
                        @elseif (Auth::user()->isAdmin())
                            <!-- Grup 1: Operasional Harian -->
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Operasional Harian
                            </div>

                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dashboard Wilayah
                            </a>
                            <a href="{{ route('admin.orders.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Pesanan Wilayah
                            </a>
                            <a href="{{ route('admin.notifications.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.notifications.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Notifikasi & Antrean
                            </a>

                            <!-- Grup 2: Mitra & Pengguna -->
                            <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Mitra & Pengguna
                            </div>

                            <a href="{{ route('admin.mitra.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.mitra.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Verifikasi Mitra
                            </a>
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Manajemen Pengguna
                            </a>
                            <a href="{{ route('admin.penalties.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.penalties.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Poin & Sanksi
                            </a>
                            <a href="{{ route('admin.appeals.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.appeals.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Banding Sanksi
                            </a>

                            <!-- Grup 3: Pusat Bantuan & Komplain -->
                            <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Pusat Bantuan & Komplain
                            </div>

                            <a href="{{ route('admin.support-chats.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.support-chats.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Live Chat Bantuan
                            </a>
                            <a href="{{ route('admin.complaints.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.complaints.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Laporan Aduan
                            </a>

                            <!-- Grup 4: Keuangan & Katalog -->
                            <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                                Keuangan & Katalog
                            </div>

                            <a href="{{ route('admin.finance.index') }}" 
                               class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.finance.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Keuangan Wilayah
                            </a>

                            @if (Auth::user()->canAccessMenu('KATALOG'))
                                <a href="{{ route('admin.catalog.index') }}" 
                                   class="flex items-center px-4 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.catalog.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                    Katalog Layanan
                                </a>
                            @endif
                        @else
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dashboard
                            </a>
                        @endif
                    </nav>

                    <!-- Mobile User & Logout -->
                    <div class="p-4 border-t border-emerald-900/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-emerald-400/80">{{ Auth::user()->email }}</p>
                            </div>
                            <form id="logout-form-mobile" method="POST" action="{{ route('logout') }}" onsubmit="event.preventDefault(); window.confirmLogout(this);">
                                @csrf
                                <button type="submit" class="p-2 text-rose-300 hover:text-white text-xs font-semibold cursor-pointer">Keluar</button>
                            </form>
                        </div>
                        @if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin())
                            <a href="{{ route('admin.profile.edit') }}" 
                               class="block text-center w-full py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-semibold transition">
                                Pengaturan Profil & Kata Sandi
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content Container -->
            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
                <!-- Topbar Header (Fixed at Top, Spacious & Clean h-20) -->
                <header class="bg-white border-b border-slate-200/80 shrink-0 px-6 sm:px-8 lg:px-10 h-20 flex items-center justify-between shadow-xs z-20">
                    <!-- Left: Mobile Toggle & Page Title -->
                    <div class="flex items-center space-x-4">
                        <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>

                        @isset($header)
                            <div>{{ $header }}</div>
                        @else
                            <h1 class="text-xl font-bold text-slate-800">Pijat In Backoffice</h1>
                        @endisset
                    </div>

                    <!-- Right: Quick Actions & Notifications (ADM-14, Web Push & Desktop Toast) -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        @if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin())

                            <a href="{{ route('admin.notifications.index') }}" 
                               class="relative p-2 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-xl transition"
                               title="Notifikasi & Antrean Tugas">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute top-1 right-1 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-600 text-[9px] font-bold text-white items-center justify-center"
                                              x-text="unreadCount > 9 ? '9+' : unreadCount">
                                        </span>
                                    </span>
                                </template>
                            </a>
                            <a href="{{ route('admin.profile.edit') }}" 
                               class="p-2 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-xl transition"
                               title="Pengaturan Profil">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </header>

                <!-- Page Main Body -->
                <main class="flex-1 overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin())
            <!-- Modal Wajib Izin Notifikasi Operasional Backoffice (SYS-06, ADM-14) -->
            <div x-show="isSupported && permissionStatus !== 'granted'"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="notification-modal-title">
                
                <!-- Backdrop Gelap (Tidak dapat ditutup klik luar karena izin wajib diaktifkan) -->
                <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-xs transition-opacity"
                     x-show="isSupported && permissionStatus !== 'granted'"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                </div>

                <!-- Card Modal -->
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200/90 overflow-hidden text-center z-10 transition-all p-6 sm:p-7"
                     x-show="isSupported && permissionStatus !== 'granted'"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-3">
                    
                    <!-- Garis Aksen Hijau Zamrud di Atas -->
                    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 absolute top-0 left-0"></div>

                    <!-- KONTEN 1: Saat status masih 'default' (Belum diminta izin) -->
                    <div x-show="permissionStatus === 'default'">
                        <!-- Icon Bel Notifikasi Bergetar Halus -->
                        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 ring-8 ring-emerald-50/70 shadow-xs">
                            <svg class="w-8 h-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider mb-2">
                            Izin Wajib Operasional
                        </span>

                        <h3 id="notification-modal-title" class="text-lg sm:text-xl font-bold text-slate-800">
                            Aktifkan Notifikasi Operasional
                        </h3>

                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Sebagai pengelola backoffice Pijat.In, izin notifikasi laptop <strong>wajib diaktifkan</strong> agar Anda dapat segera merespons antrean layanan secara real-time.
                        </p>

                        <!-- Poin Informasi Nilai Operasional -->
                        <div class="bg-slate-50/90 rounded-2xl p-4 my-5 text-left border border-slate-100 space-y-2.5 text-xs text-slate-600">
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                                <div><strong class="text-slate-800">Pesanan Masuk Wilayah:</strong> Peringatan instan saat ada pesanan baru di area Anda.</div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                                <div><strong class="text-slate-800">Laporan Aduan & Sanksi:</strong> Penanganan cepat untuk menjaga kepuasan pelanggan.</div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                                <div><strong class="text-slate-800">Nada Peringatan Audio:</strong> Panggilan otomatis berbunyi meski browser berada di latar belakang.</div>
                            </div>
                        </div>

                        <!-- Tombol Pemicu Izin Browser -->
                        <button type="button" 
                                @click="requestDesktopPermission()" 
                                class="w-full py-3 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition shadow-xs hover:shadow flex items-center justify-center gap-2 cursor-pointer">
                            <span>Izinkan Notifikasi Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>

                        <p class="text-[11px] text-slate-400 mt-3">
                            Klik tombol di atas, lalu pilih <strong>Allow (Izinkan)</strong> pada jendela pop-up browser Anda.
                        </p>
                    </div>

                    <!-- KONTEN 2: Saat status 'denied' (Izin diblokir di peramban) -->
                    <div x-show="permissionStatus === 'denied'">
                        <!-- Icon Gembok / Peringatan -->
                        <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 ring-8 ring-amber-50/70 shadow-xs">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider mb-2">
                            Izin Diblokir
                        </span>

                        <h3 class="text-lg sm:text-xl font-bold text-slate-800">
                            Izin Notifikasi Diblokir di Browser
                        </h3>

                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Browser Anda saat ini memblokir notifikasi untuk situs ini. Karena notifikasi bersifat <strong>wajib bagi Admin</strong>, silakan aktifkan kembali dengan langkah berikut:
                        </p>

                        <!-- Langkah Panduan Membuka Blokir -->
                        <div class="bg-amber-50/70 rounded-2xl p-4 my-5 text-left border border-amber-200/80 space-y-2 text-xs text-amber-900">
                            <div class="flex items-start gap-2">
                                <span class="font-bold text-amber-800 shrink-0">1.</span>
                                <span>Klik ikon <strong>Setelan Situs / Gembok 🔒</strong> di samping kiri kolom URL browser Anda.</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-bold text-amber-800 shrink-0">2.</span>
                                <span>Cari opsi <strong>Notifikasi (Notifications)</strong> lalu ubah dari <em>Block</em> menjadi <strong>Izinkan (Allow)</strong>.</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="font-bold text-amber-800 shrink-0">3.</span>
                                <span>Klik tombol <strong>Muat Ulang Halaman</strong> di bawah untuk menerapkan perubahan.</span>
                            </div>
                        </div>

                        <!-- Tombol Reload Halaman -->
                        <button type="button" 
                                @click="window.location.reload()" 
                                class="w-full py-3 px-5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-xs hover:shadow flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Saya Sudah Mengizinkan, Muat Ulang Halaman</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Global Interactive Confirmation Modal Component -->
        <x-confirm-dialog />

        <!-- Web Push & Live Desktop Notification Handler (SYS-06, ADM-14) -->
        <script>
            function desktopNotificationCenter() {
                return {
                    sidebarOpen: false,
                    isSupported: ('Notification' in window),
                    permissionStatus: ('Notification' in window) ? Notification.permission : 'denied',
                    unreadCount: {{ Auth::user()->systemNotifications()->whereNull('read_at')->count() }},
                    lastNotifiedId: parseInt(localStorage.getItem('pijatin_last_notified_id') || '0', 10),
                    swRegistration: null,

                    init() {
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.register('/sw.js')
                                .then(reg => {
                                    this.swRegistration = reg;
                                })
                                .catch(err => console.debug('Service Worker info:', err));
                        }

                        if ('Notification' in window) {
                            this.permissionStatus = Notification.permission;
                            if (this.permissionStatus === 'granted') {
                                this.subscribePushManager();
                            }
                        }

                        // Poll every 10 seconds for new unread notifications
                        setInterval(() => {
                            this.pollLatestNotifications();
                        }, 10000);
                    },

                    playChime() {
                        try {
                            const ctx = new (window.AudioContext || window.webkitAudioContext)();
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.09);
                            gain.gain.setValueAtTime(0.2, ctx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.35);
                        } catch (e) {
                            console.debug('Audio chime error:', e);
                        }
                    },

                    async requestDesktopPermission() {
                        if (!('Notification' in window)) {
                            alert('Browser Anda tidak mendukung Web Notification API.');
                            return;
                        }

                        try {
                            const perm = await Notification.requestPermission();
                            this.permissionStatus = perm;

                            if (perm === 'granted') {
                                this.testDesktopNotification();
                                this.subscribePushManager();
                            }
                        } catch (e) {
                            console.error('Gagal meminta izin notifikasi:', e);
                        }
                    },

                    testDesktopNotification() {
                        this.playChime();
                        this.triggerDesktopAlert({
                            id: Date.now(),
                            title: 'Pijat.In - Notifikasi Laptop Aktif!',
                            body: 'Izin berhasil diberikan! Peringatan antrean tugas operasional kini aktif di laptop Anda.',
                            action_url: '{{ route("admin.notifications.index") }}'
                        });
                    },

                    async subscribePushManager() {
                        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
                        try {
                            const reg = await navigator.serviceWorker.ready;
                            const res = await fetch('{{ route("push.public-key") }}');
                            const { publicKey } = await res.json();
                            if (!publicKey) return;

                            const convertedKey = this.urlBase64ToUint8Array(publicKey);
                            const subscription = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: convertedKey
                            });

                            const subData = subscription.toJSON();
                            await fetch('{{ route("push.subscribe") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    endpoint: subscription.endpoint,
                                    public_key: subData.keys?.p256dh || null,
                                    auth_token: subData.keys?.auth || null,
                                    content_encoding: 'aes128gcm'
                                })
                            });
                        } catch (e) {
                            console.debug('Push subscription info:', e);
                        }
                    },

                    async pollLatestNotifications() {
                        try {
                            const res = await fetch('{{ route("admin.notifications.check-unread") }}', {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (!res.ok) return;
                            const data = await res.json();

                            this.unreadCount = data.unread_count;

                            if (data.latest && data.latest.id > this.lastNotifiedId) {
                                this.lastNotifiedId = data.latest.id;
                                localStorage.setItem('pijatin_last_notified_id', data.latest.id.toString());

                                if (this.permissionStatus === 'granted') {
                                    this.triggerDesktopAlert(data.latest);
                                }
                            }
                        } catch (e) {
                            // Silent fail on temporary network hiccup
                        }
                    },

                    triggerDesktopAlert(notifData) {
                        this.playChime();
                        const title = notifData.title || 'Pijat.In - Tugas Masuk';
                        const options = {
                            body: notifData.body || 'Ada antrean operasional atau tugas baru yang memerlukan tindakan Anda.',
                            icon: '/favicon.ico',
                            badge: '/favicon.ico',
                            tag: 'pijatin_alert_' + (notifData.id || Date.now()),
                            renotify: true,
                            data: {
                                action_url: notifData.action_url || '{{ route("admin.notifications.index") }}'
                            }
                        };

                        let shown = false;
                        if ('Notification' in window && Notification.permission === 'granted') {
                            try {
                                const n = new Notification(title, options);
                                n.onclick = (e) => {
                                    e.preventDefault();
                                    window.focus();
                                    window.location.href = options.data.action_url;
                                };
                                shown = true;
                            } catch (err) {
                                console.debug('Direct notification constructor skipped, trying Service Worker:', err);
                            }
                        }

                        if (!shown && this.swRegistration && 'showNotification' in this.swRegistration) {
                            this.swRegistration.showNotification(title, options).catch(e => console.debug('SW showNotification err:', e));
                        }
                    },

                    urlBase64ToUint8Array(base64String) {
                        const padding = '='.repeat((4 - base64String.length % 4) % 4);
                        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                        const rawData = window.atob(base64);
                        const outputArray = new Uint8Array(rawData.length);
                        for (let i = 0; i < rawData.length; ++i) {
                            outputArray[i] = rawData.charCodeAt(i);
                        }
                        return outputArray;
                    }
                };
            }
            window.desktopNotificationCenter = desktopNotificationCenter;
            if (window.Alpine) {
                window.Alpine.data('desktopNotificationCenter', desktopNotificationCenter);
            }
        </script>
    </body>
</html>
