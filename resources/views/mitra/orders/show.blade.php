<x-pwa-layout title="Detail Pesanan & Peta - Mitra" headerTitle="Detail Pesanan" :backUrl="route('mitra.orders.my-schedule')">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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

        <!-- Card 1: Status Pesanan & Stepper Progres -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wider block">KODE PESANAN</span>
                    <h2 class="text-sm font-extrabold text-slate-900 font-mono mt-0.5">{{ $order->order_code }}</h2>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0 {{ $order->status->badgeClasses() }}">
                    {{ $order->status->label() }}
                </span>
            </div>

            <!-- Stepper Progress Alur Layanan -->
            @php
                $steps = [
                    \App\Enums\OrderStatus::Dijadwalkan->value => 1,
                    \App\Enums\OrderStatus::MenujuLokasi->value => 2,
                    \App\Enums\OrderStatus::Tiba->value => 3,
                    \App\Enums\OrderStatus::SedangBerlangsung->value => 4,
                    \App\Enums\OrderStatus::Selesai->value => 5,
                ];
                $currentStep = $steps[$order->status->value] ?? 1;
            @endphp

            <div class="pt-1">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-2">
                    <span class="{{ $currentStep >= 1 ? 'text-emerald-700' : '' }}">Dijadwalkan</span>
                    <span class="{{ $currentStep >= 2 ? 'text-emerald-700' : '' }}">Menuju Lokasi</span>
                    <span class="{{ $currentStep >= 3 ? 'text-emerald-700' : '' }}">Tiba</span>
                    <span class="{{ $currentStep >= 4 ? 'text-emerald-700' : '' }}">Layanan</span>
                    <span class="{{ $currentStep >= 5 ? 'text-emerald-700' : '' }}">Selesai</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" 
                         style="width: {{ min(100, max(20, ($currentStep / 5) * 100)) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Peta Interaktif & Jarak ke Pelanggan -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3"
             x-data="{
                mitraLat: {{ $mitraLat }},
                mitraLng: {{ $mitraLng }},
                customerLat: {{ $customerLat }},
                customerLng: {{ $customerLng }},
                distanceText: '{{ $distanceKm ? $distanceKm . ' km' : 'Menghitung rute...' }}',
                etaText: '{{ $distanceKm ? '~' . max(3, round($distanceKm * 2.5)) . ' Menit' : '-' }}',
                isLocating: false,
                isLoadingRoute: true,
                map: null,
                mitraMarker: null,
                customerMarker: null,
                routeLayer: null,

                initMap() {
                    this.map = L.map('mitra-order-map').setView([this.customerLat, this.customerLng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.map);

                    // Marker Pelanggan (Rumah)
                    const customerIcon = L.divIcon({
                        className: 'custom-customer-pin',
                        html: `<div class='w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-lg border-2 border-white font-bold text-xs'>🏠</div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });

                    this.customerMarker = L.marker([this.customerLat, this.customerLng], { icon: customerIcon }).addTo(this.map);
                    this.customerMarker.bindPopup(`<strong>Lokasi Pelanggan</strong><br>{{ addslashes($order->recipient_name) }}<br><span style='font-size:11px;'>{{ addslashes($order->service_address_text) }}</span>`).openPopup();

                    // Marker Terapis (Pijat)
                    const mitraIcon = L.divIcon({
                        className: 'custom-mitra-pin',
                        html: `<div class='w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center shadow-lg border-2 border-white font-bold text-xs'>💆</div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });

                    this.mitraMarker = L.marker([this.mitraLat, this.mitraLng], { icon: mitraIcon }).addTo(this.map);
                    this.mitraMarker.bindPopup(`<strong>Posisi Anda (Terapis)</strong>`);

                    // Gambar rute mengikuti jalur jalan sesungguhnya via OSRM
                    this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);

                    // Deteksi GPS Browser Realtime
                    if (navigator.geolocation) {
                        this.isLocating = true;
                        navigator.geolocation.getCurrentPosition((pos) => {
                            this.mitraLat = pos.coords.latitude;
                            this.mitraLng = pos.coords.longitude;
                            if (this.mitraMarker) {
                                this.mitraMarker.setLatLng([this.mitraLat, this.mitraLng]);
                            }
                            this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);
                            this.isLocating = false;
                        }, (err) => {
                            console.log('GPS error:', err);
                            this.isLocating = false;
                        }, { enableHighAccuracy: true, timeout: 8000 });
                    }
                },

                async fetchRoadRoute(startLat, startLng, destLat, destLng) {
                    this.isLoadingRoute = true;
                    try {
                        const url = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${destLng},${destLat}?overview=full&geometries=geojson`;
                        const res = await fetch(url);
                        const data = await res.json();
                        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                            const route = data.routes[0];
                            const coords = route.geometry.coordinates.map(c => [c[1], c[0]]);

                            if (this.routeLayer) {
                                this.map.removeLayer(this.routeLayer);
                            }

                            // Polyline hijau tebal mengikuti belokan jalan sesungguhnya
                            this.routeLayer = L.polyline(coords, {
                                color: '#059669',
                                weight: 5,
                                opacity: 0.9,
                                lineJoin: 'round'
                            }).addTo(this.map);

                            this.map.fitBounds(this.routeLayer.getBounds(), { padding: [35, 35] });

                            const distKm = (route.distance / 1000).toFixed(1);
                            this.distanceText = distKm + ' km (Jalur Jalan)';

                            const etaMin = Math.max(3, Math.round(route.duration / 60));
                            this.etaText = `~${etaMin} Menit`;
                            this.isLoadingRoute = false;
                            return;
                        }
                    } catch (e) {
                        console.warn('OSRM fallback to straight line:', e);
                    }

                    // Fallback garis langsung bila OSRM offline/gagal
                    if (this.routeLayer) {
                        this.map.removeLayer(this.routeLayer);
                    }
                    this.routeLayer = L.polyline([
                        [startLat, startLng],
                        [destLat, destLng]
                    ], {
                        color: '#059669',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '8, 8'
                    }).addTo(this.map);

                    this.map.fitBounds([
                        [startLat, startLng],
                        [destLat, destLng]
                    ], { padding: [35, 35] });

                    const dist = this.calcHaversine(startLat, startLng, destLat, destLng);
                    this.distanceText = dist + ' km';
                    const etaMin = Math.max(5, Math.round(dist * 3));
                    this.etaText = `~${etaMin} Menit`;
                    this.isLoadingRoute = false;
                },

                calcHaversine(lat1, lon1, lat2, lon2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon/2) * Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return (R * c).toFixed(1);
                }
             }"
             x-init="initMap()">
            
            <div class="flex items-center justify-between pb-1">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-xs font-bold text-slate-900">Rute Jalan Nyata ke Pelanggan</h3>
                </div>
                <div class="flex items-center space-x-1.5 text-[10px]">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                        Jalur Jalan Asli
                    </span>
                    <span class="text-slate-400 font-medium" x-show="isLocating">Mendeteksi GPS...</span>
                </div>
            </div>

            <!-- Wadah Peta Leaflet -->
            <div id="mitra-order-map" class="w-full h-56 rounded-xl border border-slate-200/80 shadow-inner z-10"></div>

            <!-- Indikator Jarak & Estimasi Waktu Tempuh -->
            <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 font-semibold block">Jarak Rute Jalan</span>
                        <span class="text-sm font-black text-emerald-950 font-mono" x-text="distanceText"></span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 font-semibold block">Estimasi Waktu Tempuh</span>
                        <span class="text-xs font-bold text-slate-800" x-text="etaText"></span>
                    </div>
                </div>
            </div>

            <!-- Tombol Buka Navigasi di Google Maps -->
            <a :href="'https://www.google.com/maps/dir/?api=1&origin=' + mitraLat + ',' + mitraLng + '&destination=' + customerLat + ',' + customerLng" 
               target="_blank"
               class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <span>Buka Rute Navigasi di Google Maps</span>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>

        <!-- Card 3: Informasi Penerima Layanan & Alamat Lengkap -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3 text-xs">
            <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Penerima Layanan & Kontak</span>
            </h3>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Nama Penerima:</span>
                <span class="font-extrabold text-slate-900">{{ $order->recipient_name }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Jenis Kelamin:</span>
                <span class="font-semibold text-slate-800 capitalize">{{ $order->recipient_gender ?? 'Tidak ditentukan' }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Nomor Telepon/WA:</span>
                <span class="font-bold text-slate-900 font-mono">{{ $order->recipient_phone ?? '-' }}</span>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <span class="text-slate-500 block mb-1">Alamat Tujuan:</span>
                <p class="text-slate-800 leading-relaxed font-medium bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                    {{ $order->service_address_text }}
                </p>
                @if ($order->customerAddress?->benchmark)
                    <p class="text-[11px] text-slate-500 mt-1 pl-1">
                        Patokan: <strong class="text-slate-700">{{ $order->customerAddress->benchmark }}</strong>
                    </p>
                @endif
            </div>

            <!-- Tombol Komunikasi Cepat -->
            <div class="grid grid-cols-2 gap-2 pt-1">
                @if ($order->recipient_phone)
                    <a href="tel:{{ $order->recipient_phone }}" 
                       class="min-h-[42px] px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Telepon</span>
                    </a>

                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $order->recipient_phone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Halo ' . $order->recipient_name . ', saya terapis dari Pijat.In untuk pesanan ' . $order->order_code . '.') }}" 
                       target="_blank"
                       class="min-h-[42px] px-3 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-700" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.587 1.771.889 2.796.889 3.182 0 5.768-2.586 5.768-5.766.001-3.18-2.585-5.766-5.768-5.766zm9.969 5.766c0 5.508-4.462 9.97-9.969 9.97-1.719 0-3.364-.44-4.811-1.222l-5.22 1.369 1.393-5.089c-.879-1.503-1.362-3.238-1.362-5.028 0-5.508 4.462-9.97 9.969-9.97 5.507 0 9.969 4.462 9.969 9.97z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Card 4: Catatan Medis Penerima (Transparan Bagi Mitra Setelah Diambil - MIT-07) -->
        <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-xs space-y-1.5 shadow-xs">
            <div class="flex items-center space-x-2 text-amber-900 font-bold">
                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Catatan Kondisi Medis Penerima</span>
            </div>
            <p class="text-amber-950 font-medium leading-relaxed pl-6">
                {{ $order->medical_notes ?: 'Pelanggan tidak mencantumkan keluhan medis khusus.' }}
            </p>
        </div>

        <!-- Card 5: Detail Layanan & Finansial -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2.5 text-xs">
            <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100">
                Informasi Layanan & Pendapatan
            </h3>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Paket Layanan:</span>
                <span class="font-extrabold text-slate-900">{{ $order->service_name }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Durasi Sesi:</span>
                <span class="font-semibold text-slate-800">{{ $order->duration_minutes }} Menit</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Waktu Jadwal:</span>
                <span class="font-bold text-slate-900">
                    {{ $order->scheduled_start_time ? $order->scheduled_start_time->locale('id')->translatedFormat('d M Y, H:i') . ' WIB' : 'Segera (Instan)' }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Tarif Pesanan:</span>
                <span class="font-mono text-slate-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                <span class="text-slate-600 font-semibold">Pendapatan Bersih Mitra (90%):</span>
                <span class="text-base font-black font-mono text-emerald-800">
                    Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Card 6: Tombol Pembaruan Status Layanan -->
        @if ($isAssigned)
            <div class="pt-1 space-y-2">
                @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
                    <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                          onsubmit="return confirm('Mulai perjalanan menuju lokasi pelanggan sekarang?');">
                        @csrf
                        <input type="hidden" name="status" value="menuju_lokasi">
                        <button type="submit" 
                            class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <span>Mulai Berangkat (Menuju Lokasi)</span>
                        </button>
                    </form>
                @elseif ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                    <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                          onsubmit="return confirm('Konfirmasi bahwa Anda sudah tiba di lokasi pelanggan?');">
                        @csrf
                        <input type="hidden" name="status" value="tiba">
                        <button type="submit" 
                            class="w-full min-h-[46px] py-3 px-4 bg-blue-700 hover:bg-blue-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <span>Saya Sudah Tiba di Lokasi</span>
                        </button>
                    </form>
                @elseif ($order->status === \App\Enums\OrderStatus::Tiba)
                    <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                          onsubmit="return confirm('Mulai sesi pemijatan sekarang?');">
                        @csrf
                        <input type="hidden" name="status" value="sedang_berlangsung">
                        <button type="submit" 
                            class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Mulai Sesi Layanan Pijat</span>
                        </button>
                    </form>
                @elseif ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                    <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                          onsubmit="return confirm('Sesi pijat telah selesai dilaksanakan secara lengkap?');">
                        @csrf
                        <input type="hidden" name="status" value="selesai">
                        <button type="submit" 
                            class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Selesaikan Layanan (Tugas Selesai)</span>
                        </button>
                    </form>
                @elseif ($order->status === \App\Enums\OrderStatus::Selesai)
                    <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 text-center text-xs text-emerald-950 font-bold space-y-1">
                        <p class="text-sm">✓ Layanan Selesai</p>
                        <p class="text-[11px] text-emerald-800 font-normal">Pendapatan telah dikreditkan ke dompet kemitraan Anda.</p>
                    </div>
                @endif
            </div>
        @else
            <!-- Belum diambil (Masih di Bursa) -->
            @if ($distanceKm !== null && $distanceKm > ($maxRadiusKm ?? 10.0))
                <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-center text-xs text-amber-950 space-y-1.5 shadow-xs">
                    <p class="font-bold flex items-center justify-center space-x-1.5 text-amber-900">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Di Luar Radius Maksimal Layanan (10 km)</span>
                    </p>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Jarak Anda saat ini (<strong>{{ $distanceKm }} km</strong>) melebihi batas maksimal <strong>10 km</strong> dari alamat pelanggan. Pesanan ini hanya dapat diambil jika Anda berada di dalam radius 10 km.
                    </p>
                </div>
            @else
                <form action="{{ route('mitra.orders.take', $order) }}" method="POST" class="pt-1">
                    @csrf
                    <button type="submit" 
                        class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Ambil Jadwal Pesanan Ini</span>
                    </button>
                </form>
            @endif
        @endif

    </div>
</x-pwa-layout>

