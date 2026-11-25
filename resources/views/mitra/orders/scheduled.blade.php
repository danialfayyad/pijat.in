<x-pwa-layout title="Bursa Pesanan - Pijat.In" headerTitle="Bursa Pesanan">
    <div class="py-2 space-y-3">

        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-medium flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 font-medium flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Daftar Pesanan Tersedia -->
        <div class="space-y-3">
            @forelse ($availableOrders as $order)
                <div data-order-code="{{ $order->order_code }}" class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs space-y-3">
                    
                    <!-- Header Kartu: Jadwal & Jarak -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-900">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $order->local_scheduled_start_time?->locale('id')->translatedFormat('l, d M Y') }} • {{ $order->local_scheduled_start_time?->format('H:i') }} {{ $order->timezone_abbr }}</span>
                        </div>
                        @if (isset($order->distance_km))
                            <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md shrink-0">
                                {{ $order->distance_km }} km
                            </span>
                        @endif
                    </div>

                    <!-- Layanan & Durasi -->
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-900 leading-tight">
                                {{ $order->service_name }}
                            </h3>
                            @php
                                $bursaCustRating = $order->customer?->customerRating();
                                $bursaCustCount = $order->customer?->customerRatingsCount() ?? 0;
                            @endphp
                            @if ($bursaCustRating !== null)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-900 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-md shrink-0" title="{{ $bursaCustCount }} ulasan dari mitra lain">
                                    <svg class="w-3 h-3 text-amber-500 fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span>{{ number_format($bursaCustRating, 1) }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 border border-slate-200/80 px-2 py-0.5 rounded-md shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Baru</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $order->duration_minutes }} Menit Layanan
                            @if ($order->preferred_gender && $order->preferred_gender !== 'bebas')
                                <span class="text-slate-300 mx-1">•</span>
                                <span class="text-slate-700 font-medium">Khusus {{ ucfirst($order->preferred_gender) }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Alamat -->
                    <div class="flex items-start gap-1.5 text-xs text-slate-600">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="line-clamp-2 leading-relaxed">
                            {{ $order->service_address_text }}
                        </span>
                    </div>

                    <!-- Footer Kartu: Pendapatan Bersih & Tombol Aksi -->
                    <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Pendapatan Bersih</span>
                            <span class="text-base font-bold text-slate-900">
                                Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                            </span>
                        </div>
                        <a href="{{ route('mitra.orders.show', $order) }}"
                           class="py-2 px-4 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-semibold text-xs rounded-xl transition cursor-pointer">
                            Lihat Detail
                        </a>
                    </div>

                </div>
            @empty
                <div class="min-h-[calc(100dvh-200px)] flex flex-col items-center justify-center text-center px-4">
                    <div class="w-16 h-16 rounded-3xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-center mb-3 text-slate-400">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800">Tidak ada pesanan di bursa</h4>
                    <p class="text-xs text-slate-400 max-w-xs mt-1 leading-relaxed">
                        Pesanan terjadwal baru di wilayah operasional Anda akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>
