<x-pwa-layout title="Beranda Pelanggan - Pijat.In" headerTitle="Pijat.In">
    <!-- Greeting & Header Card -->
    <div class="py-2 space-y-4">

        <!-- User Greeting & Location Badge -->
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium">Selamat Datang,</span>
                <h2 class="text-lg font-black text-slate-900 leading-tight">{{ auth()->user()->name }}</h2>
            </div>
            <div class="text-right">
                <a href="{{ route('pelanggan.addresses.index') }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                    <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    {{ auth()->user()->region?->name ?? 'Pilih Alamat' }}
                </a>
            </div>
        </div>

        <!-- Wallet Card -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-white/10 blur-xl"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-[11px] font-semibold text-emerald-100 uppercase tracking-wider block">Saldo Dompet Pijat.In</span>
                    <div class="text-2xl font-black tracking-tight mt-0.5">Rp 0</div>
                </div>
                <button class="px-3.5 py-1.5 rounded-xl bg-white text-emerald-800 text-xs font-bold shadow-md hover:bg-emerald-50 transition flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Isi Saldo</span>
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between text-[11px] text-emerald-100/90 relative z-10">
                <a href="{{ route('pelanggan.addresses.index') }}" class="hover:underline flex items-center space-x-1">
                    <span>Alamat: <strong>{{ auth()->user()->primaryAddress ? (auth()->user()->primaryAddress->label . ' (' . Str::limit(auth()->user()->primaryAddress->address_text, 22) . ')') : 'Atur Alamat Utama' }}</strong></span>
                    <svg class="w-3 h-3 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <span class="text-[10px] bg-emerald-800/60 px-2 py-0.5 rounded-md font-mono">Bebas Biaya Admin</span>
            </div>
        </div>

        <!-- Strict Gender Safety Banner (DEC-05 / BRD Rule) -->
        <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-3.5 flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="text-xs">
                <span class="font-bold text-emerald-900 block leading-tight">Protokol Keamanan Sesama Gender</span>
                <p class="text-[11px] text-emerald-700 leading-snug mt-0.5">Terapis yang melayani Anda dipastikan bergender sama demi kenyamanan & keamanan maksimal.</p>
            </div>
        </div>

        <!-- Layanan Pijat Pilihan -->
        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Katalog Layanan</h3>
                    <p class="text-[11px] text-slate-400">Pilihan paket pijat & refleksi tarif tetap</p>
                </div>
                <a href="{{ route('pelanggan.catalog.index') }}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                    Lihat Semua
                </a>
            </div>

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
                    $lowestPrice = $service->activePackages->min('price');
                    $durations = $service->activePackages->pluck('duration_minutes')->join(' / ');
                @endphp
                <a href="{{ route('pelanggan.catalog.show', $service) }}" 
                    class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between hover:border-emerald-300 transition group block">
                    <div class="flex items-center space-x-3 pr-2">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xl shadow-inner shrink-0 group-hover:scale-105 transition">
                            {{ $icon }}
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-900 group-hover:text-emerald-700 transition">{{ $service->name }}</h4>
                            <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $service->description }}</p>
                            @if ($durations)
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mt-1.5 inline-block">
                                    {{ $durations }} Menit
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        @if ($lowestPrice)
                            <span class="text-[10px] text-slate-400 block font-medium">Mulai dari</span>
                            <span class="text-xs font-black text-slate-900 block">Rp {{ number_format($lowestPrice, 0, ',', '.') }}</span>
                        @endif
                        <span class="mt-1 px-3 py-1 bg-emerald-600 group-hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition inline-block">
                            Pilih
                        </span>
                    </div>
                </a>
            @empty
                <div class="text-center py-8 bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-400">Belum ada layanan aktif yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>
