<x-pwa-layout title="Dompet Saldo - Pijat.In" :fullBleed="true" :hideHeader="true">
    @php
        $user = auth()->user();
    @endphp

    <div class="h-full flex flex-col bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] overflow-hidden">

        <!-- 1. FIXED GREEN HERO HEADER -->
        <div class="relative shrink-0 text-white pt-4 pb-6 px-5 overflow-hidden">
            <!-- Top Bar Navigasi -->
            <div class="relative z-10 flex items-center justify-between mb-4">
                <a href="{{ route('pelanggan.dashboard') }}"
                   class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                   title="Kembali ke Beranda"
                   aria-label="Kembali ke Beranda">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <span class="text-xs font-bold text-white uppercase tracking-widest">
                    Dompet Digital
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

            <!-- Saldo & Aksi Cepat di Tengah -->
            <div class="relative z-10 flex flex-col items-center text-center">
                <h2 class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">
                    Saldo Dompet Anda
                </h2>

                <div class="mt-1.5">
                    <div class="text-3xl sm:text-4xl font-bold text-white tracking-tight font-mono">
                        {{ $wallet->formatted_balance }}
                    </div>

                    @if (($wallet->held_balance ?? 0) > 0)
                        <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/30 text-xs font-semibold text-amber-200">
                            <span>Saldo Tertahan: Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-0.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-medium text-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                            <span>Saldo Tersedia</span>
                        </div>
                    @endif
                </div>

                <!-- 2 Tombol Aksi (Top-up & Tarik Saldo) -->
                <div class="flex items-center justify-center space-x-10 mt-5">
                    <div class="flex flex-col items-center">
                        <a href="{{ route('pelanggan.wallet.topup') }}"
                           class="w-12 h-12 rounded-full bg-white text-emerald-800 flex items-center justify-center shadow-md hover:bg-emerald-50 active:scale-95 transition cursor-pointer"
                           title="Isi Saldo (Top-up)"
                           aria-label="Isi Saldo">
                            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </a>
                        <span class="text-xs font-semibold text-white mt-1.5">Top-up</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <a href="{{ route('pelanggan.wallet.withdraw') }}"
                           class="w-12 h-12 rounded-full bg-white text-emerald-800 flex items-center justify-center shadow-md hover:bg-emerald-50 active:scale-95 transition cursor-pointer"
                           title="Tarik Saldo"
                           aria-label="Tarik Saldo">
                            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        <span class="text-xs font-semibold text-white mt-1.5">Tarik Saldo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. FIXED TRANSACTION CONTAINER (HANYA ISI DAFTAR YANG SCROLL) -->
        <div class="flex-1 min-h-0 flex flex-col bg-white rounded-t-3xl shadow-lg overflow-hidden">
            <!-- Fixed Header Transaksi & Filter Tabs -->
            <div class="shrink-0 px-5 pt-4 space-y-3 bg-white">
                <!-- Status Alert Messages -->
                @if (session('status'))
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold flex items-center space-x-2">
                        <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-baseline space-x-2">
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Transaksi</h3>
                        <span class="text-xs font-medium text-slate-400">({{ $transactions->total() }})</span>
                    </div>

                    <!-- Popover Filter Jenis Transaksi -->
                    @php
                        $typeOptions = [
                            ['key' => 'semua', 'label' => 'Semua Jenis'],
                            ['key' => 'masuk', 'label' => 'Masuk'],
                            ['key' => 'keluar', 'label' => 'Keluar'],
                            ['key' => 'penarikan', 'label' => 'Penarikan'],
                        ];
                        $activeTypeLabel = collect($typeOptions)->firstWhere('key', $selectedType)['label'] ?? 'Semua Jenis';
                    @endphp
                    <div x-data="{ openTypeMenu: false }" @click.outside="openTypeMenu = false" @keydown.escape.window="openTypeMenu = false" class="relative shrink-0">
                        <button type="button"
                                @click="openTypeMenu = !openTypeMenu"
                                aria-label="Filter jenis transaksi"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl px-3 py-1.5 transition cursor-pointer">
                            <span>{{ $activeTypeLabel }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150" :class="openTypeMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="openTypeMenu"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             style="display: none;"
                             class="absolute right-0 mt-1.5 w-44 bg-white rounded-2xl border border-slate-200/90 shadow-lg py-1.5 z-40 origin-top-right">
                            @foreach ($typeOptions as $opt)
                                @php
                                    $isOptActive = $selectedType === $opt['key'];
                                @endphp
                                <a href="{{ route('pelanggan.wallet.index', ['month' => $selectedMonth, 'type' => $opt['key']]) }}"
                                   class="px-3.5 py-2 text-xs flex items-center justify-between gap-2 transition {{ $isOptActive ? 'bg-emerald-50/70 text-emerald-800 font-semibold' : 'text-slate-700 hover:bg-slate-50 font-medium' }}">
                                    <span>{{ $opt['label'] }}</span>
                                    @if ($isOptActive)
                                        <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Tabs Filter Per Bulan -->
                <div class="flex items-center space-x-5 border-b border-slate-200 text-xs font-semibold overflow-x-auto no-scrollbar">
                    @foreach ($monthOptions as $monthOpt)
                        <a href="{{ route('pelanggan.wallet.index', ['month' => $monthOpt['value'], 'type' => $selectedType]) }}"
                           class="pb-2.5 transition whitespace-nowrap shrink-0 {{ $selectedMonth === $monthOpt['value'] ? 'text-emerald-800 font-bold border-b-2 border-emerald-700' : 'text-slate-400 hover:text-slate-600' }}">
                            {{ $monthOpt['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Scrollable Transaction Body -->
            <div class="flex-1 overflow-y-auto px-5 pt-3 pb-6 space-y-4 overscroll-contain">
                <!-- Riwayat Pengajuan Penarikan Dana (Tampil pada filter Semua atau Penarikan) -->
                @if (in_array($selectedType, ['semua', 'penarikan'], true) && isset($withdrawalRequests) && $withdrawalRequests->isNotEmpty())
                    <div class="space-y-2.5 pt-1">
                        <div class="text-[11px] font-bold text-slate-400 tracking-wider">
                            Pengajuan Penarikan
                        </div>
                        <div class="space-y-3">
                            @foreach ($withdrawalRequests as $wd)
                                @php
                                    if ($wd->isPending()) {
                                        $wdBorder = 'border-amber-500 text-amber-600';
                                        $wdStatusText = 'Menunggu';
                                        $wdStatusColor = 'text-amber-700';
                                    } elseif ($wd->isApproved()) {
                                        $wdBorder = 'border-emerald-600 text-emerald-600';
                                        $wdStatusText = 'Dicairkan';
                                        $wdStatusColor = 'text-emerald-700';
                                    } else {
                                        $wdBorder = 'border-rose-500 text-rose-600';
                                        $wdStatusText = 'Ditolak';
                                        $wdStatusColor = 'text-rose-700';
                                    }
                                @endphp
                                <div class="flex items-start justify-between py-1">
                                    <div class="flex items-start space-x-3.5 min-w-0 pr-3">
                                        <div class="w-9 h-9 rounded-full border-2 {{ $wdBorder }} flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-slate-900 leading-snug truncate">
                                                Penarikan ke {{ $wd->bank_name }}
                                            </h4>
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                {{ $wd->created_at->translatedFormat('d M Y, H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-xs font-bold text-slate-900 font-mono">
                                            Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                        </div>
                                        <span class="text-[11px] font-semibold {{ $wdStatusColor }}">
                                            {{ $wdStatusText }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            <!-- Daftar Transaksi Dikelompokkan per Tanggal -->
            @if ($transactions->isEmpty() && (! in_array($selectedType, ['semua', 'penarikan'], true) || empty($withdrawalRequests) || $withdrawalRequests->isEmpty()))
                <div class="text-center py-12 px-4 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-700 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Belum Ada Riwayat Transaksi</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">
                            Tidak ada mutasi saldo untuk filter yang dipilih.
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
                        <div class="space-y-2.5">
                            <!-- Header Tanggal Transaksi -->
                            <div class="text-[11px] font-bold text-slate-400 tracking-wider">
                                {{ $dateLabel }}
                            </div>

                            <!-- List Item Transaksi pada Tanggal Tersebut -->
                            <div class="space-y-3">
                                @foreach ($items as $tx)
                                    <div class="flex items-start justify-between py-1 transition">
                                        
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
                                                <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                                    {{ $tx->isCredit() ? 'Top-up Saldo' : ($tx->reference_type === 'customer_withdrawal' ? 'Penarikan Saldo' : 'Pembayaran') }}
                                                </h4>
                                                <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                                    {{ $tx->description }}
                                                </p>
                                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                                    {{ $tx->created_at->format('H:i') }} WIB
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Nominal Transaksi -->
                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-bold font-mono block {{ $tx->isCredit() ? 'text-emerald-600' : 'text-slate-900' }}">
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

    </div>
</x-pwa-layout>
