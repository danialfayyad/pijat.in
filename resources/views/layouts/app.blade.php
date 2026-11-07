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
                        <span class="text-2xl font-black text-white truncate">{{ $appName }}</span>
                    </div>

                    <!-- Mobile Navigation Links -->
                    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
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
                            <a href="{{ route('superadmin.branding.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.branding.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Identitas Aplikasi
                            </a>
                            <a href="{{ route('superadmin.banks.index') }}" 
                               class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.banks.*') ? 'bg-white text-emerald-900 font-bold shadow-md' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                                Master Rekening Bank
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

                    <!-- Right: Quick Actions & Notifications (ADM-14, Web Push & Desktop Toast) -->
                    <div class="flex items-center space-x-2 sm:space-x-3" x-data="desktopNotificationCenter()">
                        @if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin())
                            <!-- Tombol 1: Aktifkan Notifikasi (Jika belum granted) -->
                            <button type="button"
                                    x-show="permissionStatus === 'default' && isSupported"
                                    x-cloak
                                    @click="requestDesktopPermission()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer animate-pulse"
                                    title="Klik untuk mengizinkan pop-up notifikasi di layar laptop Anda">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span>Aktifkan Notif Laptop</span>
                            </button>

                            <!-- Tombol 2: Tes Notifikasi Laptop (Jika sudah granted) -->
                            <button type="button"
                                    x-show="permissionStatus === 'granted'"
                                    x-cloak
                                    @click="testDesktopNotification()"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 transition shadow-2xs cursor-pointer"
                                    title="Klik untuk menguji pop-up banner notifikasi langsung di sudut layar laptop Anda">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Tes Notif Laptop</span>
                            </button>

                            <!-- Indikator 3: Jika permission diblokir di browser -->
                            <span x-show="permissionStatus === 'denied'"
                                  x-cloak
                                  title="Izin notifikasi diblokir di browser. Klik ikon gembok 🔒 di samping URL untuk mengizinkan."
                                  class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200/80 rounded-xl cursor-help">
                                <span>🔒 Notif Diblokir</span>
                            </span>

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

        <!-- Global Interactive Confirmation Modal Component -->
        <x-confirm-dialog />

        <!-- Web Push & Live Desktop Notification Handler (SYS-06, ADM-14) -->
        <script>
            function desktopNotificationCenter() {
                return {
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
                            } else if (perm === 'denied') {
                                alert('Izin notifikasi diblokir di browser. Klik ikon gembok 🔒 di samping address bar (URL) lalu ubah Notification menjadi Allow.');
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
                            body: 'Tes berhasil! Banner notifikasi dan nada panggil di layar laptop Anda siap memantau antrean tugas masuk.',
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
