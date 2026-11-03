<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800 leading-tight">
                {{ __('Monitoring Saldo Mengendap & Settlement (SA-04)') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('superadmin.finance.cross-region') }}"
                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Laporan Lintas Wilayah
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- National Aggregate Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Saldo Pelanggan</span>
                <span class="text-xl font-bold text-emerald-700">Rp {{ number_format($totalCustomerBalance, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-1">Mengendap di dompet customer</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Saldo Mitra Tersedia</span>
                <span class="text-xl font-bold text-slate-800">Rp {{ number_format($totalPartnerBalance, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-1">Siap ditarik oleh terapis</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Komisi Platform</span>
                <span class="text-xl font-bold text-emerald-800">Rp {{ number_format($totalPlatformCommission, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-1">Pendapatan operasional nasional</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Liabilitas Saldo</span>
                <span class="text-xl font-bold text-slate-900">Rp {{ number_format($totalFloatingLiability, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-1">Customer + Mitra</span>
            </div>
        </div>

        <!-- Reconciliation Card: Xendit Settlement vs Realized Liability -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-5 text-white shadow-md">
            <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Rekonsiliasi Settlement Payment Gateway</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Total Top-up Masuk (Gross Xendit):</span>
                    <span class="text-base font-mono font-bold text-emerald-400">Rp {{ number_format($totalTopupSettled, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Total Pencairan Disetujui (Disbursement):</span>
                    <span class="text-base font-mono font-bold text-slate-200">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Estimasi Kas Riil di Rekening Escrow:</span>
                    <span class="text-base font-mono font-bold text-emerald-300">Rp {{ number_format($totalTopupSettled - $totalDisbursed, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Regional Breakdown Table -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Sebaran Dana Mengendap per Wilayah</h3>
                    <p class="text-xs text-slate-500">Pemetaan atribusi saldo dompet pelanggan & mitra berdasarkan wilayah operasional aktif</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Nama Wilayah</th>
                            <th class="py-3 px-4 text-right">Saldo Pelanggan</th>
                            <th class="py-3 px-4 text-right">Saldo Mitra</th>
                            <th class="py-3 px-4 text-right">Total Mengendap</th>
                            <th class="py-3 px-4 text-center">Pesanan Selesai</th>
                            <th class="py-3 px-4 text-right">Omzet Layanan</th>
                            <th class="py-3 px-4 text-right">Komisi Platform</th>
                            <th class="py-3 px-4 text-right">Top-up Masuk</th>
                            <th class="py-3 px-4 text-right">Pencairan Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($regionStats as $st)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $st['region']->name }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                    Rp {{ number_format($st['customer_balance'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                    Rp {{ number_format($st['partner_balance'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                    Rp {{ number_format($st['total_floating'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono">
                                    {{ $st['completed_orders_count'] }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono">
                                    Rp {{ number_format($st['total_order_volume'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-800">
                                    Rp {{ number_format($st['platform_commission'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600">
                                    Rp {{ number_format($st['topup_volume'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600">
                                    Rp {{ number_format($st['withdrawal_volume'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
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

