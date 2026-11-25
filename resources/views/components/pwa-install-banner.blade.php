@props(['shouldHideDock' => false])

<div x-data="pwaInstallBanner()"
     x-show="showBanner"
     x-cloak
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed left-3 right-3 z-40 max-w-md mx-auto {{ $shouldHideDock ? 'bottom-4' : 'bottom-20' }}"
     style="display: none;"
     role="region"
     aria-label="Ajakan Memasang Aplikasi">

    <div class="bg-white/95 backdrop-blur-md rounded-2xl p-3 border border-slate-200/90 shadow-xl flex items-center justify-between gap-2.5">
        <!-- Logo & Keterangan -->
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h4 class="text-xs font-bold text-slate-900 leading-tight truncate">Pasang Aplikasi Pijat.In</h4>
                <p class="text-[11px] text-slate-500 leading-tight truncate mt-0.5">Akses cepat & pantau dari layar HP</p>
            </div>
        </div>

        <!-- Tombol Aksi Pasang & Tutup -->
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" @click="install()"
                    class="py-1.5 px-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                Pasang
            </button>
            <button type="button" @click="dismiss()"
                    class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer"
                    title="Nanti saja" aria-label="Tutup ajakan pasang aplikasi">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    function pwaInstallBanner() {
        return {
            showBanner: false,
            isStandalone: false,

            init() {
                // Deteksi apakah sedang dibuka dalam mode aplikasi mandiri (PWA standalone)
                this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
                if (this.isStandalone) {
                    return;
                }

                // Cek apakah pengguna baru saja menutup banner (cooldown 7 hari)
                const dismissedUntil = localStorage.getItem('pijatin_pwa_install_dismissed_until');
                if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
                    return;
                }

                // Dengarkan jika aplikasi berhasil terpasang
                window.addEventListener('pwa-installed', () => {
                    this.isStandalone = true;
                    this.showBanner = false;
                });

                // Tampilkan secara halus setelah jeda 2 detik (agar tidak mengganggu render awal)
                setTimeout(() => {
                    if (!this.isStandalone) {
                        this.showBanner = true;
                    }
                }, 2000);
            },

            dismiss() {
                this.showBanner = false;
                // Jangan tampilkan lagi selama 7 hari ke depan
                localStorage.setItem('pijatin_pwa_install_dismissed_until', Date.now() + (7 * 24 * 60 * 60 * 1000));
            },

            async install() {
                if (typeof pwaInstallTrigger === 'function') {
                    const trigger = pwaInstallTrigger();
                    await trigger.triggerInstall();
                }
            }
        };
    }
</script>
