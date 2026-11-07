<x-pwa-layout title="Dompet Saldo - Pijat.In" :hideHeader="true">
    @php
        $user = auth()->user();
    @endphp

    <div class="-mx-4 -mt-3 -mb-8 flex flex-col"
         x-data="{
            activeTab: 'semua'
         }">

        <!-- Fixed Top Bar Navigasi (Selalu Mengambang di Atas) -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ route('pelanggan.dashboard') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Kembali ke Beranda"
               aria-label="Kembali ke Beranda">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                Dompet Digital
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

        <!-- 1. FIXED GREEN HERO CONTAINER (FULL SAMPAI BAWAH) -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white pt-16 pb-10 px-5 overflow-hidden">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            <!-- Saldo & Aksi Cepat di Tengah (Clean & Minimalist, Tanpa Kartu Rekening) -->
            <div class="relative z-10 flex flex-col items-center text-center pt-2 sm:pt-4">

                <!-- Judul Saldo Dompet -->
                <div>
                    <h2 class="text-xs font-black text-emerald-100 uppercase tracking-widest drop-shadow-xs">
                        Saldo Dompet Anda
                    </h2>
                </div>

                <!-- Nominal Saldo Besar (Gaya Livin' Minimalis) -->
                <div class="mt-2">
                    <div class="text-4xl sm:text-5xl font-black text-white tracking-tight font-mono drop-shadow-sm">
                        {{ $wallet->formatted_balance }}
                    </div>

                    @if (($wallet->held_balance ?? 0) > 0)
                        <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-amber-400/20 backdrop-blur-md border border-amber-300/30 text-xs font-semibold text-amber-200">
                            <span>Saldo Tertahan: Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-0.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] font-semibold text-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                            <span>Saldo Tersedia</span>
                        </div>
                    @endif
                </div>

                <!-- 2 Tombol Bulat Putih Khas Livin' (Top-up & Tarik Saldo) -->
                <div class="flex items-center justify-center space-x-10 mt-6">
                    <!-- Tombol 1: Isi Saldo (Top-up) -->
                    <div class="flex flex-col items-center">
                        <a href="{{ route('pelanggan.wallet.topup') }}"
                           class="w-14 h-14 rounded-full bg-white text-emerald-800 flex items-center justify-center shadow-xl hover:bg-emerald-50 active:scale-95 transition cursor-pointer"
                           title="Isi Saldo (Top-up)"
                           aria-label="Isi Saldo">
                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </a>
                        <span class="text-xs font-bold text-white mt-2 tracking-tight">Top-up</span>
                    </div>

                    <!-- Tombol 2: Tarik Saldo (Transfer / Tarik Tunai) -->
                    <div class="flex flex-col items-center">
                        <a href="{{ route('pelanggan.wallet.withdraw') }}"
                           class="w-14 h-14 rounded-full bg-white text-emerald-800 flex items-center justify-center shadow-xl hover:bg-emerald-50 active:scale-95 transition cursor-pointer"
                           title="Tarik Saldo"
                           aria-label="Tarik Saldo">
                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        <span class="text-xs font-bold text-white mt-2 tracking-tight">Tarik Saldo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spacer Tinggi Kartu Hijau di Awal -->
        <div class="h-[295px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET (HANYA INI YANG IKUT NAIK SAAT SCROLL) -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-3 pb-32 shadow-2xl flex-1 min-h-[calc(100vh-120px)] space-y-4">
            <!-- Sheet Drag Handle Bar -->
            <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto"></div>

            <!-- Status Alert Messages -->
            @if (session('status'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Riwayat Pengajuan Penarikan Dana (Jika Ada) -->
            @if (isset($withdrawalRequests) && $withdrawalRequests->isNotEmpty())
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Pengajuan Penarikan</h4>
                        <span class="text-[11px] font-bold text-slate-500 font-mono">{{ $withdrawalRequests->count() }} Pengajuan</span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($withdrawalRequests as $wd)
                            <div class="p-3 rounded-xl bg-white border border-slate-100 text-xs flex items-center justify-between">
                                <div>
                                    <div class="font-mono text-[11px] font-bold text-slate-700">{{ $wd->withdrawal_code }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $wd->bank_name }} • {{ $wd->created_at->format('d M Y') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-slate-900 font-mono text-xs">Rp {{ number_format($wd->amount, 0, ',', '.') }}</div>
                                    @if ($wd->isPending())
                                        <span class="text-[10px] font-bold text-amber-700">Diproses</span>
                                    @elseif ($wd->isApproved())
                                        <span class="text-[10px] font-bold text-emerald-700">Dicairkan</span>
                                    @else
                                        <span class="text-[10px] font-bold text-rose-700">Ditolak</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Header Transaksi & Filter Kategori (Gaya Livin') -->
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900 tracking-tight">
                        Transaksi
                    </h3>
                    <span class="text-xs font-bold text-emerald-700">
                        {{ $transactions->total() }} Mutasi
                    </span>
                </div>

                <!-- Tabs Filter Transaksi (Gaya Underline Livin') -->
                <div class="flex items-center space-x-6 border-b border-slate-200 text-xs font-bold overflow-x-auto no-scrollbar">
                    <button type="button" 
                            @click="activeTab = 'semua'"
                            class="pb-2.5 transition relative cursor-pointer"
                            :class="activeTab === 'semua' ? 'text-emerald-800 font-black border-b-2 border-emerald-700' : 'text-slate-400 hover:text-slate-600'">
                        Semua
                    </button>
                    <button type="button" 
                            @click="activeTab = 'bulan_ini'"
                            class="pb-2.5 transition relative cursor-pointer"
                            :class="activeTab === 'bulan_ini' ? 'text-emerald-800 font-black border-b-2 border-emerald-700' : 'text-slate-400 hover:text-slate-600'">
                        Bulan Ini
                    </button>
                    <button type="button" 
                            @click="activeTab = 'masuk'"
                            class="pb-2.5 transition relative cursor-pointer"
                            :class="activeTab === 'masuk' ? 'text-emerald-800 font-black border-b-2 border-emerald-700' : 'text-slate-400 hover:text-slate-600'">
                        Masuk
                    </button>
                    <button type="button" 
                            @click="activeTab = 'keluar'"
                            class="pb-2.5 transition relative cursor-pointer"
                            :class="activeTab === 'keluar' ? 'text-emerald-800 font-black border-b-2 border-emerald-700' : 'text-slate-400 hover:text-slate-600'">
                        Keluar
                    </button>
                </div>
            </div>

            <!-- Daftar Transaksi Dikelompokkan per Tanggal (Gaya Livin') -->
            @if ($transactions->isEmpty())
                <div class="text-center py-12 px-4 space-y-3">
                    <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-700 mx-auto flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-black text-xs text-slate-900">Belum Ada Riwayat Transaksi</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">
                            Isi saldo dompet Anda untuk kemudahan pembayaran instan tanpa uang tunai.
                        </p>
                    </div>
                </div>
            @else
                @php
                    $grouped = $transactions->groupBy(function($item) {
                        return $item->created_at->translatedFormat('d M Y');
                    });
                @endphp

                <div class="space-y-5 pt-1">
                    @foreach ($grouped as $dateLabel => $items)
                        <div class="space-y-2.5"
                             x-show="activeTab === 'semua' || 
                                     (activeTab === 'bulan_ini' && '{{ $items->first()->created_at->format('Y-m') }}' === '{{ now()->format('Y-m') }}') ||
                                     (activeTab === 'masuk' && {{ $items->contains(fn($i) => $i->isCredit()) ? 'true' : 'false' }}) ||
                                     (activeTab === 'keluar' && {{ $items->contains(fn($i) => !$i->isCredit()) ? 'true' : 'false' }})">
                            
                            <!-- Header Tanggal Transaksi (Gaya Livin') -->
                            <div class="text-[11px] font-bold text-slate-400 tracking-wider">
                                {{ $dateLabel }}
                            </div>

                            <!-- List Item Transaksi pada Tanggal Tersebut -->
                            <div class="space-y-3">
                                @foreach ($items as $tx)
                                    <div class="flex items-start justify-between py-1 transition"
                                         x-show="activeTab === 'semua' || 
                                                 (activeTab === 'bulan_ini' && '{{ $tx->created_at->format('Y-m') }}' === '{{ now()->format('Y-m') }}') ||
                                                 (activeTab === 'masuk' && {{ $tx->isCredit() ? 'true' : 'false' }}) ||
                                                 (activeTab === 'keluar' && {{ !$tx->isCredit() ? 'true' : 'false' }})">
                                        
                                        <!-- Icon Bulat Outline & Keterangan Transaksi -->
                                        <div class="flex items-start space-x-3.5 min-w-0 pr-3">
                                            @if ($tx->isCredit())
                                                <!-- Icon Transfer Masuk / Top-up (Lingkaran Panah Emerald) -->
                                                <div class="w-9 h-9 rounded-full border-2 border-emerald-600 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <!-- Icon Pembayaran / Penarikan Keluar (Lingkaran Titik/Panah Slate) -->
                                                <div class="w-9 h-9 rounded-full border-2 border-slate-400 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                    </svg>
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <h4 class="text-xs font-black text-slate-900 leading-snug">
                                                    {{ $tx->isCredit() ? 'Top-up Saldo' : 'Pembayaran / Tarik' }}
                                                </h4>
                                                <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                                    {{ $tx->description }}
                                                </p>
                                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                                    {{ $tx->created_at->format('H:i') }} WIB
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Nominal Transaksi (Gaya Livin') -->
                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-black font-mono block {{ $tx->isCredit() ? 'text-emerald-600' : 'text-slate-900' }}">
                                                {{ $tx->formatted_amount }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono block mt-0.5">
                                                Sisa: Rp {{ number_format((float) $tx->balance_after, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="pt-4">
                    {{ $transactions->links() }}
                </div>
            @endif

        </div>

    </div>
</x-pwa-layout>
