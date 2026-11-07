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
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            box-sizing: border-box !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon .swal2-icon-content {
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-question,
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-info {
            background-color: #ecfdf5 !important;
            color: #059669 !important;
            box-shadow: 0 0 0 8px #f0fdf4 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-warning {
            background-color: #fef3c7 !important;
            color: #d97706 !important;
            box-shadow: 0 0 0 8px #fffbeb !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-success {
            background-color: #ecfdf5 !important;
            color: #059669 !important;
            box-shadow: 0 0 0 8px #f0fdf4 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-error {
            background-color: #fef2f2 !important;
            color: #e11d48 !important;
            box-shadow: 0 0 0 8px #fff1f2 !important;
        }
        /* Sembunyikan elemen animasi garis/ring default SweetAlert2 yang bocor keluar kontainer */
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-success [class^='swal2-success-circular-line'],
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-success .swal2-success-fix,
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-success .swal2-success-ring,
        div:where(.swal2-container) .swal-admin-theme .swal2-icon.swal2-success [class^='swal2-success-line'] {
            display: none !important;
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
            display: flex !important;
            flex-direction: row !important;
            gap: 0.625rem !important;
            margin: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-cancel {
            margin: 0 !important;
            flex: 1 1 0% !important;
            width: auto !important;
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
        div:where(.swal2-container) .swal-admin-theme .swal2-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm {
            margin: 0 !important;
            flex: 1 1 0% !important;
            width: auto !important;
            min-height: 42px !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            background-color: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(5, 150, 105, 0.2) !important;
            transition: all 0.15s ease-in-out !important;
            border: none !important;
            cursor: pointer !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm:hover {
            background-color: #047857 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm:active {
            background-color: #065f46 !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-danger-btn {
            margin: 0 !important;
            flex: 1 1 0% !important;
            width: auto !important;
            min-height: 42px !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            background-color: #e11d48 !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(225, 29, 72, 0.2) !important;
            transition: all 0.15s ease-in-out !important;
            border: none !important;
            cursor: pointer !important;
        }

        @keyframes progressFlow {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .animate-progress-flow {
            background: linear-gradient(90deg, #047857 0%, #10b981 50%, #34d399 75%, #047857 100%) !important;
            background-size: 200% 100% !important;
            animation: progressFlow 1.8s linear infinite !important;
        }
    </style>

    <script>
        function confirmCancelOrder(event, message = 'Apakah Anda yakin ingin membatalkan pesanan ini?') {
            event.preventDefault();
            const form = event.currentTarget || event.target;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    iconHtml: '!',
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
                        confirmButton: 'swal2-danger-btn',
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
            isExpanded: ['selesai', 'dibatalkan'].includes('{{ $order->status->value }}'),
            touchStartY: 0,
            touchCurrentY: 0,
            isDragging: false,

            customerLat: {{ $customerLat ?? 'null' }},
            customerLng: {{ $customerLng ?? 'null' }},
            mitraLat: {{ $mitraLat ? $mitraLat : 'null' }},
            mitraLng: {{ $mitraLng ? $mitraLng : 'null' }},
            isArchived: {{ $isArchived ? 'true' : 'false' }},
            hasMitra: {{ $order->mitra_id ? 'true' : 'false' }},
            orderStatus: '{{ $order->status->value }}',
            serviceStartedAt: '{{ $order->service_started_at?->toIso8601String() }}',
            durationMinutes: {{ (int) $order->duration_minutes }},
            remainingSeconds: 0,
            remainingTimeFormatted: '{{ $order->duration_minutes }}:00',
            timerInterval: null,
            selectedRating: {{ $myRating?->score ?? 0 }},
            tempRating: {{ $myRating?->score ?? 0 }},
            ratingLabels: {
                1: 'Sangat Buruk',
                2: 'Kurang Puas',
                3: 'Cukup',
                4: 'Puas',
                5: 'Sangat Puas'
            },
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

            confirmSubmitRating() {
                if (this.hasRated || this.isSubmittingRating || this.tempRating === 0) return;
                const score = this.tempRating;
                const label = this.ratingLabels[score] || `${score} Bintang`;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'question',
                        iconHtml: '?',
                        title: 'Kirim Penilaian?',
                        text: `Apakah Anda yakin ingin memberikan nilai ${score} bintang (${label}) untuk terapis ini?`,
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, Kirim Ulasan',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-admin-theme',
                            icon: 'swal2-question',
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submitRating(score);
                        }
                    });
                } else {
                    if (confirm(`Apakah Anda yakin ingin memberikan nilai ${score} bintang (${label}) untuk terapis ini?`)) {
                        this.submitRating(score);
                    }
                }
            },

            async submitRating(score) {
                if (this.hasRated || this.isSubmittingRating) return;
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
                        this.selectedRating = score;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                iconHtml: '✓',
                                title: 'Ulasan Diterima',
                                text: 'Terima kasih telah memberikan ulasan penilaian rating Anda.',
                                confirmButtonText: 'Tutup',
                                buttonsStyling: false,
                                customClass: { popup: 'swal-admin-theme', icon: 'swal2-success', confirmButton: 'swal2-confirm' }
                            });
                        }
                    }
                } catch (e) {
                } finally {
                    this.isSubmittingRating = false;
                }
            },

            initMap() {
                if (this.isArchived || !this.customerLat || !document.getElementById('pelanggan-order-map')) {
                    return;
                }

                this.map = L.map('pelanggan-order-map', {
                    zoomControl: false
                }).setView([this.customerLat, this.customerLng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                // Marker Rumah Pelanggan (Gaya Pin Hijau Tua & Rumah Putih sesuai Mockup)
                const customerIcon = L.divIcon({
                    className: 'custom-customer-pin',
                    html: `<div class='w-11 h-11 rounded-full bg-[#064e3b] text-white flex items-center justify-center shadow-2xl border-2 border-white ring-4 ring-emerald-950/20'>
                        <svg class='w-5 h-5 text-white' fill='currentColor' viewBox='0 0 20 20'>
                            <path d='M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z'/>
                        </svg>
                    </div>`,
                    iconSize: [44, 44],
                    iconAnchor: [22, 22]
                });

                this.customerMarker = L.marker([this.customerLat, this.customerLng], { icon: customerIcon }).addTo(this.map);
                this.customerMarker.bindPopup(`<strong>Lokasi Layanan Anda</strong><br>{{ addslashes($order->recipient_name) }}<br><span style='font-size:11px;'>{{ addslashes($isArchived ? $order->masked_address : $order->service_address_text) }}</span>`).openPopup();

                // Jika sudah ada mitra (Gaya Motor / Scooter dengan Radar Pulse Gelombang Sesuai Mockup)
                if (this.hasMitra && this.mitraLat && this.mitraLng) {
                    const mitraIcon = L.divIcon({
                        className: 'custom-mitra-pin',
                        html: `
                            <div class='relative flex items-center justify-center' style='width: 58px; height: 58px;'>
                                <div class='absolute w-14 h-14 rounded-full border-2 border-emerald-500/50 bg-emerald-500/20 animate-ping'></div>
                                <div class='absolute w-12 h-12 rounded-full border-2 border-emerald-600/70 bg-emerald-500/30'></div>
                                <div class='w-10 h-10 rounded-full bg-[#064e3b] text-white flex items-center justify-center shadow-2xl border-2 border-white text-lg z-10'>
                                    🛵
                                </div>
                            </div>
                        `,
                        iconSize: [58, 58],
                        iconAnchor: [29, 29]
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

                // Polling posisi GPS mitra & status setiap 3.5 detik jika pesanan aktif
                if (['mencari_mitra', 'dijadwalkan', 'menuju_lokasi', 'tiba', 'sedang_berlangsung'].includes(this.orderStatus)) {
                    this.startLivePolling();
                }

                // Auto reload seketika saat notifikasi pembaruan pesanan tiba
                window.addEventListener('pwa-new-notification', (e) => {
                    const notif = e.detail;
                    if (notif && (notif.order_id == {{ $order->id }} || notif.data?.order_id == {{ $order->id }} || notif.action_url?.includes('/orders/{{ $order->id }}'))) {
                        window.location.reload();
                    }
                });

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
                            color: '#047857',
                            weight: 5.5,
                            opacity: 0.95,
                            lineJoin: 'round',
                            lineCap: 'round'
                        }).addTo(this.map);

                        this.fitRouteBounds();

                        const distKm = (route.distance / 1000).toFixed(1);
                        this.distanceText = distKm + ' km';
                        const etaMin = Math.max(3, Math.round(route.duration / 60));
                        this.etaText = `${etaMin} min`;
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
                    color: '#047857',
                    weight: 4.5,
                    opacity: 0.85,
                    dashArray: '8, 8'
                }).addTo(this.map);

                this.fitRouteBounds();
                const dist = this.calcHaversine(startLat, startLng, destLat, destLng);
                this.distanceText = dist + ' km';
                const etaMin = Math.max(5, Math.round(dist * 3));
                this.etaText = `${etaMin} min`;
                this.isLoadingRoute = false;
            },

            fitRouteBounds() {
                if (!this.map) return;
                const bounds = this.routeLayer 
                    ? this.routeLayer.getBounds() 
                    : L.latLngBounds([[this.mitraLat, this.mitraLng], [this.customerLat, this.customerLng]]);
                this.map.fitBounds(bounds, {
                    paddingTop: 85,
                    paddingBottom: 300,
                    paddingLeft: 30,
                    paddingRight: 30
                });
            },

            fitBoundsSingle() {
                if (!this.map) return;
                this.map.setView([this.customerLat, this.customerLng], 14, {
                    paddingTop: 85,
                    paddingBottom: 300
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
                }, 3500);
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

        <!-- BACKGROUND FULL MAP (Leaflet Canvas) OR ARCHIVED CARD -->
        @if ($isArchived)
            <div class="w-full h-full absolute inset-0 z-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 flex flex-col items-center justify-center p-6 text-center text-white">
                <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-700/80 text-emerald-400 flex items-center justify-center shadow-lg mb-2.5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-extrabold tracking-tight text-white">Data Perjalanan dan Peta Diarsipkan</h4>
                <p class="text-[11px] text-slate-300 max-w-xs mt-1.5 leading-relaxed">
                    Pesanan telah selesai lebih dari 24 jam. Titik rute GPS dan lokasi detail telah diarsipkan demi perlindungan privasi.
                </p>
                <div class="mt-3 inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-[10px] font-bold text-slate-300">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Ringkasan Transaksi & Tarif Tetap Tersedia</span>
                </div>
            </div>
        @else
            <div id="pelanggan-order-map" class="w-full h-full absolute inset-0 z-0"></div>
        @endif

        @if (! $isArchived)
            <!-- FLOATING TOP PILL ("DYNAMIC ISLAND") OVER MAP -->
            <div class="absolute top-3.5 left-3.5 right-3.5 z-10 pointer-events-none flex justify-center">
                <div class="pointer-events-auto w-full max-w-sm bg-white/95 backdrop-blur-md px-3.5 py-2.5 rounded-full shadow-xl border border-slate-100 flex items-center justify-between transition-all">
                    <!-- Left: Wellness Icon & Title -->
                    <div class="flex items-center space-x-3 truncate">
                        <div class="w-10 h-10 rounded-full bg-emerald-100/90 text-emerald-800 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5 text-emerald-700" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A9.49 9.49 0 0012 20c4.41 0 8-3.59 8-8 0-.41-.03-.81-.08-1.2A9.94 9.94 0 0021 8h-4zm-5 10c-2.48 0-4.6-1.52-5.51-3.69C7.45 12.16 9.38 9.77 12 8.77c.86 2.5 2.54 6.8 5.48 8.44A6.012 6.012 0 0112 18z"/>
                            </svg>
                        </div>
                        <div class="truncate">
                            <h2 class="text-xs font-black text-slate-900 tracking-tight leading-snug">Pesanan Aktif</h2>
                            <p class="text-[11px] text-slate-500 font-medium truncate">
                                @if ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                                    Tiba dalam <span class="font-bold text-slate-800" x-text="etaText"></span>
                                @elseif ($order->status === \App\Enums\OrderStatus::Tiba)
                                    <span class="font-bold text-emerald-700">Terapis tiba di lokasi</span>
                                @elseif ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                                    <span class="font-bold text-emerald-700">Sesi Pijat (<span x-text="remainingTimeFormatted"></span>)</span>
                                @elseif ($order->status === \App\Enums\OrderStatus::MencariMitra)
                                    <span class="text-slate-500">Mencari terapis...</span>
                                @else
                                    <span class="text-slate-500">{{ $order->status->label() }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Right: Recenter & Auto-Refresh Controls -->
                    <div class="flex items-center space-x-1 shrink-0 pl-2">
                        <button type="button" 
                            @click="hasMitra ? fitRouteBounds() : fitBoundsSingle()" 
                            title="Pusatkan Peta"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 flex items-center justify-center transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                            </svg>
                        </button>
                        <button type="button" 
                            @click="window.location.reload()" 
                            title="Segarkan data pesanan"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 flex items-center justify-center transition active:scale-95 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-emerald-600 animate-spin" style="animation-duration: 4s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if ($order->mitra_id)
            <x-order-chat-drawer
                :order="$order"
                :fetchUrl="route('pelanggan.orders.chat.messages', $order)"
                :sendUrl="route('pelanggan.orders.chat.send', $order)"
                :isOrderActive="in_array($order->status, [\App\Enums\OrderStatus::Dijadwalkan, \App\Enums\OrderStatus::MenujuLokasi, \App\Enums\OrderStatus::Tiba, \App\Enums\OrderStatus::SedangBerlangsung])"
                otherPartyName="Mitra Terapis" />
        @endif

        <!-- MOCKUP-STYLE DRAGGABLE BOTTOM SHEET -->
        <div class="absolute bottom-0 left-0 right-0 z-30 bg-white rounded-t-[32px] shadow-2xl border-t border-slate-100 flex flex-col transition-all duration-300 ease-out"
             :class="isExpanded ? 'h-[86vh] max-h-[86vh]' : 'h-[285px] max-h-[285px]'">

            <!-- PULL HANDLE & HEADER TAP ZONE -->
            <div class="pt-2.5 pb-2 px-4 cursor-pointer select-none bg-white rounded-t-[32px] flex flex-col items-center"
                 :class="isExpanded ? 'border-b border-slate-100' : ''"
                 @touchstart.passive="handleTouchStart($event)"
                 @touchmove.passive="handleTouchMove($event)"
                 @touchend="handleTouchEnd()"
                 @click="toggleSheet()">
                <!-- Grab Handle Pill -->
                <div class="w-10 h-1 bg-slate-300 rounded-full hover:bg-slate-400 transition" :class="isExpanded ? 'mb-2' : 'mb-0.5'"></div>

                <div x-show="isExpanded" class="w-full flex items-center justify-between pt-1">
                    <span class="text-xs font-bold text-slate-700">Rincian Lengkap Pesanan</span>
                    <button type="button" class="flex items-center space-x-1 text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 hover:bg-emerald-100 transition cursor-pointer">
                        <span>Tutup Detail ↓</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- MODE 1: PEEK / COLLAPSED VIEW (Persis Seperti Mockup Gambar) -->
            <div x-show="!isExpanded" class="p-4 flex-1 flex flex-col justify-between overflow-hidden">
                @if ($order->mitra)
                    <!-- Baris Profil Terapis -->
                    <div class="flex items-center space-x-3.5">
                        <div class="relative w-14 h-14 rounded-full overflow-hidden border-2 border-emerald-100 shadow-sm shrink-0 bg-slate-100">
                            @if ($order->mitra->profile_photo_url ?? false)
                                <img src="{{ $order->mitra->profile_photo_url }}" alt="{{ $order->mitra->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-[#064e3b] to-emerald-800 text-white font-black text-base flex items-center justify-center">
                                    {{ strtoupper(substr($order->mitra->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-extrabold text-slate-900 truncate leading-snug">{{ $order->mitra->name }}</h3>
                            <p class="text-xs text-slate-500 font-medium">Terapis Profesional</p>
                            <div class="flex items-center space-x-1.5 mt-0.5 text-xs font-bold">
                                <span class="text-amber-500">★</span>
                                <span class="text-slate-800">{{ number_format($order->mitra->partnerRating(), 1) }}</span>
                                <span class="text-slate-400 font-normal">({{ $order->mitra->completedOrdersCount() > 0 ? $order->mitra->completedOrdersCount() . ' layanan' : '1.2k' }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3-Langkah Horizontal Stepper Persis Mockup -->
                    @php
                        $isStep1Done = true;
                        $isStep2Active = $order->status === \App\Enums\OrderStatus::MenujuLokasi;
                        $isStep2Done = in_array($order->status, [
                            \App\Enums\OrderStatus::MenujuLokasi,
                            \App\Enums\OrderStatus::Tiba,
                            \App\Enums\OrderStatus::SedangBerlangsung,
                            \App\Enums\OrderStatus::Selesai,
                        ]);
                        $isStep3Active = in_array($order->status, [
                            \App\Enums\OrderStatus::Tiba,
                            \App\Enums\OrderStatus::SedangBerlangsung,
                        ]);
                        $isStep3Done = in_array($order->status, [
                            \App\Enums\OrderStatus::Tiba,
                            \App\Enums\OrderStatus::SedangBerlangsung,
                            \App\Enums\OrderStatus::Selesai,
                        ]);
                    @endphp
                    <div class="w-full py-2 px-2">
                        <div class="relative w-full">
                            <!-- Connecting Line Track (Persis dari tengah node 1 ke tengah node 3) -->
                            <div class="absolute top-[14px] h-[3px] -translate-y-1/2 bg-slate-200 z-0 rounded-full overflow-hidden flex" style="left: calc(100% / 6); right: calc(100% / 6);">
                                <!-- Segmen 1: Dipesan -> Dalam Perjalanan -->
                                <div class="w-1/2 h-full transition-all duration-500 {{ $isStep2Active ? 'animate-progress-flow' : ($isStep2Done ? 'bg-emerald-600' : 'bg-slate-200') }}"></div>
                                <!-- Segmen 2: Dalam Perjalanan -> Tiba -->
                                <div class="w-1/2 h-full transition-all duration-500 {{ $isStep3Done ? 'bg-emerald-600' : ($isStep3Active ? 'animate-progress-flow' : 'bg-slate-200') }}"></div>
                            </div>

                            <!-- 3 Kolom Presisi Berjarak Sama -->
                            <div class="grid grid-cols-3 relative z-10">
                                <!-- Step 1: Dipesan -->
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-900 mt-2 tracking-tight">Dipesan</span>
                                </div>

                                <!-- Step 2: Dalam Perjalanan -->
                                <div class="flex flex-col items-center text-center">
                                    @if ($isStep2Done)
                                        <div class="relative w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            @if ($isStep2Active)
                                                <span class="absolute -inset-1 rounded-full border-2 border-emerald-400 bg-emerald-400/20 animate-ping pointer-events-none"></span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-white border-2 border-slate-300 text-slate-400 flex items-center justify-center ring-4 ring-white"></div>
                                    @endif
                                    <span class="text-xs font-extrabold {{ $isStep2Done ? 'text-slate-900' : 'text-slate-400' }} mt-2 tracking-tight">Dalam Perjalanan</span>
                                </div>

                                <!-- Step 3: Tiba -->
                                <div class="flex flex-col items-center text-center">
                                    @if ($isStep3Done)
                                        <div class="relative w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            @if ($isStep3Active && $order->status !== \App\Enums\OrderStatus::Selesai)
                                                <span class="absolute -inset-1 rounded-full border-2 border-emerald-400 bg-emerald-400/20 animate-ping pointer-events-none"></span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-white border-2 border-slate-300 text-slate-400 flex items-center justify-center ring-4 ring-white"></div>
                                    @endif
                                    <span class="text-xs font-extrabold {{ $isStep3Done ? 'text-slate-900' : 'text-slate-400' }} mt-2 tracking-tight">Tiba</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Chat dengan Terapis -->
                    <div class="space-y-1">
                        <button type="button"
                            @click="$dispatch('open-order-chat')"
                            class="w-full py-3 px-6 rounded-full bg-[#064e3b] hover:bg-[#043d2e] active:scale-[0.99] text-white font-bold text-sm shadow-md flex items-center justify-center space-x-2 transition cursor-pointer">
                            <svg class="w-4 h-4 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0h-2v2h2V9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Chat dengan Terapis</span>
                        </button>
                        <div class="text-center pt-0.5 cursor-pointer" @click="expandSheet()">
                            <span class="text-[10px] font-bold text-slate-400 hover:text-emerald-700 transition flex items-center justify-center space-x-1">
                                <span>Tarik ke atas untuk rincian pesanan & bantuan</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </span>
                        </div>
                    </div>
                @else
                    <!-- Belum Ada Mitra / Menunggu -->
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 animate-pulse text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <div class="truncate">
                                <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $order->service_name }}</h3>
                                <p class="text-xs text-slate-500">
                                    @if ($order->order_type->value === 'terjadwal')
                                        Menunggu konfirmasi terapis di bursa
                                    @else
                                        Mencari mitra terapis terdekat...
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Stepper Saat Menunggu Mitra -->
                        <div class="w-full py-2.5 px-2">
                            <div class="relative w-full">
                                <div class="absolute top-[14px] h-[3px] -translate-y-1/2 bg-slate-200 z-0 rounded-full overflow-hidden flex" style="left: calc(100% / 6); right: calc(100% / 6);">
                                    <div class="w-1/2 h-full bg-slate-200"></div>
                                    <div class="w-1/2 h-full bg-slate-200"></div>
                                </div>
                                <div class="grid grid-cols-3 relative z-10">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <span class="text-xs font-extrabold text-slate-900 mt-2 tracking-tight">Dipesan</span>
                                    </div>
                                    <div class="flex flex-col items-center text-center">
                                        <div class="relative w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-white animate-pulse">
                                            ⏳
                                        </div>
                                        <span class="text-xs font-extrabold text-amber-700 mt-2 tracking-tight">Cari Terapis</span>
                                    </div>
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-7 h-7 rounded-full bg-white border-2 border-slate-300 text-slate-400 flex items-center justify-center ring-4 ring-white"></div>
                                        <span class="text-xs font-extrabold text-slate-400 mt-2 tracking-tight">Tiba</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="button"
                                @click="expandSheet()"
                                class="w-full py-3 px-6 rounded-full bg-[#064e3b] hover:bg-[#043d2e] active:scale-[0.99] text-white font-bold text-sm shadow-md flex items-center justify-center space-x-2 transition cursor-pointer">
                                <span>Lihat Rincian & Status Lengkap</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- MODE 2: EXPANDED VIEW (Scrollable Rincian Lengkap & Pelacakan) -->
            <div x-show="isExpanded" class="flex-1 overflow-y-auto px-4 pb-6 pt-3 space-y-4 overscroll-contain">

                @if ($isArchived)
                    <!-- Notice Privasi Riwayat > 24 Jam -->
                    <div class="p-3.5 bg-slate-100 rounded-2xl border border-slate-200 text-xs text-slate-700 flex items-start space-x-2.5 shadow-2xs">
                        <svg class="w-5 h-5 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <div class="space-y-0.5">
                            <p class="font-bold text-slate-900">Arsip Riwayat (Lebih dari 24 Jam)</p>
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Rute peta, alamat lengkap, dan nomor kontak telah diarsipkan demi perlindungan privasi. Rincian tagihan dan bukti transaksi tetap tersimpan utuh.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Visual Progress Tracker (3-Tahap Sesuai Permintaan: Dipesan -> Dalam Perjalanan -> Tiba) -->
                @php
                    $isStep1Done = true;
                    $isStep2Active = $order->status === \App\Enums\OrderStatus::MenujuLokasi;
                    $isStep2Done = in_array($order->status, [
                        \App\Enums\OrderStatus::MenujuLokasi,
                        \App\Enums\OrderStatus::Tiba,
                        \App\Enums\OrderStatus::SedangBerlangsung,
                        \App\Enums\OrderStatus::Selesai,
                    ]);
                    $isStep3Active = in_array($order->status, [
                        \App\Enums\OrderStatus::Tiba,
                        \App\Enums\OrderStatus::SedangBerlangsung,
                    ]);
                    $isStep3Done = in_array($order->status, [
                        \App\Enums\OrderStatus::Tiba,
                        \App\Enums\OrderStatus::SedangBerlangsung,
                        \App\Enums\OrderStatus::Selesai,
                    ]);
                @endphp

                <!-- Stepper 3-Tahap Horizontal Persis Gambar Referensi (Animasi Alur Status & Jarak Presisi) -->
                <div class="w-full py-2.5 px-2 bg-white">
                    <div class="relative w-full">
                        <!-- Connecting Line Track (Persis dari tengah node 1 ke tengah node 3) -->
                        <div class="absolute top-[14px] h-[3px] -translate-y-1/2 bg-slate-200 z-0 rounded-full overflow-hidden flex" style="left: calc(100% / 6); right: calc(100% / 6);">
                            <!-- Segmen 1: Dipesan -> Dalam Perjalanan -->
                            <div class="w-1/2 h-full transition-all duration-500 {{ $isStep2Active ? 'animate-progress-flow' : ($isStep2Done ? 'bg-emerald-600' : 'bg-slate-200') }}"></div>
                            <!-- Segmen 2: Dalam Perjalanan -> Tiba -->
                            <div class="w-1/2 h-full transition-all duration-500 {{ $isStep3Done ? 'bg-emerald-600' : ($isStep3Active ? 'animate-progress-flow' : 'bg-slate-200') }}"></div>
                        </div>

                        <!-- 3 Kolom Presisi Berjarak Sama -->
                        <div class="grid grid-cols-3 relative z-10">
                            <!-- Step 1: Dipesan -->
                            <div class="flex flex-col items-center text-center">
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-xs font-extrabold text-slate-900 mt-2 tracking-tight">Dipesan</span>
                            </div>

                            <!-- Step 2: Dalam Perjalanan -->
                            <div class="flex flex-col items-center text-center">
                                @if ($isStep2Done)
                                    <div class="relative w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @if ($isStep2Active)
                                            <span class="absolute -inset-1 rounded-full border-2 border-emerald-400 bg-emerald-400/20 animate-ping pointer-events-none"></span>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-full bg-white border-2 border-slate-300 text-slate-400 flex items-center justify-center ring-4 ring-white"></div>
                                @endif
                                <span class="text-xs font-extrabold {{ $isStep2Done ? 'text-slate-900' : 'text-slate-400' }} mt-2 tracking-tight">Dalam Perjalanan</span>
                            </div>

                            <!-- Step 3: Tiba -->
                            <div class="flex flex-col items-center text-center">
                                @if ($isStep3Done)
                                    <div class="relative w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs ring-4 ring-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @if ($isStep3Active && $order->status !== \App\Enums\OrderStatus::Selesai)
                                            <span class="absolute -inset-1 rounded-full border-2 border-emerald-400 bg-emerald-400/20 animate-ping pointer-events-none"></span>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-full bg-white border-2 border-slate-300 text-slate-400 flex items-center justify-center ring-4 ring-white"></div>
                                @endif
                                <span class="text-xs font-extrabold {{ $isStep3Done ? 'text-slate-900' : 'text-slate-400' }} mt-2 tracking-tight">Tiba</span>
                            </div>
                        </div>
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

                        <!-- 15-minute payment countdown (CUS-09) -->
                        @php
                            $paymentDeadline = $order->created_at->addMinutes(15)->timestamp;
                        @endphp
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between text-xs"
                             x-data="{
                                deadline: {{ $paymentDeadline }},
                                timeLeft: '',
                                updateTimer() {
                                    const now = Math.floor(Date.now() / 1000);
                                    const diff = Math.max(0, this.deadline - now);
                                    const m = Math.floor(diff / 60);
                                    const s = diff % 60;
                                    this.timeLeft = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                                },
                                init() {
                                    this.updateTimer();
                                    setInterval(() => this.updateTimer(), 1000);
                                }
                             }">
                            <span class="text-amber-950 font-semibold flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Batas Waktu Pembayaran:</span>
                            </span>
                            <span class="text-sm font-black font-mono text-amber-950" x-text="timeLeft">15:00</span>
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
                            @if (! $hasSufficient)
                                <div class="flex items-center justify-between text-rose-700 pt-1 border-t border-slate-200 font-bold">
                                    <span>Sisa Kekurangan:</span>
                                    <span class="font-mono">Rp {{ number_format($deficit, 0, ',', '.') }}</span>
                                </div>
                            @endif
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
                            @elseif ($pendingInvoice)
                                <a href="{{ route('pelanggan.wallet.invoice', $pendingInvoice->invoice_number) }}"
                                    class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Bayar via QRIS / Xendit (+Rp {{ number_format($pendingInvoice->amount, 0, ',', '.') }})</span>
                                </a>
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
                    @php
                        $hoursBefore = $order->scheduled_start_time ? \Carbon\Carbon::now()->diffInHours($order->scheduled_start_time, false) : 0;
                        $canCancelFull = $hoursBefore >= 3;
                        $canReschedule = $hoursBefore >= 3 && $order->reschedule_count < 2;
                        $cancelWarn = $canCancelFull
                            ? 'Batalkan pesanan terjadwal? Saldo dompet sebesar Rp ' . number_format($order->total_price, 0, ',', '.') . ' akan dikembalikan 100% ke dompet Anda.'
                            : 'PERINGATAN KETENTUAN 4.7: Pembatalan kurang dari 3 jam sebelum jadwal layanan dikenakan potongan 50% (Rp ' . number_format($order->total_price * 0.5, 0, ',', '.') . ') sebagai hak kompensasi terapis. Pengembalian saldo adalah 50% (Rp ' . number_format($order->total_price * 0.5, 0, ',', '.') . '). Lanjutkan pembatalan?';
                    @endphp
                    <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-emerald-950 rounded-2xl p-4 text-white shadow-sm space-y-3"
                         x-data="{ showRescheduleModal: false }">
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

                        <div class="space-y-2 pt-2 border-t border-white/10">
                            @if ($canReschedule)
                                <button type="button" @click="showRescheduleModal = true"
                                    class="w-full min-h-[42px] py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Jadwalkan Ulang / Reschedule (Sisa {{ 2 - $order->reschedule_count }}x)</span>
                                </button>
                            @elseif ($order->reschedule_count >= 2)
                                <div class="p-2 rounded-xl bg-white/5 border border-white/10 text-center text-[11px] text-amber-200">
                                    Batas maksimal reschedule telah tercapai (maksimal 2x per pesanan).
                                </div>
                            @else
                                <div class="p-2 rounded-xl bg-white/5 border border-white/10 text-center text-[11px] text-amber-200">
                                    Reschedule ditutup karena waktu layanan kurang dari 3 jam.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" 
                                  onsubmit="return confirmCancelOrder(event, '{{ $cancelWarn }}');">
                                @csrf
                                <button type="submit"
                                    class="w-full min-h-[42px] py-2 rounded-xl bg-white/10 hover:bg-rose-600/80 border border-white/20 text-white font-bold text-xs transition flex items-center justify-center space-x-1 cursor-pointer">
                                    <span>Batalkan Pesanan ({{ $canCancelFull ? 'Refund 100%' : 'Potongan Denda 50%' }})</span>
                                </button>
                            </form>
                        </div>

                        <!-- Reschedule Modal Pop-up -->
                        @if ($canReschedule)
                            <div x-show="showRescheduleModal" 
                                 x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
                                 @click.self="showRescheduleModal = false">
                                <div class="bg-white rounded-2xl p-5 max-w-sm w-full text-slate-900 shadow-2xl border border-slate-200">
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                        <h4 class="text-sm font-extrabold text-slate-900">Jadwalkan Ulang Layanan</h4>
                                        <button type="button" @click="showRescheduleModal = false" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('pelanggan.orders.reschedule', $order) }}" class="mt-4 space-y-4">
                                        @csrf
                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-slate-700">Pilih Tanggal Baru</label>
                                            <input type="date" name="scheduled_date" required
                                                min="{{ now($order->region?->timezone ?? 'Asia/Jakarta')->format('Y-m-d') }}"
                                                max="{{ now($order->region?->timezone ?? 'Asia/Jakarta')->addDays(7)->format('Y-m-d') }}"
                                                value="{{ now($order->region?->timezone ?? 'Asia/Jakarta')->format('Y-m-d') }}"
                                                class="w-full rounded-xl border-slate-200 text-xs px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500">
                                        </div>

                                        <div class="space-y-1">
                                            <label class="block text-xs font-bold text-slate-700">Pilih Jam Mulai (08:00 - 21:00)</label>
                                            <input type="time" name="scheduled_time" required min="08:00" max="21:00" step="900"
                                                value="10:00"
                                                class="w-full rounded-xl border-slate-200 text-xs px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500">
                                            <p class="text-[10px] text-slate-500">Jadwal baru minimal 3 jam dari saat ini (antara 08:00 - 21:00).</p>
                                        </div>

                                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-900 leading-relaxed">
                                            💡 <strong>Ketentuan Reschedule:</strong> Reschedule tidak dikenakan biaya tambahan. Setelah disimpan, pesanan akan kembali dipublikasikan ke Bursa Terbuka agar dapat dikonfirmasi oleh mitra.
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 pt-2">
                                            <button type="button" @click="showRescheduleModal = false"
                                                class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                                Batal
                                            </button>
                                            <button type="submit"
                                                class="w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition">
                                                Simpan Jadwal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
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
                    @php
                        $completedTime = $order->service_completed_at ?? $order->updated_at;
                        $hoursSinceCompletion = $completedTime ? \Carbon\Carbon::now()->diffInHours($completedTime, false) : 0;
                        $canComplain = $hoursSinceCompletion <= 24;
                        $existingComplaint = $order->complaints()->first();
                        $isComplainant = $existingComplaint && $existingComplaint->complainant_id === auth()->id();
                    @endphp
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
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 text-center">
                            <div>
                                <template x-if="hasRated">
                                    <div class="space-y-1">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Ulasan Terkirim</span>
                                        <p class="text-xs font-bold text-slate-800">
                                            Terima kasih atas ulasan <strong x-text="selectedRating"></strong>/5 bintang Anda untuk terapis!
                                        </p>
                                    </div>
                                </template>
                                <template x-if="!hasRated">
                                    <div class="space-y-0.5">
                                        <p class="text-xs font-extrabold text-slate-900">Beri Penilaian untuk Terapis</p>
                                        <p class="text-[11px] text-slate-500">Pilih bintang sesuai kepuasan Anda terhadap layanan pemijatan:</p>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center justify-center space-x-2 py-0.5">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button" 
                                            @click="if (!hasRated) tempRating = star"
                                            :disabled="hasRated || isSubmittingRating"
                                            class="text-3xl transition transform hover:scale-110 active:scale-95 disabled:cursor-default cursor-pointer select-none">
                                        <span :class="star <= (hasRated ? selectedRating : tempRating) ? 'text-amber-400' : 'text-slate-300'">★</span>
                                    </button>
                                </template>
                            </div>

                            <template x-if="!hasRated">
                                <div class="space-y-2 pt-2 border-t border-slate-200/70">
                                    <p class="text-[11px] font-bold text-slate-700" x-show="tempRating > 0">
                                        <span x-text="ratingLabels[tempRating] || ''"></span> (<span x-text="tempRating"></span> dari 5 Bintang)
                                    </p>
                                    <button type="button" 
                                            @click="confirmSubmitRating()"
                                            :disabled="tempRating === 0 || isSubmittingRating"
                                            class="w-full min-h-[42px] py-2 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                        <span x-show="!isSubmittingRating">Kirim Penilaian</span>
                                        <span x-show="isSubmittingRating" class="flex items-center space-x-1">
                                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            <span>Menyimpan...</span>
                                        </span>
                                    </button>
                                </div>
                            </template>

                            <!-- Prompt jika rating <= 3 bintang (hanya ditampilkan jika belum pernah ada laporan sama sekali dan jendela aduan masih buka) -->
                            @if (! $existingComplaint && $canComplain)
                                <template x-if="hasRated && selectedRating <= 3">
                                    <div class="pt-2 border-t border-slate-200/80 text-[11px] text-slate-600 space-y-1.5">
                                        <p class="text-rose-700 font-semibold">Ada kendala atau ketidaknyamanan selama layanan?</p>
                                        <a href="{{ route('pelanggan.complaints.create', $order) }}"
                                           class="inline-flex items-center space-x-1 text-xs font-bold text-rose-700 hover:text-rose-800 underline">
                                            <span>Ajukan Laporan Aduan Layanan (Maks. 24 Jam)</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </template>
                            @endif
                        </div>

                        <!-- Opsi / Status Aduan Layanan (CUS-19) -->
                        @if ($existingComplaint)
                            @if ($isComplainant)
                                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between text-xs">
                                    <div class="truncate mr-2">
                                        <span class="text-[10px] text-amber-800 font-bold block">TIKET ADUAN: {{ $existingComplaint->complaint_code }} ({{ $existingComplaint->status->label() }})</span>
                                        <span class="text-slate-800 font-medium truncate block">{{ $existingComplaint->title }}</span>
                                    </div>
                                    <a href="{{ route('pelanggan.complaints.show', $existingComplaint) }}"
                                       class="px-2.5 py-1 rounded-lg bg-amber-200 text-amber-900 font-bold text-[11px] hover:bg-amber-300 transition shrink-0">
                                        Lihat Status
                                    </a>
                                </div>
                            @else
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 space-y-1">
                                    <div class="flex items-center space-x-1.5 font-bold text-slate-800">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Pesanan Dalam Peninjauan Laporan Kendala</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">
                                        Tiket: <strong>{{ $existingComplaint->complaint_code }}</strong> (Status: {{ $existingComplaint->status->label() }}). Laporan kendala terkait pesanan ini sedang ditangani oleh Admin Wilayah.
                                    </p>
                                </div>
                            @endif
                        @elseif ($canComplain)
                            <div class="pt-1">
                                <a href="{{ route('pelanggan.complaints.create', $order) }}"
                                   class="w-full min-h-[42px] py-2 px-3 rounded-xl border border-slate-200 hover:bg-rose-50 hover:border-rose-200 text-slate-700 hover:text-rose-700 font-bold text-xs transition flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Ada Kendala? Ajukan Aduan (Batas 24 Jam)</span>
                                </a>
                            </div>
                        @else
                            <div class="p-2 text-center text-[11px] text-slate-400 italic">
                                Jendela pengajuan aduan (24 jam pasca layanan) telah ditutup.
                            </div>
                        @endif
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
                        <span class="text-slate-500">Kode Pesanan:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $order->order_code }}</span>
                    </div>

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
                        <span class="font-bold text-slate-900 font-mono">{{ $isArchived ? $order->masked_phone : ($order->recipient_phone ?? '-') }}</span>
                    </div>
                </div>

                <!-- Alamat Kunjungan -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2 text-xs">
                    <h3 class="text-xs font-bold text-slate-900 pb-1 border-b border-slate-100">Alamat Kunjungan</h3>
                    <p class="text-slate-700 leading-relaxed pt-1">{{ $isArchived ? $order->masked_address : $order->service_address_text }}</p>
                </div>

                <!-- Catatan Medis & Persetujuan Risiko (CUS-07 & SYS-08) -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2 text-xs">
                    <h3 class="text-xs font-bold text-slate-900 pb-1 border-b border-slate-100">Kepatuhan Medis & Risiko</h3>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Catatan Medis:</span>
                        <span class="font-medium text-slate-800">
                            @if ($isArchived)
                                <span class="text-slate-400 italic">[Diarsipkan demi kerahasiaan medis]</span>
                            @else
                                {{ $order->medical_notes ? 'Tercatat (Terenkripsi)' : 'Tidak ada catatan khusus' }}
                            @endif
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
