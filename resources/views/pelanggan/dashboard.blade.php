<x-pwa-layout title="Beranda Pelanggan - Pijat.In" headerTitle="Pijat.In">
    <!-- Greeting & Header Card -->
    <div class="py-2 space-y-4">

        <!-- User Greeting & Location Badge -->
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium">Selamat Datang,</span>
                <h2 class="text-lg font-black text-slate-900 leading-tight">{{ auth()->user()->name }}</h2>
            </div>
            <div class="text-right">
                <a href="{{ route('pelanggan.addresses.index') }}" 
                   class="inline-flex items-center min-h-[44px] px-3 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 hover:border-emerald-300 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ auth()->user()->region?->name ?? 'Pilih Alamat' }}</span>
                </a>
            </div>
        </div>

        <!-- Wallet Card -->
        <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-800 rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
            <div class="flex justify-between items-start relative z-10">
                <a href="{{ route('pelanggan.wallet.index') }}" class="group block cursor-pointer">
                    <span class="text-xs font-medium text-emerald-100 block group-hover:text-white transition">Saldo Dompet Pijat.In</span>
                    <div class="text-2xl font-black tracking-tight mt-1 font-mono group-hover:underline">
                        {{ auth()->user()->getOrCreateWallet()->formatted_balance }}
                    </div>
                </a>
                <a href="{{ route('pelanggan.wallet.topup') }}" 
                    class="min-h-[44px] min-w-[44px] px-4 py-2.5 rounded-xl bg-white text-emerald-800 text-xs font-extrabold shadow-sm hover:bg-emerald-50 active:scale-95 transition flex items-center space-x-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Isi Saldo</span>
                </a>
            </div>

            <div class="mt-4 pt-3 border-t border-emerald-600/60 flex items-center justify-between text-xs text-emerald-100/90 relative z-10">
                <a href="{{ route('pelanggan.addresses.index') }}" class="hover:underline flex items-center space-x-1 text-emerald-100 hover:text-white cursor-pointer py-1">
                    <span class="truncate max-w-[220px]">
                        Alamat: <strong>{{ auth()->user()->primaryAddress ? (auth()->user()->primaryAddress->label . ' (' . Str::limit(auth()->user()->primaryAddress->address_text, 22) . ')') : 'Atur Alamat Utama' }}</strong>
                    </span>
                    <svg class="w-3.5 h-3.5 text-emerald-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <span class="text-[11px] text-emerald-200 font-medium">Bebas Potongan</span>
            </div>
        </div>

        @if ($activeOrders->isNotEmpty())
            <!-- Pesanan Berjalan / Menunggu Pembayaran -->
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Pesanan Sedang Berjalan</h3>
                    </div>
                    <a href="{{ route('pelanggan.orders.index') }}" class="min-h-[44px] inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                        Semua Riwayat
                    </a>
                </div>

                <div class="space-y-2.5">
                    @foreach ($activeOrders as $activeOrder)
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition space-y-3">
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                                <div class="flex items-center space-x-2 text-xs">
                                    <span class="font-mono text-slate-400 font-bold text-[11px]">{{ $activeOrder->order_code }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-medium text-slate-600">
                                        @if ($activeOrder->order_type->value === 'instan')
                                            <span class="text-emerald-700 font-semibold">Pesanan Instan</span>
                                        @else
                                            {{ $activeOrder->scheduled_start_time?->translatedFormat('d M Y, H:i') }} WIB
                                        @endif
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $activeOrder->status->badgeClasses() }}">
                                    {{ $activeOrder->status->label() }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <h4 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $activeOrder->service_name }}</h4>
                                <div class="text-xs text-slate-500 flex items-center space-x-2">
                                    <span>{{ $activeOrder->duration_minutes }} Menit</span>
                                    <span class="text-slate-300">•</span>
                                    <span>Terapis: {{ ucfirst($activeOrder->preferred_gender ?? 'Bebas') }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-semibold">Total Tagihan</span>
                                    <span class="font-black text-slate-900 font-mono text-base leading-tight">Rp {{ number_format($activeOrder->total_price, 0, ',', '.') }}</span>
                                </div>

                                @if ($activeOrder->status === \App\Enums\OrderStatus::MenungguPembayaran)
                                    <a href="{{ route('pelanggan.orders.show', $activeOrder) }}" 
                                       class="min-h-[40px] px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                                        <span>Bayar Sekarang</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('pelanggan.orders.show', $activeOrder) }}" 
                                       class="min-h-[40px] px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                                        <span>Lacak & Rincian</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Bebas Preferensi Gender Terapis (Opsi A) -->
        <div class="bg-emerald-50/90 border border-emerald-200 rounded-2xl p-4 flex items-start space-x-3.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="text-xs">
                <span class="font-bold text-emerald-950 block leading-tight">Pilihan Bebas Preferensi Terapis</span>
                <p class="text-[11px] text-emerald-800 leading-relaxed mt-1">
                    Anda bebas memilih preferensi terapis pria, wanita, atau siapa saja demi kenyamanan maksimal.
                </p>
            </div>
        </div>

        <!-- Layanan Pijat Pilihan -->
        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Katalog Layanan</h3>
                    <p class="text-xs text-slate-500">Pilihan paket pijat dan refleksi tarif tetap</p>
                </div>
                <a href="{{ route('pelanggan.catalog.index') }}" class="min-h-[44px] inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                    Lihat Semua
                </a>
            </div>

            @forelse ($services as $service)
                @php
                    $lowestPrice = $service->activePackages->min('price');
                    $durations = $service->activePackages->pluck('duration_minutes')->join(' / ');
                @endphp
                <a href="{{ route('pelanggan.catalog.show', $service) }}" 
                    class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between hover:border-emerald-300 hover:shadow-sm transition group block cursor-pointer">
                    <div class="flex items-center space-x-3.5 pr-2 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
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
                        <div class="min-w-0">
                            <h4 class="font-bold text-xs text-slate-900 group-hover:text-emerald-700 transition truncate">{{ $service->name }}</h4>
                            <p class="text-[11px] text-slate-600 line-clamp-1 mt-0.5">{{ $service->description }}</p>
                            @if ($durations)
                                <div class="text-[11px] text-slate-500 font-medium mt-1">
                                    Durasi: {{ $durations }} Menit
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        @if ($lowestPrice)
                            <span class="text-[10px] text-slate-500 block font-medium">Mulai dari</span>
                            <span class="text-xs font-black text-slate-900 font-mono block">Rp {{ number_format($lowestPrice, 0, ',', '.') }}</span>
                        @endif
                        <span class="mt-1.5 min-h-[44px] min-w-[64px] px-3.5 py-2 bg-emerald-700 group-hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center justify-center">
                            Pilih
                        </span>
                    </div>
                </a>
            @empty
                <div class="text-center py-10 bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-500">Belum ada layanan aktif yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>
