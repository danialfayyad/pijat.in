@auth
@php
    $isMitra = Auth::user()->isMitra();
@endphp

<div x-data="pwaPushModal()"
     x-show="showModal"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @open-pwa-push-modal.window="openModal()"
     @pwa-push-permission-changed.window="handlePermissionChange($event.detail)"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
     role="dialog"
     aria-modal="true"
     aria-labelledby="push-modal-title">

    <div @click.away="dismiss()"
         class="relative w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl border border-slate-200/90 text-center overflow-hidden space-y-4">
        
        <!-- Top Emerald Accent Bar -->
        <div class="h-1.5 w-full bg-[#064e3b] absolute top-0 left-0"></div>

        <!-- TAMPILAN 1: STATUS DEFAULT (BELUM DIBERI IZIN) -->
        <div x-show="permissionStatus === 'default'" class="space-y-4 pt-1">
            <!-- Icon Bel Notifikasi -->
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center mx-auto ring-8 ring-emerald-50/70 shadow-2xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>

            @if ($isMitra)
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                        Radar & Pesanan Mitra
                    </span>
                    <h3 id="push-modal-title" class="text-base font-bold text-slate-900 mt-2">
                        Aktifkan Notifikasi Pesanan
                    </h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        Terima pemberitahuan instan saat ada tawaran pesanan baru (60 detik) dan info pencairan saldo dompet, meski aplikasi sedang di latar belakang.
                    </p>
                </div>

                <!-- Poin Manfaat untuk Mitra -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-left space-y-2 text-xs text-slate-700">
                    <div class="flex items-start space-x-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px] mt-0.5">✓</span>
                        <span><strong>Tawaran Instan 60 Detik:</strong> Nada getar & alarm agar pesanan tidak terlewat.</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px] mt-0.5">✓</span>
                        <span><strong>Pesanan Terjadwal:</strong> Update bursa pesanan baru di area tugas Anda.</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px] mt-0.5">✓</span>
                        <span><strong>Pencairan Saldo:</strong> Konfirmasi mutasi dompet dan transfer ke rekening.</span>
                    </div>
                </div>
            @else
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                        Pelacakan Layanan Real-Time
                    </span>
                    <h3 id="push-modal-title" class="text-base font-bold text-slate-900 mt-2">
                        Aktifkan Notifikasi Layanan
                    </h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        Tetap terhubung dengan terapis Anda. Dapatkan kabar seketika saat terapis mulai jalan ke lokasi, tiba di alamat, dan mengirim pesan.
                    </p>
                </div>

                <!-- Poin Manfaat untuk Pelanggan -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-left space-y-2 text-xs text-slate-700">
                    <div class="flex items-start space-x-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px] mt-0.5">✓</span>
                        <span><strong>Status OTW & Tiba:</strong> Tahu pasti saat terapis bergerak menuju lokasi.</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px] mt-0.5">✓</span>
                        <span><strong>Pesan Chat Masuk:</strong> Komunikasi langsung saat terapis berada di perjalanan.</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px] mt-0.5">✓</span>
                        <span><strong>Konfirmasi Pembayaran:</strong> Bukti top-up dan update saldo dompet Anda.</span>
                    </div>
                </div>
            @endif

            <!-- Tombol Aksi -->
            <div class="space-y-2 pt-1">
                <button type="button" 
                        @click="requestPush()"
                        :disabled="isSubscribing"
                        class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-[#064e3b] hover:bg-emerald-900 active:scale-[0.99] text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-75">
                    <template x-if="!isSubscribing">
                        <span class="flex items-center space-x-1.5">
                            <span>Izinkan Notifikasi</span>
                            <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </template>
                    <template x-if="isSubscribing">
                        <span class="flex items-center space-x-2">
                            <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>Mengaktifkan...</span>
                        </span>
                    </template>
                </button>

                <button type="button" 
                        @click="dismiss()"
                        class="w-full min-h-[42px] py-2 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 active:scale-[0.99] font-semibold text-xs transition cursor-pointer">
                    Nanti Saja
                </button>
            </div>
        </div>

        <!-- TAMPILAN 2: STATUS DENIED (DIBLOKIR DI PENGATURAN BROWSER) -->
        <div x-show="permissionStatus === 'denied'" class="space-y-4 pt-1">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center mx-auto ring-8 ring-amber-50/70 shadow-2xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                    Izin Diblokir di Peramban
                </span>
                <h3 class="text-base font-bold text-slate-900 mt-2">
                    Notifikasi Dinonaktifkan
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                    Peramban Anda saat ini memblokir izin notifikasi untuk situs ini.
                </p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-left space-y-2 text-xs text-slate-700 leading-relaxed">
                <p class="font-semibold text-slate-900 text-[11px]">Cara mengaktifkan kembali:</p>
                <ol class="list-decimal list-inside space-y-1 text-[11px] text-slate-600">
                    <li>Klik ikon gembok / setelan di bilah alamat browser.</li>
                    <li>Ubah izin <strong>Notifikasi</strong> menjadi <strong>Izinkan (Allow)</strong>.</li>
                    <li>Muat ulang (*refresh*) halaman ini.</li>
                </ol>
            </div>

            <button type="button" 
                    @click="dismiss()"
                    class="w-full min-h-[44px] py-2.5 px-4 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-[0.99] font-bold text-xs transition cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>

<script>
    function pwaPushModal() {
        return {
            showModal: false,
            isSubscribing: false,
            isSupported: ('Notification' in window) && ('serviceWorker' in navigator),
            permissionStatus: ('Notification' in window) ? Notification.permission : 'unsupported',

            init() {
                if (!this.isSupported) {
                    return;
                }

                this.permissionStatus = Notification.permission;

                // Tampilkan otomatis hanya jika izin masih 'default' dan belum pernah ditutup sesi ini
                if (this.permissionStatus === 'default' && !sessionStorage.getItem('pijatin_pwa_push_dismissed')) {
                    setTimeout(() => {
                        if (this.permissionStatus === 'default' && !sessionStorage.getItem('pijatin_pwa_push_dismissed')) {
                            this.showModal = true;
                        }
                    }, 1200);
                }
            },

            openModal() {
                this.permissionStatus = ('Notification' in window) ? Notification.permission : 'unsupported';
                this.showModal = true;
            },

            dismiss() {
                sessionStorage.setItem('pijatin_pwa_push_dismissed', '1');
                this.showModal = false;
            },

            handlePermissionChange(status) {
                this.permissionStatus = status;
                if (status === 'granted') {
                    this.showModal = false;
                }
            },

            async requestPush() {
                this.isSubscribing = true;
                try {
                    let perm = 'unsupported';
                    if (window.PwaPushNotification) {
                        perm = await window.PwaPushNotification.requestPermission();
                    } else if ('Notification' in window) {
                        perm = await Notification.requestPermission();
                    }

                    this.permissionStatus = perm;

                    if (perm === 'granted') {
                        this.showModal = false;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                iconHtml: '✓',
                                title: 'Notifikasi Layar Aktif',
                                text: 'Pemberitahuan instan kini aktif untuk pesanan dan pembaruan layanan Anda.',
                                confirmButtonText: 'Siap',
                                buttonsStyling: false,
                                customClass: {
                                    popup: 'swal-admin-theme',
                                    icon: 'swal2-success',
                                    confirmButton: 'swal2-confirm'
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.error('Gagal meminta izin push notifikasi:', e);
                } finally {
                    this.isSubscribing = false;
                }
            }
        };
    }
</script>
@endauth
