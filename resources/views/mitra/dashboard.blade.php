<x-pwa-layout title="Portal Mitra - Pijat.In" headerTitle="Mitra Pijat.In">
    <div class="py-2 space-y-4"
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
                    // Turning ON: Request GPS coordinate
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                        this.isToggling = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            await this.sendToggleRequest(true, position.coords.latitude, position.coords.longitude);
                        },
                        async (error) => {
                            console.warn('Geolocation error:', error);
                            alert('Izin akses lokasi GPS diperlukan untuk mengaktifkan status Online agar sistem dapat mencocokkan pesanan di sekitar Anda.');
                            this.isToggling = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                    // Turning OFF
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
                        alert(data.message || 'Gagal mengubah status kesiapan.');
                    }
                } catch (e) {
                    console.error('Toggle error:', e);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isToggling = false;
                }
            },

            async enableOnlineFeatures() {
                // 1. Screen Wake Lock (MIT-04)
                if ('wakeLock' in navigator) {
                    try {
                        this.wakeLock = await navigator.wakeLock.request('screen');
                    } catch (e) {
                        console.log('Wake Lock denied:', e);
                    }
                }

                // 2. Location Ping every 5 minutes (300000 ms) (MIT-04)
                this.startLocationPing();

                // 3. Sequential Offer Polling every 3 seconds (MIT-05 & SYS-01)
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
                        });
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

                // Vibrate and Audio Alert (MIT-05)
                if ('vibrate' in navigator) {
                    navigator.vibrate([300, 120, 300, 120, 400]);
                }
                this.playAudioAlert();

                // 1-second countdown
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
                        alert(data.message || 'Gagal menerima pesanan.');
                        this.activeOffer = null;
                    }
                } catch (e) {
                    alert('Gagal menghubungi server.');
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
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Profile Greeting & Region Header -->
        <div class="flex items-center justify-between pt-1">
            <div class="flex items-center space-x-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-700 text-white font-extrabold text-base flex items-center justify-center shadow-xs">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 block">Halo, Terapis</span>
                    <h2 class="text-sm font-extrabold text-slate-900 leading-tight">{{ $user->name }}</h2>
                    <div class="flex items-center space-x-1 text-[11px] text-emerald-800 font-semibold mt-0.5">
                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $profile?->region?->name ?? 'Wilayah Terdaftar' }}</span>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                    {{ now()->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Work Status Toggle Card (MIT-04) -->
        <div class="rounded-2xl p-4 transition-all shadow-xs"
             :class="isActive ? 'bg-emerald-800 text-white border border-emerald-700' : 'bg-white text-slate-900 border border-slate-200/90'">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                         :class="isActive ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium block" :class="isActive ? 'text-emerald-200' : 'text-slate-400'">Status Kesiapan</span>
                        <div class="text-sm font-extrabold flex items-center space-x-1.5" :class="isActive ? 'text-white' : 'text-slate-900'">
                            <span x-text="isActive ? 'Online (Aktif)' : 'Istirahat (Nonaktif)'"></span>
                            <span class="inline-block w-2 h-2 rounded-full" :class="isActive ? 'bg-emerald-300 animate-pulse' : 'bg-slate-300'"></span>
                        </div>
                    </div>
                </div>

                <button type="button" @click="toggleStatus()" :disabled="isToggling"
                    class="min-h-[44px] px-4 py-2.5 rounded-xl font-bold text-xs transition cursor-pointer flex items-center space-x-1.5 shadow-xs"
                    :class="isActive ? 'bg-white text-emerald-900 hover:bg-slate-100' : 'bg-emerald-700 hover:bg-emerald-800 text-white'">
                    <span x-text="isToggling ? 'Memproses...' : (isActive ? 'Istirahat' : 'Aktifkan')"></span>
                </button>
            </div>

            <!-- Screen Wake Lock & App reminder (MIT-04) -->
            <div x-show="isActive" class="mt-3 pt-3 border-t border-emerald-700/60 flex items-center space-x-2 text-[11px] text-emerald-100">
                <svg class="w-4 h-4 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Biarkan aplikasi tetap terbuka agar siap menerima tawaran pesanan baru (Wake Lock aktif).</span>
            </div>
        </div>

        <!-- Quick Metrics Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold text-slate-500">Saldo Dompet</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="text-lg font-black text-slate-900 font-mono">Rp {{ number_format($user->wallet_balance ?? 0, 0, ',', '.') }}</div>
                <span class="text-[11px] text-emerald-800 font-semibold block mt-1 truncate">Bank: {{ $user->primaryBankAccount?->bank_name ?? 'BCA' }}</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-semibold text-slate-500">Status Kepatuhan</span>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="text-lg font-black text-emerald-800">Bebas SP</div>
                <span class="text-[11px] text-slate-500 block mt-1 truncate">Rekam jejak bersih</span>
            </div>
        </div>

        <!-- Quick Links: Bursa & Jadwal Saya (MIT-06, MIT-07) -->
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('mitra.orders.scheduled') }}" 
               class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 hover:shadow-sm transition flex items-center space-x-3 group cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 leading-tight">Bursa Pesanan</h3>
                    <span class="text-[11px] text-slate-500 font-medium">Ambil Pesanan</span>
                </div>
            </a>

            <a href="{{ route('mitra.orders.my-schedule') }}" 
               class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 hover:shadow-sm transition flex items-center space-x-3 group cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 group-hover:bg-teal-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 leading-tight">Jadwal Saya</h3>
                    <span class="text-[11px] text-slate-500 font-medium">Tugas Terjadwal</span>
                </div>
            </a>
        </div>

        <!-- Active / Scheduled Orders Placeholder -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800">Pesanan Hari Ini</h3>
                <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md font-mono">0 Pesanan</span>
            </div>
            <div class="py-6 text-center text-slate-500 text-xs">
                <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="font-semibold text-slate-700">Belum Ada Pesanan Aktif</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pastikan status <strong>Online</strong> untuk menerima tawaran pesanan baru.</p>
            </div>
        </div>

        <!-- Modal Tawaran Pesanan Instan 60 Detik (MIT-05) -->
        <div x-show="activeOffer" style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-sm w-full p-5 space-y-4 shadow-2xl border-2 border-emerald-500 animate-bounce-short">
                
                <!-- Header with Countdown Pill -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-ping"></span>
                        <span class="text-xs font-extrabold text-emerald-800 uppercase tracking-wide">Tawaran Pesanan Baru</span>
                    </div>
                    <div class="px-2.5 py-1 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-900 font-black font-mono text-xs flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="remainingSeconds + 's'"></span>
                    </div>
                </div>

                <!-- Order Details (WITHOUT Full Address as per MIT-05) -->
                <div class="space-y-3">
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900 leading-tight" x-text="activeOffer ? activeOffer.service_name : ''"></h4>
                        <div class="flex items-center space-x-3 text-xs text-slate-500 mt-1 font-medium">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="(activeOffer ? activeOffer.duration_minutes : '') + ' Menit'"></span>
                            </span>
                            <span>•</span>
                            <span class="flex items-center space-x-1 text-emerald-800 font-semibold">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span x-text="'Jarak: ' + (activeOffer ? activeOffer.distance_km : '')"></span>
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between text-xs">
                        <span class="text-emerald-900 font-medium">Tarif Pesanan:</span>
                        <span class="text-base font-black text-emerald-950 font-mono" x-text="activeOffer ? activeOffer.formatted_price : ''"></span>
                    </div>

                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 leading-relaxed">
                        Alamat lengkap dan data pemesan akan dibuka setelah Anda menekan tombol Terima.
                    </div>
                </div>

                <!-- Action Buttons: Tolak vs Terima (MIT-05) -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" @click="rejectOffer('Mitra menolak tawaran.')" :disabled="isRejecting"
                        class="min-h-[44px] py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition cursor-pointer">
                        <span x-text="isRejecting ? 'Menolak...' : 'Tolak'"></span>
                    </button>
                    <button type="button" @click="acceptOffer()" :disabled="isAccepting"
                        class="min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="isAccepting ? 'Memproses...' : 'Terima'"></span>
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-pwa-layout>
