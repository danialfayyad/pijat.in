<x-pwa-layout title="Katalog Layanan - Pijat.In" headerTitle="Katalog Layanan" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">

        <!-- Header Title -->
        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Pilihan Layanan Pijat</h2>
            <p class="text-xs text-slate-500">Tarif tetap, higienis, dan terapis berlisensi profesional.</p>
        </div>

        <!-- Transparent Pricing Banner (DEC-01 & CUS-03) -->
        <div class="bg-emerald-50/90 border border-emerald-200 rounded-2xl p-4 flex items-start space-x-3.5 text-xs text-emerald-900">
            <div class="w-8 h-8 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-emerald-950 block">Harga Pasti Tanpa Biaya Tersembunyi</span>
                <p class="text-[11px] text-emerald-800 leading-relaxed mt-0.5">
                    Semua paket layanan menggunakan tarif flat seragam di seluruh kota tanpa biaya transportasi tambahan dan tanpa opsi add-on.
                </p>
            </div>
        </div>

        <!-- Services List -->
        <div class="space-y-4">
            @forelse ($services as $service)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5">
                    <!-- Service Header -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                            @if (str_contains($service->slug, 'hot-stone'))
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343a7.975 7.975 0 012.344 5.657 7.975 7.975 0 01-2.343 5.657z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                                </svg>
                            @elseif (str_contains($service->slug, 'thai') || str_contains($service->slug, 'refleksi'))
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif (str_contains($service->slug, 'swedish') || str_contains($service->slug, 'traditional') || str_contains($service->slug, 'herbal'))
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-extrabold text-sm text-slate-900 leading-tight">{{ $service->name }}</h3>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $service->description }}</p>
                        </div>
                    </div>

                    <!-- Packages Options Grid -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-500 block mb-2">
                            Pilihan Durasi Paket
                        </span>

                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($service->activePackages as $pkg)
                                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-center">
                                    <span class="block text-xs font-bold text-slate-800">{{ $pkg->duration_minutes }} Menit</span>
                                    <span class="block text-xs font-black font-mono text-emerald-700 mt-0.5">{{ $pkg->formatted_price }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-1">
                        <a href="{{ route('pelanggan.catalog.show', $service) }}" 
                            class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs text-center shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <span>Pilih Paket Layanan</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <p class="text-xs text-slate-500">Belum ada layanan yang aktif saat ini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>

