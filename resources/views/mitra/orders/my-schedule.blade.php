<x-pwa-layout title="Jadwal Saya - Pijat.In" headerTitle="Jadwal Saya" :backUrl="route('mitra.dashboard')">
    <div class="py-2 space-y-4">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Header Ringkas -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Jadwal Tugas Aktif</h2>
                <p class="text-xs text-slate-500">Daftar pesanan terjadwal yang menjadi tanggung jawab Anda.</p>
            </div>
            <a href="{{ route('mitra.orders.scheduled') }}" 
               class="min-h-[44px] px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer">
                <span>+ Bursa Pesanan</span>
            </a>
        </div>

        <!-- Orders List -->
        <div class="space-y-4">
            @forelse ($myOrders as $order)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wider block">{{ $order->order_code }}</span>
                            <h3 class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $order->service_name }}</h3>
                            <span class="text-[11px] text-slate-500 font-medium">{{ $order->duration_minutes }} Menit Layanan</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $order->status->badgeClasses() }} border">
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    <!-- Waktu Pelaksanaan -->
                    <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2 text-emerald-950 font-bold">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $order->local_scheduled_start_time?->translatedFormat('l, d M Y') }}</span>
                        </div>
                        <span class="font-extrabold text-emerald-900 font-mono">
                            {{ $order->local_scheduled_start_time?->format('H:i') }} {{ $order->timezone_abbr }}
                        </span>
                    </div>

                    <!-- Informasi Penerima & Kontak -->
                    <div class="space-y-2 text-xs border-t border-slate-100 pt-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Penerima Layanan</span>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nama Penerima:</span>
                            <span class="font-bold text-slate-900">{{ $order->recipient_name }} ({{ ucfirst($order->recipient_gender) }})</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nomor Kontak:</span>
                            <a href="tel:{{ $order->recipient_phone }}" class="font-bold text-emerald-700 hover:underline font-mono inline-flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $order->recipient_phone ?? '-' }}</span>
                            </a>
                        </div>
                        <div class="pt-1">
                            <span class="text-slate-500 block mb-0.5">Alamat Lokasi:</span>
                            <p class="text-slate-800 leading-relaxed font-medium bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                                {{ $order->service_address_text }}
                            </p>
                        </div>
                    </div>

                    <!-- Catatan Kondisi Medis Penerima (MIT-07: Terbuka & Dapat Dibaca) -->
                    <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200 text-xs space-y-1">
                        <div class="flex items-center space-x-1.5 text-amber-900 font-bold">
                            <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Catatan Kondisi Medis Penerima</span>
                        </div>
                        <p class="text-amber-950 font-medium leading-relaxed pl-5.5">
                            {{ $order->medical_notes ?: 'Pelanggan tidak mencantumkan catatan kondisi medis khusus.' }}
                        </p>
                    </div>

                    <!-- Ringkasan Finansial -->
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                        <span class="text-slate-500">Estimasi Pendapatan:</span>
                        <span class="font-extrabold text-emerald-800 font-mono text-sm">
                            Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Aksi: Lihat Detail, Peta & Rute -->
                    <div class="pt-1">
                        <a href="{{ route('mitra.orders.show', $order) }}" 
                           class="w-full min-h-[42px] py-2 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Lihat Detail, Peta & Rute</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-200/90 shadow-xs text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Belum Ada Jadwal Tugas</h4>
                        <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                            Anda belum memiliki pesanan terjadwal yang diambil. Kunjungi Bursa Pesanan untuk mengambil pesanan baru.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('mitra.orders.scheduled') }}" 
                           class="inline-flex items-center justify-center min-h-[44px] px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            Lihat Bursa Pesanan Terjadwal
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>

