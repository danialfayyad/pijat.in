<x-pwa-layout title="Hapus Akun - Pijat.In" :hideHeader="true">
    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen">

        <!-- Fixed Top Bar Navigasi (Selalu Mengambang di Atas) -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ route('profile.edit') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
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
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Pusat Bantuan"
               aria-label="Pusat Bantuan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </div>

        <!-- 1. FIXED GREEN HERO BACKGROUND DENGAN IKON & PENGANTAR -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white pt-16 px-5 overflow-hidden pointer-events-none">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            <!-- Ikon & Teks Pengantar Contextual Hero -->
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-rose-500/20 backdrop-blur-md text-rose-200 flex items-center justify-center border border-rose-300/30 shadow-lg mb-2">
                    <svg class="w-7 h-7 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h2 class="text-sm font-black text-white tracking-tight">Hapus Akun Permanen</h2>
                <p class="text-xs text-rose-100/90 mt-0.5 max-w-xs">Seluruh data profil dan riwayat pesanan akan dihapus permanen</p>
            </div>
        </div>

        <!-- Spacer Tinggi Hero -->
        <div class="h-[148px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-6 pb-24 shadow-2xl flex-1 min-h-[calc(100vh-148px)] space-y-4">
            <div class="bg-white rounded-2xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>
</x-pwa-layout>
