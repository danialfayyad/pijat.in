<x-pwa-layout title="Saldo & Keuangan - Mitra Pijat.In" :fullBleed="true" :hideHeader="true">
    @php
        $user = auth()->user();
    @endphp

    <div class="h-full flex flex-col bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] overflow-hidden">

        <!-- 1. FIXED GREEN HERO HEADER (SESUAI DESAIN CUSTOMER) -->
        <div class="relative shrink-0 text-white pt-4 pb-6 px-5 overflow-hidden">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 300" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 300 L-20 300 Z" fill="white" opacity="0.3" />
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 300 L-20 300 Z" fill="white" opacity="0.4" />
                </svg>
            </div>

            <!-- Top Bar Navigasi -->
            <div class="relative z-10 flex items-center justify-between mb-3">
                <div class="w-10 h-10" aria-hidden="true"></div>

                <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                    Saldo & Keuangan
                </span>

                <a href="{{ route('mitra.help.index') }}"
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
                    Saldo Tersedia
                </h2>

                <div class="mt-1">
                    <div class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight font-mono drop-shadow-sm">
                        Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                    </div>

                    @if (($wallet->held_balance ?? 0) > 0)
                        <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/30 text-xs font-semibold text-amber-200">
                            <span>Saldo Tertahan (24 Jam): Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="mt-2 inline-flex items-center space-x-1.5 px-3 py-0.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-medium text-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                            <span>Saldo Tersedia</span>
                        </div>
                    @endif
                </div>

                <!-- 2 Tombol Aksi Bulat Bersih (Tarik Saldo & Rekening Bank) -->
                <div class="flex items-center justify-center space-x-12 mt-5">
                    <div class="flex flex-col items-center">
                        <a href="{{ route('mitra.finance.withdraw') }}"
                           class="w-12 h-12 rounded-full bg-white text-emerald-800 flex items-center justify-center shadow-md hover:bg-emerald-50 active:scale-95 transition cursor-pointer"
                           title="Tarik Saldo"
                           aria-label="Tarik Saldo">
                            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        <span class="text-xs font-semibold text-white mt-1.5">Tarik Saldo</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <a href="{{ route('mitra.profile.bank') }}"
                           class="w-12 h-12 rounded-full bg-white text-emerald-800 flex items-center justify-center shadow-md hover:bg-emerald-50 active:scale-95 transition cursor-pointer"
                           title="Rekening Bank"
                           aria-label="Rekening Bank">
                            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </a>
                        <span class="text-xs font-semibold text-white mt-1.5">Rekening Bank</span>
                        @if ($bankAccount)
                            <span class="sr-only">{{ $bankAccount->bank_name }} {{ $bankAccount->account_number }}</span>
                        @endif
                    </div>
                </div>

                <!-- Performa Keuangan Ribbon -->
                <div class="w-full max-w-xs mt-5 pt-3.5 border-t border-white/15 grid grid-cols-2 divide-x divide-white/15 text-center text-white">
                    <div class="px-2">
                        <div class="text-xs sm:text-sm font-bold font-mono">Rp {{ number_format($currentMonthIncome, 0, ',', '.') }}</div>
                        <div class="text-[10px] text-emerald-100 font-medium mt-0.5">Pendapatan Bulan Ini</div>
                    </div>
                    <div class="px-2">
                        <div class="text-xs sm:text-sm font-bold font-mono">Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}</div>
                        <div class="text-[10px] text-emerald-100 font-medium mt-0.5">Total Dicairkan</div>
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
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-baseline space-x-2">
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Transaksi</h3>
                        @if ($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <span class="text-xs font-medium text-slate-400">({{ $transactions->total() }})</span>
                        @endif
                    </div>

                    <!-- Popover Filter Jenis Transaksi (Customer Style) -->
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
                                <a href="{{ route('mitra.finance.index', array_filter(['month' => $selectedMonth === 'semua' ? null : $selectedMonth, 'type' => $opt['key'] === 'semua' ? null : $opt['key'], 'range' => $range])) }}"
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

                <!-- Tabs Filter Per Bulan (Customer Style) -->
                <div class="flex items-center space-x-5 border-b border-slate-200 text-xs font-semibold overflow-x-auto no-scrollbar">
                    @foreach ($monthOptions as $monthOpt)
                        @php
                            $isMonthActive = $selectedMonth === $monthOpt['value'];
                        @endphp
                        <a href="{{ route('mitra.finance.index', array_filter(['month' => $monthOpt['value'] === 'semua' ? null : $monthOpt['value'], 'type' => $selectedType === 'semua' ? null : $selectedType])) }}"
                           class="pb-2.5 transition whitespace-nowrap shrink-0 {{ $isMonthActive ? 'text-emerald-800 font-bold border-b-2 border-emerald-700' : 'text-slate-400 hover:text-slate-600' }}">
                            {{ $monthOpt['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Scrollable Transaction Body -->
            <div class="flex-1 overflow-y-auto px-5 pt-3 pb-28 space-y-4 overscroll-contain">

                <!-- Saldo Tertahan (24 Jam Masa Garansi - hanya tampil pada bulan berjalan atau filter Semua) -->
                @if (($selectedMonth === 'semua' || $selectedMonth === now()->format('Y-m')) && ($wallet->held_balance ?? 0) > 0)
                    <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-200/80 shadow-2xs space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-900">Saldo Tertahan (24 Jam)</span>
                            <span class="text-xs font-bold font-mono text-amber-700">Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            Pendapatan pesanan selesai ditahan selama 24 jam sebagai masa jaminan layanan. Saldo akan otomatis dipindahkan ke saldo tersedia setelah 24 jam jika tidak ada aduan aktif.
                        </p>
                        @if ($heldTransactions->isNotEmpty())
                            <div class="pt-2 border-t border-amber-200/60 divide-y divide-amber-200/50">
                                @foreach ($heldTransactions as $ht)
                                    <div class="py-2 flex items-center justify-between text-xs">
                                        <div class="min-w-0 flex-1 mr-2">
                                            <span class="font-semibold text-slate-800 block truncate">{{ $ht->description }}</span>
                                            <span class="text-[10px] text-slate-500 font-medium">Selesai: {{ $ht->created_at->format('d M, H:i') }}</span>
                                        </div>
                                        <span class="font-bold text-amber-700 shrink-0 font-mono">
                                            +Rp {{ number_format($ht->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Riwayat Pengajuan Penarikan (Tampil pada filter Semua atau Penarikan) -->
                @if (in_array($selectedType, ['semua', 'penarikan'], true) && $withdrawalRequests->isNotEmpty())
                    <div class="space-y-2 pt-1">
                        <div class="text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                            Pengajuan Penarikan
                        </div>
                        <div class="space-y-2.5">
                            @foreach ($withdrawalRequests as $wd)
                                @php
                                    if ($wd->isPending()) {
                                        $wdBorder = 'border-amber-500 text-amber-600 bg-amber-50';
                                        $wdStatusText = 'Menunggu Verifikasi';
                                        $wdStatusColor = 'text-amber-700';
                                    } elseif ($wd->isApproved()) {
                                        $wdBorder = 'border-emerald-600 text-emerald-600 bg-emerald-50';
                                        $wdStatusText = 'Dicairkan';
                                        $wdStatusColor = 'text-emerald-700';
                                    } elseif ($wd->isCancelled()) {
                                        $wdBorder = 'border-slate-300 text-slate-400 bg-slate-50';
                                        $wdStatusText = 'Dibatalkan';
                                        $wdStatusColor = 'text-slate-500';
                                    } else {
                                        $wdBorder = 'border-rose-500 text-rose-600 bg-rose-50';
                                        $wdStatusText = 'Ditolak';
                                        $wdStatusColor = 'text-rose-700';
                                    }
                                @endphp
                                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-start justify-between gap-3">
                                    <div class="flex items-start space-x-3.5 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-full border-2 {{ $wdBorder }} flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <h4 class="text-xs font-bold text-slate-900 leading-snug truncate">
                                                    Penarikan ke {{ $wd->bank_name }}
                                                </h4>
                                                <span class="text-[10px] font-mono font-semibold text-slate-400">({{ $wd->withdrawal_code }})</span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                {{ $wd->created_at->translatedFormat('d M Y, H:i') }}
                                            </p>
                                            @if ($wd->isRejected() && $wd->rejection_reason)
                                                <p class="text-[11px] text-rose-600 mt-1 font-medium">Alasan: {{ $wd->rejection_reason }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-xs font-bold text-slate-900 font-mono">
                                            Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                        </div>
                                        <span class="text-[11px] font-semibold {{ $wdStatusColor }} block mt-0.5">
                                            {{ $wdStatusText }}
                                        </span>
                                        @if ($wd->isPending())
                                            <form id="cancel-wd-{{ $wd->id }}" method="POST" action="{{ route('mitra.finance.withdraw.cancel', $wd) }}" class="mt-1">
                                                @csrf
                                                <button type="button" 
                                                        onclick="confirmCancelWithdrawal('cancel-wd-{{ $wd->id }}')"
                                                        class="text-[11px] font-bold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Daftar Mutasi Saldo Dompet (Dikelompokkan per Tanggal) -->
                @if ($transactions->isEmpty() && (! in_array($selectedType, ['semua', 'penarikan'], true) || empty($withdrawalRequests) || $withdrawalRequests->isEmpty()))
                    <div class="text-center py-12 px-4 space-y-3 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
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
                @elseif ($transactions->isNotEmpty())
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
                                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden px-4">
                                    @foreach ($items as $tx)
                                        <div class="py-3.5 flex items-start justify-between gap-3 text-xs">
                                            <!-- Icon Bulat Outline & Keterangan Transaksi -->
                                            <div class="flex items-start space-x-3.5 min-w-0 pr-2 flex-1">
                                                @if ($tx->isCredit())
                                                    <!-- Icon Pendapatan Masuk -->
                                                    <div class="w-9 h-9 rounded-full border-2 border-emerald-600 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <!-- Icon Pengeluaran / Penarikan Keluar -->
                                                    <div class="w-9 h-9 rounded-full border-2 border-slate-400 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                        </svg>
                                                    </div>
                                                @endif

                                                <div class="min-w-0">
                                                    <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                                        @if ($tx->isCredit())
                                                            {{ in_array($tx->reference_type, ['withdrawal_refund'], true) ? 'Pengembalian Penarikan' : 'Pendapatan Layanan' }}
                                                        @else
                                                            {{ in_array($tx->reference_type, ['withdrawal_request', 'partner_withdrawal', 'withdrawal'], true) ? 'Penarikan Saldo' : 'Pengeluaran Saldo' }}
                                                        @endif
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
                    @if ($transactions->hasPages())
                        <div class="pt-2">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif

            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function confirmCancelWithdrawal(formId) {
                const form = document.getElementById(formId);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batalkan Penarikan?',
                        text: 'Apakah Anda yakin ingin membatalkan pengajuan penarikan dana ini?',
                        showCancelButton: true,
                        cancelButtonText: 'Kembali',
                        confirmButtonText: 'Ya, Batalkan',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-danger-theme',
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel',
                        }
                    }).then((result) => {
                        if (result.isConfirmed && form) {
                            form.submit();
                        }
                    });
                } else if (confirm('Apakah Anda yakin ingin membatalkan pengajuan penarikan dana ini?')) {
                    if (form) form.submit();
                }
            }
        </script>
    @endpush
</x-pwa-layout>
