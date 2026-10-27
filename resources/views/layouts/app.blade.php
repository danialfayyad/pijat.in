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
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen flex">
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
                        @elseif (Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dashboard Wilayah
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Dashboard
                            </a>
                        @endif
                    </nav>

                    <!-- Mobile User & Logout -->
                    <div class="p-4 border-t border-emerald-900/50">
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
                    </div>
                </div>
            </div>

            <!-- Main Content Container -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Topbar Header (Spacious & Clean h-20) -->
                <header class="bg-white border-b border-slate-200/80 sticky top-0 z-20 px-6 sm:px-8 lg:px-10 h-20 flex items-center justify-between shadow-xs">
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

                    <!-- Right: User Quick Status -->
                    <div class="flex items-center space-x-3">
                        <div class="hidden sm:flex items-center space-x-2.5 px-3.5 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-700 shadow-2xs">
                            <div class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="text-xs font-semibold">{{ Auth::user()->name }}</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                    </div>
                </header>

                <!-- Page Main Body -->
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
