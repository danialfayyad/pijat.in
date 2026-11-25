<x-pwa-layout title="Portal Mitra - Pijat.In" :hideHeader="true">
    <div class="py-1 space-y-4"
         x-data="{
            isActive: {{ $profile?->is_active ? 'true' : 'false' }},
            isToggling: false,
            wakeLock: null,
            pingTimer: null,
            pollTimer: null,
            countdownTimer: null,
            activeOffer: null,
            remainingSeconds: 60,
            isAccepting: false,
            isRejecting: false,

            init() {
                if (this.isActive) {
                    this.enableOnlineFeatures();
                }
            },

            async toggleStatus() {
                if (this.isToggling) return;
                this.isToggling = true;

                if (!this.isActive) {
                    // Aktifkan Online: Request GPS
                    if (!navigator.geolocation) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                iconHtml: 'i',
                                title: 'GPS Tidak Didukung',
                                text: 'Browser Anda tidak mendukung deteksi lokasi GPS.',
                                confirmButtonText: 'Tutup',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-info',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        } else {
                            alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                        }
                        this.isToggling = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            await this.sendToggleRequest(true, position.coords.latitude, position.coords.longitude);
                        },
                        async (error) => {
                            console.warn('Geolocation error:', error);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    iconHtml: '📍',
                                    title: 'Akses GPS Diperlukan',
                                    text: 'Izin akses lokasi GPS diperlukan untuk mengaktifkan status Online agar sistem dapat mencocokkan pesanan di sekitar Anda.',
                                    confirmButtonText: 'Mengerti',
                                    buttonsStyling: false,
                                    customClass: {
                                        popup: 'swal-admin-theme',
                                        icon: 'swal2-warning',
                                        confirmButton: 'swal2-confirm'
                                    }
                                });
                            } else {
                                alert('Izin akses lokasi GPS diperlukan untuk mengaktifkan status Online.');
                            }
                            this.isToggling = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                    // Nonaktifkan Online
                    await this.sendToggleRequest(false);
                }
            },

            async sendToggleRequest(targetActive, lat = null, lng = null) {
                try {
                    const payload = {
                        is_active: targetActive ? 1 : 0,
                        _token: '{{ csrf_token() }}'
                    };
                    if (lat && lng) {
                        payload.latitude = lat;
                        payload.longitude = lng;
                    }

                    const res = await fetch('{{ route('mitra.toggle-active') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.isActive = data.is_active;
                        if (this.isActive) {
                            this.enableOnlineFeatures();
                        } else {
                            this.disableOnlineFeatures();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                iconHtml: '✕',
                                title: 'Gagal Mengubah Status',
                                text: data.message || 'Gagal mengubah status kesiapan.',
                                confirmButtonText: 'Tutup',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-error',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        } else {
                            alert(data.message || 'Gagal mengubah status kesiapan.');
                        }
                    }
                } catch (e) {
                    console.error('Toggle error:', e);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            iconHtml: '✕',
                            title: 'Gangguan Koneksi',
                            text: 'Gagal menghubungi server. Periksa koneksi internet Anda.',
                            confirmButtonText: 'Tutup',
                            buttonsStyling: false,
                            customClass: {
                                popup: 'swal-admin-theme',
                                icon: 'swal2-error',
                                confirmButton: 'swal2-confirm'
                            }
                        });
                    } else {
                        alert('Gangguan koneksi internet.');
                    }
                } finally {
                    this.isToggling = false;
                }
            },

            async enableOnlineFeatures() {
                if ('wakeLock' in navigator) {
                    try {
                        this.wakeLock = await navigator.wakeLock.request('screen');
                    } catch (e) {}
                }
                this.startLocationPing();
                this.startOfferPolling();
            },

            disableOnlineFeatures() {
                if (this.wakeLock) {
                    try { this.wakeLock.release(); } catch(e) {}
                    this.wakeLock = null;
                }
                if (this.pingTimer) {
                    clearInterval(this.pingTimer);
                    this.pingTimer = null;
                }
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;
                }
                if (this.countdownTimer) {
                    clearInterval(this.countdownTimer);
                    this.countdownTimer = null;
                }
                this.activeOffer = null;
            },

            startLocationPing() {
                if (this.pingTimer) clearInterval(this.pingTimer);
                this.pingTimer = setInterval(() => {
                    if (navigator.geolocation && this.isActive) {
                        navigator.geolocation.getCurrentPosition((pos) => {
                            fetch('{{ route('mitra.location.ping') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    latitude: pos.coords.latitude,
                                    longitude: pos.coords.longitude
                                })
                            }).catch(() => {});
                        }, () => {}, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
                    }
                }, 300000);
            },

            startOfferPolling() {
                if (this.pollTimer) clearInterval(this.pollTimer);
                this.pollTimer = setInterval(async () => {
                    if (!this.isActive || this.activeOffer) return;
                    try {
                        const res = await fetch('{{ route('mitra.orders.pending-offer') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data && data.has_offer) {
                            this.triggerOffer(data);
                        }
                    } catch (e) {}
                }, 3000);
            },

            triggerOffer(offerData) {
                this.activeOffer = offerData;
                this.remainingSeconds = offerData.remaining_seconds || 60;

                if ('vibrate' in navigator) {
                    navigator.vibrate([300, 120, 300, 120, 400]);
                }
                this.playAudioAlert();

                if (this.countdownTimer) clearInterval(this.countdownTimer);
                this.countdownTimer = setInterval(() => {
                    this.remainingSeconds--;
                    if (this.remainingSeconds <= 0) {
                        clearInterval(this.countdownTimer);
                        this.rejectOffer('Waktu konfirmasi 60 detik telah habis.');
                    }
                }, 1000);
            },

            playAudioAlert() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(659.25, ctx.currentTime);
                    osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.45);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.5);
                } catch(e) {}
            },

            async acceptOffer() {
                if (this.isAccepting || !this.activeOffer) return;
                this.isAccepting = true;
                clearInterval(this.countdownTimer);

                try {
                    const url = '/mitra/orders/offers/' + this.activeOffer.dispatch_id + '/accept';
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                iconHtml: '⏰',
                                title: 'Waktu Penawaran Berakhir',
                                text: data.message || 'Waktu penawaran telah habis atau pesanan sudah tidak tersedia.',
                                confirmButtonText: 'Mengerti',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-warning',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        } else {
                            alert(data.message || 'Gagal menerima pesanan.');
                        }
                        this.activeOffer = null;
                    }
                } catch (e) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            iconHtml: '✕',
                            title: 'Gangguan Koneksi',
                            text: 'Gagal menghubungi server. Periksa koneksi internet Anda.',
                            confirmButtonText: 'Tutup',
                            buttonsStyling: false,
                            customClass: {
                                popup: 'swal-admin-theme',
                                icon: 'swal2-error',
                                confirmButton: 'swal2-confirm'
                            }
                        });
                    } else {
                        alert('Gagal menghubungi server.');
                    }
                    this.activeOffer = null;
                } finally {
                    this.isAccepting = false;
                }
            },

            async rejectOffer(reason = 'Ditolak oleh mitra.') {
                if (this.isRejecting || !this.activeOffer) return;
                this.isRejecting = true;
                clearInterval(this.countdownTimer);

                try {
                    const url = '/mitra/orders/offers/' + this.activeOffer.dispatch_id + '/reject';
                    await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ reason })
                    });
                } catch(e) {} finally {
                    this.activeOffer = null;
                    this.isRejecting = false;
                }
            }
         }">

        @if (session('status'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="flex-1 leading-snug">{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="flex-1 leading-snug">{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- 1. TOP BAR SESUAI CUSTOMER: LOKASI OPERASIONAL & NOTIFIKASI -->
        <div class="flex items-center justify-between pt-1">
            <!-- Lokasi Operasional Terpilih -->
            <a href="{{ route('mitra.profile') }}" 
               class="inline-flex items-center space-x-2 py-1 px-3 rounded-full bg-white border border-slate-200/90 shadow-2xs hover:border-emerald-300 hover:shadow-xs transition group cursor-pointer"
               title="Wilayah Operasional Mitra">
                <div class="w-6 h-6 rounded-full bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-slate-900 group-hover:text-emerald-700 transition max-w-[170px] truncate">
                    {{ $profile?->region?->name ?? 'Wilayah Terdaftar' }}
                </span>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-700 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </a>

            <!-- Lonceng Notifikasi PWA -->
            @php
                $unreadNotificationCount = $user->systemNotifications()->unread()->count();
            @endphp
            <button type="button" 
                    x-data="{ unread: {{ $unreadNotificationCount }} }"
                    @notifications-read-all.window="unread = 0"
                    @notification-read.window="unread = Math.max(0, unread - 1)"
                    @notifications-updated.window="unread = $event.detail"
                    @click="$dispatch('open-notification-modal')"
                    onclick="window.dispatchEvent(new CustomEvent('open-notification-modal'))"
                    class="relative w-10 h-10 rounded-full bg-white border border-slate-200/90 shadow-2xs text-slate-800 hover:bg-slate-50 active:scale-95 flex items-center justify-center transition cursor-pointer"
                    title="Buka Notifikasi">
                <svg class="w-5 h-5 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span x-show="unread > 0" 
                      class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-600 text-[9px] font-black text-white ring-2 ring-white"
                      x-text="unread > 99 ? '99+' : unread">
                </span>
            </button>
        </div>

        <!-- 2. GREETING & SAPAAN HANGAT (PERSIS CUSTOMER) -->
        <div class="space-y-0.5 pt-0.5">
            <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-1.5">
                <span>Halo, {{ explode(' ', trim($user->name))[0] }}</span>
                <span class="inline-block animate-wave origin-bottom-right">👋</span>
            </h2>
            <p class="text-xs text-slate-500 font-medium">Siap memberikan layanan terbaik hari ini?</p>
        </div>

        <!-- 3. KARTU SALDO MITRA (GAYA BOTANIKAL EMERALD PERSIS MOCKUP CUSTOMER) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#059669] via-[#047857] to-[#064e3b] p-5 text-white shadow-lg">
            <!-- Pola Ilustrasi Daun / Botanikal Transparan di Sisi Kanan -->
            <div class="absolute -right-6 -bottom-8 pointer-events-none opacity-20">
                <svg width="220" height="220" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M160 20C110 30 70 80 80 140C90 200 170 190 190 130C210 70 190 30 160 20Z" fill="white"/>
                    <path d="M120 70C80 80 40 120 50 170C60 210 120 200 140 150C160 100 140 80 120 70Z" fill="white" opacity="0.6"/>
                    <path d="M80 140C120 100 160 60 190 30" stroke="white" stroke-width="4" stroke-linecap="round"/>
                    <path d="M50 170C90 130 120 100 150 70" stroke="white" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <!-- Konten Kartu Saldo -->
            <div class="relative z-10 space-y-3">
                <div>
                    <span class="text-xs font-medium text-emerald-100/90 block">Saldo Dompet Anda</span>
                    <a href="{{ route('mitra.finance.index') }}" class="group inline-block mt-0.5">
                        <div class="text-2xl sm:text-3xl font-black tracking-tight text-white font-mono group-hover:underline">
                            {{ $user->getOrCreateWallet()->formatted_balance }}
                        </div>
                    </a>
                    @if (($user->wallet?->held_balance ?? 0) > 0)
                        <div class="text-[11px] text-amber-200 font-semibold mt-0.5">
                            +Rp {{ number_format($user->wallet->held_balance, 0, ',', '.') }} kliring 24 jam
                        </div>
                    @endif
                </div>

                <div class="pt-1 flex items-center justify-between gap-2">
                    <a href="{{ route('mitra.finance.withdraw') }}" 
                       class="inline-flex items-center space-x-2 py-2 px-4 rounded-full bg-white text-emerald-900 text-xs font-black shadow-md hover:bg-emerald-50 active:scale-95 transition cursor-pointer">
                        <span>Tarik Saldo</span>
                        <svg class="w-3.5 h-3.5 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>

                    <!-- Rating & Selesai Ringkas -->
                    <div class="flex items-center space-x-1.5 text-xs text-white bg-white/20 px-3 py-1.5 rounded-full font-bold backdrop-blur-2xs">
                        <span>⭐ {{ number_format($user->partnerRating(), 1) }}</span>
                        <span>•</span>
                        <span>{{ $user->completedOrdersCount() }} Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. STATUS KESIAPAN (WORK MODE TOGGLE) -->
        <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between transition-all">
            <div class="flex items-center space-x-3.5 min-w-0">
                <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 transition-colors shadow-2xs"
                     :class="isActive ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'">
                    <template x-if="isActive">
                        <span class="relative flex h-3.5 w-3.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-600"></span>
                        </span>
                    </template>
                    <template x-if="!isActive">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </template>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-black text-slate-900 leading-tight"
                         x-text="isActive ? 'Status: Online (Siap Kerja)' : 'Status: Istirahat'"></div>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5 truncate"
                       x-text="isActive ? 'Aplikasi siap menerima pesanan di sekitar Anda' : 'Aktifkan untuk mulai menerima pesanan masuk'"></p>
                </div>
            </div>

            <!-- Native iOS-Style Toggle Switch -->
            <button type="button" @click="toggleStatus()" :disabled="isToggling"
                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none disabled:opacity-50 ml-3"
                    :class="isActive ? 'bg-emerald-600' : 'bg-slate-300'"
                    role="switch" :aria-checked="isActive">
                <span class="sr-only">Ubah status online</span>
                <span aria-hidden="true" 
                      class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-xs transition duration-200 ease-in-out"
                      :class="isActive ? 'translate-x-5' : 'translate-x-0'">
                </span>
            </button>
        </div>

        <!-- 5. DUA TOMBOL AKSI CEPAT: BURSA PESANAN & JADWAL SAYA (PERSIS GRID CUSTOMER) -->
        <div class="grid grid-cols-2 gap-3.5">
            <!-- Card 1: Bursa Pesanan -->
            <a href="{{ route('mitra.orders.scheduled') }}"
               class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs hover:border-emerald-300 transition flex flex-col items-center justify-center text-center group cursor-pointer relative">
                @if (isset($availableBursaCount) && $availableBursaCount > 0)
                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[10px] bg-emerald-600 text-white font-bold">
                        {{ $availableBursaCount }}
                    </span>
                @endif
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2 shadow-2xs transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-snug block">
                    Bursa Pesanan
                </span>
            </a>

            <!-- Card 2: Jadwal Saya -->
            <a href="{{ route('mitra.orders.my-schedule') }}"
               class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs hover:border-emerald-300 transition flex flex-col items-center justify-center text-center group cursor-pointer">
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-2 shadow-2xs transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 leading-snug block">
                    Jadwal Saya
                </span>
            </a>
        </div>

        <!-- 6. DAFTAR PESANAN HARI INI -->
        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <h3 class="text-sm font-bold text-slate-800">Pesanan Hari Ini</h3>
                    @if (isset($todayOrders) && $todayOrders->isNotEmpty())
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
                <span class="text-xs font-semibold text-slate-500 font-mono">
                    {{ isset($todayOrders) ? $todayOrders->count() : 0 }} Pesanan
                </span>
            </div>

            @if (isset($todayOrders) && $todayOrders->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($todayOrders as $order)
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-300 transition space-y-3">
                            <!-- Header Item: Kode, Jadwal, & Status -->
                            <div class="flex items-center justify-between text-xs gap-2">
                                <div class="flex items-center space-x-2 min-w-0 font-mono">
                                    <span class="font-bold text-slate-800 text-[11px]">{{ $order->order_code }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-sans font-medium text-slate-600 truncate text-[11px]">
                                        @if ($order->order_type->value === 'instan')
                                            <span class="text-emerald-700 font-bold">Instan</span>
                                        @else
                                            Hari Ini, {{ $order->local_scheduled_start_time?->format('H:i') }} {{ $order->timezone_abbr }}
                                        @endif
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $order->status->badgeClasses() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </div>

                            <!-- Layanan & Alamat -->
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $order->service_name }}</h4>
                                <div class="flex items-center space-x-1 text-slate-500 mt-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="truncate text-[11px] text-slate-600">
                                        {{ $order->service_address_text }}
                                    </p>
                                </div>
                            </div>

                            <!-- Footer Item: Tarif & Aksi Buka Detail -->
                            <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-medium">Tarif Pesanan</span>
                                    <span class="font-bold text-slate-900 font-mono text-sm">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <a href="{{ route('mitra.orders.show', $order) }}"
                                   class="py-2 px-3.5 bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs rounded-xl shadow-2xs transition flex items-center space-x-1.5 cursor-pointer">
                                    <span>Buka Detail &amp; Rute</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-7 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
                    <div class="w-11 h-11 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="font-bold text-slate-800 text-sm">Belum Ada Pesanan Hari Ini</p>
                    <p class="text-xs text-slate-500 mt-0.5">Aktifkan status Online di atas untuk menerima pesanan baru di sekitar Anda.</p>
                    @if (isset($availableBursaCount) && $availableBursaCount > 0)
                        <div class="mt-3">
                            <a href="{{ route('mitra.orders.scheduled') }}" 
                               class="inline-flex items-center space-x-1 py-1.5 px-3.5 rounded-full bg-emerald-50 text-emerald-800 font-semibold text-xs hover:bg-emerald-100 transition">
                                <span>Cek Bursa Pesanan ({{ $availableBursaCount }})</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- 7. SHORTCUT PUSAT BANTUAN & LIVE CHAT (PERSIS CUSTOMER SECTION 8) -->
        <div class="pt-1 pb-2">
            <a href="{{ route('mitra.help.index') }}" 
               class="p-4 bg-emerald-50/70 hover:bg-emerald-50 rounded-2xl border border-emerald-200/80 flex items-center justify-between text-xs transition cursor-pointer group">
                <div class="flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-black text-slate-900 block leading-tight group-hover:text-emerald-800 transition">Butuh Bantuan atau Panduan Mitra?</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Tim Live Chat &amp; Pusat Bantuan wilayah siap membantu</span>
                    </div>
                </div>
                <div class="flex items-center space-x-1 text-emerald-700 font-bold shrink-0">
                    <span>Bantuan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>

        <!-- 8. MODAL TAWARAN PESANAN INSTAN 60 DETIK -->
        <div x-show="activeOffer" style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-sm w-full p-5 space-y-4 shadow-2xl border border-emerald-500/30">
                
                <!-- Header Modal & Timer -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-600"></span>
                        </span>
                        <span class="text-xs font-black text-emerald-900 uppercase tracking-wider">Tawaran Pesanan Masuk</span>
                    </div>
                    <div class="px-2.5 py-1 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-950 font-black font-mono text-xs flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="remainingSeconds + 's'"></span>
                    </div>
                </div>

                <!-- Detail Singkat -->
                <div class="space-y-3 text-xs">
                    <div>
                        <h4 class="text-base font-black text-slate-900 leading-tight" x-text="activeOffer ? activeOffer.service_name : ''"></h4>
                        <div class="flex items-center space-x-2 text-slate-500 mt-1 font-semibold">
                            <span x-text="(activeOffer ? activeOffer.duration_minutes : '') + ' Menit'"></span>
                            <span>•</span>
                            <span class="font-bold text-emerald-800" x-text="'Jarak: ' + (activeOffer ? activeOffer.distance_km : '')"></span>
                        </div>
                        <div class="mt-2">
                            <template x-if="activeOffer && activeOffer.customer_rating">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs font-semibold">
                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>Rating Pemesan: <strong x-text="activeOffer.customer_rating"></strong></span>
                                    <span class="text-amber-700 font-normal text-[11px]" x-text="'(' + activeOffer.customer_ratings_count + ' ulasan mitra)'"></span>
                                </div>
                            </template>
                            <template x-if="activeOffer && !activeOffer.customer_rating">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-700 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Pelanggan Baru</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="p-3.5 bg-emerald-50/80 rounded-2xl border border-emerald-100 flex items-center justify-between">
                        <span class="text-emerald-900 font-semibold">Tarif Pesanan:</span>
                        <span class="text-base font-black text-emerald-950 font-mono" x-text="activeOffer ? activeOffer.formatted_price : ''"></span>
                    </div>

                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Alamat lengkap dan kontak pemesan akan ditampilkan setelah Anda menerima pesanan ini.
                    </p>
                </div>

                <!-- Tombol Tolak vs Terima -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" @click="rejectOffer('Mitra menolak tawaran.')" :disabled="isRejecting"
                        class="min-h-[44px] py-2.5 px-3 rounded-full border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition cursor-pointer active:scale-95 disabled:opacity-50">
                        <span x-text="isRejecting ? 'Menolak...' : 'Tolak'"></span>
                    </button>
                    <button type="button" @click="acceptOffer()" :disabled="isAccepting"
                        class="min-h-[44px] py-2.5 px-3 rounded-full bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs shadow-md transition flex items-center justify-center space-x-1.5 cursor-pointer active:scale-95 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="isAccepting ? 'Memproses...' : 'Terima'"></span>
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-pwa-layout>
