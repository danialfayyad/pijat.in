<x-pwa-layout title="Beranda Pelanggan - Pijat.In" :hideHeader="true">
    <div class="py-1 space-y-4">

        <!-- 1. TOP BAR SESUAI MOCKUP: LOKASI & NOTIFIKASI -->
        <div class="flex items-center justify-between pt-1">
            <!-- Lokasi Terpilih -->
            <a href="{{ route('pelanggan.addresses.index') }}" 
               class="inline-flex items-center space-x-2 py-1 px-3 rounded-full bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 hover:shadow-xs transition group cursor-pointer"
               title="Ubah Alamat Layanan">
                <div class="w-6 h-6 rounded-full bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-slate-900 group-hover:text-emerald-700 transition max-w-[170px] truncate">
                    {{ auth()->user()->primaryAddress?->region?->name ?? auth()->user()->region?->name ?? 'Jakarta Selatan' }}
                </span>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-700 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </a>

            <!-- Lonceng Notifikasi PWA -->
            @php
                $unreadNotificationCount = auth()->user()->systemNotifications()->unread()->count();
            @endphp
            <button type="button" 
                    x-data="{ unread: {{ $unreadNotificationCount }} }"
                    @notifications-read-all.window="unread = 0"
                    @notification-read.window="unread = Math.max(0, unread - 1)"
                    @notifications-updated.window="unread = $event.detail"
                    @click="$dispatch('open-notification-modal')"
                    onclick="window.dispatchEvent(new CustomEvent('open-notification-modal'))"
                    class="relative w-10 h-10 rounded-full bg-white border border-slate-200/90 shadow-2xs text-slate-800 hover:bg-slate-50 active:scale-95 flex items-center justify-center transition cursor-pointer"
                    title="Buka Notifikasi">
                <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span x-show="unread > 0" 
                      class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-600 text-[9px] font-black text-white ring-2 ring-white"
                      x-text="unread > 99 ? '99+' : unread">
                </span>
            </button>
        </div>

        <!-- 2. GREETING & SAPAAN HANGAT -->
        <div class="space-y-0.5 pt-0.5">
            <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-1.5">
                <span>Halo, {{ explode(' ', trim(auth()->user()->name))[0] }}</span>
                <span class="inline-block animate-wave origin-bottom-right">👋</span>
            </h2>
            <p class="text-xs text-slate-500 font-medium">Relaksasi hari ini, yuk?</p>
        </div>

        <!-- 3. KARTU SALDO ANDA (GAYA BOTANIKAL EMERALD PERSIS MOCKUP) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#059669] via-[#047857] to-[#064e3b] p-5 text-white shadow-lg">
            <!-- Pola Ilustrasi Daun / Botanikal Transparan di Sisi Kanan -->
            <div class="absolute -right-6 -bottom-8 pointer-events-none opacity-20">
                <svg width="220" height="220" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M160 20C110 30 70 80 80 140C90 200 170 190 190 130C210 70 190 30 160 20Z" fill="white"/>
                    <path d="M120 70C80 80 40 120 50 170C60 210 120 200 140 150C160 100 140 80 120 70Z" fill="white" opacity="0.6"/>
                    <path d="M80 140C120 100 160 60 190 30" stroke="white" stroke-width="4" stroke-linecap="round"/>
                    <path d="M50 170C90 130 120 100 150 70" stroke="white" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <!-- Konten Kartu Saldo -->
            <div class="relative z-10 space-y-3">
                <div>
                    <span class="text-xs font-medium text-emerald-100/90 block">Saldo Anda</span>
                    <a href="{{ route('pelanggan.wallet.index') }}" class="group inline-block mt-0.5">
                        <div class="text-2xl sm:text-3xl font-black tracking-tight text-white font-mono group-hover:underline">
                            {{ auth()->user()->getOrCreateWallet()->formatted_balance }}
                        </div>
                    </a>
                </div>

                <div class="pt-1">
                    <a href="{{ route('pelanggan.wallet.topup') }}" 
                       class="inline-flex items-center space-x-2 py-2 px-4 rounded-full bg-white text-emerald-900 text-xs font-black shadow-md hover:bg-emerald-50 active:scale-95 transition cursor-pointer">
                        <span>Isi Saldo</span>
                        <svg class="w-3.5 h-3.5 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. DUA TOMBOL AKSI CEPAT: PESAN INSTAN & JADWALKAN LAYANAN -->
        <div class="grid grid-cols-2 gap-3.5">
            <!-- Card 1: Pesan Instan (Mengarahkan ke Katalog Layanan dengan jenis Instan) -->
            <a href="{{ route('pelanggan.catalog.index', ['type' => 'instan']) }}"
               class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-md active:scale-[0.98] transition flex flex-col items-center justify-center text-center group cursor-pointer">
                <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-2.5 shadow-2xs group-hover:scale-110 transition">
                    <svg class="w-6 h-6 fill-amber-400 stroke-amber-500" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-xs font-black text-slate-900 leading-snug block group-hover:text-emerald-700 transition">
                    Pesan<br>Instan
                </span>
            </a>

            <!-- Card 2: Jadwalkan Layanan (Mengarahkan ke Katalog Layanan dengan jenis Terjadwal) -->
            <a href="{{ route('pelanggan.catalog.index', ['type' => 'terjadwal']) }}"
               class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-md active:scale-[0.98] transition flex flex-col items-center justify-center text-center group cursor-pointer">
                <div class="w-12 h-12 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center mb-2.5 shadow-2xs group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-xs font-black text-slate-900 leading-snug block group-hover:text-emerald-700 transition">
                    Jadwalkan<br>Layanan
                </span>
            </a>
        </div>

        @if ($activeOrders->isNotEmpty())
            <!-- 5. PESANAN SEDANG BERJALAN (JIKA ADA PESANAN AKTIF) -->
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Pesanan Sedang Aktif</h3>
                    </div>
                    <a href="{{ route('pelanggan.orders.index') }}" class="text-xs font-bold text-emerald-700 hover:underline cursor-pointer">
                        Lihat Semua Riwayat →
                    </a>
                </div>

                <div class="space-y-2.5">
                    @foreach ($activeOrders as $activeOrder)
                        <div class="bg-white rounded-2xl p-4 border border-emerald-200/90 shadow-sm space-y-3">
                            <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-100">
                                <div class="flex items-center space-x-2 text-xs">
                                    <span class="font-mono text-slate-500 font-bold text-[11px]">{{ $activeOrder->order_code }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-semibold text-slate-700">
                                        @if ($activeOrder->order_type->value === 'instan')
                                            <span class="text-emerald-700 font-bold">⚡ Pesanan Instan</span>
                                        @else
                                            📅 {{ $activeOrder->local_scheduled_start_time?->translatedFormat('d M Y, H:i') }}
                                        @endif
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0 {{ $activeOrder->status->badgeClasses() }}">
                                    {{ $activeOrder->status->label() }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-black text-slate-900 leading-snug">{{ $activeOrder->service_name }}</h4>
                                    <p class="text-xs text-slate-500">{{ $activeOrder->duration_minutes }} Menit • Terapis: {{ ucfirst($activeOrder->preferred_gender ?? 'Bebas') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-slate-900 font-mono text-sm leading-tight block">
                                        Rp {{ number_format($activeOrder->total_price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100">
                                <a href="{{ route('pelanggan.orders.show', $activeOrder) }}" 
                                   class="w-full py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-black text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                    <span>Lacak Status & Rincian Pesanan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 6. KATEGORI LAYANAN DIPERBESAR & LEBIH IMERSIF -->
        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Kategori Layanan</h3>
                    <p class="text-xs text-slate-500">Pilihan paket relaksasi & refleksi terbaik</p>
                </div>
                <a href="{{ route('pelanggan.catalog.index') }}" class="inline-flex items-center space-x-1 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                    <span>Lihat Semua</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Deretan Kartu Horisontal Berukuran Besar & Proporsional -->
            <div class="flex space-x-3.5 overflow-x-auto pb-3 pt-1 scrollbar-none snap-x -mx-4 px-4">
                @forelse ($services as $service)
                    @php
                        $lowestPrice = $service->activePackages->min('price');
                        $durations = $service->activePackages->pluck('duration_minutes')->join(' / ');
                    @endphp
                    <a href="{{ route('pelanggan.catalog.show', $service) }}" 
                       class="shrink-0 w-52 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:border-emerald-300 hover:shadow-md transition group snap-start overflow-hidden flex flex-col justify-between cursor-pointer">
                        <!-- Foto Spa & Relaksasi yang Menenangkan (Rasio Lebih Tinggi) -->
                        <div class="relative w-full h-32 bg-slate-100 overflow-hidden">
                            <img src="/image/hero.jpg" alt="{{ $service->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>
                            
                            @if ($lowestPrice)
                                <div class="absolute bottom-2 right-2 px-2.5 py-1 rounded-full bg-emerald-950/85 backdrop-blur-xs text-[11px] font-black text-emerald-300 font-mono shadow-xs border border-emerald-500/30">
                                    Mulai Rp {{ number_format($lowestPrice, 0, ',', '.') }}
                                </div>
                            @endif

                            @if ($durations)
                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-white/90 backdrop-blur-xs text-[10px] font-bold text-slate-800 shadow-2xs">
                                    ⏱️ {{ $durations }} Min
                                </div>
                            @endif
                        </div>

                        <!-- Konten Kartu: Nama Layanan Utuh & Deskripsi -->
                        <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                            <div>
                                <h4 class="text-sm font-black text-slate-900 group-hover:text-emerald-700 transition leading-snug line-clamp-2">
                                    {{ $service->name }}
                                </h4>
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                    {{ $service->description }}
                                </p>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700 group-hover:text-emerald-800">
                                <span>Pesan Layanan</span>
                                <span class="group-hover:translate-x-0.5 transition">→</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-6 bg-white rounded-2xl border border-slate-200 p-4 w-full">
                        <p class="text-xs text-slate-500">Belum ada layanan aktif yang tersedia saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 7. GABUNGAN OPSI 3: BANNER PROMO & PENAWARAN SPESIAL (DINAMIS DARI SUPERADMIN) -->
        @if ($banners->isNotEmpty())
            <div class="space-y-3 pt-1" 
                 x-data="{
                     activeSlide: 0,
                     totalSlides: {{ $banners->count() }},
                     next() {
                         this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                     },
                     prev() {
                         this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
                     }
                 }">
                <!-- Kontainer Slider Horisontal Responsif PWA -->
                <div class="flex space-x-3.5 overflow-x-auto pb-1 scrollbar-none snap-x snap-mandatory -mx-4 px-4">
                    @foreach ($banners as $index => $banner)
                        <div class="shrink-0 w-full snap-center">
                            @if ($banner->type === 'image' && $banner->image_url)
                                <!-- Banner Format Poster Gambar Penuh -->
                                <a href="{{ $banner->action_url ?: 'javascript:void(0)' }}" 
                                   class="block relative overflow-hidden rounded-3xl border border-slate-200/90 shadow-md group cursor-pointer aspect-[16/7] sm:aspect-[21/9]">
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-102 transition duration-500">
                                    @if ($banner->action_text)
                                        <div class="absolute bottom-3 right-3 py-1.5 px-3 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[11px] font-bold shadow-xs">
                                            {{ $banner->action_text }} &rarr;
                                        </div>
                                    @endif
                                </a>
                            @else
                                <!-- Banner Format Template Teks & Gradasi Elegan -->
                                @php
                                    $bgClasses = match ($banner->background_color) {
                                        'teal' => 'bg-gradient-to-r from-teal-800 via-emerald-800 to-teal-900',
                                        'slate' => 'bg-gradient-to-r from-slate-900 via-slate-800 to-zinc-900',
                                        'amber' => 'bg-gradient-to-r from-amber-700 via-orange-700 to-amber-900',
                                        'indigo' => 'bg-gradient-to-r from-indigo-900 via-blue-900 to-slate-900',
                                        'rose' => 'bg-gradient-to-r from-rose-800 via-pink-800 to-rose-950',
                                        default => 'bg-gradient-to-r from-emerald-800 via-teal-800 to-emerald-900',
                                    };
                                @endphp
                                <div class="relative overflow-hidden rounded-3xl {{ $bgClasses }} p-4 sm:p-5 text-white shadow-md">
                                    <!-- Hiasan Watermark Bintang / Daun -->
                                    <div class="absolute -right-4 -bottom-6 opacity-15 pointer-events-none">
                                        <svg class="w-36 h-36" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <div class="relative z-10 space-y-2.5">
                                        <div class="flex items-center space-x-2">
                                            @if ($banner->badge_text)
                                                <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                                                    {{ $banner->badge_text }}
                                                </span>
                                            @endif
                                            <span class="text-xs font-semibold text-emerald-200 truncate">{{ $banner->title }}</span>
                                        </div>
                                        <div>
                                            <h4 class="text-base font-black tracking-tight text-white leading-tight">
                                                {{ $banner->subtitle ?: $banner->title }}
                                            </h4>
                                            @if ($banner->description)
                                                <p class="text-xs text-white/90 mt-0.5 leading-relaxed line-clamp-2">
                                                    {{ $banner->description }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="pt-1 flex items-center justify-between gap-2">
                                            @if ($banner->promo_code)
                                                <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-white/20">
                                                    <span class="text-[10px] text-emerald-300 uppercase font-bold tracking-wider">Kode:</span>
                                                    <span class="font-mono font-black text-xs text-white tracking-widest">{{ $banner->promo_code }}</span>
                                                </div>
                                            @else
                                                <div></div>
                                            @endif

                                            @if ($banner->action_url)
                                                <a href="{{ $banner->action_url }}" 
                                                   class="py-1.5 px-3.5 rounded-full bg-white text-emerald-900 text-xs font-black shadow-xs hover:bg-emerald-50 active:scale-95 transition cursor-pointer">
                                                    {{ $banner->action_text ?: 'Gunakan Promo' }} &rarr;
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($banners->count() > 1)
                    <div class="flex items-center justify-center gap-1.5 pt-0.5">
                        <span class="text-[10px] font-medium text-slate-400">Geser untuk promo lainnya &bull; {{ $banners->count() }} Penawaran</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- 8. GABUNGAN OPSI 1: 3 PILAR KEUNGGULAN & KEPERCAYAAN PELANGGAN -->
        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900 tracking-tight">Kenapa Memilih Pijat.In?</h3>
                <span class="text-[11px] font-bold text-slate-400">Jaminan Kualitas</span>
            </div>

            <div class="grid grid-cols-1 gap-2.5">
                <!-- Pilar 1: Terapis Tersertifikasi -->
                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-black text-slate-900">Terapis Tersertifikasi & Terverifikasi</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Mitra profesional lulus seleksi kompetensi & uji latar belakang SKCK.</p>
                    </div>
                </div>

                <!-- Pilar 2: Minyak Alami & Higienis -->
                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-black text-slate-900">Alami, Steril & Higienis</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Minyak aromaterapi esensial bermutu tinggi & linen bersih higienis.</p>
                    </div>
                </div>

                <!-- Pilar 3: Bebas Biaya Transportasi -->
                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-black text-slate-900">Tarif Tetap & Tanpa Biaya Tersembunyi</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Harga resmi transparan, bebas ongkir transport dan tanpa biaya liar.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9. GABUNGAN OPSI 3: SHORTCUT PUSAT BANTUAN & LIVE CHAT -->
        <div class="pt-1 pb-2">
            <a href="{{ route('pelanggan.help.index') }}" 
               class="p-4 bg-emerald-50/70 hover:bg-emerald-50 rounded-2xl border border-emerald-200/80 flex items-center justify-between text-xs transition cursor-pointer group">
                <div class="flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-black text-slate-900 block leading-tight group-hover:text-emerald-800 transition">Butuh Bantuan atau Konsultasi?</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Tim Live Chat & Pusat FAQ siap membantu kebutuhan Anda</span>
                    </div>
                </div>
                <div class="flex items-center space-x-1 text-emerald-700 font-bold shrink-0">
                    <span>Hubungi</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>

    </div>
</x-pwa-layout>
