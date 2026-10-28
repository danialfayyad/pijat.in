<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Pijat.In') }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-900 text-slate-800">
    <!-- PWA Outer Shell Container: Centered on desktop, full-width on mobile -->
    <div class="min-h-screen flex flex-col justify-between max-w-md mx-auto bg-slate-50 relative shadow-2xl border-x border-slate-200/80">

        <!-- Top PWA App Bar -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/70 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                @if (isset($backUrl))
                    <a href="{{ $backUrl }}" class="w-8 h-8 -ml-1 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold tracking-tight text-slate-900 leading-none">
                            {{ $headerTitle ?? 'Pijat.In' }}
                        </h1>
                        <span class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider block mt-0.5">
                            {{ Auth::user()?->role?->label() ?? 'PWA App' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Header Right Action -->
            <div class="flex items-center space-x-2">
                @if (isset($headerAction))
                    {{ $headerAction }}
                @else
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Keluar Akun" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center text-slate-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    @endauth
                @endif
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 pb-24 px-4 pt-3">
            {{ $slot }}
        </main>

        <!-- Bottom Navigation Bar (Dock) -->
        @auth
            @if (Auth::user()->isMitra() && Auth::user()->isTerverifikasi())
                <!-- Mitra Verified Dock -->
                <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 py-1.5 shadow-lg flex items-center justify-around">
                    <a href="{{ route('mitra.dashboard') }}" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition {{ request()->routeIs('mitra.dashboard') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
                        <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('mitra.dashboard') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-[10px]">Beranda</span>
                    </a>

                    <a href="{{ route('mitra.dashboard') }}#orders" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition text-slate-400 hover:text-slate-600 font-medium">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span class="text-[10px]">Pesanan</span>
                    </a>

                    <a href="{{ route('mitra.dashboard') }}#wallet" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition text-slate-400 hover:text-slate-600 font-medium">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-[10px]">Dompet</span>
                    </a>

                    <a href="{{ route('mitra.profile') }}" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition {{ request()->routeIs('mitra.profile') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
                        <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('mitra.profile') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[10px]">Profil</span>
                    </a>
                </nav>
            @elseif (Auth::user()->isPelanggan())
                <!-- Pelanggan Dock -->
                <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 py-1.5 shadow-lg flex items-center justify-around">
                    <a href="{{ route('pelanggan.dashboard') }}" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition {{ request()->routeIs('pelanggan.dashboard') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
                        <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('pelanggan.dashboard') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-[10px]">Beranda</span>
                    </a>

                    <a href="{{ route('pelanggan.dashboard') }}#orders" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition text-slate-400 hover:text-slate-600 font-medium">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="text-[10px]">Pesanan</span>
                    </a>

                    <a href="{{ route('pelanggan.dashboard') }}#wallet" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition text-slate-400 hover:text-slate-600 font-medium">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-[10px]">Dompet</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" 
                        class="flex flex-col items-center py-1 px-3 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
                        <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('profile.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[10px]">Akun</span>
                    </a>
                </nav>
            @endif
        @endauth

    </div>
</body>
</html>
