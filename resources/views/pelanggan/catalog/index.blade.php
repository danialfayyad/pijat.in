<x-pwa-layout title="Katalog Layanan - Pijat.In" headerTitle="Katalog Layanan" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">

        <!-- Header Title -->
        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Pilihan Layanan Pijat</h2>
            <p class="text-xs text-slate-500">Tarif tetap, higienis, dan terapis berlisensi profesional.</p>
        </div>

        <!-- Transparent Pricing Banner (DEC-01 & CUS-03) -->
        <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-3.5 flex items-start space-x-3 text-xs text-emerald-800">
            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-emerald-950 block">Harga Pasti Tanpa Biaya Tersembunyi</span>
                <p class="text-[11px] text-emerald-700 leading-snug mt-0.5">
                    Semua paket layanan menggunakan tarif flat seragam di seluruh kota tanpa biaya transportasi tambahan dan tanpa opsi add-on.
                </p>
            </div>
        </div>

        <!-- Services List -->
        <div class="space-y-4">
            @forelse ($services as $service)
                @php
                    $icons = [
                        'full-body-massage' => '💆',
                        'hot-stone-massage' => '🔥',
                        'thai-massage' => '🧘',
                        'deep-tissue-massage' => '💪',
                        'swedish-massage' => '🌿',
                        'traditional-massage' => '🍵',
                    ];
                    $icon = $icons[$service->slug] ?? '💆';
                @endphp
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                    <!-- Service Header -->
                    <div class="flex items-start space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                            {{ $icon }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-extrabold text-sm text-slate-900 leading-tight">{{ $service->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $service->description }}</p>
                        </div>
                    </div>

                    <!-- Packages Options Grid -->
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">
                            Pilihan Durasi Paket
                        </span>

                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($service->activePackages as $pkg)
                                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/70 text-center">
                                    <span class="block text-xs font-bold text-slate-800">{{ $pkg->duration_minutes }} Menit</span>
                                    <span class="block text-[11px] font-black text-emerald-700 mt-0.5">{{ $pkg->formatted_price }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-1">
                        <a href="{{ route('pelanggan.catalog.show', $service) }}" 
                            class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center shadow-xs transition flex items-center justify-center space-x-1.5">
                            <span>Pilih Paket Layanan</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <p class="text-xs text-slate-400">Belum ada layanan yang aktif saat ini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>

