<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Monitoring Saldo Mengendap & Settlement') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{ viewMode: 'liability' }">

        <!-- 1. National Aggregate Metric Cards (Clean 4-Card Row) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            <!-- Total Saldo Pelanggan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Saldo Pelanggan</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalCustomerBalance, 0, ',', '.') }}</div>
            </div>

            <!-- Total Saldo Mitra Tersedia -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Saldo Mitra Tersedia</span>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalPartnerBalance, 0, ',', '.') }}</div>
            </div>

            <!-- Total Komisi Platform -->
            @php
                $commPercent = $platformCommissionPercent ?? (float) \App\Models\Parameter::get('platform_commission_percent', 20.0);
                $formattedCommPercent = rtrim(rtrim(number_format($commPercent, 2, ',', '.'), '0'), ',');
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Komisi Platform</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                </div>
                <div class="text-2xl font-bold text-emerald-700">Rp {{ number_format($totalPlatformCommission, 0, ',', '.') }}</div>
                <div class="text-[11px] text-emerald-600 font-medium">Pendapatan bersih ({{ $formattedCommPercent }}%)</div>
            </div>

            <!-- Total Liabilitas Saldo -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Liabilitas Saldo</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($totalFloatingLiability, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- 2. Rekonsiliasi Settlement Payment Gateway & Escrow (Clean & Direct) -->
        @php
            $estimatedEscrow = $totalTopupSettled - $totalDisbursed;
            $isDeficit = $estimatedEscrow < 0;
            $isFullyCovered = $totalFloatingLiability > 0 ? ($estimatedEscrow >= $totalFloatingLiability) : ($estimatedEscrow >= 0);
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rekonsiliasi Kas Escrow & Gateway</h3>
                <div>
                    @if ($isDeficit)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Defisit Kas Penampung
                        </span>
                    @elseif ($isFullyCovered)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Likuiditas Kas Aman
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Cadangan Terbatas
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                <div class="pt-3 sm:pt-0">
                    <span class="text-xs text-slate-400 block mb-1">Total Top-up Masuk</span>
                    <span class="text-xl font-mono font-bold text-slate-800">Rp {{ number_format($totalTopupSettled, 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 sm:pt-0 sm:pl-6">
                    <span class="text-xs text-slate-400 block mb-1">Total Pencairan Disetujui</span>
                    <span class="text-xl font-mono font-bold text-slate-800">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 sm:pt-0 sm:pl-6">
                    <span class="text-xs text-slate-400 block mb-1">Estimasi Kas Riil Escrow</span>
                    <span class="text-xl font-mono font-bold {{ $isDeficit ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ $estimatedEscrow < 0 ? '-' : '' }}Rp {{ number_format(abs($estimatedEscrow), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Regional Breakdown Table (Header Toolbar Terpadu Dengan Tombol Aksi) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Kartu: Judul di Kiri, Tab Switcher & Tombol Ekspor di Kanan -->
            <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <h3 class="font-bold text-slate-800 text-base">Sebaran Dana Mengendap per Wilayah</h3>
                    
                    <!-- Tab Switcher Ringkas -->
                    <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl shrink-0">
                        <button type="button"
                                @click="viewMode = 'liability'"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition"
                                :class="viewMode === 'liability' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800'">
                            Dana Mengendap
                        </button>
                        <button type="button"
                                @click="viewMode = 'performance'"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition"
                                :class="viewMode === 'performance' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800'">
                            Performa & Komisi
                        </button>
                    </div>
                </div>

                <!-- Tombol Aksi (Dipindah dari Header Atas) -->
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('superadmin.finance.cross-region') }}"
                       class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Laporan Lintas Wilayah
                    </a>
                    <a href="{{ route('superadmin.finance.export.print') }}"
                       target="_blank"
                       class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Ringkasan
                    </a>
                </div>
            </div>

            <!-- Tampilan Tab 1: Dana Mengendap & Liabilitas (Bersih & Rapi) -->
            <div x-show="viewMode === 'liability'" class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap">Nama Wilayah</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Saldo Pelanggan</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Saldo Mitra</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Total Mengendap</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Porsi Liabilitas</th>
                            <th class="px-5 py-3.5 text-center whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($regionStats as $st)
                            @php
                                $percent = $totalFloatingLiability > 0 ? ($st['total_floating'] / $totalFloatingLiability) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                                    {{ $st['region']->name }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-slate-800 whitespace-nowrap">
                                    Rp {{ number_format($st['customer_balance'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-slate-800 whitespace-nowrap">
                                    Rp {{ number_format($st['partner_balance'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-emerald-700 whitespace-nowrap">
                                    Rp {{ number_format($st['total_floating'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-mono font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md text-[11px]">
                                        {{ number_format($percent, 1) }}%
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                    Belum ada wilayah operasional yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tampilan Tab 2: Performa Omzet, Komisi, & Arus Kas (Bersih & Rapi) -->
            <div x-show="viewMode === 'performance'" class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap">Nama Wilayah</th>
                            <th class="px-5 py-3.5 text-center whitespace-nowrap">Pesanan Selesai</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Omzet Layanan</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Komisi Platform</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Top-up Masuk</th>
                            <th class="px-5 py-3.5 text-right whitespace-nowrap">Pencairan Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($regionStats as $st)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                                    {{ $st['region']->name }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono font-bold text-slate-700 whitespace-nowrap">
                                    {{ $st['completed_orders_count'] }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-slate-800 whitespace-nowrap">
                                    Rp {{ number_format($st['total_order_volume'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-emerald-700 whitespace-nowrap">
                                    Rp {{ number_format($st['platform_commission'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-slate-600 whitespace-nowrap">
                                    Rp {{ number_format($st['topup_volume'], 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-slate-600 whitespace-nowrap">
                                    Rp {{ number_format($st['withdrawal_volume'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                    Belum ada wilayah operasional yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
