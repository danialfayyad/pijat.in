<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('superadmin.finance.index') }}" class="text-slate-500 hover:text-slate-800 transition">
                    &larr; Kembali
                </a>
                <h2 class="text-xl font-bold text-slate-800 leading-tight">
                    {{ __('Laporan Keuangan Lintas Wilayah (SA-08)') }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('superadmin.finance.export.csv', request()->query()) }}"
                   class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ekspor CSV
                </a>
                <a href="{{ route('superadmin.finance.export.print', request()->query()) }}"
                   target="_blank"
                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Filter Controls -->
        <form method="GET" action="{{ route('superadmin.finance.cross-region') }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-1">
                    <label class="font-bold text-slate-700 block mb-1">Pilih Wilayah Operasional</label>
                    <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1">
                        @foreach ($allRegions as $reg)
                            <label class="flex items-center space-x-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox"
                                       name="regions[]"
                                       value="{{ $reg->id }}"
                                       {{ in_array($reg->id, $selectedRegionIds, true) ? 'checked' : '' }}
                                       class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>{{ $reg->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full p-2 rounded-xl border border-slate-200 text-xs">
                </div>

                <div>
                    <label class="font-bold text-slate-700 block mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full p-2 rounded-xl border border-slate-200 text-xs">
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-100">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition">
                    Terapkan Filter Laporan
                </button>
            </div>
        </form>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Mutasi Transaksi</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($totalTransactions) }} Transaksi</span>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Arus Masuk</span>
                <span class="text-xl font-bold text-emerald-700">Rp {{ number_format($totalInflow, 0, ',', '.') }}</span>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Arus Keluar</span>
                <span class="text-xl font-bold text-slate-800">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</span>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Arus Kas Bersih (Net)</span>
                <span class="text-xl font-bold {{ $totalInflow >= $totalOutflow ? 'text-emerald-800' : 'text-rose-600' }}">
                    Rp {{ number_format($totalInflow - $totalOutflow, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Regional Summary Matrix Table -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Ringkasan Finansial per Wilayah</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Wilayah</th>
                            <th class="py-3 px-4 text-right">Arus Masuk</th>
                            <th class="py-3 px-4 text-right">Arus Keluar</th>
                            <th class="py-3 px-4 text-right">Net Kas</th>
                            <th class="py-3 px-4 text-right">Komisi Platform</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($summaryPerRegion as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $item['region']->name }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-700">Rp {{ number_format($item['inflow'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-700">Rp {{ number_format($item['outflow'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ $item['net'] >= 0 ? 'text-emerald-800' : 'text-rose-600' }}">
                                    Rp {{ number_format($item['net'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($item['commission'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ledger Transactions Table -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Rincian Buku Kas Lintas Wilayah</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Wilayah</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Referensi</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $tx->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    {{ $tx->region?->name ?? 'Nasional' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-900 block">{{ $tx->user?->name ?? 'Sistem' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $tx->user?->role?->label() ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-slate-100 text-slate-700">
                                        {{ $tx->reference_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap font-bold {{ $tx->isCredit() ? 'text-emerald-700' : 'text-slate-800' }}">
                                    {{ $tx->isCredit() ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600 whitespace-nowrap">
                                    Rp {{ number_format($tx->balance_after, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $tx->description }}">
                                    {{ $tx->description }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada transaksi mutasi pada periode dan filter wilayah ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

