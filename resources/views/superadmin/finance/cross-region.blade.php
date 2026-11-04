<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.finance.index') }}" class="text-slate-500 hover:text-slate-800 transition flex items-center gap-1 text-xs font-semibold">
                <span>&larr;</span>
                <span>Kembali</span>
            </a>
            <h2 class="text-xl font-bold text-slate-800 leading-tight">
                {{ __('Laporan Keuangan Lintas Wilayah') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 1. Filter Card (Kompak & Rapi) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4 text-xs">
            <form method="GET" action="{{ route('superadmin.finance.cross-region') }}" class="space-y-4">
                <!-- Baris 1: Filter Wilayah (Pill Toggle Bersih) -->
                <div>
                    <span class="font-bold text-slate-700 block mb-2">Pilih Wilayah Operasional</span>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($allRegions as $reg)
                            @php
                                $isChecked = in_array($reg->id, $selectedRegionIds, true);
                            @endphp
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold cursor-pointer transition select-none {{ $isChecked ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                <input type="checkbox"
                                       name="regions[]"
                                       value="{{ $reg->id }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       class="rounded text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
                                <span>{{ $reg->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Baris 2: Rentang Tanggal + Tombol Filter Hijau & Reset -->
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-slate-500 font-semibold">Periode:</span>
                        <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl shadow-2xs px-2 py-1">
                            <input type="date"
                                   id="start_date"
                                   name="start_date"
                                   value="{{ $startDate }}"
                                   class="text-xs border-0 p-1 text-slate-700 focus:ring-0 cursor-pointer bg-transparent"
                                   placeholder="Dari Tanggal">
                            <span class="text-slate-300 font-bold px-1 select-none">&rarr;</span>
                            <input type="date"
                                   id="end_date"
                                   name="end_date"
                                   value="{{ $endDate }}"
                                   class="text-xs border-0 p-1 text-slate-700 focus:ring-0 cursor-pointer bg-transparent"
                                   placeholder="Sampai Tanggal">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-xs flex items-center gap-1.5 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Terapkan Filter Laporan</span>
                        </button>

                        <a href="{{ route('superadmin.finance.cross-region') }}"
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- 2. Kartu Ringkasan Metrik (Standar 4-Card Modular) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            <!-- Total Mutasi Transaksi -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Mutasi Transaksi</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    {{ number_format($totalTransactions) }} <span class="text-sm font-semibold text-slate-500">Transaksi</span>
                </div>
                <div class="text-[11px] text-slate-400">Aktivitas keuangan tercatat</div>
            </div>

            <!-- Total Arus Masuk -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Arus Masuk</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
                <div class="text-[11px] text-emerald-600 font-medium">Top-up & komisi layanan</div>
            </div>

            <!-- Total Arus Keluar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Arus Keluar</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-400">Pencairan & refund</div>
            </div>

            <!-- Arus Kas Bersih (Net) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Arus Kas Bersih (Net)</span>
                    <span class="w-2 h-2 rounded-full {{ $totalInflow >= $totalOutflow ? 'bg-emerald-600' : 'bg-rose-500' }}"></span>
                </div>
                <div class="text-2xl font-bold {{ $totalInflow >= $totalOutflow ? 'text-emerald-700' : 'text-rose-600' }}">
                    Rp {{ number_format($totalInflow - $totalOutflow, 0, ',', '.') }}
                </div>
                <div class="text-[11px] {{ $totalInflow >= $totalOutflow ? 'text-emerald-600' : 'text-rose-500' }} font-medium">
                    {{ $totalInflow >= $totalOutflow ? 'Surplus kas periode ini' : 'Defisit kas periode ini' }}
                </div>
            </div>
        </div>

        <!-- 3. Tabel Ringkasan Finansial per Wilayah (Dengan Tombol Ekspor di Toolbar) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-bold text-slate-800 text-base">Ringkasan Finansial per Wilayah</h3>

                <!-- Tombol Ekspor (Dipindahkan dari Header) -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('superadmin.finance.export.csv', request()->query()) }}"
                       class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ekspor CSV
                    </a>
                    <a href="{{ route('superadmin.finance.export.print', request()->query()) }}"
                       target="_blank"
                       class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak / PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-5 whitespace-nowrap">Wilayah</th>
                            <th class="py-3 px-5 text-right whitespace-nowrap">Arus Masuk</th>
                            <th class="py-3 px-5 text-right whitespace-nowrap">Arus Keluar</th>
                            <th class="py-3 px-5 text-right whitespace-nowrap">Net Kas</th>
                            <th class="py-3 px-5 text-right whitespace-nowrap">Komisi Platform</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($summaryPerRegion as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-5 font-bold text-slate-900 whitespace-nowrap">{{ $item['region']->name }}</td>
                                <td class="py-3.5 px-5 text-right font-semibold text-emerald-700 whitespace-nowrap">Rp {{ number_format($item['inflow'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-5 text-right font-semibold text-slate-700 whitespace-nowrap">Rp {{ number_format($item['outflow'], 0, ',', '.') }}</td>
                                <td class="py-3.5 px-5 text-right font-bold {{ $item['net'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }} whitespace-nowrap">
                                    Rp {{ number_format($item['net'], 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-5 text-right font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($item['commission'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Tabel Rincian Buku Kas Lintas Wilayah (5 Kolom Bersih, Tanpa Scrollbar Horizontal) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base">Rincian Buku Kas Lintas Wilayah</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-slate-600">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-5 whitespace-nowrap">Waktu</th>
                            <th class="py-3 px-5 whitespace-nowrap">Wilayah</th>
                            <th class="py-3 px-5 whitespace-nowrap">Pengguna</th>
                            <th class="py-3 px-5 whitespace-nowrap">Jenis</th>
                            <th class="py-3 px-5 text-right whitespace-nowrap">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $tx)
                            @php
                                $jenisLabel = match ($tx->reference_type) {
                                    'topup' => 'Top-up Saldo',
                                    'order_payment' => 'Pembayaran Pesanan',
                                    'order_earning' => 'Pendapatan Layanan',
                                    'order_refund' => 'Refund Pesanan',
                                    'withdrawal_request' => 'Pencairan Mitra',
                                    'customer_withdrawal' => 'Pencairan Pelanggan',
                                    'withdrawal_refund' => 'Refund Pencairan',
                                    default => ucwords(str_replace('_', ' ', $tx->reference_type)),
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $tx->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-5 text-slate-700 font-medium whitespace-nowrap">
                                    {{ $tx->region?->name ?? 'Nasional' }}
                                </td>
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <span class="font-semibold text-slate-900 block">{{ $tx->user?->name ?? 'Sistem' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $tx->user?->role?->label() ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-5 whitespace-nowrap" title="{{ $tx->description }}">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $tx->isCredit() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $jenisLabel }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap font-bold {{ $tx->isCredit() ? 'text-emerald-700' : 'text-slate-800' }}">
                                    {{ $tx->isCredit() ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                    Tidak ada transaksi mutasi pada periode dan filter wilayah ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
