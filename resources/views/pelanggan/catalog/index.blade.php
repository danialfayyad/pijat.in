<x-pwa-layout title="Layanan Pijat - Pijat.In" :hideHeader="true" :hideDock="true">
    <div class="py-2 space-y-4"
         x-data="{
            activeCategory: 'semua',
            searchQuery: '',
            showSearch: false,
            filterService(cat, name, desc) {
                const matchCategory = this.activeCategory === 'semua' || this.activeCategory === cat;
                const query = this.searchQuery.trim().toLowerCase();
                const matchQuery = query === '' || name.toLowerCase().includes(query) || desc.toLowerCase().includes(query);
                return matchCategory && matchQuery;
            }
         }"
         @toggle-catalog-search.window="showSearch = !showSearch; if (showSearch) $nextTick(() => $refs.searchInput.focus())">

        <!-- Floating Top Bar Navigasi (Sesuai Gaya Desain Aplikasi) -->
        <div class="flex items-center justify-between pb-1 pt-1">
            <a href="{{ route('pelanggan.dashboard') }}" 
               class="w-10 h-10 rounded-full bg-white text-slate-700 border border-slate-200/90 shadow-sm flex items-center justify-center hover:bg-slate-50 hover:text-emerald-800 active:scale-95 transition cursor-pointer"
               title="Kembali ke Beranda"
               aria-label="Kembali ke Beranda">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-black text-slate-900 uppercase tracking-widest">
                Pilihan Layanan Pijat
            </span>

            <button type="button" 
                    @click="showSearch = !showSearch; if (showSearch) $nextTick(() => $refs.searchInput.focus())"
                    class="w-10 h-10 rounded-full bg-white text-slate-700 border border-slate-200/90 shadow-sm flex items-center justify-center hover:bg-slate-50 hover:text-emerald-800 active:scale-95 transition cursor-pointer"
                    title="Cari Layanan"
                    aria-label="Cari Layanan">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </div>

        <!-- Input Pencarian Dropdown / Expandable -->
        <div x-show="showSearch" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="relative flex items-center w-full">
            <svg class="w-4 h-4 text-emerald-600 absolute left-3.5 inset-y-0 my-auto pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   x-ref="searchInput"
                   x-model="searchQuery"
                   placeholder="Cari jenis pijat atau keluhan (misal: relaksasi, otot, refleksi)..."
                   class="w-full pl-10 pr-10 py-2.5 rounded-full bg-white border border-emerald-300 text-xs font-medium text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            <button type="button" 
                    x-show="searchQuery.length > 0" 
                    @click="searchQuery = ''"
                    class="absolute right-3.5 inset-y-0 my-auto flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- 2. CATEGORY FILTER PILLS (HORIZONTAL SCROLL PERSIS MOCKUP) -->
        <div class="flex space-x-2 overflow-x-auto pb-1 no-scrollbar -mx-4 px-4" style="scrollbar-width: none; -ms-overflow-style: none;">
            <button type="button"
                    @click="activeCategory = 'semua'"
                    :class="activeCategory === 'semua' ? 'bg-[#064e3b] text-white shadow-xs font-black' : 'bg-white text-slate-600 border border-slate-200/90 hover:bg-slate-50 font-bold'"
                    class="min-h-[38px] px-5 py-2 rounded-full text-xs transition cursor-pointer shrink-0">
                Semua
            </button>
            <button type="button"
                    @click="activeCategory = 'relaksasi'"
                    :class="activeCategory === 'relaksasi' ? 'bg-[#064e3b] text-white shadow-xs font-black' : 'bg-white text-slate-600 border border-slate-200/90 hover:bg-slate-50 font-bold'"
                    class="min-h-[38px] px-4 py-2 rounded-full text-xs transition cursor-pointer shrink-0">
                Relaksasi
            </button>
            <button type="button"
                    @click="activeCategory = 'terapeutik'"
                    :class="activeCategory === 'terapeutik' ? 'bg-[#064e3b] text-white shadow-xs font-black' : 'bg-white text-slate-600 border border-slate-200/90 hover:bg-slate-50 font-bold'"
                    class="min-h-[38px] px-4 py-2 rounded-full text-xs transition cursor-pointer shrink-0">
                Terapeutik
            </button>
            <button type="button"
                    @click="activeCategory = 'tradisional'"
                    :class="activeCategory === 'tradisional' ? 'bg-[#064e3b] text-white shadow-xs font-black' : 'bg-white text-slate-600 border border-slate-200/90 hover:bg-slate-50 font-bold'"
                    class="min-h-[38px] px-4 py-2 rounded-full text-xs transition cursor-pointer shrink-0">
                Tradisional
            </button>
            <button type="button"
                    @click="activeCategory = 'refleksi'"
                    :class="activeCategory === 'refleksi' ? 'bg-[#064e3b] text-white shadow-xs font-black' : 'bg-white text-slate-600 border border-slate-200/90 hover:bg-slate-50 font-bold'"
                    class="min-h-[38px] px-4 py-2 rounded-full text-xs transition cursor-pointer shrink-0">
                Refleksi
            </button>
        </div>

        <!-- 3. DAFTAR KARTU BESAR PERSIS MOCKUP REFERENSI -->
        <div class="space-y-4 pt-1">
            @forelse ($services as $service)
                @php
                    $lowestPrice = $service->activePackages->min('price');
                    $primaryDuration = $service->activePackages->first()?->duration_minutes ?? 60;
                    $categoryTag = match(true) {
                        str_contains($service->slug, 'relaksasi') || str_contains($service->slug, 'full-body') || str_contains($service->slug, 'swedish') => 'relaksasi',
                        str_contains($service->slug, 'terapeutik') || str_contains($service->slug, 'hot-stone') || str_contains($service->slug, 'deep-tissue') => 'terapeutik',
                        str_contains($service->slug, 'refleksi') => 'refleksi',
                        default => 'tradisional',
                    };
                @endphp

                <!-- Large Service Card -->
                <div x-show="filterService('{{ $categoryTag }}', '{{ addslashes($service->name) }}', '{{ addslashes($service->description) }}')"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="group relative overflow-hidden rounded-3xl bg-white border border-slate-200/90 shadow-sm hover:border-emerald-300 hover:shadow-md transition">
                    
                    <!-- Area Foto Landscape Imersif dengan Badge Durasi Mengambang -->
                    <a href="{{ route('pelanggan.catalog.show', array_merge(['service' => $service], request()->has('type') ? ['type' => request()->query('type')] : [])) }}" class="block relative w-full h-44 sm:h-48 bg-slate-100 overflow-hidden cursor-pointer">
                        <img src="/image/hero.jpg" 
                             alt="{{ $service->name }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>
                        
                        <!-- Floating Duration Badge (Persis Gambar: 60 Min / 90 Min) -->
                        <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-slate-950/75 backdrop-blur-md text-white text-xs font-black shadow-md border border-white/20">
                            {{ $primaryDuration }} Min
                        </div>
                    </a>

                    <!-- Area Konten & Tombol Tambah (+) Persis Mockup -->
                    <div class="p-4 sm:p-5 flex items-end justify-between gap-3">
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <a href="{{ route('pelanggan.catalog.show', array_merge(['service' => $service], request()->has('type') ? ['type' => request()->query('type')] : [])) }}" class="block cursor-pointer">
                                <h3 class="text-base font-black text-slate-900 group-hover:text-emerald-700 transition leading-snug truncate">
                                    {{ $service->name }}
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">
                                    {{ $service->description }}
                                </p>
                            </a>

                            <!-- Harga Mulai Dari -->
                            <div class="pt-0.5">
                                @if ($lowestPrice)
                                    <span class="text-base font-black text-slate-900 font-mono tracking-tight block">
                                        Rp {{ number_format($lowestPrice, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Pilihan Durasi Paket Tersedia (Chips Rapi untuk Akses Cepat & Kompatibilitas Uji) -->
                            <div class="flex flex-wrap gap-1.5 pt-1.5">
                                @foreach ($service->activePackages as $pkg)
                                    <a href="{{ route('pelanggan.orders.create', array_merge(['package_id' => $pkg->id], request()->has('type') ? ['type' => request()->query('type')] : [])) }}"
                                       class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-800 text-slate-700 border border-slate-200/60 transition cursor-pointer">
                                        <span>{{ $pkg->duration_minutes }} Menit</span>
                                        <span class="text-slate-400 font-normal">•</span>
                                        <span class="font-mono text-emerald-800">{{ $pkg->formatted_price }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tombol Lingkaran Hijau (+) Persis Mockup -->
                        <div class="shrink-0 pb-1">
                            <a href="{{ route('pelanggan.catalog.show', array_merge(['service' => $service], request()->has('type') ? ['type' => request()->query('type')] : [])) }}"
                               class="w-11 h-11 rounded-full bg-[#064e3b] hover:bg-[#043d2e] active:scale-95 text-white flex items-center justify-center shadow-md hover:shadow-lg transition cursor-pointer"
                               title="Pilih Paket {{ $service->name }}"
                               aria-label="Pilih Paket {{ $service->name }}">
                                <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 bg-white rounded-3xl border border-slate-200/80 shadow-xs space-y-2">
                    <p class="text-xs text-slate-500 font-medium">Belum ada layanan yang aktif saat ini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>
