@auth
<div x-data="pwaPushModal()"
     x-show="showModal"
     x-cloak
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @open-pwa-push-modal.window="openModal()"
     @pwa-push-permission-changed.window="handlePermissionChange($event.detail)"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
     style="display: none;"
     role="dialog"
     aria-modal="true"
     aria-labelledby="push-modal-title">

    <div @click.away="dismiss()"
         class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl border border-slate-200/90 space-y-4">
        
        <!-- Icon Notifikasi dengan Pulse & Ring (Matching Mitra GPS Style) -->
        <div class="relative mx-auto w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center ring-8 ring-emerald-50">
            <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="absolute top-0 right-0 w-4 h-4 rounded-full bg-emerald-600 border-2 border-white flex items-center justify-center text-[9px] text-white font-bold">!</span>
        </div>

        <div>
            <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wide mb-1.5">
                Pelanggan: Notifikasi Layanan
            </p>
            <h3 id="push-modal-title" class="text-base font-extrabold text-slate-900 leading-snug">
                Aktifkan Notifikasi Pesanan
            </h3>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                Dapatkan pembaruan langsung saat terapis bergerak menuju lokasi Anda, tiba di alamat, dan mengirim pesan konfirmasi.
            </p>
        </div>

        <!-- Panduan jika izin diblokir di browser -->
        <div x-show="permissionStatus === 'denied'" class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-left text-[11px] text-amber-950 space-y-1">
            <p class="font-bold flex items-center space-x-1">
                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Izin Notifikasi Diblokir di Browser</span>
            </p>
            <ol class="list-decimal pl-4 space-y-0.5 text-amber-900 text-[10px] leading-relaxed">
                <li>Klik ikon <strong>Gembok / Setelan Situs</strong> di sebelah kiri address bar.</li>
                <li>Ubah opsi <strong>Notifikasi</strong> menjadi <strong>Izinkan (Allow)</strong>.</li>
                <li>Muat ulang halaman ini.</li>
            </ol>
        </div>

        <!-- Action Buttons (Matching Mitra GPS Style) -->
        <div class="space-y-2 pt-1">
            <button type="button" 
                    @click="requestPush()" 
                    :disabled="isSubscribing"
                    class="w-full min-h-[46px] py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-75">
                <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-text="isSubscribing ? 'Memproses Izin...' : 'Aktifkan Izin Notifikasi'"></span>
            </button>

            <button type="button" 
                    @click="dismiss()" 
                    class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                <span x-text="permissionStatus === 'denied' ? 'Tutup' : 'Nanti Saja'"></span>
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
                if (this.isSubscribing) return;
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
                                title: 'Notifikasi Layanan Aktif',
                                text: 'Pemberitahuan instan kini aktif untuk pembaruan pesanan dan pesan terapis Anda.',
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
