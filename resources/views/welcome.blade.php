<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d9488">
    @php
        $appLogo = \App\Models\Parameter::get('app_logo');
        $appFavicon = \App\Models\Parameter::get('app_favicon');
        $appName = \App\Models\Parameter::get('app_name', config('app.name', 'Pijat.In'));
    @endphp
    @if ($appFavicon && file_exists(public_path('storage/' . $appFavicon)))
        <link rel="icon" href="{{ asset('storage/' . $appFavicon) }}">
    @endif
    <title>{{ $appName }}: Layanan Pijat Panggilan Profesional & Terpercaya</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <!-- NAVIGATION -->
    <header x-data="{ mobileMenu: false, scrolled: false }" 
            x-init="scrolled = (window.scrollY > 20)"
            @scroll.window="scrolled = (window.scrollY > 20)"
            :class="scrolled 
                ? 'bg-white/95 backdrop-blur-md shadow-xs border-slate-200/80' 
                : 'bg-transparent backdrop-blur-none shadow-none border-transparent'"
            class="fixed top-0 left-0 right-0 z-50 border-b transition-all duration-300 ease-in-out">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand -->
                <a href="#beranda" class="flex items-center gap-2.5">
                    @if ($appLogo && file_exists(public_path('storage/' . $appLogo)))
                        <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="h-9 sm:h-10 w-auto object-contain shrink-0">
                    @elseif ($appFavicon && file_exists(public_path('storage/' . $appFavicon)))
                        <img src="{{ asset('storage/' . $appFavicon) }}" alt="{{ $appName }}" class="w-9 h-9 object-contain rounded-xl shrink-0">
                    @else
                        <span class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-xs">
                            {{ mb_substr($appName, 0, 1) }}
                        </span>
                    @endif
                    <span class="text-lg font-bold tracking-tight transition-colors duration-300" :class="scrolled ? 'text-slate-900' : 'text-white'">
                        {{ $appName }}
                    </span>
                </a>

                <!-- Desktop Links -->
                <nav class="hidden md:flex items-center gap-7 text-sm font-medium transition-colors duration-300" :class="scrolled ? 'text-slate-600' : 'text-slate-200'">
                    <a href="#tentang" class="hover:text-emerald-500 transition-colors">Tentang</a>
                    <a href="#layanan" class="hover:text-emerald-500 transition-colors">Layanan</a>
                    <a href="#cara-kerja" class="hover:text-emerald-500 transition-colors">Cara Pesan</a>
                    <a href="#mitra" class="hover:text-emerald-500 transition-colors">Mitra Terapis</a>
                    <a href="#faq" class="hover:text-emerald-500 transition-colors">FAQ</a>
                </nav>

                <!-- Desktop Actions -->
                <div class="hidden md:flex items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" 
                           :class="scrolled ? 'text-slate-700 hover:text-slate-900' : 'text-white hover:text-slate-200'"
                           class="text-sm font-semibold px-3 py-2 transition-colors duration-300">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" 
                           class="text-sm font-semibold px-4 py-2 rounded-xl bg-emerald-600 text-white shadow-xs hover:bg-emerald-700 transition">
                            Pesan Sekarang
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" 
                           class="text-sm font-semibold px-4 py-2 rounded-xl bg-emerald-600 text-white shadow-xs hover:bg-emerald-700 transition">
                            Buka Dashboard
                        </a>
                    @endguest
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button type="button" 
                            @click="mobileMenu = !mobileMenu" 
                            class="p-2 rounded-lg transition-colors duration-300"
                            :class="scrolled ? 'text-slate-800 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                            aria-label="Menu navigasi">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenu" 
             x-cloak 
             @click.away="mobileMenu = false"
             class="md:hidden bg-white border-b border-slate-200 px-4 pt-3 pb-5 space-y-2 shadow-lg">
            <a href="#tentang" @click="mobileMenu = false" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Tentang</a>
            <a href="#layanan" @click="mobileMenu = false" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Layanan</a>
            <a href="#cara-kerja" @click="mobileMenu = false" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Cara Pesan</a>
            <a href="#mitra" @click="mobileMenu = false" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Mitra Terapis</a>
            <a href="#faq" @click="mobileMenu = false" class="block px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">FAQ</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @guest
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-800">Masuk</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-emerald-600 text-sm font-semibold text-white">Pesan Sekarang</a>
                @else
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-emerald-600 text-sm font-semibold text-white">Buka Dashboard</a>
                @endguest
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section id="beranda" 
             class="relative min-h-[85dvh] lg:min-h-screen flex items-center justify-center pt-20 pb-12 sm:pt-28 sm:pb-16 px-4 sm:px-6 bg-slate-950 bg-cover bg-center"
             style="background-image: linear-gradient(180deg, rgba(2, 6, 23, 0.75) 0%, rgba(2, 6, 23, 0.88) 100%), url('{{ asset('image/hero.jpg') }}');">
        
        <div class="max-w-3xl mx-auto text-center space-y-5 sm:space-y-6 relative z-10 py-6 sm:py-12">
            <h1 class="text-2xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-snug sm:leading-tight">
                Layanan Pijat Profesional Langsung ke Tempat Anda
            </h1>

            <p class="text-sm sm:text-lg lg:text-xl text-slate-200 max-w-xl mx-auto leading-relaxed">
                Pesan terapis berlisensi dan berpengalaman ke rumah, hotel, atau apartemen Anda dengan praktis, aman, dan nyaman.
            </p>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                @guest
                    <a href="{{ route('register') }}" 
                       class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-500 shadow-sm transition text-center">
                        Pesan Sekarang
                    </a>
                    <a href="{{ route('mitra.register') }}" 
                       class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/20 transition text-center">
                        Daftar Jadi Mitra
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" 
                       class="w-full sm:w-auto px-7 py-3 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-500 shadow-sm transition text-center">
                        Buka Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </section>

    <!-- SECTION 1: TENTANG KAMI -->
    <section id="tentang" class="py-16 sm:py-20 lg:py-24 px-4 sm:px-8 lg:px-12 bg-white border-b border-slate-200/80">
        <div class="w-full max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                
                <div class="space-y-6 sm:space-y-7 lg:py-4 lg:pr-8 xl:pr-12">
                    <div class="space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block">Tentang Layanan Kami</span>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight leading-snug">
                            Kenyamanan Pijat Berkualitas Tanpa Perlu Keluar Rumah
                        </h2>
                    </div>
                    
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        Pijat.in menghadirkan layanan relaksasi tubuh langsung ke tempat Anda. Tidak perlu repot menembus kemacetan, terapis profesional kami siap hadir ke alamat Anda dengan membawa perlengkapan lengkap dan higienis.
                    </p>

                    <!-- Feature Cards (Grid 2 Kolom) -->
                    <div class="pt-1 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Terapis Terverifikasi</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 leading-relaxed">
                                Mitra berlisensi dengan seleksi identitas ketat dan rekam jejak aman.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Higienis & Steril</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 leading-relaxed">
                                Perlengkapan bersih dan minyak aromaterapi steril terjaga.
                            </p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 sm:col-span-2">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Tarif Pasti & Transparan</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 leading-relaxed">
                                Biaya tertera jelas saat pemesanan tanpa biaya tambahan tersembunyi.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="rounded-xl sm:rounded-2xl overflow-hidden border border-slate-200/80 shadow-xs bg-slate-100">
                        <img src="{{ asset('image/terapis_1.jpg') }}" 
                             alt="Terapis profesional Pijat.in" 
                             class="w-full h-56 sm:h-72 lg:h-[440px] object-cover object-center">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION 2: LAYANAN -->
    <section id="layanan" class="py-16 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 bg-slate-50 border-b border-slate-200/80">
        <div class="w-full max-w-6xl mx-auto" x-data="{ showAll: false }">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16 space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block">Katalog Layanan</span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                    Pilihan Perawatan Terpopuler
                </h2>
                <p class="text-xs sm:text-sm lg:text-base text-slate-600">
                    Pijat panggilan profesional langsung ke tempat tinggal Anda. Ditangani terapis terlatih dengan minyak alami dan perlengkapan higienis.
                </p>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @if (isset($services) && $services->isNotEmpty())
                    @foreach ($services as $index => $service)
                        @php
                            $packages = $service->packages?->where('is_active', true)->sortBy('price');
                            $minPkg = $packages?->first();
                            $rawImages = is_string($service->images) ? json_decode($service->images, true) : (is_array($service->images) ? $service->images : []);
                            $firstImage = !empty($rawImages) ? $rawImages[0] : null;
                            $hasValidImage = $firstImage && file_exists(public_path('storage/' . $firstImage));
                            $bookingUrl = auth()->check()
                                ? (auth()->user()->role === \App\Enums\Role::Pelanggan
                                    ? ($minPkg ? route('pelanggan.orders.create', ['package_id' => $minPkg->id]) : route('pelanggan.catalog.index'))
                                    : route('dashboard'))
                                : route('login');
                        @endphp
                        <article 
                            @if ($index >= 6)
                                x-show="showAll" 
                                x-cloak 
                                class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col justify-between group"
                            @elseif ($index >= 4)
                                :class="showAll ? 'flex' : 'hidden lg:flex'"
                                class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex-col justify-between group"
                            @elseif ($index >= 2)
                                :class="showAll ? 'flex' : 'hidden md:flex'"
                                class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex-col justify-between group"
                            @else
                                class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col justify-between group"
                            @endif
                        >
                            <!-- Top Visual Area -->
                            @if ($hasValidImage)
                                <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                                    <img src="{{ asset('storage/' . $firstImage) }}" 
                                         alt="{{ $service->name }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                    <span class="absolute bottom-3 left-3 text-[11px] font-semibold text-white bg-slate-900/70 backdrop-blur-xs px-2.5 py-1 rounded-md">
                                        {{ $packages && $packages->isNotEmpty() ? $packages->pluck('duration_minutes')->implode(' / ') . ' Mnt' : '60 - 120 Mnt' }}
                                    </span>
                                </div>
                            @else
                                <div class="px-6 pt-6 pb-4 bg-gradient-to-br from-emerald-50/70 to-slate-50 border-b border-slate-100 flex items-center justify-between">
                                    <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-[11px] font-medium text-slate-600 bg-white border border-slate-200/80 px-2.5 py-1 rounded-full shadow-2xs">
                                        {{ $packages && $packages->isNotEmpty() ? $packages->pluck('duration_minutes')->implode(' / ') . ' Mnt' : '60 - 120 Mnt' }}
                                    </span>
                                </div>
                            @endif

                            <!-- Body Content -->
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug">
                                        {{ $service->name }}
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed line-clamp-3">
                                        {{ $service->description ?? 'Layanan pijat berkualitas dengan teknik pemijatan terukur untuk meredakan ketegangan otot dan menyegarkan tubuh.' }}
                                    </p>

                                    <!-- Duration & Price chips preview -->
                                    @if ($packages && $packages->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5 mt-4">
                                            @foreach ($packages as $pkg)
                                                <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200/60">
                                                    {{ $pkg->duration_minutes }}m: Rp {{ number_format($pkg->price / 1000, 0) }}k
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Action & Price Footer -->
                                <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tarif Mulai</span>
                                        <span class="text-base sm:text-lg font-extrabold text-slate-900 font-mono block">
                                            {{ $minPkg ? 'Rp ' . number_format($minPkg->price, 0, ',', '.') : 'Rp 100.000' }}
                                        </span>
                                    </div>
                                    <a href="{{ $bookingUrl }}" 
                                       class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                        <span>Pesan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @else
                    <article class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                        <div class="px-6 pt-6 pb-4 bg-gradient-to-br from-emerald-50/70 to-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[11px] font-medium text-slate-600 bg-white border border-slate-200/80 px-2.5 py-1 rounded-full shadow-2xs">
                                60 / 90 / 120 Mnt
                            </span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug">Pijat Tradisional</h3>
                                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                    Teknik urut menyeluruh untuk melancarkan sirkulasi darah serta meredakan pegal dan otot tegang.
                                </p>
                            </div>
                            <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tarif Mulai</span>
                                    <span class="text-base sm:text-lg font-extrabold text-slate-900 font-mono block">Rp 100.000</span>
                                </div>
                                <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                    <span>Pesan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col justify-between group">
                        <div class="px-6 pt-6 pb-4 bg-gradient-to-br from-emerald-50/70 to-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <span class="text-[11px] font-medium text-slate-600 bg-white border border-slate-200/80 px-2.5 py-1 rounded-full shadow-2xs">
                                60 / 90 Mnt
                            </span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug">Refleksi Kaki</h3>
                                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                    Pijatan titik saraf telapak kaki untuk kebugaran organ tubuh serta meredakan rasa lelah berlebih.
                                </p>
                            </div>
                            <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tarif Mulai</span>
                                    <span class="text-base sm:text-lg font-extrabold text-slate-900 font-mono block">Rp 90.000</span>
                                </div>
                                <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                    <span>Pesan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>

                    <article 
                        :class="showAll ? 'flex' : 'hidden md:flex'"
                        class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 hover:shadow-lg transition-all duration-200 overflow-hidden flex-col justify-between group">
                        <div class="px-6 pt-6 pb-4 bg-gradient-to-br from-emerald-50/70 to-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/></svg>
                            </div>
                            <span class="text-[11px] font-medium text-slate-600 bg-white border border-slate-200/80 px-2.5 py-1 rounded-full shadow-2xs">
                                90 / 120 Mnt
                            </span>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug">Pijat Aromaterapi</h3>
                                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                    Relaksasi lembut dengan baluran minyak esensial alami untuk menenangkan pikiran dan meredakan stres.
                                </p>
                            </div>
                            <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tarif Mulai</span>
                                    <span class="text-base sm:text-lg font-extrabold text-slate-900 font-mono block">Rp 130.000</span>
                                </div>
                                <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                    <span>Pesan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endif
            </div>

            @php
                $serviceCount = isset($services) ? $services->count() : 3;
            @endphp
            @if ($serviceCount > 2)
                <div class="mt-10 sm:mt-12 text-center @if ($serviceCount <= 4) md:hidden @elseif ($serviceCount <= 6) lg:hidden @endif">
                    <button type="button" 
                            @click="showAll = !showAll"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:bg-emerald-100/60 transition cursor-pointer">
                        <span x-text="showAll ? 'Tampilkan Lebih Sedikit' : 'Lihat Seluruh Layanan'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" 
                             :class="showAll ? 'rotate-180' : ''" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </section>

    <!-- SECTION: CARA PEMESANAN PRAKTIS (HOW IT WORKS) -->
    <section id="cara-kerja" class="py-16 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200/80">
        <div class="w-full max-w-6xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16 space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block">Alur Praktis</span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                    3 Langkah Mudah Menikmati Pijat
                </h2>
                <p class="text-xs sm:text-sm lg:text-base text-slate-600">
                    Tidak perlu antre dan repot keluar rumah. Pesan terapis resmi langsung ke tempat tinggal Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                <!-- Step 1 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white font-extrabold text-base flex items-center justify-center shadow-xs mb-4">
                            1
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">Pilih Layanan & Waktu</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                            Pilih jenis pijat, durasi paket yang Anda perlukan, dan tentukan apakah memesan instan atau untuk jadwal tertentu.
                        </p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-200/60 text-[11px] font-semibold text-emerald-700 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Instan & Terjadwal</span>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white font-extrabold text-base flex items-center justify-center shadow-xs mb-4">
                            2
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">Terapis Menuju Lokasi</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                            Mitra terdekat menerima pesanan dan langsung berangkat ke alamat Anda. Posisi terapis terpantau di peta secara real-time.
                        </p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-200/60 text-[11px] font-semibold text-emerald-700 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>Live GPS Tracking</span>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white font-extrabold text-base flex items-center justify-center shadow-xs mb-4">
                            3
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">Relaksasi di Rumah</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                            Nikmati sesi pemijatan higienis dengan perlengkapan lengkap. Pembayaran aman dan otomatis tanpa biaya tak terduga.
                        </p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-200/60 text-[11px] font-semibold text-emerald-700 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Higienis & Bergaransi</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: PELUANG KEMITRAAN -->
    <section id="mitra" class="py-16 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 bg-slate-50 border-b border-slate-200/80">
        <div class="w-full max-w-5xl mx-auto">
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                
                <!-- Left Side: Seamless Edge-to-Edge Photo -->
                <div class="lg:col-span-5 relative min-h-[240px] sm:min-h-[300px] lg:min-h-[420px] bg-slate-100">
                    <img src="{{ asset('image/terapis_1.jpg') }}" 
                         alt="Mitra Terapis {{ $appName }}" 
                         class="w-full h-full object-cover object-top absolute inset-0">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent lg:hidden"></div>
                    <span class="absolute bottom-3 left-3 lg:hidden text-[11px] font-semibold text-white bg-slate-900/80 backdrop-blur-xs px-2.5 py-1 rounded-md">
                        Mitra Resmi • D.I. Yogyakarta
                    </span>
                </div>

                <!-- Right Side: Concise & Airy Content (No Wall of Text) -->
                <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center space-y-6">
                    <div class="space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 block">Kemitraan Terapis</span>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 leading-snug">
                            Bekerja Fleksibel, Penghasilan Adil
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Gabung menjadi mitra terapis {{ $appName }} di Yogyakarta. Bebas atur jadwal sendiri dengan sistem bagi hasil 80% transparan.
                        </p>
                    </div>

                    <!-- 3 Single-Line Bullets (Crisp & Scannable) -->
                    <ul class="space-y-2.5 text-xs sm:text-sm text-slate-700">
                        <li class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-xs font-bold">&check;</span>
                            <span>Bagi hasil 80% langsung cair ke rekening bank Anda</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-xs font-bold">&check;</span>
                            <span>Jadwal kerja mandiri tanpa target minimum mengikat</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-xs font-bold">&check;</span>
                            <span>Alamat & identitas pemesan terekam resmi demi keamanan</span>
                        </li>
                    </ul>

                    <!-- Action & Short Note -->
                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center gap-3.5">
                        <a href="{{ route('mitra.register') }}" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-semibold text-xs sm:text-sm shadow-xs transition shrink-0">
                            <span>Daftar Sebagai Mitra</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <span class="text-[11px] sm:text-xs text-slate-400">
                            Pendaftaran online gratis • Butuh KTP & HP Android
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION 5: FAQ -->
    <section id="faq" class="py-14 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200/80">
        <div class="w-full max-w-4xl mx-auto space-y-6 sm:space-y-8" x-data="{ activeAccordion: null, showAllFaq: false }">
            
            <!-- Section Header (Responsive & Breathable) -->
            <div class="text-center space-y-2 max-w-2xl mx-auto px-2">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block">Pusat Bantuan</span>
                <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 tracking-tight leading-snug">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Temukan jawaban cepat seputar pemesanan, metode pembayaran, dan ketentuan layanan di {{ $appName }}.
                </p>
            </div>

            <!-- Accordion List: Optimized Mobile Spacing & Leading (Anti-Menumpuk) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 divide-y divide-slate-100 shadow-xs overflow-hidden">
                @if (isset($faqs) && $faqs->isNotEmpty())
                    @foreach ($faqs as $index => $faq)
                        <div @if ($index >= 4) x-show="showAllFaq" x-cloak @endif>
                            <button type="button" 
                                    @click="activeAccordion = (activeAccordion === {{ $faq->id }} ? null : {{ $faq->id }})"
                                    class="w-full px-4 py-4 sm:px-6 sm:py-4.5 md:px-8 md:py-5 text-left flex items-start justify-between gap-3 sm:gap-4 font-semibold text-slate-900 hover:text-emerald-700 hover:bg-slate-50/70 transition cursor-pointer select-none">
                                <span class="text-sm sm:text-base leading-relaxed sm:leading-normal pr-1">{{ $faq->question }}</span>
                                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-500 transition-transform duration-200 mt-0.5"
                                      :class="activeAccordion === {{ $faq->id }} ? 'rotate-180 bg-emerald-100 text-emerald-800' : ''">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </button>
                            <div x-show="activeAccordion === {{ $faq->id }}" 
                                 x-cloak 
                                 class="px-4 pb-5 pt-1 sm:px-6 sm:pb-6 sm:pt-2 md:px-8 md:pb-7 text-xs sm:text-sm text-slate-600 leading-relaxed bg-slate-50/50 border-t border-slate-100">
                                {{ $faq->answer }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-8 text-center text-slate-400 text-xs sm:text-sm">
                        Belum ada pertanyaan yang dipublikasikan.
                    </div>
                @endif
            </div>

            <!-- Expand / Collapse Button -->
            @if (isset($faqs) && $faqs->count() > 4)
                <div class="text-center pt-1">
                    <button type="button" 
                            @click="showAllFaq = !showAllFaq"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs transition cursor-pointer">
                        <span x-text="showAllFaq ? 'Tampilkan Lebih Sedikit' : 'Lihat Pertanyaan Lainnya (' + ({{ $faqs->count() }} - 4) + ' lainnya)'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="showAllFaq ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Help Contact Footer -->
            <div class="p-4 sm:p-6 rounded-2xl bg-slate-50 border border-slate-200/80 text-center space-y-1.5">
                <p class="text-xs sm:text-sm font-bold text-slate-900">Belum menemukan jawaban yang Anda butuhkan?</p>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tim dukungan kami siap melayani pertanyaan Anda melalui email 
                    <a href="mailto:support@pijat.in" class="text-emerald-700 font-semibold hover:underline">support@pijat.in</a>.
                </p>
            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-12 px-4 sm:px-6">
        <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="space-y-3 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2.5">
                    @if ($appLogo && file_exists(public_path('storage/' . $appLogo)))
                        <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="h-8 w-auto object-contain shrink-0 bg-white/10 rounded-lg p-1">
                    @elseif ($appFavicon && file_exists(public_path('storage/' . $appFavicon)))
                        <img src="{{ asset('storage/' . $appFavicon) }}" alt="{{ $appName }}" class="w-8 h-8 object-contain rounded-lg shrink-0">
                    @else
                        <span class="w-7 h-7 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ mb_substr($appName, 0, 1) }}
                        </span>
                    @endif
                    <span class="text-sm font-bold text-white tracking-tight">{{ $appName }}</span>
                </div>
                <p class="text-xs leading-relaxed text-slate-400">
                    Layanan pijat panggilan profesional langsung ke lokasi Anda dengan terapis berlisensi dan terpercaya.
                </p>
            </div>

            <!-- Navigasi -->
            <div class="space-y-2.5">
                <h4 class="text-white font-semibold text-xs uppercase tracking-wider">Navigasi</h4>
                <ul class="space-y-1.5">
                    <li><a href="#tentang" class="hover:text-emerald-400 transition">Tentang Kami</a></li>
                    <li><a href="#layanan" class="hover:text-emerald-400 transition">Pilihan Layanan</a></li>
                    <li><a href="#cara-kerja" class="hover:text-emerald-400 transition">Cara Pesan</a></li>
                    <li><a href="#mitra" class="hover:text-emerald-400 transition">Mitra Terapis</a></li>
                    <li><a href="#faq" class="hover:text-emerald-400 transition">Pusat Bantuan</a></li>
                </ul>
            </div>

            <!-- Kemitraan -->
            <div class="space-y-2.5">
                <h4 class="text-white font-semibold text-xs uppercase tracking-wider">Kemitraan</h4>
                <ul class="space-y-1.5">
                    <li><a href="{{ route('mitra.register') }}" class="hover:text-emerald-400 transition">Daftar Mitra Terapis</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition">Masuk Akun Mitra</a></li>
                    <li><a href="#mitra" class="hover:text-emerald-400 transition">Keuntungan Kemitraan</a></li>
                </ul>
            </div>

            <!-- Kontak -->
            <div class="space-y-2.5">
                <h4 class="text-white font-semibold text-xs uppercase tracking-wider">Kontak</h4>
                <div class="space-y-1 text-slate-300">
                    <div>Email: support@pijat.in</div>
                    <div>Lokasi: Yogyakarta, Indonesia</div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto pt-8 mt-8 border-t border-slate-800 text-slate-500 text-[11px] flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                &copy; {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi.
            </div>
            <div class="flex items-center gap-4">
                <a href="#faq" class="hover:text-slate-400 transition">Kebijakan Privasi</a>
                <a href="#faq" class="hover:text-slate-400 transition">Syarat Ketentuan</a>
            </div>
        </div>
    </footer>

</body>
</html>