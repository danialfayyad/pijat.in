@php
    $showMap = ! $isArchived && in_array($order->status, [
        \App\Enums\OrderStatus::MenujuLokasi,
        \App\Enums\OrderStatus::Tiba,
        \App\Enums\OrderStatus::SedangBerlangsung,
    ]);
    $existingComplaint = $isAssigned ? $order->complaints()->first() : null;
    $isMyIncident = $existingComplaint && $existingComplaint->complainant_id === auth()->id();
    $cleanServiceName = trim(preg_replace('/\s*\(\d+\s*menit\)/i', '', (string) $order->service_name));
@endphp
<x-pwa-layout :title="$showMap ? 'Pelacakan Pesanan & Rute - Mitra' : 'Rincian Pesanan - Mitra'" :fullBleed="true" :hideDock="true" :hideHeader="true">
    @if ($showMap)
        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endif

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
    </style>

    <script>
        function confirmOrderAction(event, title, message, confirmBtnText = 'Ya, Lanjutkan', icon = 'question') {
            event.preventDefault();
            const form = event.currentTarget || event.target;

            if (typeof Swal !== 'undefined') {
                const glyph = icon === 'warning' ? '!' : (icon === 'success' ? '✓' : (icon === 'error' ? '✕' : '?'));
                Swal.fire({
                    icon: icon,
                    iconHtml: glyph,
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

        function mitraOrderTracker() {
            return {
                showMap: @json($showMap),
                isExpanded: !@json($showMap) || ['selesai', 'dibatalkan'].includes('{{ $order->status->value }}'),
                touchStartY: 0,
                touchCurrentY: 0,
                isDragging: false,
                showSpecialCancelModal: false,
                showCancelModal: false,

                mitraLat: {{ $mitraLat ?? 'null' }},
                mitraLng: {{ $mitraLng ?? 'null' }},
                customerLat: {{ $customerLat ?? 'null' }},
                customerLng: {{ $customerLng ?? 'null' }},
                isArchived: {{ $isArchived ? 'true' : 'false' }},
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
                isLocating: false,
                isLoadingRoute: true,
                map: null,
                mitraMarker: null,
                customerMarker: null,
                routeLayer: null,
                googleMapsUrl: @js($mitraLat && $customerLat ? "https://www.google.com/maps/dir/?api=1&origin={$mitraLat},{$mitraLng}&destination={$customerLat},{$customerLng}" : ''),

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

                    let meters = this.distanceMeters || 0;
                    if (this.mitraLat && this.mitraLng && this.customerLat && this.customerLng) {
                        meters = Math.round(this.calcHaversine(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng) * 1000);
                    }

                    if (meters > 50) {
                        const distFormatted = meters >= 1000 ? (meters / 1000).toFixed(1) + ' km' : Math.round(meters) + ' meter';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                iconHtml: '!',
                                title: 'Belum Sampai di Lokasi',
                                html: `Posisi Anda terdeteksi masih berjarak <b>${distFormatted}</b> dari alamat pelanggan.<br><span class="text-xs text-slate-500 mt-2 block">Anda belum dapat mengonfirmasi tiba karena batas maksimal radius kedatangan adalah 50 meter. Silakan mendekat ke lokasi pelanggan terlebih dahulu.</span>`,
                                confirmButtonText: 'Oke, Mengerti',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-warning',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        } else {
                            alert(`Anda belum sampai di lokasi pelanggan. Jarak terdeteksi ${distFormatted} (maksimal radius 50 meter). Silakan mendekat ke lokasi pelanggan terlebih dahulu.`);
                        }
                        return false;
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
                                iconHtml: '!',
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

                confirmSubmitRating() {
                    if (this.hasRated || this.isSubmittingRating || this.tempRating === 0) return;
                    const score = this.tempRating;
                    const label = this.ratingLabels[score] || `${score} Bintang`;

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'question',
                            iconHtml: '?',
                            title: 'Kirim Penilaian?',
                            text: `Apakah Anda yakin ingin memberikan nilai ${score} bintang (${label}) untuk pelanggan ini?`,
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            confirmButtonText: 'Ya, Kirim Penilaian',
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
                        if (confirm(`Apakah Anda yakin ingin memberikan nilai ${score} bintang (${label}) untuk pelanggan ini?`)) {
                            this.submitRating(score);
                        }
                    }
                },

                async submitRating(score) {
                    if (this.hasRated || this.isSubmittingRating) return;
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
                            this.selectedRating = score;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    iconHtml: '✓',
                                    title: 'Penilaian Terkirim',
                                    text: 'Terima kasih telah memberikan ulasan penilaian kepada pelanggan.',
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

                initPage() {
                    window.confirmArrival = (e) => this.confirmArrival(e);
                    window.confirmCompleteService = (e) => this.confirmCompleteService(e);

                    this.initCountdown();

                    window.addEventListener('pwa-new-notification', (e) => {
                        const notif = e.detail;
                        if (notif && (notif.order_id == {{ $order->id }} || notif.data?.order_id == {{ $order->id }} || notif.action_url?.includes('/orders/{{ $order->id }}'))) {
                            window.location.reload();
                        }
                    });

                    @if (session('location_warning'))
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                iconHtml: '!',
                                title: 'Anda Masih Jauh dari Lokasi',
                                html: @js(session('location_warning')['message'] ?? 'Jarak Anda masih melebihi radius 50 meter.') + '<br><span class="text-xs text-slate-500 mt-2 block">Gunakan tombol konfirmasi tiba manual jika GPS meleset atau Anda sudah berada di lokasi pelanggan.</span>',
                                confirmButtonText: 'Saya Mengerti',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-warning',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        }
                    @endif

                    @if ($showMap)
                        this.initMap();
                    @endif
                },

                initMap() {
                    if (this.isArchived || !this.customerLat || !document.getElementById('mitra-order-map')) {
                        return;
                    }

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
                    this.customerMarker.bindPopup(@js("<strong>Lokasi Pelanggan</strong><br>" . e($order->recipient_name) . "<br><span style='font-size:11px;'>" . e($isArchived ? $order->masked_address : $order->service_address_text) . "</span>")).openPopup();

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

                    // Live GPS Tracking: hanya aktif saat mitra dalam perjalanan (menuju lokasi)
                    const isTerminal = ['selesai', 'dibatalkan'].includes(this.orderStatus);
                    const isTraveling = this.orderStatus === 'menuju_lokasi';

                    if (!isTerminal && navigator.geolocation) {
                        if (isTraveling) {
                            this.isLocating = true;
                            navigator.geolocation.getCurrentPosition((pos) => {
                                this.updateMitraPosition(pos.coords.latitude, pos.coords.longitude, true);
                                this.isLocating = false;
                            }, (err) => {
                                console.log('GPS error:', err);
                                this.isLocating = false;
                            }, { enableHighAccuracy: true, timeout: 8000 });

                            navigator.geolocation.watchPosition((pos) => {
                                this.updateMitraPosition(pos.coords.latitude, pos.coords.longitude, false);
                            }, () => {}, { enableHighAccuracy: true, maximumAge: 10000 });
                        } else if (navigator.permissions && navigator.permissions.query) {
                            // Untuk status terjadwal/tiba, hanya sinkronkan jika izin sudah granted (tanpa memunculkan pop-up browser paksa)
                            navigator.permissions.query({ name: 'geolocation' }).then((result) => {
                                if (result.state === 'granted') {
                                    navigator.geolocation.getCurrentPosition((pos) => {
                                        this.updateMitraPosition(pos.coords.latitude, pos.coords.longitude, false);
                                    }, () => {}, { timeout: 5000, maximumAge: 60000 });
                                }
                            }).catch(() => {});
                        }
                    }
                },

                updateMitraPosition(lat, lng, fetchRoute = true) {
                    this.mitraLat = lat;
                    this.mitraLng = lng;
                    this.googleMapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${this.mitraLat},${this.mitraLng}&destination=${this.customerLat},${this.customerLng}`;
                    if (this.mitraMarker) {
                        this.mitraMarker.setLatLng([this.mitraLat, this.mitraLng]);
                    }
                    if (fetchRoute) {
                        this.fetchRoadRoute(this.mitraLat, this.mitraLng, this.customerLat, this.customerLng);
                    }
                    this.sendLocationToServer(this.mitraLat, this.mitraLng);
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
                    this.distanceMeters = Math.round(dist * 1000);
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
            };
        }
        window.mitraOrderTracker = mitraOrderTracker;
    </script>

    <div class="relative w-full h-full overflow-hidden select-none"
         x-data="mitraOrderTracker()"
         x-init="initPage()">

        @if ($showMap)
            <!-- BACKGROUND FULL MAP (Leaflet Canvas) -->
            <div id="mitra-order-map" class="w-full h-full absolute inset-0 z-0"></div>

            <!-- FLOATING TOP NAVIGATION OVER MAP -->
            <div class="absolute top-4 left-4 right-4 z-20 pointer-events-none flex items-center space-x-2.5">
                <a href="{{ $order->mitra_id ? route('mitra.orders.my-schedule') : route('mitra.orders.scheduled') }}"
                   class="pointer-events-auto w-11 h-11 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-slate-200/80 text-slate-700 hover:text-emerald-700 hover:bg-emerald-50/80 flex items-center justify-center shrink-0 transition-all active:scale-95 cursor-pointer"
                   aria-label="Kembali"
                   title="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div class="pointer-events-auto flex-1 min-w-0 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-full shadow-md border border-slate-200/80 flex items-center justify-between transition-all">
                    <div class="flex items-center space-x-2.5 truncate">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
                        <div class="truncate">
                            <span class="text-xs font-bold text-slate-900 block truncate">Rute Pelanggan</span>
                            <span class="text-[11px] text-slate-500 font-medium block truncate">
                                Jarak: <strong class="text-slate-800 font-semibold" x-text="distanceText"></strong> • <span class="text-emerald-700 font-bold" x-text="etaText"></span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 shrink-0 pl-1.5">
                        <button type="button" @click="fitRouteBounds()" title="Pusatkan Peta"
                            class="w-7.5 h-7.5 rounded-full bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 flex items-center justify-center transition active:scale-95 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                            </svg>
                        </button>
                        <button type="button" @click="window.location.reload()" title="Segarkan"
                            class="w-7.5 h-7.5 rounded-full bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 flex items-center justify-center transition active:scale-95 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Sheet Container -->
            <div class="absolute bottom-0 inset-x-0 z-30 bg-white rounded-t-3xl shadow-2xl border-t border-slate-200/90 flex flex-col transition-all duration-300 ease-out"
                 :class="isExpanded ? 'h-[85vh] max-h-[85vh]' : 'h-auto max-h-[60vh]'">

                <!-- Header & Drag Zone -->
                <div class="pt-3 pb-2.5 px-4 cursor-pointer select-none bg-white rounded-t-3xl border-b border-slate-100 flex flex-col items-center"
                     @touchstart.passive="handleTouchStart($event)"
                     @touchmove.passive="handleTouchMove($event)"
                     @touchend="handleTouchEnd()"
                     @click="toggleSheet()">
                    <!-- Perfectly Centered Drag Indicator Pill with Comfortable Spacing -->
                    <div class="w-12 h-1.5 bg-slate-300 rounded-full hover:bg-slate-400 transition mb-2"></div>

                    <div class="w-full flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">Detail Pesanan</span>
                        <button type="button" class="flex items-center space-x-1 font-semibold text-emerald-700 hover:text-emerald-800 transition cursor-pointer">
                            <span x-text="isExpanded ? 'Tutup' : 'Rincian Lengkap'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="isExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>

            <!-- MODE 1: PEEK / COMPACT VIEW -->
            <div x-show="!isExpanded" class="p-4 pb-6 sm:pb-7 space-y-3">
                @if (session('location_warning'))
                    <div class="p-2.5 bg-amber-50 border border-amber-200/90 rounded-xl text-amber-900 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-amber-800 text-xs leading-relaxed flex-1">{{ session('location_warning')['message'] ?? 'Jarak Anda masih melebihi radius tiba 50 meter.' }}</p>
                    </div>
                @elseif (session('error'))
                    <div class="p-2.5 bg-rose-50 border border-rose-200/90 rounded-xl text-rose-900 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-medium text-xs">{{ session('error') }}</p>
                    </div>
                @elseif (session('status'))
                    <div class="p-2.5 bg-emerald-50 border border-emerald-200/90 rounded-xl text-emerald-900 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <p class="font-medium text-xs">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 truncate">{{ $order->recipient_name }}</h2>
                            @php
                                $peekCustRating = $order->customer?->customerRating();
                                $peekCustCount = $order->customer?->customerRatingsCount() ?? 0;
                            @endphp
                            @if ($peekCustRating !== null)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-900 text-[10px] font-semibold shrink-0" title="{{ $peekCustCount }} ulasan mitra">
                                    <svg class="w-2.5 h-2.5 text-amber-500 fill-current shrink-0" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>{{ number_format($peekCustRating, 1) }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-medium shrink-0">
                                    <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                    <span>Baru</span>
                                </span>
                            @endif
                            @if (! $isArchived && $order->recipient_phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $order->recipient_phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    }
                                @endphp
                                <div class="inline-flex items-center gap-1.5 shrink-0" @click.stop>
                                    <a href="tel:{{ $order->recipient_phone }}" title="Telepon Pelanggan"
                                       class="w-6.5 h-6.5 rounded-full border border-slate-200 hover:bg-slate-50 text-slate-600 flex items-center justify-center transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </a>
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Halo ' . $order->recipient_name . ', saya terapis dari Pijat.In untuk pesanan #' . $order->order_code . '.') }}" 
                                       target="_blank" title="WhatsApp Pelanggan"
                                       class="w-6.5 h-6.5 rounded-full border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center transition">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.587 1.771.889 2.796.889 3.182 0 5.768-2.586 5.768-5.766.001-3.18-2.585-5.766-5.768-5.766zm9.969 5.766c0 5.508-4.462 9.97-9.969 9.97-1.719 0-3.364-.44-4.811-1.222l-5.22 1.369 1.393-5.089c-.879-1.503-1.362-3.238-1.362-5.028 0-5.508 4.462-9.97 9.969-9.97 5.507 0 9.969 4.462 9.969 9.97z"/>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $cleanServiceName }} <span class="text-slate-300">•</span> {{ $order->duration_minutes }} menit</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 block font-medium">Pendapatan Bersih</span>
                        <span class="text-base font-bold text-slate-900">Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 text-xs text-slate-600 pt-0.5">
                    <div class="flex items-center space-x-1.5 min-w-0">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="truncate">{{ $isArchived ? $order->masked_address : $order->service_address_text }}</p>
                    </div>
                    @if (! $isArchived)
                        <a :href="googleMapsUrl" target="_blank" @click.stop
                           class="shrink-0 text-emerald-700 hover:text-emerald-800 font-semibold inline-flex items-center gap-1 text-[11px]">
                            <span>Buka Maps</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif
                </div>

                <!-- Primary & Secondary Actions in Peek Mode -->
                <div class="pt-0.5 space-y-2">
                    @if ($isAssigned)
                        @php
                            $existingComplaint = $order->complaints()->first();
                            $isMyIncident = $existingComplaint && $existingComplaint->complainant_id === auth()->id();
                        @endphp

                        @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
                            @php
                                $canDepartNow = $order->canPartnerDepartNow(45);
                                $earliestTime = $order->earliestPartnerDepartureTime(45);
                            @endphp
                            @if ($canDepartNow)
                                <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                      onsubmit="return confirmOrderAction(event, 'Mulai Perjalanan?', 'Mulai perjalanan menuju lokasi pelanggan sekarang?', 'Ya, Berangkat', 'question');">
                                    @csrf
                                    <input type="hidden" name="status" value="menuju_lokasi">
                                    <button type="submit" 
                                        class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        <span>Mulai Berangkat (Menuju Lokasi)</span>
                                    </button>
                                </form>
                            @else
                                <div class="space-y-1.5">
                                    <div class="py-2 px-3 bg-amber-50 rounded-xl border border-amber-200/90 text-center">
                                        <p class="text-xs font-semibold text-amber-900">
                                            Berangkat aktif pukul {{ $earliestTime?->format('H:i') }} WIB
                                        </p>
                                        <p class="text-[11px] text-amber-700 mt-0.5">
                                            Jadwal layanan: {{ $order->scheduled_start_time?->translatedFormat('H:i') }} WIB
                                        </p>
                                    </div>
                                    <button type="button" disabled
                                        class="w-full min-h-[44px] py-2 px-4 bg-slate-100 text-slate-400 font-medium text-xs rounded-xl transition flex items-center justify-center gap-2 cursor-not-allowed select-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Belum Waktunya Berangkat (Terkunci)</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Secondary Action -->
                            <div>
                                @if (! $existingComplaint)
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                           class="min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Laporkan Kendala</span>
                                        </a>
                                        <button type="button" @click="showCancelModal = true"
                                            class="min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Batalkan</span>
                                        </button>
                                    </div>
                                @else
                                    <button type="button" @click="showCancelModal = true"
                                        class="w-full min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Batalkan Pesanan</span>
                                    </button>
                                @endif
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                            <!-- Tier 1: Tiba di Lokasi -->
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  @submit="confirmArrival($event)"
                                  onsubmit="return window.confirmArrival ? window.confirmArrival(event) : true;">
                                @csrf
                                <input type="hidden" name="status" value="tiba">
                                <button type="submit" 
                                    class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <span>Saya Sudah Tiba di Lokasi</span>
                                </button>
                            </form>

                            <!-- Secondary Action -->
                            <div>
                                @if (! $existingComplaint)
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                           class="min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Laporkan Kendala</span>
                                        </a>
                                        <button type="button" @click="showCancelModal = true"
                                            class="min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Batalkan</span>
                                        </button>
                                    </div>
                                @else
                                    <button type="button" @click="showCancelModal = true"
                                        class="w-full min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Batalkan Pesanan</span>
                                    </button>
                                @endif
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::Tiba)
                            <!-- Tier 1: Mulai Sesi -->
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                   onsubmit="return confirmOrderAction(event, 'Mulai Layanan?', 'Mulai sesi pemijatan sekarang?', 'Mulai Sesi', 'question');">
                                @csrf
                                <input type="hidden" name="status" value="sedang_berlangsung">
                                <button type="submit" 
                                    class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    <span>Mulai Sesi Layanan Pijat</span>
                                </button>
                            </form>

                            <!-- Secondary Action -->
                            <div>
                                @if (! $existingComplaint)
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                           class="min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Laporkan Kendala</span>
                                        </a>
                                        <button type="button" @click="showSpecialCancelModal = true"
                                            class="min-h-[40px] py-2 px-3 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-800 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Batal di Lokasi</span>
                                        </button>
                                    </div>
                                @else
                                    <button type="button" @click="showSpecialCancelModal = true"
                                        class="w-full min-h-[40px] py-2 px-3 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-800 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Batal di Lokasi</span>
                                    </button>
                                @endif
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                            <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between text-xs">
                                <span class="text-emerald-900 font-medium">Sisa Waktu Layanan:</span>
                                <span class="font-bold text-emerald-800 text-sm font-mono" x-text="remainingTimeFormatted">
                                    {{ $order->duration_minutes }}:00
                                </span>
                            </div>
                            <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                  @submit="confirmCompleteService($event)"
                                  onsubmit="return window.confirmCompleteService ? window.confirmCompleteService(event) : true;">
                                @csrf
                                <input type="hidden" name="status" value="selesai">
                                <button type="submit" 
                                    class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Selesaikan Layanan</span>
                                </button>
                            </form>

                            @if (! $existingComplaint)
                                <div>
                                    <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                       class="w-full min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Laporkan Kendala Lapangan</span>
                                    </a>
                                </div>
                            @endif
                        @elseif ($order->status === \App\Enums\OrderStatus::Selesai)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center space-y-1">
                                <p class="text-xs font-semibold text-slate-900">Layanan Telah Selesai</p>
                                <p class="text-[11px] text-slate-500">Buka rincian untuk memberi penilaian atau melihat laporan.</p>
                            </div>
                        @endif
                    @else
                        @if ($distanceKm !== null && $distanceKm > ($maxRadiusKm ?? 10.0))
                            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-center text-xs text-amber-900 font-semibold">
                                Di Luar Radius Maksimal Layanan (10 km)
                            </div>
                        @else
                            <form action="{{ route('mitra.orders.take', $order) }}" method="POST"
                                  onsubmit="return confirmOrderAction(event, 'Ambil Jadwal Pesanan?', 'Apakah Anda bersedia mengambil dan melayani pesanan ini sesuai jadwal dan lokasi yang tertera?', 'Ya, Ambil Jadwal', 'question');">
                                @csrf
                                <button type="submit" 
                                    class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Ambil Pesanan Ini</span>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @else
            <!-- FULL-PAGE LAYOUT (Belum Berangkat / Selesai / Dibatalkan) -->
            <div class="w-full h-full flex flex-col bg-slate-50">
                <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/70 px-4 py-2.5 min-h-[56px] flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center space-x-2 min-w-0">
                        <a href="{{ $order->mitra_id ? route('mitra.orders.my-schedule') : route('mitra.orders.scheduled') }}"
                           class="w-8 h-8 -ml-1 rounded-full flex items-center justify-center text-slate-700 hover:bg-slate-100 transition shrink-0 cursor-pointer"
                           aria-label="Kembali"
                           title="Kembali">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <h1 class="text-sm font-bold text-slate-900 leading-tight truncate">Rincian Pesanan</h1>
                    </div>
                </header>
        @endif

            <!-- MODE 2: EXPANDED VIEW / FULL-PAGE VIEW (Scrollable Rincian Lengkap) -->
            <div @if ($showMap) x-show="isExpanded" @endif class="flex-1 overflow-y-auto px-4 pb-6 {{ $showMap ? 'pt-2' : 'pt-4' }} space-y-3.5 overscroll-contain">
                <!-- Top Summary Card: Nomor Pesanan & Status -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Nomor Pesanan</span>
                        <span class="text-xs font-bold font-mono text-slate-800">#{{ $order->order_code }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $order->status->textColorClass() }} shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full {{ str_replace('animate-pulse', '', $order->status->dotClass()) }}"></span>
                        {{ $order->status->label() }}
                    </span>
                </div>
                @if (session('location_warning'))
                    <div class="p-3.5 bg-amber-50 border border-amber-200/90 rounded-2xl text-amber-900 text-xs flex items-start gap-3 shadow-xs">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="flex-1">
                            <p class="font-semibold text-amber-950">Anda Masih Jauh dari Titik Pelanggan</p>
                            <p class="text-amber-800 mt-0.5 leading-relaxed">{{ session('location_warning')['message'] ?? 'Jarak Anda masih melebihi radius tiba 50 meter.' }}</p>
                        </div>
                    </div>
                @elseif (session('error'))
                    <div class="p-3.5 bg-rose-50 border border-rose-200/90 rounded-2xl text-rose-900 text-xs flex items-start gap-3 shadow-xs">
                        <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="flex-1 font-medium">{{ session('error') }}</p>
                    </div>
                @elseif (session('status'))
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200/90 rounded-2xl text-emerald-900 text-xs flex items-start gap-3 shadow-xs">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <p class="flex-1 font-medium">{{ session('status') }}</p>
                    </div>
                @endif
                @if ($order->status === \App\Enums\OrderStatus::Dibatalkan)
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs space-y-1 text-xs">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Alasan Pembatalan</span>
                        <p class="text-slate-700 leading-relaxed font-medium">
                            {{ $order->cancellation_reason ?: 'Tidak ada alasan yang dicantumkan.' }}
                        </p>
                    </div>
                @endif

                <!-- Stepper Progress if Assigned and Not Cancelled -->
                @if ($isAssigned && $order->status !== \App\Enums\OrderStatus::Dibatalkan)
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
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2.5">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                            <span class="{{ $currentStep >= 1 ? 'text-emerald-700 font-bold' : '' }}">Dijadwalkan</span>
                            <span class="{{ $currentStep >= 2 ? 'text-emerald-700 font-bold' : '' }}">Menuju Lokasi</span>
                            <span class="{{ $currentStep >= 3 ? 'text-emerald-700 font-bold' : '' }}">Tiba</span>
                            <span class="{{ $currentStep >= 4 ? 'text-emerald-700 font-bold' : '' }}">Layanan</span>
                            <span class="{{ $currentStep >= 5 ? 'text-emerald-700 font-bold' : '' }}">Selesai</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden p-0.5 border border-slate-200/50">
                            <div class="bg-emerald-600 h-full rounded-full transition-all duration-300" 
                                 style="width: {{ min(100, max(20, ($currentStep / 5) * 100)) }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- Penerima & Alamat -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Penerima Layanan</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <h3 class="text-sm font-semibold text-slate-900">{{ $order->recipient_name }}</h3>
                                @php
                                    $expCustRating = $order->customer?->customerRating();
                                    $expCustCount = $order->customer?->customerRatingsCount() ?? 0;
                                @endphp
                                @if ($expCustRating !== null)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-semibold" title="{{ $expCustCount }} ulasan dari mitra lain">
                                        <svg class="w-3 h-3 text-amber-500 fill-current shrink-0" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span>{{ number_format($expCustRating, 1) }}</span>
                                        <span class="text-amber-700 font-normal">({{ $expCustCount }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Pelanggan Baru</span>
                                    </span>
                                @endif
                            </div>
                            @if (! $isArchived && $order->recipient_phone)
                                <span class="text-slate-600 font-medium block mt-0.5">{{ $order->recipient_phone }}</span>
                            @elseif ($isArchived)
                                <span class="text-slate-400 block mt-0.5">{{ $order->masked_phone }}</span>
                            @endif
                        </div>

                        @if (! $isArchived && $order->recipient_phone)
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $order->recipient_phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                            @endphp
                            <div class="flex items-center space-x-1.5 shrink-0">
                                <a href="tel:{{ $order->recipient_phone }}" title="Telepon Pelanggan"
                                   class="w-8 h-8 rounded-full border border-slate-200 hover:bg-slate-50 text-slate-600 flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </a>
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Halo ' . $order->recipient_name . ', saya terapis dari Pijat.In untuk pesanan #' . $order->order_code . '.') }}" 
                                   target="_blank" title="WhatsApp Pelanggan"
                                   class="w-8 h-8 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.587 1.771.889 2.796.889 3.182 0 5.768-2.586 5.768-5.766.001-3.18-2.585-5.766-5.768-5.766zm9.969 5.766c0 5.508-4.462 9.97-9.969 9.97-1.719 0-3.364-.44-4.811-1.222l-5.22 1.369 1.393-5.089c-.879-1.503-1.362-3.238-1.362-5.028 0-5.508 4.462-9.97 9.969-9.97 5.507 0 9.969 4.462 9.969 9.97z"/>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Alamat Layanan</span>
                        <p class="text-slate-800 leading-relaxed font-medium mt-0.5">
                            {{ $isArchived ? $order->masked_address : $order->service_address_text }}
                        </p>
                        @if (! $isArchived && $order->customerAddress?->benchmark)
                            <p class="text-[11px] text-slate-500 mt-1">
                                Patokan: <span class="text-slate-700 font-medium">{{ $order->customerAddress->benchmark }}</span>
                            </p>
                        @endif
                    </div>

                    @if (! $isArchived)
                        <div class="pt-1">
                            <a :href="googleMapsUrl" target="_blank"
                               class="w-full min-h-[40px] py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <span>Buka Rute Navigasi di Google Maps</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Catatan Medis (Hanya tampil jika ada) -->
                @if ($order->medical_notes || $isArchived)
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs text-xs space-y-1">
                        <span class="text-slate-400 text-[11px] block font-medium">Catatan Kondisi Medis</span>
                        <p class="text-slate-800 leading-relaxed font-medium">
                            @if ($isArchived)
                                <span class="text-slate-400 italic">[Catatan medis telah diarsipkan]</span>
                            @else
                                {{ $order->medical_notes }}
                            @endif
                        </p>
                    </div>
                @endif

                <!-- Rincian Layanan & Tarif -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Layanan</span>
                        <span class="font-medium text-slate-900">{{ $cleanServiceName }} ({{ $order->duration_minutes }} Menit)</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Jadwal</span>
                        <span class="font-medium text-slate-900">
                            {{ $order->local_scheduled_start_time ? $order->local_scheduled_start_time->locale('id')->translatedFormat('d M Y, H:i') . ' ' . $order->timezone_abbr : 'Segera' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Tarif Total</span>
                        <span class="text-slate-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-slate-700 font-medium">Pendapatan Bersih Mitra (90%)</span>
                        <span class="text-sm font-bold text-emerald-700">
                            Rp {{ number_format($order->total_price * 0.9, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Actions in Expanded View -->
                @if ($isAssigned)
                    <div class="space-y-2 pt-1">
                        @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
                            @php
                                $canDepartNow = $order->canPartnerDepartNow(45);
                                $earliestTime = $order->earliestPartnerDepartureTime(45);
                            @endphp
                            <div class="space-y-2">
                                @if ($canDepartNow)
                                    <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                          onsubmit="return confirmOrderAction(event, 'Mulai Perjalanan?', 'Mulai perjalanan menuju lokasi pelanggan sekarang?', 'Ya, Berangkat', 'question');">
                                        @csrf
                                        <input type="hidden" name="status" value="menuju_lokasi">
                                        <button type="submit" 
                                            class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            <span>Mulai Berangkat (Menuju Lokasi)</span>
                                        </button>
                                    </form>
                                @else
                                    <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-center">
                                        <p class="text-xs font-semibold text-amber-900">
                                            Berangkat aktif pukul {{ $earliestTime?->format('H:i') }} WIB
                                        </p>
                                    </div>
                                    <button type="button" disabled
                                        class="w-full min-h-[44px] py-2.5 px-4 bg-slate-100 text-slate-400 font-medium text-xs rounded-xl transition flex items-center justify-center space-x-2 cursor-not-allowed select-none">
                                        <span>Belum Waktunya Berangkat (Terkunci)</span>
                                    </button>
                                @endif

                                <!-- Secondary Action -->
                                <div class="pt-0.5">
                                    @if ($existingComplaint)
                                        <button type="button" @click="showCancelModal = true"
                                            class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Batalkan Pesanan</span>
                                        </button>
                                    @else
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                               class="min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Laporkan Kendala</span>
                                            </a>
                                            <button type="button" @click="showCancelModal = true"
                                                class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Batalkan</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
                            <div class="space-y-2">
                                <!-- Tier 1: Tiba di Lokasi -->
                                <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                      @submit="confirmArrival($event)"
                                      onsubmit="return window.confirmArrival ? window.confirmArrival(event) : true;">
                                    @csrf
                                    <input type="hidden" name="status" value="tiba">
                                    <button type="submit" 
                                        class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        <span>Saya Sudah Tiba di Lokasi</span>
                                    </button>
                                </form>

                                <!-- Secondary Action -->
                                <div class="pt-0.5">
                                    @if ($existingComplaint)
                                        <button type="button" @click="showCancelModal = true"
                                            class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Batalkan Pesanan</span>
                                        </button>
                                    @else
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                               class="min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Laporkan Kendala</span>
                                            </a>
                                            <button type="button" @click="showCancelModal = true"
                                                class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Batalkan</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::Tiba)
                            <div class="space-y-2">
                                <!-- Tier 1: Mulai Sesi -->
                                <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                      onsubmit="return confirmOrderAction(event, 'Mulai Layanan?', 'Mulai sesi pemijatan sekarang?', 'Mulai Sesi', 'question');">
                                    @csrf
                                    <input type="hidden" name="status" value="sedang_berlangsung">
                                    <button type="submit" 
                                        class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                        <span>Mulai Sesi Layanan Pijat</span>
                                    </button>
                                </form>

                                <!-- Secondary Action -->
                                <div class="pt-0.5">
                                    @if ($existingComplaint)
                                        <button type="button" @click="showSpecialCancelModal = true"
                                            class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-800 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Batal di Lokasi</span>
                                        </button>
                                    @else
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                               class="min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>Laporkan Kendala</span>
                                            </a>
                                            <button type="button" @click="showSpecialCancelModal = true"
                                                class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100 text-amber-800 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Batal di Lokasi</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::SedangBerlangsung)
                            <div class="space-y-2">
                                <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between text-xs">
                                    <span class="text-emerald-900 font-medium">Sisa Waktu Layanan:</span>
                                    <span class="font-bold text-emerald-800 text-sm font-mono" x-text="remainingTimeFormatted">
                                        {{ $order->duration_minutes }}:00
                                    </span>
                                </div>
                                <form action="{{ route('mitra.orders.update-status', $order) }}" method="POST"
                                      @submit="confirmCompleteService($event)"
                                      onsubmit="return window.confirmCompleteService ? window.confirmCompleteService(event) : true;">
                                    @csrf
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit" 
                                        class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Selesaikan Layanan</span>
                                    </button>
                                </form>

                                @if (! $existingComplaint)
                                    <div class="pt-0.5">
                                        <a href="{{ route('mitra.orders.incident.create', $order) }}"
                                           class="w-full min-h-[38px] py-2 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Laporkan Kendala Lapangan</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif ($order->status === \App\Enums\OrderStatus::Selesai)
                            <div class="p-4 bg-white rounded-xl border border-slate-200 text-center space-y-3">
                                <div>
                                    <span class="inline-flex items-center space-x-1.5 text-slate-800 font-semibold text-xs">
                                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>Layanan Telah Selesai</span>
                                    </span>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Pendapatan telah masuk ke dompet Anda.</p>
                                </div>

                                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                                    <template x-if="hasRated">
                                        <div class="space-y-1">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-semibold">Penilaian Selesai</span>
                                            <p class="text-xs font-medium text-slate-900">
                                                Anda memberikan nilai <strong x-text="selectedRating"></strong>/5 bintang.
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="!hasRated">
                                        <div class="space-y-0.5">
                                            <p class="text-xs font-semibold text-slate-900">Beri Penilaian Pelanggan</p>
                                            <p class="text-[11px] text-slate-500">Pilih bintang sesuai pelayanan:</p>
                                        </div>
                                    </template>

                                    <div class="flex items-center justify-center space-x-2 py-0.5">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" 
                                                    @click="if (!hasRated) tempRating = star"
                                                    :disabled="hasRated || isSubmittingRating"
                                                    class="text-3xl transition transform hover:scale-105 active:scale-95 disabled:cursor-default cursor-pointer select-none">
                                                <span :class="star <= (hasRated ? selectedRating : tempRating) ? 'text-amber-400' : 'text-slate-300'">★</span>
                                            </button>
                                        </template>
                                    </div>

                                    <template x-if="!hasRated">
                                        <div class="space-y-2 pt-2 border-t border-slate-100">
                                            <p class="text-[11px] font-medium text-slate-800" x-show="tempRating > 0">
                                                <span x-text="ratingLabels[tempRating] || ''"></span> (<span x-text="tempRating"></span> dari 5)
                                            </p>
                                            <button type="button" 
                                                    @click="confirmSubmitRating()"
                                                    :disabled="tempRating === 0 || isSubmittingRating"
                                                    class="w-full min-h-[40px] py-2 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                                <span x-show="!isSubmittingRating">Kirim Penilaian</span>
                                                <span x-show="isSubmittingRating" class="flex items-center space-x-1">
                                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                    <span>Menyimpan...</span>
                                                </span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif

                        @if ($existingComplaint)
                            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs text-xs text-left space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center space-x-2 min-w-0">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                        <span class="font-bold text-slate-900 truncate">
                                            {{ $isMyIncident ? 'Laporan Kendala Terkirim' : 'Laporan Kendala Pelanggan' }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-semibold text-amber-700 shrink-0">
                                        {{ $existingComplaint->status->label() }}
                                    </span>
                                </div>
                                @if ($existingComplaint->title)
                                    <p class="text-xs text-slate-700 font-medium truncate">{{ $existingComplaint->title }}</p>
                                @endif
                                <p class="text-[11px] text-slate-500 leading-relaxed pt-1 border-t border-slate-100">
                                    Tiket <span class="font-mono font-bold text-slate-700">#{{ $existingComplaint->complaint_code }}</span> sedang ditinjau oleh Admin Wilayah.
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    @if ($distanceKm !== null && $distanceKm > ($maxRadiusKm ?? 10.0))
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-center text-xs text-amber-950 space-y-1">
                            <p class="font-semibold text-amber-900">
                                Di Luar Radius Maksimal Layanan (10 km)
                            </p>
                            <p class="text-[11px] text-amber-800 leading-relaxed">
                                Jarak Anda saat ini ({{ $distanceKm }} km) melebihi batas maksimal 10 km dari alamat pelanggan.
                            </p>
                        </div>
                    @else
                        <form action="{{ route('mitra.orders.take', $order) }}" method="POST" class="pt-1"
                              onsubmit="return confirmOrderAction(event, 'Ambil Jadwal Pesanan?', 'Apakah Anda bersedia mengambil dan melayani pesanan ini sesuai jadwal dan lokasi yang tertera?', 'Ya, Ambil Jadwal', 'question');">
                            @csrf
                            <div class="space-y-2">
                                <button type="submit" 
                                    class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Ambil Pesanan Ini</span>
                                </button>

                                <a href="{{ route('mitra.orders.scheduled') }}"
                                   class="w-full min-h-[40px] py-2 px-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                    <span>Kembali ke Bursa</span>
                                </a>
                            </div>
                        </form>
                    @endif
                @endif

                @if ($showMap)
                    <!-- Close Details Button -->
                    <button type="button" @click="collapseSheet()"
                        class="w-full min-h-[40px] py-2 px-4 border border-slate-200 hover:bg-slate-50 text-slate-600 font-medium text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <span>Tutup Detail</span>
                    </button>
                @endif
            </div>

        @if ($showMap)
            </div> <!-- Close Bottom Sheet Container -->
        @else
            </div> <!-- Close FULL-PAGE LAYOUT flex-col -->
        @endif

        <!-- Special Cancellation Modal (MIT-11) -->
        <div x-show="showSpecialCancelModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
             @click.self="showSpecialCancelModal = false">
            <div class="bg-white rounded-2xl p-5 max-w-sm w-full text-slate-900 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900">Pembatalan Khusus di Lokasi</h4>
                        <p class="text-[10px] text-emerald-700 font-semibold">Tiba di Lokasi • Bebas Sanksi Mitra</p>
                    </div>
                    <button type="button" @click="showSpecialCancelModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('mitra.orders.cancel', $order) }}" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-800">Pilih Jenis Kendala / Alasan Khusus:</label>
                        
                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer space-x-2.5 transition">
                            <input type="radio" name="cancellation_type" value="customer_not_present" required class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <strong class="text-slate-900 block font-bold">Pelanggan Tidak Hadir di Lokasi</strong>
                                <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">Sudah menunggu minimal 15 menit & tidak merespons. Mitra menerima kompensasi transport 50%.</span>
                            </div>
                        </label>

                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer space-x-2.5 transition">
                            <input type="radio" name="cancellation_type" value="gender_mismatch" required class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <strong class="text-slate-900 block font-bold">Ketidaksesuaian Gender Penerima</strong>
                                <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">Gender penerima layanan di tempat tidak sesuai ketentuan. Mitra menerima kompensasi 50%.</span>
                            </div>
                        </label>

                        <label class="flex items-start p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer space-x-2.5 transition">
                            <input type="radio" name="cancellation_type" value="medical_condition" required class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <strong class="text-slate-900 block font-bold">Kontraindikasi Medis Berbahaya</strong>
                                <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">Ditemukan riwayat/kondisi medis yang berbahaya untuk dipijat. Pelanggan refund 100%, mitra bebas sanksi.</span>
                            </div>
                        </label>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">Rincian Penjelasan (Wajib diisi):</label>
                        <textarea name="reason" rows="2" required minlength="5" maxlength="500"
                            placeholder="Jelaskan detail kendala yang terjadi di lokasi pelanggan..."
                            class="w-full rounded-xl border-slate-200 text-xs p-2.5 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" @click="showSpecialCancelModal = false"
                            class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Kembali
                        </button>
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">
                            Batalkan Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Regular Cancellation Modal (MIT-12) -->
        <div x-show="showCancelModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
             @click.self="showCancelModal = false">
            <div class="relative bg-white rounded-2xl p-6 max-w-sm w-full text-slate-900 shadow-2xl border border-slate-100 space-y-4">
                <button type="button" @click="showCancelModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Centered Icon & Brief Warning -->
                <div class="text-center pt-1">
                    <div class="mx-auto w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-900">Batalkan Pesanan?</h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Pembatalan sepihak akan dikenakan sanksi {{ \App\Models\Parameter::get('partner_cancellation_penalty_points', 15) }} poin pelanggaran (SP) dan pesanan dikembalikan ke bursa.
                    </p>
                </div>

                <!-- Form Alasan Penolakan/Pembatalan -->
                <form method="POST" action="{{ route('mitra.orders.cancel', $order) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="cancellation_type" value="regular">

                    <div class="space-y-1.5">
                        <label for="cancel-regular-reason" class="block text-xs font-semibold text-slate-700">
                            Alasan Pembatalan <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="cancel-regular-reason" name="reason" rows="3" required minlength="5" maxlength="500"
                            placeholder="Tulis alasan pembatalan (minimal 5 karakter)..."
                            class="w-full rounded-xl border border-slate-200 text-xs p-3 focus:border-rose-500 focus:ring-rose-500 placeholder:text-slate-400 leading-relaxed resize-none transition"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" @click="showCancelModal = false"
                            class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer">
                            Kembali
                        </button>
                        <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                            Ya, Batalkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-pwa-layout>
