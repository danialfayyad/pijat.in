<x-pwa-layout title="Pesanan Terjadwal - Pijat.In" headerTitle="Pesanan Terjadwal" :backUrl="route('mitra.dashboard')">
    <div class="py-2 space-y-4">

        @if (session('status'))
            <div class="p-3.5 rounded-2xl bg-emerald-50/90 border border-emerald-200/80 text-emerald-950 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="flex-1 leading-snug">{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-2xl bg-rose-50/90 border border-rose-200/80 text-rose-950 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <span class="flex-1 leading-snug">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header Ringkas Bursa Jadwal -->
        <div class="flex items-center justify-between pb-1">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Bursa Pesanan Terjadwal</h2>
                <p class="text-xs text-slate-500">Ambil slot pesanan yang cocok dengan jadwal luang Anda.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                {{ $availableOrders->count() }} Tersedia
            </span>
        </div>

        <!-- Banner Panduan Ringkas -->
        <div class="p-3 rounded-2xl bg-slate-100/90 border border-slate-200/80 text-xs text-slate-700 flex items-center space-x-2.5">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-[11px] leading-relaxed text-slate-600">
                Menampilkan pesanan terjadwal di wilayah Anda dalam radius operasional maksimal <strong>10 km</strong>. Pesanan dapat langsung diambil secara cepat (FCFS).
            </p>
        </div>

        <!-- Daftar Pesanan Tersedia -->
        <div class="space-y-3">
            @forelse ($availableOrders as $order)
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition space-y-3">
                    
                    <!-- Header Kartu: Kode & Pendapatan Bersih -->
                    <div class="flex items-start justify-between gap-3 pb-2.5 border-b border-slate-100">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 font-bold block">{{ $order->order_code }}</span>
                            <h3 class="text-sm font-extrabold text-slate-900 leading-snug mt-0.5">
                                {{ $order->service_name }}
                            </h3>
                            <div class="flex items-center space-x-2 mt-0.5 text-[11px] text-slate-500 font-medium">
                                <span>Durasi: {{ $order->duration_minutes }} Menit</span>
                                @if (isset($order->distance_km))
                                    <span>•</span>
                                    <span class="text-emerald-700 font-bold font-mono">Jarak: {{ $order->distance_km }} km</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-base font-black text-emerald-700 font-mono block leading-tight">
                                Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-semibold block">Pendapatan Bersih</span>
                        </div>
                    </div>

                    <!-- Informasi Jadwal & Preferensi -->
                    <div class="space-y-2 text-xs">
                        
                        <!-- Baris Waktu Jadwal -->
                        <div class="flex items-center space-x-2.5 p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-slate-900 leading-tight">
                                    {{ $order->scheduled_start_time?->locale('id')->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="text-[11px] text-emerald-800 font-mono font-bold mt-0.5">
                                    Pukul {{ $order->scheduled_start_time?->format('H:i') }} WIB
                                </div>
                            </div>
                        </div>

                        <!-- Baris Preferensi & Lokasi Ringkas -->
                        <div class="pt-1 space-y-1.5 text-[11px] text-slate-600">
                            <div class="flex items-center space-x-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>
                                    Preferensi Pelanggan: 
                                    <strong class="text-slate-800 capitalize">
                                        {{ ($order->preferred_gender ?? 'bebas') === 'bebas' ? 'Bebas (Pria / Wanita)' : ucfirst($order->preferred_gender) }}
                                    </strong>
                                </span>
                            </div>

                            <div class="flex items-start space-x-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="line-clamp-2 leading-relaxed text-slate-500">
                                    {{ $order->service_address_text }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi: Lihat Detail & Ambil Jadwal -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('mitra.orders.show', $order) }}"
                           class="min-h-[42px] py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Detail & Peta</span>
                        </a>

                        <form action="{{ route('mitra.orders.take', $order) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full min-h-[42px] py-2 px-3 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Ambil Jadwal</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-xs text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Belum Ada Pesanan Terjadwal</h4>
                        <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                            Pesanan terjadwal baru yang sesuai wilayah dan preferensi layanan Anda akan otomatis muncul di halaman ini.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>
