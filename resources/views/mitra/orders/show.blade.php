<x-pwa-layout title="Detail Pesanan & Peta - Mitra" headerTitle="Detail Pesanan & Rute" :backUrl="$order->mitra_id ? route('mitra.orders.my-schedule') : route('mitra.orders.scheduled')" :fullBleed="true" :hideDock="true">
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
        div:where(.swal2-container) .swal-admin-theme .swal2-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        div:where(.swal2-container) .swal-admin-theme .swal2-confirm {
            margin: 0 !important;
            width: 100% !important;
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
    </style>

    <script>
        function confirmOrderAction(event, title, message, confirmBtnText = 'Ya, Lanjutkan', icon = 'question') {
            event.preventDefault();
            const form = event.currentTarget || event.target;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: message,
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: confirmBtnText,
                    reverseButtons: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-admin-theme',
                        icon: 'swal2-' + icon,
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
        window.confirmOrderAction = confirmOrderAction;
    </script>

    <div class="relative w-full h-full overflow-hidden select-none"
         x-data="{
            isExpanded: false,
            touchStartY: 0,
            touchCurrentY: 0,
            isDragging: false,

            mitraLat: {{ $mitraLat }},
            mitraLng: {{ $mitraLng }},
            customerLat: {{ $customerLat }},
            customerLng: {{ $customerLng }},
            orderStatus: '{{ $order->status->value }}',
            distanceText: '{{ $distanceKm ? $distanceKm . ' km' : 'Menghitung...' }}',
            distanceMeters: {{ $distanceKm ? round($distanceKm * 1000) : 0 }},
            etaText: '{{ $distanceKm ? '~' . max(3, round($distanceKm * 2.5)) . ' Menit' : '-' }}',
            serviceStartedAt: '{{ $order->service_started_at?->toIso8601String() }}',
            durationMinutes: {{ (int) $order->duration_minutes }},
            remainingSeconds: 0,
            remainingTimeFormatted: '{{ $order->duration_minutes }}:00',
            timerInterval: null,
            selectedRating: {{ $myRating?->score ?? 0 }},
            hasRated: {{ $myRating ? 'true' : 'false' }},
            isSubmittingRating: false,
            isLocating: false,
            isLoadingRoute: true,
            map: null,
            mitraMarker: null,
            customerMarker: null,
            routeLayer: null,
            googleMapsUrl: 'https://www.google.com/maps/dir/?api=1&origin={{ $mitraLat }},{{ $mitraLng }}&destination={{ $customerLat }},{{ $customerLng }}',

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

            confirmArrival(event) {
                event.preventDefault();
                const form = event.currentTarget || event.target;

                let latInput = form.querySelector('input[name="latitude"]');
                if (!latInput) {
                    latInput = document.createElement('input');
                    latInput.type = 'hidden';
                    latInput.name = 'latitude';
                    form.appendChild(latInput);
                }
                latInput.value = this.mitraLat;

                let lngInput = form.querySelector('input[name="longitude"]');
                if (!lngInput) {
                    lngInput = document.createElement('input');
                    lngInput.type = 'hidden';
                    lngInput.name = 'longitude';
                    form.appendChild(lngInput);
                }
                lngInput.value = this.mitraLng;

                const meters = this.distanceMeters || 0;
                if (meters > 50) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Verifikasi Lokasi Tiba',
                            html: `Jarak Anda terdeteksi <b>${Math.round(meters)} meter</b> dari titik pelanggan.<br><span class="text-xs text-slate-500 mt-2 block">Apakah Anda sudah benar-benar berada di depan alamat pelanggan?</span>`,
                            showCancelButton: true,
                            cancelButtonText: 'Periksa Peta',
                            confirmButtonText: 'Ya, Saya Sudah di Lokasi',
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
                                let forceInput = form.querySelector('input[name="force_arrived"]');
                                if (!forceInput) {
                                    forceInput = document.createElement('input');
                                    forceInput.type = 'hidden';
                                    forceInput.name = 'force_arrived';
                                    form.appendChild(forceInput);
                                }
                                forceInput.value = '1';
                                form.submit();
                            }
                        });
                    } else {
                        if (confirm(`Jarak Anda terdeteksi ${Math.round(meters)} meter. Konfirmasi Anda sudah di lokasi pelanggan?`)) {
                            let forceInput = form.querySelector('input[name="force_arrived"]');
                            if (!forceInput) {
                                forceInput = document.createElement('input');
                                forceInput.type = 'hidden';
                                forceInput.name = 'force_arrived';
                                form.appendChild(forceInput);
                            }
                            forceInput.value = '1';
                            form.submit();
                        }
                    }
                } else {
                    confirmOrderAction(event, 'Konfirmasi Tiba?', 'Konfirmasi bahwa Anda sudah tiba di lokasi pelanggan?', 'Ya, Sudah Tiba', 'question');
                }
            },

            confirmCompleteService(event) {
                event.preventDefault();
                const form = event.currentTarget || event.target;

                if (this.remainingSeconds > 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Selesaikan Lebih Awal?',
                            html: `Durasi layanan paket ${this.durationMinutes} menit masih tersisa <b>${this.remainingTimeFormatted}</b>.<br><span class="text-xs text-slate-500 mt-2 block">Apakah Anda yakin ingin menyelesaikan sesi pemijatan sekarang?</span>`,
                            showCancelButton: true,
                            cancelButtonText: 'Lanjutkan Layanan',
                            confirmButtonText: 'Ya, Selesaikan',
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
                        if (confirm(`Durasi masih tersisa ${this.remainingTimeFormatted}. Yakin ingin menyelesaikan layanan lebih awal?`)) {
                            form.submit();
                        }
                    }
                } else {
                    confirmOrderAction(event, 'Selesaikan Layanan?', 'Sesi pemijatan telah selesai dilaksanakan secara lengkap?', 'Ya, Selesaikan', 'question');
                }
            },

            async submitRating(score) {
                if (this.hasRated || this.isSubmittingRating) return;
                this.selectedRating = score;
                this.isSubmittingRating = true;
                try {
                    const res = await fetch('{{ route('mitra.orders.rating', $order) }}', {
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
                                title: 'Penilaian Terkirim',
                                text: 'Terima kasih telah memberikan penilaian pelanggan.',
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
                this.map = L.map('mitra-order-map', {
                    zoomControl: false
                }).setView([this.customerLat, this.customerLng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                // Marker Pelanggan (Rumah)
                const customerIcon = L.divIcon({
                    className: 'custom-customer-pin',
                    html: `<div class='w-9 h-9 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-xl border-2 border-white text-base animate-bounce'>🏠</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                });

                this.customerMarker = L.marker([this.customerLat, this.customerLng], { icon: customerIcon }).addTo(this.map);
                this.customerMarker.bindPopup(`<strong>Lokasi Pelanggan</strong><br>{{ addslashes($order->recipient_name) }}<br><span style='font-size:11px;'>{{ addslashes($order->service_address_text) }}</span>`).openPopup();

                // Marker Terapis (Pijat)
                const mitraIcon = L.divIcon({
                    className: 'custom-mitra-pin',
                    html: `<div class='w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center shadow-xl border-2 border-white text-base'>💆</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                });

                this.mitraMarker = L.marker([this.mitraLat, this.mitraLng], { icon: mitraIcon }).addTo(this.map);
                this.mitraMarker.bindPopup(`<strong>Posisi Anda (Terapis)</strong>`);

                // Gambar rute mengikuti jalan nyata
                this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);

                // Jalankan timer hitung mundur jika sesi sedang berlangsung
                this.initCountdown();

                // Deteksi GPS Browser Realtime & Live Broadcast ke Server
                if (navigator.geolocation) {
                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition((pos) => {
                        this.mitraLat = pos.coords.latitude;
                        this.mitraLng = pos.coords.longitude;
                        this.googleMapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${this.mitraLat},${this.mitraLng}&destination=${this.customerLat},${this.customerLng}`;
                        if (this.mitraMarker) {
                            this.mitraMarker.setLatLng([this.mitraLat, this.mitraLng]);
                        }
                        this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);
                        this.sendLocationToServer(this.mitraLat, this.mitraLng);
                        this.isLocating = false;
                    }, (err) => {
                        console.log('GPS error:', err);
                        this.isLocating = false;
                    }, { enableHighAccuracy: true, timeout: 8000 });

                    // Live Tracking kontinyu jika pesanan sedang aktif (menuju lokasi / tiba)
                    if (['dijadwalkan', 'menuju_lokasi', 'tiba', 'sedang_berlangsung'].includes(this.orderStatus)) {
                        navigator.geolocation.watchPosition((pos) => {
                            this.mitraLat = pos.coords.latitude;
                            this.mitraLng = pos.coords.longitude;
                            if (this.mitraMarker) {
                                this.mitraMarker.setLatLng([this.mitraLat, this.mitraLng]);
                            }
                            this.sendLocationToServer(this.mitraLat, this.mitraLng);
                        }, (err) => {}, { enableHighAccuracy: true, maximumAge: 10000 });
                    }
                }
            },

            sendLocationToServer(lat, lng) {
                fetch('{{ route('mitra.location.ping') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                }).catch(() => {});
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
                        this.distanceMeters = Math.round(route.distance);
                        this.distanceText = distKm + ' km (Jalur Jalan)';
                        const etaMin = Math.max(3, Math.round(route.duration / 60));
                        this.etaText = `~${etaMin} Menit`;
                        this.isLoadingRoute = false;
                        return;
                    }
                } catch (e) {
                    console.warn('OSRM fallback to straight line:', e);
                }

                // Fallback
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
                // Beri padding bawah yang lega agar rute berada di area atas peta (tidak tertutup bottom sheet)
                this.map.fitBounds(bounds, {
                    paddingTop: 50,
                    paddingBottom: 240,
                    paddingLeft: 30,
                    paddingRight: 30
                });
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
        <div id="mitra-order-map" class="w-full h-full absolute inset-0 z-0"></div>

        <!-- FLOATING TOP CONTROLS OVER MAP -->
        <div class="absolute top-3 left-3 right-3 z-10 flex items-center justify-between pointer-events-none">
            <!-- Status & Jarak Chip -->
            <div class="pointer-events-auto bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full shadow-md border border-slate-200/80 flex items-center space-x-2 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-extrabold text-slate-800 font-mono text-[11px]" x-text="distanceText"></span>
                <span class="text-slate-300">•</span>
                <span class="font-bold text-emerald-800 text-[11px]" x-text="etaText"></span>
            </div>

            <!-- Floating Actions: Recenter & Navigasi External -->
            <div class="pointer-events-auto flex items-center space-x-2">
                <button type="button" @click="fitRouteBounds()" 
                    title="Pusatkan Peta"
                    class="w-9 h-9 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-slate-200/80 text-slate-700 hover:text-emerald-700 active:scale-95 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                    </svg>
                </button>

                <a :href="googleMapsUrl" target="_blank" 
                   title="Buka Rute Navigasi di Google Maps"
                   class="h-9 px-3 rounded-full bg-slate-900/95 backdrop-blur-md shadow-md text-white font-bold text-[11px] flex items-center space-x-1.5 hover:bg-slate-800 active:scale-95 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span>Buka Rute Navigasi di Google Maps</span>
                </a>
            </div>
        </div>

        @if ($isAssigned)
            <x-order-chat-drawer
                :order="$order"
                :fetchUrl="route('mitra.orders.chat.messages', $order)"
                :sendUrl="route('mitra.orders.chat.send', $order)"
                :isOrderActive="in_array($order->status, [\App\Enums\OrderStatus::Dijadwalkan, \App\Enums\OrderStatus::MenujuLokasi, \App\Enums\OrderStatus::Tiba, \App\Enums\OrderStatus::SedangBerlangsung])"
                otherPartyName="Pelanggan" />
        @endif

        <!-- FLOATING FLASH MESSAGES -->
        @if (session('status'))
            <div class="absolute top-14 left-3 right-3 z-20 p-3 rounded-xl bg-emerald-600/95 text-white text-xs font-semibold shadow-lg backdrop-blur-md flex items-center space-x-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span class="flex-1">{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="absolute top-14 left-3 right-3 z-20 p-3 rounded-xl bg-rose-600/95 text-white text-xs font-semibold shadow-lg backdrop-blur-md flex items-center space-x-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="flex-1">{{ session('error') }}</span>
            </div>
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
                            {{ $order->status->label() }}
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

            <!-- MODE 1: PEEK / COLLAPSED VIEW (Cepat & Ringkas saat Menyetir/Jalan) -->
            <div x-show="!isExpanded" class="p-4 flex-1 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-slate-900 text-sm truncate">{{ $order->recipient_name }}</span>
                        <span class="font-mono font-bold text-emerald-800 text-xs">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-600 line-clamp-1">
                        📍 {{ $order->service_address_text }}
                    </p>
                    <div class="text-[11px] text-slate-500 font-medium pt-0.5 flex items-center justify-between">
                        <span>Jarak ke Pelanggan: <strong class="text-slate-800 font-mono" x-text="distanceText"></strong></span>
                        <span class="text-emerald-700 font-bold" x-text="etaText"></span>
                    </div>
                </div>

                <!-- Primary Action Button in Peek Mode -->
                <div class="pt-2">
                    @if ($isAssigned)
                        @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  onsubmit="return confirmOrderAction(event, 'Mulai Perjalanan?', 'Mulai perjalanan menuju lokasi pelanggan sekarang?', 'Ya, Berangkat', 'question');">
                                @csrf
                                <input type="hidden" name="status" value="menuju_lokasi">
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <span>Mulai Berangkat (Menuju Lokasi)</span>
                                </button>
                            </form>
                        @elseif ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  onsubmit="return confirmArrival(event);">
                                @csrf
                                <input type="hidden" name="status" value="tiba">
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-2.5 px-4 bg-blue-700 hover:bg-blue-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <span>Saya Sudah Tiba di Lokasi</span>
                                </button>
                            </form>
                        @elseif ($order->status === \App\Enums\OrderStatus::Tiba)
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  onsubmit="return confirmOrderAction(event, 'Mulai Layanan?', 'Mulai sesi pemijatan sekarang?', 'Mulai Sesi', 'question');">
                                @csrf
                                <input type="hidden" name="status" value="sedang_berlangsung">
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    <span>Mulai Sesi Layanan Pijat</span>
                                </button>
                            </form>
                        @elseif ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                            <div class="space-y-2">
                                <div class="p-2 bg-emerald-50 rounded-xl border border-emerald-200/90 flex items-center justify-between text-xs">
                                    <span class="text-emerald-900 font-bold flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Sisa Waktu Layanan:</span>
                                    </span>
                                    <span class="font-extrabold text-emerald-800 font-mono text-sm" x-text="remainingTimeFormatted">
                                        {{ $order->duration_minutes }}:00
                                    </span>
                                </div>
                                <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                      onsubmit="return confirmCompleteService(event);">
                                    @csrf
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" 
                                        class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Selesaikan Layanan (Tugas Selesai)</span>
                                    </button>
                                </form>
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::Selesai)
                            <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200/90 text-center space-y-2">
                                <p class="text-xs font-bold text-emerald-950">
                                    <template x-if="hasRated">
                                        <span>✓ Anda telah memberi nilai <strong x-text="selectedRating"></strong>/5 bintang untuk pelanggan.</span>
                                    </template>
                                    <template x-if="!hasRated">
                                        <span>Beri Penilaian untuk Pelanggan:</span>
                                    </template>
                                </p>
                                <div class="flex items-center justify-center space-x-2 py-0.5">
                                    <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                        <button type="button" @click="submitRating(star)"
                                                :disabled="hasRated || isSubmittingRating"
                                                class="text-2xl transition transform hover:scale-110 active:scale-95 disabled:cursor-default cursor-pointer">
                                            <span :class="star <= selectedRating ? 'text-amber-400' : 'text-slate-300'">★</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    @else
                        @if ($distanceKm !== null && $distanceKm > ($maxRadiusKm ?? 10.0))
                            <div class="p-2 bg-amber-50 rounded-xl border border-amber-200 text-center text-[11px] text-amber-950 font-bold">
                                ⚠ Di Luar Radius Maksimal Layanan (10 km)
                            </div>
                        @else
                            <form action="{{ route('mitra.orders.take', $order) }}" method="POST"
                                  onsubmit="return confirmOrderAction(event, 'Ambil Jadwal Pesanan?', 'Apakah Anda bersedia mengambil dan melayani pesanan ini sesuai jadwal dan lokasi yang tertera?', 'Ya, Ambil Jadwal', 'question');">
                                @csrf
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Konfirmasi Ambil Jadwal Pesanan Ini</span>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            <!-- MODE 2: EXPANDED VIEW (Scrollable Rincian Lengkap & Data Pemesan) -->
            <div x-show="isExpanded" class="flex-1 overflow-y-auto px-4 pb-6 pt-3 space-y-4 overscroll-contain">

                @if ($order->mitra_id === null)
                    <!-- Banner Tinjau Pesanan Sebelum Konfirmasi -->
                    <div class="p-3.5 rounded-2xl bg-sky-50 border border-sky-200 text-sky-950 text-xs shadow-xs space-y-1">
                        <p class="font-bold flex items-center space-x-1.5 text-sky-900">
                            <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Pesanan Bursa Jadwal Terbuka</span>
                        </p>
                        <p class="text-[11px] text-sky-800 leading-relaxed">
                            Silakan tinjau rincian lokasi, estimasi jarak jalan, dan jadwal di bawah ini. Jika Anda bersedia melayani, klik tombol <strong>Konfirmasi Ambil Jadwal</strong> di bagian bawah.
                        </p>
                    </div>
                @endif

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

                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-2">
                        <span class="{{ $currentStep >= 1 ? 'text-emerald-700 font-extrabold' : '' }}">Dijadwalkan</span>
                        <span class="{{ $currentStep >= 2 ? 'text-emerald-700 font-extrabold' : '' }}">Menuju Lokasi</span>
                        <span class="{{ $currentStep >= 3 ? 'text-emerald-700 font-extrabold' : '' }}">Tiba</span>
                        <span class="{{ $currentStep >= 4 ? 'text-emerald-700 font-extrabold' : '' }}">Layanan</span>
                        <span class="{{ $currentStep >= 5 ? 'text-emerald-700 font-extrabold' : '' }}">Selesai</span>
                    </div>
                    <div class="w-full bg-slate-200/80 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" 
                             style="width: {{ min(100, max(20, ($currentStep / 5) * 100)) }}%"></div>
                    </div>
                </div>

                <!-- Informasi Jarak & Rute Navigasi -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Jarak ke Pelanggan & Estimasi Waktu</span>
                    </h3>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 font-semibold block">Jarak ke Pelanggan</span>
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

                    <!-- Tombol Navigasi Google Maps -->
                    <a :href="googleMapsUrl" target="_blank"
                       class="w-full min-h-[42px] py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span>Buka Rute Navigasi di Google Maps</span>
                    </a>
                </div>

                <!-- Informasi Penerima Layanan & Alamat Lengkap -->
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

                <!-- Catatan Medis Penerima (Transparan Bagi Mitra Setelah Diambil - MIT-07) -->
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

                <!-- Detail Layanan & Finansial -->
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
                            {{ $order->local_scheduled_start_time ? $order->local_scheduled_start_time->locale('id')->translatedFormat('d M Y, H:i') . ' ' . $order->timezone_abbr : 'Segera (Instan)' }}
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

                <!-- Tombol Pembaruan Status Layanan / Tombol Bursa -->
                @if ($isAssigned)
                    <div class="pt-1 space-y-2">
                        @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  onsubmit="return confirmOrderAction(event, 'Mulai Perjalanan?', 'Mulai perjalanan menuju lokasi pelanggan sekarang?', 'Ya, Berangkat', 'question');">
                                @csrf
                                <input type="hidden" name="status" value="menuju_lokasi">
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <span>Mulai Berangkat (Menuju Lokasi)</span>
                                </button>
                            </form>
                        @elseif ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  onsubmit="return confirmArrival(event);">
                                @csrf
                                <input type="hidden" name="status" value="tiba">
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-3 px-4 bg-blue-700 hover:bg-blue-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <span>Saya Sudah Tiba di Lokasi</span>
                                </button>
                            </form>
                        @elseif ($order->status === \App\Enums\OrderStatus::Tiba)
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  onsubmit="return confirmOrderAction(event, 'Mulai Layanan?', 'Mulai sesi pemijatan sekarang?', 'Mulai Sesi', 'question');">
                                @csrf
                                <input type="hidden" name="status" value="sedang_berlangsung">
                                <button type="submit" 
                                    class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    <span>Mulai Sesi Layanan Pijat</span>
                                </button>
                            </form>
                        @elseif ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                            <div class="space-y-2">
                                <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200/90 flex items-center justify-between text-xs">
                                    <span class="text-emerald-900 font-bold flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Sisa Waktu Layanan:</span>
                                    </span>
                                    <span class="font-extrabold text-emerald-800 font-mono text-sm" x-text="remainingTimeFormatted">
                                        {{ $order->duration_minutes }}:00
                                    </span>
                                </div>
                                <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                      onsubmit="return confirmCompleteService(event);">
                                    @csrf
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" 
                                        class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Selesaikan Layanan (Tugas Selesai)</span>
                                    </button>
                                </form>
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::Selesai)
                            <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 text-center text-xs text-emerald-950 font-bold space-y-2">
                                <p class="text-sm">✓ Layanan Selesai</p>
                                <p class="text-[11px] text-emerald-800 font-normal">Pendapatan telah dikreditkan ke dompet kemitraan Anda.</p>
                                <div class="pt-2 border-t border-emerald-200/70 space-y-1">
                                    <p class="text-[11.5px] font-bold text-emerald-950">
                                        <template x-if="hasRated">
                                            <span>✓ Anda telah memberikan nilai <strong x-text="selectedRating"></strong>/5 bintang untuk pelanggan.</span>
                                        </template>
                                        <template x-if="!hasRated">
                                            <span>Beri Penilaian untuk Pelanggan:</span>
                                        </template>
                                    </p>
                                    <div class="flex items-center justify-center space-x-2 py-0.5">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" @click="submitRating(star)"
                                                    :disabled="hasRated || isSubmittingRating"
                                                    class="text-2xl transition transform hover:scale-110 active:scale-95 disabled:cursor-default cursor-pointer">
                                                <span :class="star <= selectedRating ? 'text-amber-400' : 'text-slate-300'">★</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
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
                        <form action="{{ route('mitra.orders.take', $order) }}" method="POST" class="pt-1"
                              onsubmit="return confirmOrderAction(event, 'Ambil Jadwal Pesanan?', 'Apakah Anda bersedia mengambil dan melayani pesanan ini sesuai jadwal dan lokasi yang tertera?', 'Ya, Ambil Jadwal', 'question');">
                            @csrf
                            <div class="space-y-2">
                                <button type="submit" 
                                    class="w-full min-h-[48px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Konfirmasi Ambil Jadwal Pesanan Ini</span>
                                </button>

                                <a href="{{ route('mitra.orders.scheduled') }}"
                                   class="w-full min-h-[44px] py-2.5 px-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                    <span>Kembali ke Bursa Jadwal</span>
                                </a>
                            </div>
                        </form>
                    @endif
                @endif

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
