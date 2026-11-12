<x-pwa-layout title="Ajukan Penghapusan Akun - Pijat.In" :hideHeader="true" :fullBleed="true">
    <div class="flex-1 flex flex-col h-full min-h-0 overflow-hidden bg-white">

        <!-- 1. GREEN HERO HEADER (NON-FIXED, BALANCED CENTERED ICON) -->
        <div class="relative bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white px-5 pt-3 pb-6 overflow-hidden shrink-0">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <svg class="w-full h-full" viewBox="0 0 400 300" fill="none" preserveAspectRatio="none">
                    <path d="M-20 40 C80 100 180 0 300 60 C360 90 420 70 450 110 L450 300 L-20 300 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 100 C100 170 220 60 340 150 C380 180 420 160 450 190 L450 300 L-20 300 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            <!-- Top Bar Navigasi -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ route('profile.edit') }}" 
                   class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                   title="Kembali ke Pengaturan Akun"
                   aria-label="Kembali ke Pengaturan Akun">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                    Hapus Akun
                </span>

                <a href="{{ route('pelanggan.help.index') }}" 
                   class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                   title="Pusat Bantuan"
                   aria-label="Pusat Bantuan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </div>

            <!-- Ikon & Teks Pengantar Centered dengan Padding Atas-Bawah Seimbang -->
            <div class="relative z-10 py-3.5 flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center border border-white/20 shadow-lg mb-2">
                    <svg class="w-6 h-6 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-white tracking-tight">Ajukan Penghapusan Akun</h2>
                <p class="text-xs text-emerald-100/90 mt-0.5 max-w-xs">Pengajuan akan diperiksa dan diproses oleh admin</p>
            </div>
        </div>

        <!-- 2. FIXED WHITE CONTENT AREA -->
        <div class="relative z-10 -mt-4 bg-white rounded-t-3xl px-5 pt-5 pb-5 flex-1 min-h-0 overflow-y-auto space-y-3">
            @if (session('status') === 'deletion-requested')
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-semibold flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Pengajuan penghapusan akun berhasil dikirim dan menunggu persetujuan admin.</span>
                </div>
            @endif

            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-pwa-layout>
