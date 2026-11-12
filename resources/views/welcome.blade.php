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
    <section id="layanan" class="py-14 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 bg-slate-50 border-b border-slate-200/80">
        <div class="w-full max-w-6xl mx-auto" x-data="{ showAll: false }">
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12 space-y-1 sm:space-y-2">
                <h2 class="text-xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                    Pilihan Layanan
                </h2>
                <p class="text-xs sm:text-base text-slate-600">
                    Pilih jenis perawatan yang sesuai dengan kebutuhan kebugaran tubuh Anda.
                </p>
            </div>

            <!-- 2-Column Grid on Mobile (default 4 cards), 3-Column on Desktop (default 6 cards) -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                @if (isset($services) && $services->isNotEmpty())
                    @foreach ($services as $index => $service)
                        @php
                            $minPkg = $service->packages?->sortBy('price')->first();
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
                                class="h-[180px] sm:h-[210px] bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 p-3 sm:p-5 shadow-xs flex flex-col justify-between"
                            @elseif ($index >= 4)
                                :class="showAll ? 'flex' : 'hidden lg:flex'"
                                class="h-[180px] sm:h-[210px] bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 p-3 sm:p-5 shadow-xs flex-col justify-between"
                            @else
                                class="h-[180px] sm:h-[210px] bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 p-3 sm:p-5 shadow-xs flex flex-col justify-between"
                            @endif
                        >
                            <div>
                                <h3 class="text-xs sm:text-base font-bold text-slate-900 line-clamp-1 sm:line-clamp-2">{{ $service->name }}</h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $service->description ?? 'Layanan pijat berkualitas ditangani oleh terapis berpengalaman untuk kebugaran tubuh optimal.' }}
                                </p>
                            </div>

                            <div class="pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                                <div class="min-w-0">
                                    <span class="text-[10px] sm:text-[11px] text-slate-400 block leading-tight">Mulai</span>
                                    <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">
                                        {{ $minPkg ? 'Rp ' . number_format($minPkg->price, 0, ',', '.') : 'Terjangkau' }}
                                    </span>
                                </div>
                                <a href="{{ $bookingUrl }}" 
                                   class="px-3 py-1.5 sm:px-3.5 sm:py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-[11px] sm:text-xs font-semibold shadow-xs transition-colors shrink-0 text-center">
                                    Pesan
                                </a>
                            </div>
                        </article>
                    @endforeach
                @else
                    <article class="h-[180px] sm:h-[210px] bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 p-3 sm:p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs sm:text-base font-bold text-slate-900 line-clamp-1 sm:line-clamp-2">Pijat Tradisional</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                Teknik urut menyeluruh untuk melancarkan sirkulasi darah serta meredakan pegal.
                            </p>
                        </div>
                        <div class="pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                            <div class="min-w-0">
                                <span class="text-[10px] sm:text-[11px] text-slate-400 block leading-tight">Durasi</span>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">60-120 Menit</span>
                            </div>
                            <a href="{{ route('login') }}" class="px-3 py-1.5 sm:px-3.5 sm:py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-[11px] sm:text-xs font-semibold shadow-xs transition-colors shrink-0 text-center">
                                Pesan
                            </a>
                        </div>
                    </article>

                    <article class="h-[180px] sm:h-[210px] bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 p-3 sm:p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs sm:text-base font-bold text-slate-900 line-clamp-1 sm:line-clamp-2">Refleksi Kaki</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                Pijatan titik saraf telapak kaki untuk kebugaran organ dan redakan ketegangan.
                            </p>
                        </div>
                        <div class="pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                            <div class="min-w-0">
                                <span class="text-[10px] sm:text-[11px] text-slate-400 block leading-tight">Durasi</span>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">60-90 Menit</span>
                            </div>
                            <a href="{{ route('login') }}" class="px-3 py-1.5 sm:px-3.5 sm:py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-[11px] sm:text-xs font-semibold shadow-xs transition-colors shrink-0 text-center">
                                Pesan
                            </a>
                        </div>
                    </article>

                    <article class="h-[180px] sm:h-[210px] bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 p-3 sm:p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs sm:text-base font-bold text-slate-900 line-clamp-1 sm:line-clamp-2">Pijat Aromaterapi</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                Relaksasi lembut minyak esensial alami untuk menenangkan pikiran dan stres.
                            </p>
                        </div>
                        <div class="pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                            <div class="min-w-0">
                                <span class="text-[10px] sm:text-[11px] text-slate-400 block leading-tight">Durasi</span>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">90-120 Menit</span>
                            </div>
                            <a href="{{ route('login') }}" class="px-3 py-1.5 sm:px-3.5 sm:py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-[11px] sm:text-xs font-semibold shadow-xs transition-colors shrink-0 text-center">
                                Pesan
                            </a>
                        </div>
                    </article>
                @endif
            </div>

            @if (isset($services) && $services->count() > 4)
                <div class="mt-8 sm:mt-10 text-center @if ($services->count() <= 6) lg:hidden @endif">
                    <button type="button" 
                            @click="showAll = !showAll"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50/80 transition cursor-pointer">
                        <span x-text="showAll ? 'Lebih sedikit' : 'Lebih banyak'"></span>
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

    <!-- SECTION 3: MITRA TERAPIS -->
    <section id="mitra" class="py-12 sm:py-16 lg:py-0 lg:min-h-screen lg:flex lg:items-center px-4 sm:px-6 bg-white border-b border-slate-200/80">
        <div class="w-full max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-14 items-center">
                
                <div class="space-y-4">
                    <h2 class="text-xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight leading-snug">
                        Bergabung Menjadi Mitra Terapis
                    </h2>
                    
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        Punya keahlian memijat dan ingin mendapatkan penghasilan tambahan? Bergabunglah bersama jaringan mitra terapis Pijat.in dengan kebebasan mengatur jadwal kerja sendiri.
                    </p>

                    <div class="pt-1 sm:pt-2">
                        <a href="{{ route('mitra.register') }}" 
                           class="inline-block px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-xs transition">
                            Daftar Sebagai Mitra
                        </a>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="bg-slate-50 rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200/80">
                        <h4 class="text-sm font-bold text-slate-900">Bagi Hasil Transparan</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Penghasilan jelas untuk setiap pesanan yang Anda selesaikan, tanpa potongan yang tidak jelas.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200/80">
                        <h4 class="text-sm font-bold text-slate-900">Waktu Fleksibel</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Bebas mengatur kapan Anda siap menerima pesanan cukup dengan menyalakan status online di aplikasi.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200/80">
                        <h4 class="text-sm font-bold text-slate-900">Keamanan Terpantau</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Sistem mendata identitas pelanggan dan menyediakan bantuan darurat untuk menjaga keamanan kerja mitra.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION 4: FAQ -->
    <section id="faq" class="py-16 sm:py-20 lg:py-24 px-4 sm:px-6 lg:px-8 bg-slate-50 border-b border-slate-200/80">
        <div class="w-full max-w-3xl mx-auto space-y-8 sm:space-y-10" 
             x-data="{ 
                 activeTab: 'semua', 
                 activeAccordion: null,
                 showAllFaq: false,
                 faqList: {{ isset($faqs) && $faqs->isNotEmpty() ? \Illuminate\Support\Js::from($faqs->map(fn($f) => ['id' => $f->id, 'audience' => $f->target_audience])) : '[]' }},
                 isFaqVisible(faqId, audience) {
                     if (this.activeTab !== 'semua' && audience !== this.activeTab && audience !== 'semua') {
                         return false;
                     }
                     if (this.showAllFaq) return true;
                     let matching = this.faqList.filter(f => this.activeTab === 'semua' || f.audience === this.activeTab || f.audience === 'semua');
                     let index = matching.findIndex(f => f.id === faqId);
                     return index !== -1 && index < 4;
                 },
                 hasMoreFaqs() {
                     let matching = this.faqList.filter(f => this.activeTab === 'semua' || f.audience === this.activeTab || f.audience === 'semua');
                     return matching.length > 4;
                 }
             }">
            <div class="text-center space-y-2 sm:space-y-3 max-w-xl mx-auto">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="text-xs sm:text-sm lg:text-base text-slate-600">
                    Informasi seputar pemesanan dan ketentuan layanan {{ $appName }}.
                </p>
            </div>

            <!-- Audience Filter Tabs -->
            <div class="flex items-center justify-center gap-2 overflow-x-auto py-1">
                <button type="button" 
                        @click="activeTab = 'semua'; showAllFaq = false; activeAccordion = null"
                        :class="activeTab === 'semua' ? 'bg-emerald-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm transition shrink-0">
                    Semua
                </button>
                <button type="button" 
                        @click="activeTab = 'pelanggan'; showAllFaq = false; activeAccordion = null"
                        :class="activeTab === 'pelanggan' ? 'bg-emerald-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm transition shrink-0">
                    Pelanggan
                </button>
                <button type="button" 
                        @click="activeTab = 'mitra'; showAllFaq = false; activeAccordion = null"
                        :class="activeTab === 'mitra' ? 'bg-emerald-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm transition shrink-0">
                    Mitra Terapis
                </button>
            </div>

            <!-- Accordion List -->
            <div class="space-y-3 sm:space-y-3.5">
                @if (isset($faqs) && $faqs->isNotEmpty())
                    @foreach ($faqs as $index => $faq)
                        <div x-show="isFaqVisible({{ $faq->id }}, '{{ $faq->target_audience }}')"
                             class="border border-slate-200/90 rounded-xl sm:rounded-2xl overflow-hidden bg-white shadow-xs transition hover:border-slate-300">
                            <button type="button" 
                                    @click="activeAccordion = (activeAccordion === {{ $faq->id }} ? null : {{ $faq->id }})"
                                    class="w-full px-5 py-4 sm:px-6 sm:py-4.5 text-left flex items-center justify-between text-xs sm:text-sm font-semibold text-slate-900 hover:bg-slate-50/75 transition gap-3">
                                <span class="pr-2">{{ $faq->question }}</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0" 
                                     :class="activeAccordion === {{ $faq->id }} ? 'rotate-180 text-emerald-600' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="activeAccordion === {{ $faq->id }}" 
                                 x-cloak 
                                 class="px-5 pb-5 sm:px-6 sm:pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3 sm:pt-4">
                                {{ $faq->answer }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs sm:text-sm">
                        Belum ada data FAQ yang dipublikasikan.
                    </div>
                @endif
            </div>

            <!-- Tombol Lebih Banyak FAQ -->
            @if (isset($faqs) && $faqs->count() > 4)
                <div x-show="hasMoreFaqs()" class="pt-2 text-center">
                    <button type="button" 
                            @click="showAllFaq = !showAllFaq; if(!showAllFaq) activeAccordion = null"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50/80 transition cursor-pointer">
                        <span x-text="showAllFaq ? 'Lebih sedikit' : 'Lebih banyak'"></span>
                        <svg class="w-4 h-4 transition-transform duration-200" 
                             :class="showAllFaq ? 'rotate-180' : ''" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            @endif
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