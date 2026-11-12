<x-pwa-layout title="{{ $service->name }} - Pijat.In" :hideHeader="true" :hideDock="true">
    @php
        $firstPkg = $service->activePackages->first();
        $photos = !empty($service->image_urls) ? $service->image_urls : ['/image/hero.jpg'];
    @endphp

    <div class="-mx-4 -mt-3 -mb-8 flex flex-col"
         x-data="{
            packages: @js($service->activePackages),
            selectedPackageId: {{ $firstPkg?->id ?? 'null' }},
            activePhotoIdx: 0,
            photos: @js($photos),
            getSelectedPackage() {
                return this.packages.find(p => p.id === this.selectedPackageId) || this.packages[0];
            }
         }">

        <!-- 1. HERO IMAGE / SLIDER -->
        <div class="relative w-full h-64 sm:h-72 overflow-hidden bg-slate-900">
            <template x-if="photos.length > 0">
                <img :src="photos[activePhotoIdx]" 
                     alt="{{ $service->name }}" 
                     class="w-full h-full object-cover transition-all duration-300">
            </template>
            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/15 to-black/25 pointer-events-none"></div>

            <!-- Back Button -->
            <a href="{{ route('pelanggan.catalog.index') }}" 
               class="absolute top-4 left-4 z-10 w-10 h-10 rounded-full bg-black/40 backdrop-blur-md text-white border border-white/20 flex items-center justify-center hover:bg-black/60 active:scale-95 transition shadow-sm cursor-pointer"
               title="Kembali ke Katalog"
               aria-label="Kembali ke Katalog">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <!-- Indicator Foto jika lebih dari 1 foto (dari CRUD Layanan) -->
            <div class="absolute bottom-7 right-4 z-10 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold"
                 x-show="photos.length > 1">
                <span x-text="activePhotoIdx + 1"></span>/<span x-text="photos.length"></span>
            </div>
        </div>

        <!-- 2. WHITE CONTENT BODY (MENYATU DI BAWAH HERO IMAGE, TIDAK SLIDE OVER) -->
        <div class="relative z-10 -mt-4 bg-white rounded-t-3xl px-5 pt-6 pb-28 space-y-5 flex-1">
            
            <!-- Judul Layanan & Harga Real dari Database -->
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">
                        {{ $service->name }}
                    </h1>
                    @if ($service->activePackages->count() > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/70 shrink-0">
                            {{ $service->activePackages->count() }} Pilihan Paket
                        </span>
                    @endif
                </div>

                <!-- Thumbnail Switcher jika admin mengunggah lebih dari 1 foto -->
                <div class="flex items-center gap-2 pt-1 overflow-x-auto no-scrollbar" x-show="photos.length > 1">
                    <template x-for="(photoUrl, pIdx) in photos" :key="pIdx">
                        <button type="button" 
                                @click="activePhotoIdx = pIdx"
                                class="w-12 h-12 rounded-xl overflow-hidden border-2 transition shrink-0 cursor-pointer"
                                :class="activePhotoIdx === pIdx ? 'border-emerald-700 ring-2 ring-emerald-500/30' : 'border-slate-200 opacity-60 hover:opacity-100'">
                            <img :src="photoUrl" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>

                <!-- Harga Dinamis Sesuai Paket Terpilih -->
                <div class="pt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900 font-mono tracking-tight"
                          x-text="'Rp ' + (getSelectedPackage() ? Number(getSelectedPackage().price).toLocaleString('id-ID') : '{{ number_format($firstPkg?->price ?? 0, 0, ',', '.') }}')">
                        Rp {{ number_format($firstPkg?->price ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">/ sesi</span>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- 3. PILIHAN DURASI PAKET LAYANAN (CUS-03) -->
            <div class="space-y-2.5">
                <span class="text-xs font-black text-slate-900 uppercase tracking-wider block">
                    Pilihan Durasi Sesi
                </span>

                <div class="grid grid-cols-3 gap-2">
                    @foreach ($service->activePackages as $pkg)
                        <button type="button" 
                                @click="selectedPackageId = {{ $pkg->id }}"
                                class="p-3 rounded-2xl border text-center transition cursor-pointer select-none"
                                :class="selectedPackageId === {{ $pkg->id }} ? 'bg-[#064e3b] text-white border-[#064e3b] shadow-md ring-2 ring-emerald-600/30 font-black' : 'bg-white text-slate-700 border-slate-200/90 hover:border-emerald-300 font-bold'">
                            <span class="block text-xs">{{ $pkg->duration_minutes }} Menit</span>
                            <span class="block text-[11px] font-mono mt-0.5" 
                                  :class="selectedPackageId === {{ $pkg->id }} ? 'text-emerald-200 font-black' : 'text-emerald-700 font-black'">
                                {{ $pkg->formatted_price }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 4. DESKRIPSI LAYANAN (DARI CRUD LAYANAN) -->
            <div class="space-y-1.5 pt-1">
                <span class="text-xs font-black text-slate-900 uppercase tracking-wider block">
                    Deskripsi Layanan
                </span>
                <p class="text-xs text-slate-600 leading-relaxed font-normal">
                    {{ $service->description }}
                </p>
            </div>

            <!-- 5. JAMINAN STANDAR LAYANAN (Bebas Ongkir, Protokol Gender, Tanpa Add-on) -->
            <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-4 space-y-2.5 text-xs text-emerald-950">
                <div class="flex items-center space-x-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] font-black shrink-0">✓</span>
                    <span><strong>Bebas Ongkir:</strong> Tidak ada biaya transportasi terapis tambahan.</span>
                </div>
                <div class="flex items-center space-x-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] font-black shrink-0">✓</span>
                    <span><strong>Protokol Gender:</strong> Terapis dipastikan bergender sama dengan Anda.</span>
                </div>
                <div class="flex items-center space-x-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] font-black shrink-0">✓</span>
                    <span><strong>Tanpa Add-on:</strong> Tarif flat transparan tanpa biaya tambahan tersembunyi.</span>
                </div>
            </div>

        </div>

        <!-- 6. STICKY BOTTOM ACTION BAR (PESAN SEKARANG) -->
        <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-2xl z-40">
            <button type="button" 
                    @click="window.location.href = '{{ route('pelanggan.orders.create') }}?package_id=' + selectedPackageId + '{{ request()->has('type') ? '&type=' . request()->query('type') : '' }}'"
                    class="w-full py-3.5 px-6 rounded-full bg-[#064e3b] hover:bg-[#043d2e] active:scale-[0.99] text-white font-black text-sm shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                <span>Pesan Sekarang</span>
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>

    </div>
</x-pwa-layout>
