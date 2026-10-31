<x-pwa-layout title="Pelacakan Pesanan - Pijat.In" headerTitle="Pelacakan Pesanan" :backUrl="route('pelanggan.dashboard')" :fullBleed="true" :hideDock="true">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        div:where(.swal2-container).swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }
        div:where(.swal2-container) div:where(.swal2-popup).swal-admin-theme {
            width: calc(100% - 2rem) !important;
            max-width: 380px !important;
            padding: 1.5rem !important;
            border-radius: 1.5rem !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            font-family: inherit !important;
            background: #ffffff !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon {
            width: 3.5rem !important;
            height: 3.5rem !important;
            margin: 0.25rem auto 1rem !important;
            border: none !important;
            border-radius: 9999px !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-warning {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
            box-shadow: 0 0 0 8px #fffbeb !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-title {
            font-size: 1.05rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            line-height: 1.25 !important;
            padding: 0 !important;
            margin: 0 0 0.5rem 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-html-container {
            font-size: 0.8rem !important;
            color: #64748b !important;
            line-height: 1.5 !important;
            margin: 0 0 1.25rem 0 !important;
            padding: 0 0.25rem !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 0.625rem !important;
            margin: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-cancel {
            margin: 0 !important;
            width: 100% !important;
            min-height: 42px !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.15s ease-in-out !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm {
            margin: 0 !important;
            width: 100% !important;
            min-height: 42px !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            background-color: #e11d48 !important; /* rose-600 */
            color: #ffffff !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.15s ease-in-out !important;
        }
    </style>

    <script>
        function confirmCancelOrder(event, message = 'Apakah Anda yakin ingin membatalkan pesanan ini?') {
            event.preventDefault();
            const form = event.currentTarget || event.target;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batalkan Pesanan?',
                    text: message,
                    showCancelButton: true,
                    cancelButtonText: 'Kembali',
                    confirmButtonText: 'Ya, Batalkan',
                    reverseButtons: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-admin-theme',
                        icon: 'swal2-warning',
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(message)) {
                    form.submit();
                }
            }
            return false;
        }
        window.confirmCancelOrder = confirmCancelOrder;
    </script>

    <div class="relative w-full h-full overflow-hidden select-none"
         x-data="{
            isExpanded: false,
            touchStartY: 0,
            touchCurrentY: 0,
            isDragging: false,

            customerLat: {{ $customerLat }},
            customerLng: {{ $customerLng }},
            mitraLat: {{ $mitraLat ? $mitraLat : 'null' }},
            mitraLng: {{ $mitraLng ? $mitraLng : 'null' }},
            hasMitra: {{ $order->mitra_id ? 'true' : 'false' }},
            orderStatus: '{{ $order->status->value }}',
            serviceStartedAt: '{{ $order->service_started_at?->toIso8601String() }}',
            durationMinutes: {{ (int) $order->duration_minutes }},
            remainingSeconds: 0,
            remainingTimeFormatted: '{{ $order->duration_minutes }}:00',
            timerInterval: null,
            selectedRating: {{ $myRating?->score ?? 0 }},
            hasRated: {{ $myRating ? 'true' : 'false' }},
            isSubmittingRating: false,

            distanceText: 'Menghitung rute...',
            etaText: '{{ $order->status === \App\Enums\OrderStatus::MenujuLokasi ? '~15 - 20 Menit' : '-' }}',
            isLoadingRoute: false,
            pollTimer: null,

            map: null,
            customerMarker: null,
            mitraMarker: null,
            routeLayer: null,
            radarCircle: null,

            toggleSheet() {
                this.isExpanded = !this.isExpanded;
                this.invalidateMap();
            },
            expandSheet() {
                this.isExpanded = true;
                this.invalidateMap();
            },
            collapseSheet() {
                this.isExpanded = false;
                this.invalidateMap();
            },
            invalidateMap() {
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize();
                    }
                }, 320);
            },

            handleTouchStart(e) {
                this.touchStartY = e.touches[0].clientY;
                this.touchCurrentY = this.touchStartY;
                this.isDragging = true;
            },
            handleTouchMove(e) {
                if (!this.isDragging) return;
                this.touchCurrentY = e.touches[0].clientY;
            },
            handleTouchEnd() {
                if (!this.isDragging) return;
                const diff = this.touchStartY - this.touchCurrentY;
                if (diff > 40) {
                    this.expandSheet();
                } else if (diff < -40) {
                    this.collapseSheet();
                }
                this.isDragging = false;
            },

            initCountdown() {
                if (this.orderStatus === 'sedang_berlangsung' && this.serviceStartedAt) {
                    const startMs = new Date(this.serviceStartedAt).getTime();
                    const endMs = startMs + (this.durationMinutes * 60 * 1000);
                    const update = () => {
                        const now = Date.now();
                        const diff = Math.max(0, Math.floor((endMs - now) / 1000));
                        this.remainingSeconds = diff;
                        const mins = Math.floor(diff / 60);
                        const secs = diff % 60;
                        this.remainingTimeFormatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    };
                    update();
                    this.timerInterval = setInterval(update, 1000);
                }
            },

            async submitRating(score) {
                if (this.hasRated || this.isSubmittingRating) return;
                this.selectedRating = score;
                this.isSubmittingRating = true;
                try {
                    const res = await fetch('{{ route('pelanggan.orders.rating', $order) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ score: score })
                    });
                    if (res.ok) {
                        this.hasRated = true;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Ulasan Diterima',
                                text: 'Terima kasih telah memberikan ulasan penilaian rating Anda.',
                                confirmButtonText: 'Tutup',
                                buttonsStyling: false,
                                customClass: { popup: 'swal-admin-theme', confirmButton: 'swal2-confirm' }
                            });
                        }
                    }
                } catch (e) {
                } finally {
                    this.isSubmittingRating = false;
                }
            },

            initMap() {
                this.map = L.map('pelanggan-order-map', {
                    zoomControl: false
                }).setView([this.customerLat, this.customerLng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                // Marker Rumah Pelanggan
                const customerIcon = L.divIcon({
                    className: 'custom-customer-pin',
                    html: `<div class='w-9 h-9 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-xl border-2 border-white text-base'>🏠</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                });

                this.customerMarker = L.marker([this.customerLat, this.customerLng], { icon: customerIcon }).addTo(this.map);
                this.customerMarker.bindPopup(`<strong>Lokasi Layanan Anda</strong><br>{{ addslashes($order->recipient_name) }}<br><span style='font-size:11px;'>{{ addslashes($order->service_address_text) }}</span>`).openPopup();

                // Jika sudah ada mitra
                if (this.hasMitra && this.mitraLat && this.mitraLng) {
                    const mitraIcon = L.divIcon({
                        className: 'custom-mitra-pin',
                        html: `<div class='w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center shadow-xl border-2 border-white text-base animate-bounce'>💆</div>`,
                        iconSize: [36, 36],
                        iconAnchor: [18, 36]
                    });

                    this.mitraMarker = L.marker([this.mitraLat, this.mitraLng], { icon: mitraIcon }).addTo(this.map);
                    this.mitraMarker.bindPopup(`<strong>Terapis Pijat.In</strong><br>{{ addslashes($order->mitra?->name ?? 'Terapis') }}`);

                    this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);
                } else {
                    // Belum ada mitra: buat radar pencarian di sekitar alamat pelanggan
                    this.radarCircle = L.circle([this.customerLat, this.customerLng], {
                        color: '#059669',
                        fillColor: '#10b981',
                        fillOpacity: 0.15,
                        radius: 2000
                    }).addTo(this.map);

                    this.fitBoundsSingle();
                }

                // Polling posisi GPS mitra setiap 10 detik jika sedang aktif/menuju lokasi
                if (['dijadwalkan', 'menuju_lokasi', 'tiba', 'sedang_berlangsung'].includes(this.orderStatus)) {
                    this.startLivePolling();
                }

                // Jalankan timer hitung mundur jika sesi sedang berlangsung
                this.initCountdown();
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

                        this.routeLayer = L.polyline(coords, {
                            color: '#059669',
                            weight: 5,
                            opacity: 0.9,
                            lineJoin: 'round'
                        }).addTo(this.map);

                        this.fitRouteBounds();

                        const distKm = (route.distance / 1000).toFixed(1);
                        this.distanceText = distKm + ' km';
                        const etaMin = Math.max(3, Math.round(route.duration / 60));
                        this.etaText = `~${etaMin} Menit`;
                        this.isLoadingRoute = false;
                        return;
                    }
                } catch (e) {
                    console.warn('OSRM fallback:', e);
                }

                // Fallback
                if (this.routeLayer) {
                    this.map.removeLayer(this.routeLayer);
                }
                this.routeLayer = L.polyline([[startLat, startLng], [destLat, destLng]], {
                    color: '#059669',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '8, 8'
                }).addTo(this.map);

                this.fitRouteBounds();
                const dist = this.calcHaversine(startLat, startLng, destLat, destLng);
                this.distanceText = dist + ' km';
                const etaMin = Math.max(5, Math.round(dist * 3));
                this.etaText = `~${etaMin} Menit`;
                this.isLoadingRoute = false;
            },

            fitRouteBounds() {
                if (!this.map) return;
                const bounds = this.routeLayer 
                    ? this.routeLayer.getBounds() 
                    : L.latLngBounds([[this.mitraLat, this.mitraLng], [this.customerLat, this.customerLng]]);
                this.map.fitBounds(bounds, {
                    paddingTop: 50,
                    paddingBottom: 240,
                    paddingLeft: 30,
                    paddingRight: 30
                });
            },

            fitBoundsSingle() {
                if (!this.map) return;
                this.map.setView([this.customerLat, this.customerLng], 14, {
                    paddingTop: 50,
                    paddingBottom: 220
                });
            },

            startLivePolling() {
                this.pollTimer = setInterval(async () => {
                    try {
                        const res = await fetch('{{ route('pelanggan.orders.live-tracking', $order) }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.status && data.status !== this.orderStatus) {
                                // Status berubah (misal dari Menuju Lokasi -> Tiba)
                                window.location.reload();
                                return;
                            }
                            if (data.service_started_at && !this.serviceStartedAt) {
                                this.serviceStartedAt = data.service_started_at;
                                this.initCountdown();
                            }
                            if (data.mitra_lat && data.mitra_lng) {
                                this.mitraLat = data.mitra_lat;
                                this.mitraLng = data.mitra_lng;
                                if (this.mitraMarker) {
                                    this.mitraMarker.setLatLng([this.mitraLat, this.mitraLng]);
                                }
                                this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);
                            }
                        }
                    } catch (e) {}
                }, 10000);
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

        <!-- BACKGROUND FULL MAP (Leaflet Canvas) -->
        <div id="pelanggan-order-map" class="w-full h-full absolute inset-0 z-0"></div>

        <!-- FLOATING TOP CONTROLS OVER MAP -->
        <div class="absolute top-3 left-3 right-3 z-10 flex items-center justify-between pointer-events-none">
            <!-- Status & Jarak Chip -->
            <div class="pointer-events-auto bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full shadow-md border border-slate-200/80 flex items-center space-x-2 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                @if ($order->order_type->value === 'terjadwal' && $order->status === \App\Enums\OrderStatus::MencariMitra)
                    <span class="font-bold text-slate-800 text-[11px]">Bursa Jadwal Mitra</span>
                @else
                    <span class="font-bold text-slate-800 text-[11px]">{{ $order->status->label() }}</span>
                @endif
                @if ($order->mitra_id)
                    <span class="text-slate-300">•</span>
                    <span class="font-extrabold text-emerald-800 font-mono text-[11px]" x-text="distanceText"></span>
                @endif
            </div>

            <!-- Recenter Button -->
            <div class="pointer-events-auto flex items-center space-x-2">
                <button type="button" @click="hasMitra ? fitRouteBounds() : fitBoundsSingle()" 
                    title="Pusatkan Peta"
                    class="w-9 h-9 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-slate-200/80 text-slate-700 hover:text-emerald-700 active:scale-95 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                    </svg>
                </button>
            </div>
        </div>

        @if ($order->mitra_id)
            <x-order-chat-drawer
                :order="$order"
                :fetchUrl="route('pelanggan.orders.chat.messages', $order)"
                :sendUrl="route('pelanggan.orders.chat.send', $order)"
                :isOrderActive="in_array($order->status, [\App\Enums\OrderStatus::Dijadwalkan, \App\Enums\OrderStatus::MenujuLokasi, \App\Enums\OrderStatus::Tiba, \App\Enums\OrderStatus::SedangBerlangsung])"
                otherPartyName="Mitra Terapis" />
        @endif

        <!-- GOJEK-STYLE DRAGGABLE BOTTOM SHEET -->
        <div class="absolute bottom-0 left-0 right-0 z-30 bg-white rounded-t-3xl shadow-2xl border-t border-slate-200/90 flex flex-col transition-all duration-300 ease-out"
             :class="isExpanded ? 'h-[85vh] max-h-[85vh]' : 'h-[235px] max-h-[235px]'">

            <!-- PULL HANDLE & HEADER TAP ZONE -->
            <div class="pt-2.5 pb-2 px-4 cursor-pointer select-none bg-white rounded-t-3xl border-b border-slate-100 flex flex-col items-center"
                 @touchstart.passive="handleTouchStart($event)"
                 @touchmove.passive="handleTouchMove($event)"
                 @touchend="handleTouchEnd()"
                 @click="toggleSheet()">
                <!-- Grab Handle Pill -->
                <div class="w-12 h-1.5 bg-slate-300 rounded-full mb-2 hover:bg-slate-400 transition"></div>

                <div class="w-full flex items-center justify-between">
                    <div class="flex items-center space-x-2 truncate">
                        <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wider">KODE:</span>
                        <h2 class="text-xs font-extrabold text-slate-900 font-mono">{{ $order->order_code }}</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status->badgeClasses() }}">
                            @if ($order->order_type->value === 'terjadwal' && $order->status === \App\Enums\OrderStatus::MencariMitra)
                                Bursa Mitra
                            @else
                                {{ $order->status->label() }}
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center space-x-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 hover:bg-emerald-100 transition">
                        <span x-text="isExpanded ? 'Tutup Detail ↓' : 'Tarik Detail ↑'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="isExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- MODE 1: PEEK / COLLAPSED VIEW (Ringkas & Peta Terlihat Jelas) -->
            <div x-show="!isExpanded" class="p-4 flex-1 flex flex-col justify-between">
                @if ($order->mitra)
                    <!-- Terapis Terhubung -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 truncate">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-700 text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-xs">
                                {{ strtoupper(substr($order->mitra->name, 0, 2)) }}
                            </div>
                            <div class="truncate">
                                <h3 class="text-xs font-extrabold text-slate-900 truncate">{{ $order->mitra->name }}</h3>
                                <p class="text-[11px] text-slate-500 capitalize">Terapis {{ $order->mitra->gender }} • ★ {{ number_format($order->mitra->partnerRating(), 1) }}</p>
                            </div>
                        </div>
                        <a href="tel:{{ $order->mitra->phone }}" 
                           class="py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200 flex items-center space-x-1 transition shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>Hubungi</span>
                        </a>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/70 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-1.5 text-slate-600">
                            <span>Status:</span>
                            <strong class="text-slate-900">{{ $order->status->label() }}</strong>
                        </div>
                        <div class="font-mono text-emerald-800 font-bold">
                            <span x-text="distanceText"></span> (<span x-text="etaText"></span>)
                        </div>
                    </div>
                @else
                    <!-- Belum Ada Mitra / Menunggu -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-extrabold text-slate-900">{{ $order->service_name }}</span>
                            <span class="font-mono font-bold text-emerald-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 line-clamp-1">
                            📍 {{ $order->service_address_text }}
                        </p>
                        <div class="text-[11px] text-emerald-700 font-medium">
                            @if ($order->order_type->value === 'terjadwal')
                                Bursa Jadwal Mitra: Menunggu konfirmasi terapis
                            @else
                                Sistem sedang mencocokkan terapis terdekat...
                            @endif
                        </div>
                    </div>
                @endif

                <div class="pt-2 flex items-center justify-between border-t border-slate-100 text-xs">
                    <span class="text-slate-500">Total Tagihan:</span>
                    <span class="text-sm font-extrabold text-slate-900 font-mono">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- MODE 2: EXPANDED VIEW (Scrollable Rincian Lengkap & Pelacakan) -->
            <div x-show="isExpanded" class="flex-1 overflow-y-auto px-4 pb-6 pt-3 space-y-4 overscroll-contain">

                <!-- Visual Progress Tracker -->
                @php
                    $statusSteps = [
                        \App\Enums\OrderStatus::MenungguPembayaran->value => 1,
                        \App\Enums\OrderStatus::MencariMitra->value => 2,
                        \App\Enums\OrderStatus::Dijadwalkan->value => 3,
                        \App\Enums\OrderStatus::MenujuLokasi->value => 4,
                        \App\Enums\OrderStatus::Tiba->value => 5,
                        \App\Enums\OrderStatus::SedangBerlangsung->value => 6,
                        \App\Enums\OrderStatus::Selesai->value => 7,
                    ];
                    $currentStep = $statusSteps[$order->status->value] ?? 1;
                @endphp

                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-2">
                        <span class="{{ $currentStep >= 2 ? 'text-emerald-700 font-extrabold' : '' }}">
                            {{ $order->order_type->value === 'terjadwal' ? 'Bursa Mitra' : 'Cari Mitra' }}
                        </span>
                        <span class="{{ $currentStep >= 3 ? 'text-emerald-700 font-extrabold' : '' }}">Dijadwalkan</span>
                        <span class="{{ $currentStep >= 4 ? 'text-emerald-700 font-extrabold' : '' }}">Menuju Lokasi</span>
                        <span class="{{ $currentStep >= 7 ? 'text-emerald-700 font-extrabold' : '' }}">Selesai</span>
                    </div>
                    <div class="w-full bg-slate-200/80 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" 
                             style="width: {{ min(100, max(15, ($currentStep / 7) * 100)) }}%"></div>
                    </div>
                </div>

                <!-- 0. Kondisi: Menunggu Pembayaran (CUS-09) -->
                @if ($order->status === \App\Enums\OrderStatus::MenungguPembayaran)
                    @php
                        $userWallet = auth()->user()->getOrCreateWallet();
                        $hasSufficient = (float) $userWallet->balance >= (float) $order->total_price;
                        $deficit = (float) $order->total_price - (float) $userWallet->balance;
                    @endphp
                    <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-xs space-y-3">
                        <div class="flex items-center space-x-3 text-amber-900">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 leading-tight">Konfirmasi Pembayaran Pesanan</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Selesaikan pembayaran saldo untuk mulai mencari mitra terapis.</p>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Saldo Dompet Anda:</span>
                                <span class="font-extrabold text-slate-900 font-mono">{{ $userWallet->formatted_balance }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Total Tagihan:</span>
                                <span class="font-extrabold text-slate-900 font-mono">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @if ($hasSufficient)
                                <form method="POST" action="{{ route('pelanggan.orders.pay', $order) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                        <span>Bayar Sekarang ({{ $userWallet->formatted_balance }})</span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('pelanggan.wallet.topup') }}"
                                    class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                    <span>Isi Saldo Dompet (+Rp {{ number_format($deficit, 0, ',', '.') }})</span>
                                </a>
                            @endif

                            <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" onsubmit="return confirmCancelOrder(event);">
                                @csrf
                                <button type="submit"
                                    class="w-full min-h-[42px] py-2 rounded-xl border border-slate-200 hover:bg-rose-50 hover:text-rose-700 text-slate-600 font-bold text-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                                    <span>Batalkan Pesanan Ini</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- 1A. Kondisi: Pesanan Instan - Sedang Mencari Mitra Terdekat (CUS-10: Hitung Mundur 30 Menit) -->
                @if ($order->status === \App\Enums\OrderStatus::MencariMitra && $order->order_type->value === 'instan')
                    @php
                        $searchDeadline = $order->created_at->addMinutes(30)->timestamp;
                    @endphp
                    <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-4 text-white shadow-sm space-y-3"
                         x-data="{
                            deadline: {{ $searchDeadline }},
                            minutes: 0,
                            seconds: 0,
                            timer: null,
                            updateCountdown() {
                                const now = Math.floor(Date.now() / 1000);
                                const diff = Math.max(0, this.deadline - now);
                                this.minutes = Math.floor(diff / 60);
                                this.seconds = diff % 60;
                            },
                            init() {
                                this.updateCountdown();
                                this.timer = setInterval(() => this.updateCountdown(), 1000);
                            }
                         }">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-ping"></span>
                            <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">Mencari Terapis Instan</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Sistem Sedang Menghubungkan Terapis</h3>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Mencari mitra terapis berlisensi terdekat di wilayah operasional Anda yang siap berangkat saat ini.
                            </p>
                        </div>

                        <div class="p-3 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between text-xs">
                            <span class="text-slate-200 font-medium">Batas Waktu Pencarian:</span>
                            <div class="text-base font-black font-mono text-amber-300 tracking-wider">
                                <span x-text="String(minutes).padStart(2, '0')"></span>:<span x-text="String(seconds).padStart(2, '0')"></span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" 
                              onsubmit="return confirmCancelOrder(event, 'Batalkan pencarian mitra? Saldo dompet sebesar Rp {{ number_format($order->total_price, 0, ',', '.') }} akan segera dikembalikan 100% ke dompet Anda.');">
                            @csrf
                            <button type="submit"
                                class="w-full min-h-[42px] py-2 rounded-xl bg-white/10 hover:bg-rose-600/80 border border-white/20 text-white font-bold text-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                                <span>Batalkan Pencarian (Pengembalian Dana 100%)</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- 1B. Kondisi: Pesanan Terjadwal - Menunggu Mitra Mengambil Jadwal di Bursa (MIT-06) -->
                @if ($order->status === \App\Enums\OrderStatus::MencariMitra && $order->order_type->value === 'terjadwal')
                    <div class="bg-gradient-to-br from-emerald-950 via-teal-900 to-slate-900 rounded-2xl p-4 text-white shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Bursa Jadwal Mitra</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/15 text-emerald-200 border border-white/20">
                                Pesanan Terjadwal
                            </span>
                        </div>

                        <div>
                            <h3 class="text-sm font-extrabold text-white">Menunggu Konfirmasi Terapis</h3>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Pesanan Anda telah dipublikasikan ke Bursa Jadwal Mitra Pijat.In. Mitra terapis di wilayah Anda akan mengambil slot jadwal ini sesuai ketersediaan.
                            </p>
                        </div>

                        <div class="p-3 bg-white/10 rounded-xl border border-white/15 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300">Waktu Pelaksanaan:</span>
                                <span class="font-bold text-emerald-300 font-mono">
                                    {{ $order->local_scheduled_start_time?->translatedFormat('d M Y, H:i') }} {{ $order->timezone_abbr }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-white/10">
                                <span class="text-slate-300">Preferensi Terapis:</span>
                                <span class="font-semibold text-white capitalize">
                                    {{ $order->preferred_gender === 'bebas' ? 'Siapa Saja (Pria / Wanita)' : 'Terapis ' . $order->preferred_gender }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-white/10">
                                <span class="text-slate-300">Batas Klaim Bursa:</span>
                                <span class="font-medium text-amber-300">
                                    H-2 Jam (pk. {{ $order->local_scheduled_start_time?->copy()->subHours(2)->translatedFormat('H:i') }} {{ $order->timezone_abbr }})
                                </span>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-[11px] text-slate-300 leading-relaxed">
                            💡 Anda akan menerima notifikasi begitu mitra terapis mengonfirmasi jadwal ini. Anda juga dapat membatalkan pesanan kapan saja dengan garansi pengembalian saldo 100%.
                        </div>

                        <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" 
                              onsubmit="return confirmCancelOrder(event, 'Batalkan pesanan terjadwal? Saldo dompet sebesar Rp {{ number_format($order->total_price, 0, ',', '.') }} akan dikembalikan 100% ke dompet Anda.');">
                            @csrf
                            <button type="submit"
                                class="w-full min-h-[42px] py-2 rounded-xl bg-white/10 hover:bg-rose-600/80 border border-white/20 text-white font-bold text-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                                <span>Batalkan Pesanan (Pengembalian Dana 100%)</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- 1C. Kondisi: Pesanan Terjadwal - Terapis Sudah Mengonfirmasi Jadwal (Dijadwalkan) -->
                @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
                    <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-emerald-950 rounded-2xl p-4 text-white shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Jadwal Dikonfirmasi</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/15 text-emerald-200 border border-white/20">
                                {{ $order->local_scheduled_start_time?->diffForHumans() }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-sm font-extrabold text-white">Terapis Siap Datang Sesuai Jadwal</h3>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Terapis telah mengonfirmasi kesediaan untuk jadwal layanan Anda pada <strong class="text-white">{{ $order->local_scheduled_start_time?->translatedFormat('l, d F Y pk. H:i') }} {{ $order->timezone_abbr }}</strong>. Terapis akan mulai berangkat menuju alamat Anda menjelang waktu tersebut.
                            </p>
                        </div>

                        <div class="p-2.5 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between text-xs">
                            <span class="text-slate-300">Waktu Layanan:</span>
                            <span class="font-extrabold text-emerald-300 font-mono">
                                {{ $order->local_scheduled_start_time?->translatedFormat('d M Y, H:i') }} {{ $order->timezone_abbr }}
                            </span>
                        </div>
                    </div>
                @endif

                <!-- 2. Kondisi: Terapis Dalam Perjalanan (CUS-10: OTW & Estimasi Kedatangan) -->
                @if ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                    <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-2xl p-4 text-white shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-400 animate-pulse"></span>
                                <span class="text-xs font-bold text-purple-200 uppercase tracking-wider">Sedang Menuju Lokasi</span>
                            </div>
                            <span class="text-[11px] text-purple-200 font-semibold bg-white/10 px-2.5 py-0.5 rounded-full">GPS Live</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Terapis Dalam Perjalanan</h3>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Terapis telah berangkat dan sedang menuju alamat Anda. Harap persiapkan tempat layanan.
                            </p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between text-xs">
                            <span class="text-slate-200">Estimasi Kedatangan:</span>
                            <span class="font-extrabold text-amber-300 font-mono">~15 - 20 Menit</span>
                        </div>
                        <div class="text-[11px] text-purple-200/80 italic flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Posisi diperbarui setiap 30 detik</span>
                        </div>
                    </div>
                @endif

                <!-- 2B. Kondisi: Terapis Telah Tiba di Lokasi -->
                @if ($order->status === \App\Enums\OrderStatus::Tiba)
                    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-4 text-white shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-pulse"></span>
                                <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">Terapis Telah Tiba</span>
                            </div>
                            <span class="text-[11px] text-blue-200 font-semibold bg-white/10 px-2.5 py-0.5 rounded-full">Di Lokasi</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Terapis Sudah di Lokasi Anda</h3>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Terapis telah sampai di depan alamat layanan. Silakan sambut terapis Anda untuk memulai sesi layanan.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- 2C. Kondisi: Layanan Sedang Berlangsung (Timer Durasi Pijat) -->
                @if ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                    <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 rounded-2xl p-4 text-white shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                                <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Layanan Berlangsung</span>
                            </div>
                            <span class="text-[11px] text-emerald-200 font-semibold bg-white/10 px-2.5 py-0.5 rounded-full font-mono" x-text="remainingTimeFormatted">
                                {{ $order->duration_minutes }}:00
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Sesi Pemijatan Sedang Berjalan</h3>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Nikmati layanan pemijatan Anda. Durasi paket: <strong>{{ $order->duration_minutes }} Menit</strong>.
                            </p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between text-xs">
                            <span class="text-slate-200">Sisa Waktu Layanan:</span>
                            <span class="font-extrabold text-emerald-300 font-mono text-sm" x-text="remainingTimeFormatted">
                                Menghitung...
                            </span>
                        </div>
                    </div>
                @endif

                <!-- 2D. Kondisi: Layanan Selesai (Rating Bintang 1-5 Pelanggan - CUS-14) -->
                @if ($order->status === \App\Enums\OrderStatus::Selesai)
                    <div class="bg-white rounded-2xl p-4 border border-emerald-200 shadow-xs space-y-3">
                        <div class="flex items-center space-x-3 text-emerald-900">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 leading-tight">Layanan Telah Selesai</h3>
                                <p class="text-xs text-slate-600 mt-0.5">Terima kasih telah mempercayakan kenyamanan Anda pada Pijat.In.</p>
                            </div>
                        </div>

                        <!-- Interactive 5-Star Rating (CUS-14) -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-center">
                            <p class="text-xs font-bold text-slate-800">
                                <template x-if="hasRated">
                                    <span>✓ Terima kasih atas ulasan <strong x-text="selectedRating"></strong>/5 bintang Anda!</span>
                                </template>
                                <template x-if="!hasRated">
                                    <span>Berikan Penilaian Bintang untuk Terapis:</span>
                                </template>
                            </p>
                            <div class="flex items-center justify-center space-x-2 py-1">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button" @click="submitRating(star)"
                                            :disabled="hasRated || isSubmittingRating"
                                            class="text-3xl transition transform hover:scale-110 active:scale-95 disabled:cursor-default cursor-pointer">
                                        <span :class="star <= selectedRating ? 'text-amber-400' : 'text-slate-300'">★</span>
                                    </button>
                                </template>
                            </div>

                            <!-- Prompt jika rating <= 3 bintang -->
                            <template x-if="hasRated && selectedRating <= 3">
                                <div class="pt-2 border-t border-slate-200/80 text-[11px] text-slate-600 space-y-1.5">
                                    <p class="text-rose-700 font-semibold">Ada kendala atau ketidaknyamanan selama layanan?</p>
                                    <a href="{{ route('pelanggan.dashboard') }}"
                                       class="inline-flex items-center space-x-1 text-xs font-bold text-rose-700 hover:text-rose-800 underline">
                                        <span>Laporkan Masalah ke Menu Bantuan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif

                <!-- 3. Profil Terapis Penanggung Jawab (CUS-10: Tanpa NIK/Data Sensitif) -->
                @if ($order->mitra)
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-bold text-slate-900">Terapis Penanggung Jawab</h3>
                            <span class="text-[11px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                Terverifikasi
                            </span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-700 text-white font-extrabold text-base flex items-center justify-center shrink-0 shadow-xs">
                                {{ strtoupper(substr($order->mitra->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-extrabold text-slate-900 truncate">{{ $order->mitra->name }}</h4>
                                <div class="flex items-center space-x-2 mt-0.5 text-xs">
                                    <span class="text-amber-600 font-bold flex items-center space-x-0.5">
                                        <span>★</span>
                                        <span>{{ number_format($order->mitra->partnerRating(), 1) }}</span>
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-500 font-medium">
                                        {{ $order->mitra->completedOrdersCount() }} Layanan Selesai
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Gender Terapis:</span>
                            <span class="font-bold text-slate-800 capitalize">Terapis {{ $order->mitra->gender }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="tel:{{ $order->mitra->phone }}" 
                               class="w-full min-h-[42px] py-2 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>Hubungi Terapis</span>
                            </a>

                            @php
                                $cleanMitraPhone = preg_replace('/[^0-9]/', '', $order->mitra->phone);
                                if (str_starts_with($cleanMitraPhone, '0')) {
                                    $cleanMitraPhone = '62' . substr($cleanMitraPhone, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $cleanMitraPhone }}?text={{ urlencode('Halo ' . $order->mitra->name . ', saya pemesan untuk pesanan ' . $order->order_code . '.') }}" 
                               target="_blank"
                               class="w-full min-h-[42px] py-2 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.587 1.771.889 2.796.889 3.182 0 5.768-2.586 5.768-5.766.001-3.18-2.585-5.766-5.768-5.766zm9.969 5.766c0 5.508-4.462 9.97-9.969 9.97-1.719 0-3.364-.44-4.811-1.222l-5.22 1.369 1.393-5.089c-.879-1.503-1.362-3.238-1.362-5.028 0-5.508 4.462-9.97 9.969-9.97 5.507 0 9.969 4.462 9.969 9.97z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Detail Layanan & Waktu -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2.5 text-xs">
                    <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100">Informasi Layanan</h3>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Nama Layanan:</span>
                        <span class="font-bold text-slate-900">{{ $order->service_name }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Durasi Pijat:</span>
                        <span class="font-bold text-slate-900">{{ $order->duration_minutes }} Menit</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Jenis Pesanan:</span>
                        <span class="font-bold text-slate-900 capitalize">{{ $order->order_type->value }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Waktu Layanan:</span>
                        <span class="font-bold text-slate-900">
                            @if ($order->local_scheduled_start_time)
                                {{ $order->local_scheduled_start_time->translatedFormat('d M Y, H:i') }} {{ $order->timezone_abbr }}
                            @else
                                Segera (Instan)
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Penerima Layanan (CUS-04) -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2.5 text-xs">
                    <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100">Penerima Layanan</h3>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Penerima:</span>
                        <span class="font-bold text-slate-900">
                            {{ $order->recipient_name }}
                            <span class="text-[10px] text-slate-400 font-normal">({{ $order->recipient_type === 'myself' ? 'Diri Sendiri' : 'Orang Lain' }})</span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Jenis Kelamin Penerima:</span>
                        <span class="font-bold text-slate-900 capitalize">
                            {{ $order->recipient_gender }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Preferensi Terapis:</span>
                        <span class="font-bold text-emerald-800 capitalize">
                            @if(($order->preferred_gender ?? 'bebas') === 'bebas')
                                Bebas (Siapa Saja)
                            @else
                                Terapis {{ ucfirst($order->preferred_gender) }}
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Telepon / WhatsApp:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $order->recipient_phone ?? '-' }}</span>
                    </div>
                </div>

                <!-- Alamat Kunjungan -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2 text-xs">
                    <h3 class="text-xs font-bold text-slate-900 pb-1 border-b border-slate-100">Alamat Kunjungan</h3>
                    <p class="text-slate-700 leading-relaxed pt-1">{{ $order->service_address_text }}</p>
                </div>

                <!-- Catatan Medis & Persetujuan Risiko (CUS-07 & SYS-08) -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2 text-xs">
                    <h3 class="text-xs font-bold text-slate-900 pb-1 border-b border-slate-100">Kepatuhan Medis & Risiko</h3>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Catatan Medis:</span>
                        <span class="font-medium text-slate-800">
                            {{ $order->medical_notes ? 'Tercatat (Terenkripsi)' : 'Tidak ada catatan khusus' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Persetujuan Risiko:</span>
                        <span class="font-bold text-emerald-800 font-mono">
                            {{ $order->risk_agreement_version ?? 'v1.0' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Waktu Persetujuan:</span>
                        <span class="text-slate-700 font-mono text-[11px]">
                            {{ $order->consent_timestamp?->translatedFormat('d M Y, H:i:s') ?? '-' }}
                        </span>
                    </div>
                </div>

                <!-- Button to Minimize Sheet -->
                <button type="button" @click="collapseSheet()"
                    class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    <span>Tutup Detail & Lihat Peta Penuh</span>
                </button>
            </div>

        </div>

    </div>
</x-pwa-layout>
