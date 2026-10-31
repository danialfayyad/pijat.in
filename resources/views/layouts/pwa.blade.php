@props([
    'title' => null,
    'headerTitle' => null,
    'backUrl' => null,
    'headerAction' => null,
    'fullBleed' => false,
    'hideDock' => false,
    'requireLocation' => true,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
    <div class="{{ ($fullBleed ?? false) ? 'h-screen max-h-screen flex flex-col justify-between max-w-md mx-auto bg-slate-50 relative shadow-2xl border-x border-slate-200/80 overflow-hidden' : 'min-h-screen flex flex-col justify-between max-w-md mx-auto bg-slate-50 relative shadow-2xl border-x border-slate-200/80' }}">

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
                @endif
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="{{ ($fullBleed ?? false) ? 'flex-1 relative overflow-hidden h-full' : 'flex-1 pb-24 px-4 pt-3' }}">
            {{ $slot }}
        </main>

        <!-- Bottom Navigation Bar (Dock) -->
        @auth
            @if (!($hideDock ?? false))
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

                    <a href="{{ route('mitra.profile') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('mitra.profile') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('mitra.profile') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[11px] leading-none">Profil</span>
                    </a>
                </nav>
            @elseif (Auth::user()->isPelanggan())
                <!-- Pelanggan Dock -->
                <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 py-2 shadow-lg flex items-center justify-around" aria-label="Navigasi Utama Pelanggan">
                    <a href="{{ route('pelanggan.dashboard') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.dashboard') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.dashboard') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="text-[11px] leading-none">Beranda</span>
                    </a>

                    <a href="{{ route('pelanggan.catalog.index') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.catalog.*') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.catalog.*') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-[11px] leading-none">Layanan</span>
                    </a>

                    <a href="{{ route('pelanggan.wallet.index') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.wallet.*') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.wallet.*') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-[11px] leading-none">Dompet</span>
                    </a>

                    <a href="{{ route('pelanggan.addresses.index') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('pelanggan.addresses.*') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('pelanggan.addresses.*') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-[11px] leading-none">Alamat</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" 
                        class="flex flex-col items-center justify-center min-h-[44px] min-w-[56px] py-1 px-2 rounded-xl transition cursor-pointer {{ request()->routeIs('profile.*') ? 'text-emerald-700 font-bold' : 'text-slate-600 hover:text-slate-900 font-medium' }}">
                        <svg class="w-5 h-5 mb-1 {{ request()->routeIs('profile.*') ? 'text-emerald-700' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[11px] leading-none">Akun</span>
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
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-block mb-1.5">
                            Mitra Terapis: Akses Lokasi Wajib
                        </span>
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
</body>
</html>
