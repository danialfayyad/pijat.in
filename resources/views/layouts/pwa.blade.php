@props([
    'title' => null,
    'headerTitle' => null,
    'headerSubtitle' => null,
    'backUrl' => null,
    'headerAction' => null,
    'fullBleed' => false,
    'hideDock' => null,
    'hideLogo' => null,
    'hideNotification' => false,
    'hideHeader' => false,
    'requireLocation' => true,
])
@php
    $isRootTab = false;
    if (Auth::check()) {
        if (Auth::user()->isMitra()) {
            $isRootTab = request()->routeIs([
                'mitra.dashboard',
                'mitra.orders.scheduled',
                'mitra.orders.my-schedule',
                'mitra.finance.index',
                'mitra.profile',
            ]);
        } elseif (Auth::user()->isPelanggan()) {
            $isRootTab = request()->routeIs([
                'pelanggan.dashboard',
                'pelanggan.catalog.index',
                'pelanggan.wallet.index',
                'pelanggan.orders.index',
                'profile.edit',
            ]);
        }
    }

    $shouldHideDock = $hideDock ?? (!$isRootTab);
    $shouldHideLogo = $hideLogo ?? isset($backUrl);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $appFavicon = \App\Models\Parameter::get('app_favicon');
        $appName = \App\Models\Parameter::get('app_name', config('app.name', 'Pijat.In'));
        $appLogo = \App\Models\Parameter::get('app_logo');
    @endphp

    <title>{{ $title ?? $appName }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="/manifest.json">
    @if ($appFavicon)
        <link rel="icon" href="{{ asset('storage/' . $appFavicon) }}">
    @else
        <link rel="icon" href="/favicon.ico">
    @endif

    <!-- Fonts: Maison Neue & Fallbacks -->
    <link rel="preconnect" href="https://db.onlinewebfonts.com">
    <link href="https://db.onlinewebfonts.com/c/2173dceabf0710a335c0bca178efc162?family=Maison+Neue" rel="stylesheet" type="text/css"/>
    <link href="https://db.onlinewebfonts.com/c/b596fe7f763c7633f489a1acdbb13c70?family=Maison+Neue+Demi" rel="stylesheet" type="text/css"/>
    <link href="https://db.onlinewebfonts.com/c/4a373e70cd01dec089c1dd1185ceba51?family=Maison+Neue+Bold" rel="stylesheet" type="text/css"/>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        div:where(.swal2-container).swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            z-index: 99999 !important;
        }
        div:where(.swal2-container) div:where(.swal2-popup).swal-admin-theme {
            width: calc(100% - 2rem) !important;
            max-width: 380px !important;
            padding: 1.5rem !important;
            border-radius: 1.5rem !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            font-family: inherit !important;
            background: #ffffff !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon {
            width: 3.5rem !important;
            height: 3.5rem !important;
            margin: 0.25rem auto 1rem !important;
            border: none !important;
            border-radius: 9999px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            box-sizing: border-box !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon .swal2-icon-content {
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-question,
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-info {
            background-color: #ecfdf5 !important;
            color: #059669 !important;
            box-shadow: 0 0 0 8px #f0fdf4 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-warning {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
            box-shadow: 0 0 0 8px #fffbeb !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-success {
            background-color: #ecfdf5 !important;
            color: #059669 !important;
            box-shadow: 0 0 0 8px #f0fdf4 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-error {
            background-color: #fef2f2 !important;
            color: #e11d48 !important;
            box-shadow: 0 0 0 8px #fff1f2 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-title {
            font-size: 1.05rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            line-height: 1.25 !important;
            padding: 0 !important;
            margin: 0 0 0.5rem 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-html-container {
            font-size: 0.8rem !important;
            color: #64748b !important;
            line-height: 1.5 !important;
            margin: 0 0 1.25rem 0 !important;
            padding: 0 0.25rem !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-actions {
            display: flex !important;
            flex-direction: row !important;
            gap: 0.625rem !important;
            margin: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm {
            margin: 0 !important;
            flex: 1 1 0% !important;
            width: auto !important;
            min-height: 42px !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            background-color: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(5, 150, 105, 0.2) !important;
            transition: all 0.15s ease-in-out !important;
            border: none !important;
            cursor: pointer !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm:hover {
            background-color: #047857 !important;
        }
    </style>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-900 text-slate-800">
    <!-- PWA Outer Shell Container: Centered on desktop, full-width on mobile -->
    <div class="{{ ($fullBleed ?? false) ? 'h-[100dvh] max-h-[100dvh] flex flex-col justify-between max-w-md mx-auto bg-slate-50 relative shadow-2xl border-x border-slate-200/80 overflow-hidden' : 'min-h-screen flex flex-col justify-between max-w-md mx-auto bg-slate-50 relative shadow-2xl border-x border-slate-200/80' }}">

        <!-- Top PWA App Bar -->
        @if (!($hideHeader ?? false))
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/70 px-4 py-2.5 flex items-center justify-between shrink-0 min-h-[56px]">
            <div class="flex items-center space-x-2 min-w-0">
                @if (isset($backUrl))
                    <a href="{{ $backUrl }}" class="w-8 h-8 -ml-1 rounded-full flex items-center justify-center text-slate-700 hover:bg-slate-100 transition shrink-0" aria-label="Kembali">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif
                <div class="flex items-center space-x-2 min-w-0">
                    @if (!$shouldHideLogo)
                        @if ($appLogo)
                            <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="w-8 h-8 rounded-xl object-contain bg-white p-0.5 shadow-xs shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-xs shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                            </div>
                        @endif
                    @endif
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-slate-900 leading-tight truncate">
                            {{ $headerTitle ?? $appName }}
                        </h1>
                        <span class="text-[11px] text-slate-500 truncate block mt-0.5">
                            {{ $headerSubtitle ?? (Auth::user()?->role?->label() ?? 'PWA App') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Header Right Action -->
            <div class="flex items-center space-x-1.5 shrink-0">
                @if (isset($headerAction))
                    {{ $headerAction }}
                @endif

                @if (!$hideNotification && Auth::check())
                    <!-- Notification Bell Button -->
                    @php
                        $unreadNotificationCount = Auth::user()->systemNotifications()->unread()->count();
                    @endphp
                    <button type="button" 
                            x-data="{ unread: {{ $unreadNotificationCount }} }"
                            @notifications-read-all.window="unread = 0"
                            @notification-read.window="unread = Math.max(0, unread - 1)"
                            @notifications-updated.window="unread = $event.detail"
                            @click="$dispatch('open-notification-modal')"
                            onclick="window.dispatchEvent(new CustomEvent('open-notification-modal'))"
                            class="relative p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer"
                            title="Buka Notifikasi"
                            aria-label="Lihat Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unread > 0" 
                              class="absolute top-1 right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-600 text-[9px] font-bold text-white ring-2 ring-white"
                              x-text="unread > 99 ? '99+' : unread">
                        </span>
                    </button>
                @endif
            </div>
        </header>
        @endif

        <!-- Main Content Area -->
        <main class="{{ ($fullBleed ?? false) ? 'flex-1 relative overflow-hidden h-full flex flex-col min-h-0' : ($shouldHideDock ? 'flex-1 pb-8 px-4 pt-3' : 'flex-1 pb-24 px-4 pt-3') }}">
            {{ $slot }}
        </main>

        <!-- Bottom Navigation Bar (Dock) -->
        @auth
            @if (!$shouldHideDock)
                @if (Auth::user()->isMitra() && Auth::user()->isTerverifikasi())
                <!-- Mitra Verified Dock -->
                <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 py-2 shadow-lg flex items-center justify-around" aria-label="Navigasi Utama Mitra">
                    <a href="{{ route('mitra.dashboard') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('mitra.dashboard') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('mitra.dashboard') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-[11px] leading-none">Beranda</span>
                    </a>

                    <a href="{{ route('mitra.orders.scheduled') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('mitra.orders.scheduled') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('mitra.orders.scheduled') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="text-[11px] leading-none">Bursa</span>
                    </a>

                    <a href="{{ route('mitra.orders.my-schedule') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('mitra.orders.my-schedule') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('mitra.orders.my-schedule') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-[11px] leading-none">Jadwal</span>
                    </a>

                    <a href="{{ route('mitra.finance.index') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('mitra.finance.*') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('mitra.finance.*') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[11px] leading-none">Keuangan</span>
                    </a>

                    <a href="{{ route('mitra.profile') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('mitra.profile') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('mitra.profile') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[11px] leading-none">Profil</span>
                    </a>
                </nav>
            @elseif (Auth::user()->isPelanggan())
                <!-- Pelanggan Dock (4 Tab Sesuai Desain: Beranda, Pesanan, Chat, Profil) -->
                <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-4 py-2 shadow-lg flex items-center justify-around" aria-label="Navigasi Utama Pelanggan">
                    <a href="{{ route('pelanggan.dashboard') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.dashboard') ? 'text-emerald-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.dashboard') ? 'text-emerald-700' : 'text-slate-500' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="text-[11px] leading-none">Beranda</span>
                    </a>

                    <a href="{{ route('pelanggan.orders.index') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.orders.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.orders.*') ? 'text-emerald-700' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span class="text-[11px] leading-none">Pesanan</span>
                    </a>

                    <a href="{{ route('pelanggan.help.index') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.help.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.help.*') ? 'text-emerald-700' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="text-[11px] leading-none">Chat</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('profile.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-500 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('profile.*') ? 'text-emerald-700' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[11px] leading-none">Profil</span>
                    </a>
                </nav>
            @endif
            @endif
        @endauth

    </div>

    @if (Auth::check() && Auth::user()->isMitra() && ($requireLocation ?? true))
        <!-- Mandatory Location Access Guard for Mitra (SYS-01 & MIT-04) -->
        <div x-data="pwaLocationGuard()" x-init="initGuard()" x-cloak>
            <div x-show="isBlocked" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
                 style="display: none;">
                
                <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl border border-slate-200/90 space-y-4">
                    <!-- Icon GPS with pulse -->
                    <div class="relative mx-auto w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center ring-8 ring-emerald-50">
                        <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="absolute top-0 right-0 w-4 h-4 rounded-full bg-rose-500 border-2 border-white flex items-center justify-center text-[9px] text-white font-bold">!</span>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold text-rose-600 uppercase tracking-wide mb-1.5">
                            Mitra Terapis: Akses Lokasi Wajib
                        </p>
                        <h3 class="text-base font-extrabold text-slate-900 leading-snug">
                            Izin Lokasi GPS Mitra Diperlukan
                        </h3>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Sebagai mitra terapis Pijat.In, akses GPS wajib diaktifkan agar sistem dapat memvalidasi kehadiran Anda, membagikan rute posisi ke pelanggan, serta menerima tawaran pesanan baru di sekitar Anda.
                        </p>
                    </div>

                    <!-- Panduan jika diblokir -->
                    <div x-show="permissionState === 'denied'" class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-left text-[11px] text-amber-950 space-y-1">
                        <p class="font-bold flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Izin Lokasi Diblokir di Browser</span>
                        </p>
                        <ol class="list-decimal pl-4 space-y-0.5 text-amber-900 text-[10px] leading-relaxed">
                            <li>Klik ikon <strong>Gembok / Setelan Situs</strong> di sebelah kiri address bar.</li>
                            <li>Ubah opsi <strong>Lokasi</strong> menjadi <strong>Izinkan (Allow)</strong>.</li>
                            <li>Klik tombol <strong>Muat Ulang Halaman</strong> di bawah.</li>
                        </ol>
                    </div>

                    <div x-show="errorMessage && permissionState !== 'denied'" class="p-2.5 bg-rose-50 rounded-xl border border-rose-200 text-rose-800 text-[11px] text-center" x-text="errorMessage"></div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-1">
                        <button type="button" 
                                @click="requestLocation()" 
                                :disabled="isRequesting"
                                class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span x-text="isRequesting ? 'Memproses Izin...' : 'Aktifkan Izin Lokasi GPS'"></span>
                        </button>

                        <button type="button" 
                                @click="window.location.reload()" 
                                class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                            <span>Muat Ulang Halaman</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function pwaLocationGuard() {
                return {
                    isBlocked: false,
                    permissionState: 'checking',
                    isRequesting: false,
                    errorMessage: '',

                    async initGuard() {
                        if (!navigator.geolocation) {
                            this.permissionState = 'unsupported';
                            this.isBlocked = true;
                            this.errorMessage = 'Browser Anda tidak mendukung deteksi lokasi GPS.';
                            return;
                        }

                        if (navigator.permissions && navigator.permissions.query) {
                            try {
                                const status = await navigator.permissions.query({ name: 'geolocation' });
                                this.updateFromState(status.state);

                                status.onchange = () => {
                                    this.updateFromState(status.state);
                                };
                                return;
                            } catch (e) {
                                // Fallback
                            }
                        }

                        // Fallback jika Permissions API query tidak tersedia
                        this.testLocationAccess();
                    },

                    updateFromState(state) {
                        this.permissionState = state;
                        if (state === 'granted') {
                            this.isBlocked = false;
                            this.syncLocation();
                        } else {
                            this.isBlocked = true;
                        }
                    },

                    testLocationAccess() {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.permissionState = 'granted';
                                this.isBlocked = false;
                                this.syncLocation(pos.coords.latitude, pos.coords.longitude);
                            },
                            (err) => {
                                this.isBlocked = true;
                                if (err.code === 1) { // PERMISSION_DENIED
                                    this.permissionState = 'denied';
                                }
                            },
                            { timeout: 5000, maximumAge: 30000 }
                        );
                    },

                    requestLocation() {
                        if (this.isRequesting) return;
                        this.isRequesting = true;
                        this.errorMessage = '';

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.isRequesting = false;
                                this.permissionState = 'granted';
                                this.isBlocked = false;
                                this.syncLocation(pos.coords.latitude, pos.coords.longitude);
                            },
                            (err) => {
                                this.isRequesting = false;
                                this.isBlocked = true;
                                if (err.code === 1) {
                                    this.permissionState = 'denied';
                                    this.errorMessage = 'Izin akses lokasi ditolak oleh browser.';
                                } else if (err.code === 2) {
                                    this.errorMessage = 'Sinyal GPS tidak tersedia. Aktifkan GPS di setelan perangkat.';
                                } else {
                                    this.errorMessage = 'Gagal mendeteksi lokasi GPS.';
                                }
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    },

                    syncLocation(lat = null, lng = null) {
                        if (lat && lng) {
                            window.dispatchEvent(new CustomEvent('pwa-location-ready', {
                                detail: { latitude: lat, longitude: lng }
                            }));
                        }
                    }
                };
            }
        </script>
    @endif
    @auth
        <!-- PWA Web Push Notification Manager (SYS-06, CUS-21, MIT-21) -->
        <script>
            window.PwaPushNotification = {
                isSupported: ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window),
                get permission() {
                    return ('Notification' in window) ? Notification.permission : 'unsupported';
                },

                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                    const base64 = (base64String + padding)
                        .replace(/-/g, '+')
                        .replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                },

                async registerServiceWorker() {
                    if (!('serviceWorker' in navigator)) return null;
                    try {
                        const reg = await navigator.serviceWorker.register('/sw.js');
                        return reg;
                    } catch (e) {
                        console.debug('PWA Service Worker register info:', e);
                        return null;
                    }
                },

                async syncSubscription() {
                    if (!this.isSupported || this.permission !== 'granted') return null;
                    try {
                        const reg = await navigator.serviceWorker.ready;
                        let subscription = await reg.pushManager.getSubscription();

                        if (!subscription) {
                            const res = await fetch('{{ route("push.public-key") }}');
                            const { publicKey } = await res.json();
                            if (!publicKey) return null;

                            subscription = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: this.urlBase64ToUint8Array(publicKey)
                            });
                        }

                        const subData = subscription.toJSON();
                        await fetch('{{ route("push.subscribe") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            body: JSON.stringify({
                                endpoint: subscription.endpoint,
                                public_key: subData.keys?.p256dh || null,
                                auth_token: subData.keys?.auth || null,
                                content_encoding: 'aes128gcm'
                            })
                        });

                        window.dispatchEvent(new CustomEvent('pwa-push-subscribed', { detail: subscription }));
                        return subscription;
                    } catch (e) {
                        console.debug('PWA push sync error:', e);
                        return null;
                    }
                },

                async requestPermission() {
                    if (!('Notification' in window)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                iconHtml: 'i',
                                title: 'Tidak Didukung',
                                text: 'Peramban Anda tidak mendukung fitur Web Push Notification.',
                                confirmButtonText: 'Tutup',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-info',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        } else {
                            alert('Peramban Anda tidak mendukung Web Push Notification.');
                        }
                        return 'unsupported';
                    }

                    try {
                        const perm = await Notification.requestPermission();
                        if (perm === 'granted') {
                            window.dispatchEvent(new CustomEvent('pwa-push-permission-changed', { detail: 'granted' }));
                            try {
                                await this.syncSubscription();
                            } catch (syncErr) {
                                console.debug('Push sync warning:', syncErr);
                            }
                        } else if (perm === 'denied') {
                            window.dispatchEvent(new CustomEvent('pwa-push-permission-changed', { detail: 'denied' }));
                        }
                        return perm;
                    } catch (e) {
                        console.error('PWA push request error:', e);
                        return 'error';
                    }
                }
            };

            // Auto-register service worker and sync if permission already granted
            if ('serviceWorker' in navigator) {
                window.PwaPushNotification.registerServiceWorker().then(() => {
                    if (window.PwaPushNotification.permission === 'granted') {
                        window.PwaPushNotification.syncSubscription();
                    }
                });
            }

            // Real-time notification poller and screen alerter for PWA (Pelanggan & Mitra)
            window.PwaLiveNotifications = {
                pollTimer: null,
                lastNotifiedId: parseInt(localStorage.getItem('pijatin_last_notified_id') || '0', 10),

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        osc.frequency.setValueAtTime(880.00, ctx.currentTime + 0.12);
                        gain.gain.setValueAtTime(0.2, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.45);
                    } catch (e) {}
                },

                triggerAlert(notif) {
                    this.playChime();

                    // Native Browser / Desktop Notification Toast (if allowed)
                    if ('Notification' in window && Notification.permission === 'granted') {
                        try {
                            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                                navigator.serviceWorker.ready.then((reg) => {
                                    reg.showNotification(notif.title || 'Pijat.In', {
                                        body: notif.body || 'Pemberitahuan baru diterima.',
                                        icon: '/favicon.ico',
                                        badge: '/favicon.ico',
                                        tag: 'pijatin_notif_' + notif.id,
                                        renotify: true,
                                        data: { action_url: notif.action_url || '/' }
                                    });
                                });
                            } else {
                                const n = new Notification(notif.title || 'Pijat.In', {
                                    body: notif.body || 'Pemberitahuan baru diterima.',
                                    icon: '/favicon.ico'
                                });
                                n.onclick = () => {
                                    window.focus();
                                    if (notif.action_url) window.location.href = notif.action_url;
                                };
                            }
                        } catch (e) {
                            console.debug('Native notification display error:', e);
                        }
                    }

                    // In-App Toast Alert via SweetAlert2
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: notif.priority === 'high' ? 'warning' : 'info',
                            title: notif.title,
                            text: notif.body,
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.style.cursor = 'pointer';
                                toast.addEventListener('click', () => {
                                    if (notif.action_url) window.location.href = notif.action_url;
                                });
                            }
                        });
                    }

                    window.dispatchEvent(new CustomEvent('pwa-new-notification', { detail: notif }));
                },

                async poll() {
                    try {
                        const res = await fetch('{{ route("notifications.check-unread") }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();

                        window.dispatchEvent(new CustomEvent('notifications-updated', { detail: data.unread_count }));

                        if (data.latest && data.latest.id > this.lastNotifiedId) {
                            const isFirstRun = this.lastNotifiedId === 0;
                            this.lastNotifiedId = data.latest.id;
                            localStorage.setItem('pijatin_last_notified_id', data.latest.id.toString());

                            if (!isFirstRun) {
                                this.triggerAlert(data.latest);
                            }
                        }
                    } catch (e) {}
                },

                start() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.poll();
                    this.pollTimer = setInterval(() => this.poll(), 4000);
                }
            };

            window.PwaLiveNotifications.start();
        </script>
        <x-notification-drawer />
    @endauth
</body>
</html>
