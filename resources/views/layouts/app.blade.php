<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Pijat In') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">
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
                        <span class="text-2xl font-black text-white">Pijat.In</span>
                    </div>

                    <!-- Mobile Navigation Links -->
                    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                        @if (Auth::user()->isSuperadmin())
                            <a href="{{ route('superadmin.regions.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.regions.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Master Wilayah
                            </a>
                            <a href="{{ route('superadmin.admins.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.admins.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Penugasan Admin
                            </a>
                            <a href="{{ route('superadmin.complaint-categories.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.complaint-categories.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Kategori Aduan
                            </a>
                            <a href="{{ route('admin.orders.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Pesanan Wilayah
                            </a>
                            <a href="{{ route('admin.catalog.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.catalog.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Katalog Layanan
                            </a>
                            <a href="{{ route('superadmin.parameters.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.parameters.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Parameter Sistem
                            </a>
                            <a href="{{ route('superadmin.audit-logs.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.audit-logs.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Log Audit Terpusat
                            </a>
                            <a href="{{ route('superadmin.legal-documents.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.legal-documents.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dokumen Legal & SOP
                            </a>
                        @elseif (Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dashboard Wilayah
                            </a>
                            <a href="{{ route('admin.orders.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Pesanan Wilayah
                            </a>
                            <a href="{{ route('admin.complaints.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.complaints.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Laporan Aduan
                            </a>
                            <a href="{{ route('admin.penalties.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.penalties.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Poin & Sanksi
                            </a>
                            <a href="{{ route('admin.appeals.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.appeals.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Banding Sanksi
                            </a>
                            <a href="{{ route('admin.mitra.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.mitra.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Verifikasi Mitra
                            </a>
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Manajemen Pengguna
                            </a>
                            @if (Auth::user()->canAccessMenu('KATALOG'))
                                <a href="{{ route('admin.catalog.index') }}" 
                                   class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.catalog.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
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
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 text-rose-300 hover:text-white">Keluar</button>
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

                    <!-- Right: Quick Actions & Notifications (ADM-14) -->
                    <div class="flex items-center space-x-3">
                        @if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin())
                            <a href="{{ route('admin.notifications.index') }}" 
                               class="relative p-2 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-xl transition"
                               title="Notifikasi & Antrean Tugas">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @php
                                    $unreadNotifsCount = Auth::user()->systemNotifications()->whereNull('read_at')->count();
                                @endphp
                                @if ($unreadNotifsCount > 0)
                                    <span class="absolute top-1 right-1 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-600 text-[9px] font-bold text-white items-center justify-center">
                                            {{ $unreadNotifsCount > 9 ? '9+' : $unreadNotifsCount }}
                                        </span>
                                    </span>
                                @endif
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
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Global Interactive Confirmation Modal Component -->
        <x-confirm-dialog />
    </body>
</html>
