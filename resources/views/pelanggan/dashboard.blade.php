<x-pwa-layout title="Beranda Pelanggan - Pijat.In" headerTitle="Pijat.In">
    <!-- Greeting & Header Card -->
    <div class="py-2 space-y-4">

        <!-- User Greeting & Location Badge -->
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-medium">Selamat Datang,</span>
                <h2 class="text-lg font-black text-slate-900 leading-tight">{{ auth()->user()->name }}</h2>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    Kota Bandung
                </span>
            </div>
        </div>

        <!-- Wallet Card -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-white/10 blur-xl"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-[11px] font-semibold text-emerald-100 uppercase tracking-wider block">Saldo Dompet Pijat.In</span>
                    <div class="text-2xl font-black tracking-tight mt-0.5">Rp 0</div>
                </div>
                <button class="px-3.5 py-1.5 rounded-xl bg-white text-emerald-800 text-xs font-bold shadow-md hover:bg-emerald-50 transition flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Isi Saldo</span>
                </button>
            </div>

            <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between text-[11px] text-emerald-100/90 relative z-10">
                <span>Alamat Utama: <strong>Jl. Asia Afrika</strong></span>
                <span class="text-[10px] bg-emerald-800/60 px-2 py-0.5 rounded-md font-mono">Bebas Biaya Admin</span>
            </div>
        </div>

        <!-- Strict Gender Safety Banner (DEC-05 / BRD Rule) -->
        <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-3.5 flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="text-xs">
                <span class="font-bold text-emerald-900 block leading-tight">Protokol Keamanan Sesama Gender</span>
                <p class="text-[11px] text-emerald-700 leading-snug mt-0.5">Terapis yang melayani Anda dipastikan bergender sama demi kenyamanan & keamanan maksimal.</p>
            </div>
        </div>

        <!-- Layanan Pijat Pilihan -->
        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Katalog Layanan</h3>
                <span class="text-[11px] font-semibold text-emerald-600">Tarif Tetap</span>
            </div>

            <!-- Service Item 1 -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between hover:border-emerald-300 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg shadow-inner">
                        💆
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Pijat Tradisional Tubuh</h4>
                        <p class="text-[11px] text-slate-500">Meredakan pegal, lelah & melancarkan sirkulasi</p>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mt-1 inline-block">60 / 90 / 120 Menit</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-slate-900 block">Rp 100.000</span>
                    <button class="mt-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition">
                        Pesan
                    </button>
                </div>
            </div>

            <!-- Service Item 2 -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between hover:border-emerald-300 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-lg shadow-inner">
                        🦶
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Refleksi Kaki & Tangan</h4>
                        <p class="text-[11px] text-slate-500">Titik akupresur untuk relaksasi organ vital</p>
                        <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md mt-1 inline-block">60 Menit</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-slate-900 block">Rp 80.000</span>
                    <button class="mt-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition">
                        Pesan
                    </button>
                </div>
            </div>

            <!-- Service Item 3 -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between hover:border-emerald-300 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg shadow-inner">
                        🌿
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Pijat Aromaterapi Relaksasi</h4>
                        <p class="text-[11px] text-slate-500">Essential oil alami menenangkan pikiran & otot</p>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md mt-1 inline-block">90 Menit</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-slate-900 block">Rp 140.000</span>
                    <button class="mt-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-xs transition">
                        Pesan
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-pwa-layout>
