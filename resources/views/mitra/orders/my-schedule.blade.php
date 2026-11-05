<x-pwa-layout title="Jadwal Saya - Pijat.In" headerTitle="Jadwal Saya">
    <div class="space-y-4 pb-6" x-data="{ activeTab: '{{ $defaultTab }}' }">

        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200/80 p-3.5 text-xs text-emerald-900 font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Tab Navigasi: Berjalan, Terjadwal, Riwayat -->
        <div class="p-1 bg-slate-100 rounded-xl flex items-center space-x-1 text-xs select-none">
            <!-- Tab 1: Berjalan -->
            <button type="button" @click="activeTab = 'berjalan'"
                    class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="activeTab === 'berjalan' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800'">
                <span>Berjalan</span>
                @if ($runningOrders->count() > 0)
                    <span class="text-[11px] font-bold text-emerald-700 font-mono">({{ $runningOrders->count() }})</span>
                @endif
            </button>

            <!-- Tab 2: Terjadwal -->
            <button type="button" @click="activeTab = 'terjadwal'"
                    class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="activeTab === 'terjadwal' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800'">
                <span>Terjadwal</span>
                @if ($scheduledOrders->count() > 0)
                    <span class="text-[11px] font-bold text-slate-700 font-mono">({{ $scheduledOrders->count() }})</span>
                @endif
            </button>

            <!-- Tab 3: History -->
            <button type="button" @click="activeTab = 'history'"
                    class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="activeTab === 'history' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800'">
                <span>Riwayat</span>
                @if ($historyOrders->count() > 0)
                    <span class="text-[11px] font-bold text-slate-700 font-mono">({{ $historyOrders->count() }})</span>
                @endif
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: ORDERAN BERJALAN                        -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'berjalan'" class="space-y-4">
            @forelse ($runningOrders as $order)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-4">
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wider block">{{ $order->order_code }}</span>
                            <h3 class="text-base font-bold text-slate-900 mt-0.5">{{ $order->service_name }}</h3>
                            <span class="text-xs text-slate-500">{{ $order->duration_minutes }} Menit • {{ $order->order_type->label() }}</span>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 shrink-0">
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    <!-- Informasi Penerima & Lokasi -->
                    <div class="space-y-2 text-xs text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Penerima Layanan:</span>
                            <span class="font-bold text-slate-900">{{ $order->recipient_name }} ({{ ucfirst($order->recipient_gender) }})</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nomor Kontak:</span>
                            <a href="tel:{{ $order->recipient_phone }}" class="font-bold text-emerald-700 hover:underline font-mono inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $order->recipient_phone ?? '-' }}</span>
                            </a>
                        </div>
                        <div class="pt-1">
                            <span class="text-slate-500 block mb-0.5">Alamat Lokasi:</span>
                            <p class="text-slate-800 leading-relaxed font-medium">
                                {{ $order->service_address_text }}
                            </p>
                        </div>
                    </div>

                    @if ($order->medical_notes)
                        <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/80 text-xs text-amber-950 space-y-1">
                            <span class="text-amber-900 font-bold block">Catatan Medis Penerima:</span>
                            <p class="leading-relaxed">{{ $order->medical_notes }}</p>
                        </div>
                    @endif

                    <!-- Ringkasan Finansial -->
                    <div class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                        <span class="text-slate-500">Estimasi Bersih:</span>
                        <span class="font-bold text-slate-900 text-sm">
                            Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-1">
                        <a href="{{ route('mitra.orders.show', $order) }}"
                           class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Buka Peta & Rute Layanan</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-2xs text-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">Tidak Ada Pesanan Berjalan</h4>
                    <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                        Saat ini Anda tidak memiliki tugas layanan yang sedang berjalan.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: ORDERAN TERJADWAL                       -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'terjadwal'" class="space-y-4">
            @forelse ($scheduledOrders as $order)
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-4">
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wider block">{{ $order->order_code }}</span>
                            <h3 class="text-base font-bold text-slate-900 mt-0.5">{{ $order->service_name }}</h3>
                            <span class="text-xs text-slate-500">{{ $order->duration_minutes }} Menit Layanan</span>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 shrink-0">
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    <!-- Jadwal Pelaksanaan -->
                    <div class="flex items-center justify-between text-xs text-slate-700 py-1">
                        <div class="flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $order->local_scheduled_start_time?->translatedFormat('l, d M Y') }}</span>
                        </div>
                        <span class="font-bold text-slate-900 font-mono">
                            {{ $order->local_scheduled_start_time?->format('H:i') }} {{ $order->timezone_abbr }}
                        </span>
                    </div>

                    <!-- Informasi Penerima & Lokasi -->
                    <div class="space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Penerima Layanan:</span>
                            <span class="font-bold text-slate-900">{{ $order->recipient_name }} ({{ ucfirst($order->recipient_gender) }})</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nomor Kontak:</span>
                            <a href="tel:{{ $order->recipient_phone }}" class="font-bold text-emerald-700 hover:underline font-mono inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $order->recipient_phone ?? '-' }}</span>
                            </a>
                        </div>
                        <div class="pt-1">
                            <span class="text-slate-500 block mb-0.5">Alamat Lokasi:</span>
                            <p class="text-slate-800 leading-relaxed font-medium">
                                {{ $order->service_address_text }}
                            </p>
                        </div>
                    </div>

                    <!-- Catatan Kondisi Medis Penerima -->
                    @if ($order->medical_notes)
                        <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/80 text-xs text-amber-950 space-y-1">
                            <span class="text-amber-900 font-bold block">Catatan Kondisi Medis Penerima:</span>
                            <p class="leading-relaxed">{{ $order->medical_notes }}</p>
                        </div>
                    @endif

                    <!-- Ringkasan Finansial -->
                    <div class="flex items-center justify-between text-xs pt-3 border-t border-slate-100">
                        <span class="text-slate-500">Estimasi Pendapatan:</span>
                        <span class="font-bold text-slate-900 text-sm">
                            Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-1">
                        <a href="{{ route('mitra.orders.show', $order) }}"
                           class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Lihat Detail, Peta & Rute</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-2xs text-center space-y-3">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Belum Ada Jadwal Tugas</h4>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                            Anda belum memiliki pesanan terjadwal yang diambil.
                        </p>
                    </div>
                    <div class="pt-1">
                        <a href="{{ route('mitra.orders.scheduled') }}"
                           class="inline-flex items-center justify-center py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                            Buka Bursa Pesanan
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: HISTORY (RIWAYAT)                       -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'history'" class="space-y-3">
            @forelse ($historyOrders as $order)
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs space-y-3">
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-2.5">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wider block">{{ $order->order_code }}</span>
                            <h3 class="text-xs font-bold text-slate-900 mt-0.5">{{ $order->service_name }}</h3>
                            <span class="text-[11px] text-slate-400">{{ $order->updated_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        @if ($order->status === \App\Enums\OrderStatus::Selesai)
                            <span class="text-xs font-semibold text-emerald-700">Selesai</span>
                        @else
                            <span class="text-xs font-semibold text-rose-600">Dibatalkan</span>
                        @endif
                    </div>

                    <!-- Penerima & Finansial -->
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Penerima:</span>
                            <span class="font-bold text-slate-800">{{ $order->recipient_name }}</span>
                        </div>
                        <div class="text-right">
                            @if ($order->status === \App\Enums\OrderStatus::Selesai)
                                <span class="text-slate-400 text-[11px] block">Pendapatan Bersih:</span>
                                <span class="font-bold text-slate-900 text-xs">
                                    Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px] block">Status:</span>
                                <span class="font-semibold text-rose-600 text-xs">Dibatalkan</span>
                            @endif
                        </div>
                    </div>

                    @if ($order->cancellation_reason)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600">
                            <strong>Alasan Pembatalan:</strong> {{ $order->cancellation_reason }}
                        </div>
                    @endif

                    <div class="pt-1">
                        <a href="{{ route('mitra.orders.show', $order) }}"
                           class="w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200/60 transition flex items-center justify-center gap-1 cursor-pointer">
                            <span>Lihat Detail Nota & Riwayat</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-2xs text-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">Belum Ada Riwayat Pesanan</h4>
                    <p class="text-[11px] text-slate-400">Pesanan yang telah selesai atau dibatalkan akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-pwa-layout>
