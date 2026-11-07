<x-pwa-layout title="Keamanan Kata Sandi - Pijat.In" :hideHeader="true">
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
                Keamanan Akun
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
                <div class="w-14 h-14 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center border border-white/20 shadow-lg mb-2">
                    <svg class="w-7 h-7 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-black text-white tracking-tight">Perbarui Kata Sandi</h2>
                <p class="text-xs text-emerald-100/90 mt-0.5 max-w-xs">Gunakan kata sandi kuat untuk menjaga akun Anda</p>
            </div>
        </div>

        <!-- Spacer Tinggi Hero -->
        <div class="h-[148px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-6 pb-24 shadow-2xl flex-1 min-h-[calc(100vh-148px)] space-y-4">
            @if (session('status') === 'password-updated')
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Kata sandi Anda berhasil diperbarui.</span>
                </div>
            @endif

            <div class="bg-white rounded-2xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

    </div>
</x-pwa-layout>
