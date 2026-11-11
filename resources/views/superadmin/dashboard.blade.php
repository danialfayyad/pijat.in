<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Platform Nasional') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN for Strategic Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

        <!-- 1. EMPAT METRIK UTAMA MAKRO PLATFORM -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total GMV -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="text-xs font-medium text-slate-500">
                    Total GMV
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    Rp {{ number_format($metrics['total_gmv'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500">
                    Komisi: <span class="text-emerald-700 font-semibold">Rp {{ number_format($metrics['platform_commission'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 2. Pesanan Selesai -->
            <a href="{{ route('admin.orders.index') }}" 
               class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1 hover:border-slate-300 transition group block">
                <div class="text-xs font-medium text-slate-500">
                    Pesanan Selesai
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    {{ number_format($metrics['completed_orders_count'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-blue-600 font-medium flex items-center justify-between">
                    <span>{{ $metrics['active_orders_count'] }} aktif berjalan</span>
                    <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
                </div>
            </a>

            <!-- 3. Mitra Siaga -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="text-xs font-medium text-slate-500">
                    Mitra Siaga
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    {{ number_format($metrics['active_mitra_count'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500">
                    {{ number_format($metrics['total_mitra_count'], 0, ',', '.') }} mitra · {{ number_format($metrics['total_customer_count'], 0, ',', '.') }} pelanggan
                </div>
            </div>

            <!-- 4. Wilayah Operasional -->
            <a href="{{ route('superadmin.regions.index') }}" 
               class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1 hover:border-slate-300 transition group block">
                <div class="text-xs font-medium text-slate-500">
                    Wilayah Operasional
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    {{ $metrics['active_regions_count'] }} <span class="text-sm font-normal text-slate-400">/ {{ $metrics['total_regions_count'] }}</span>
                </div>
                <div class="text-[11px] text-emerald-700 font-medium flex items-center justify-between">
                    <span>Kelola master wilayah</span>
                    <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
                </div>
            </a>
        </div>

        <!-- 2. DUA GRAFIK STRATEGIS PLATFORM -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik 1: Tren Pertumbuhan Nasional dengan Filter Waktu -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-800 text-sm">Tren Transaksi</h4>
                    <!-- Filter Rentang Waktu (7 Hari, 1 Bulan, Semua) -->
                    <div class="inline-flex items-center p-1 bg-slate-100/80 rounded-xl text-xs font-medium text-slate-600 gap-1 self-start sm:self-auto">
                        <button type="button" 
                                onclick="switchTrendRange('7d')" 
                                id="btn-trend-7d"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition bg-white text-slate-900 shadow-xs">
                            7 Hari
                        </button>
                        <button type="button" 
                                onclick="switchTrendRange('30d')" 
                                id="btn-trend-30d"
                                class="px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                            1 Bulan
                        </button>
                        <button type="button" 
                                onclick="switchTrendRange('all')" 
                                id="btn-trend-all"
                                class="px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                            Semua
                        </button>
                    </div>
                </div>
                
                <div class="relative w-full h-64">
                    <canvas id="nationalTrendChart"></canvas>
                </div>
            </div>

            <!-- Komparasi Wilayah: Top Wilayah Teraktif -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-slate-800 text-sm">Wilayah Teraktif</h4>
                        <a href="{{ route('superadmin.finance.cross-region') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Komparasi &rarr;
                        </a>
                    </div>

                    @if ($topRegions->isEmpty() || $topRegions->sum('orders_count') === 0)
                        <div class="h-64 flex flex-col items-center justify-center text-center p-6 text-slate-400">
                            <p class="text-xs font-medium text-slate-500">Belum Ada Transaksi</p>
                        </div>
                    @else
                        @php
                            $maxOrders = $topRegions->max('orders_count') ?: 1;
                        @endphp
                        <div class="space-y-3.5 my-1">
                            @foreach ($topRegions as $index => $region)
                                @php
                                    $pct = $maxOrders > 0 ? round(($region->orders_count / $maxOrders) * 100) : 0;
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-slate-800 truncate">
                                            <span class="text-slate-400 font-bold mr-1">#{{ $index + 1 }}</span>
                                            {{ ucwords(strtolower($region->name)) }}
                                        </span>
                                        <span class="font-semibold text-slate-900 tabular-nums shrink-0 ml-2">
                                            {{ number_format($region->orders_count, 0, ',', '.') }} <span class="text-slate-400 font-normal text-[11px]">order</span>
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ max($pct, 4) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 mt-3">
                    <a href="{{ route('superadmin.regions.index') }}" class="text-xs font-medium text-slate-500 hover:text-emerald-700 transition flex items-center justify-between">
                        <span>Kelola Seluruh Wilayah</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. PENGAWASAN FINANSIAL & PERINGATAN TATA KELOLA -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Kolom 1: Likuiditas Finansial & Liabilitas Dompet -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-slate-900 text-sm">Likuiditas & Liabilitas</h4>
                        <a href="{{ route('superadmin.finance.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Laporan Keuangan &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 mt-2">
                        <div class="py-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Saldo Mengendap (Mitra & Pelanggan)</span>
                            <span class="font-bold text-slate-900 tabular-nums">
                                Rp {{ number_format($financeWatch['total_liability'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="py-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Saldo Tertahan (Escrow 24 Jam)</span>
                            <span class="font-bold text-slate-900 tabular-nums">
                                Rp {{ number_format($financeWatch['held_escrow'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="py-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Antrean Penarikan Dana</span>
                            <span class="font-semibold text-slate-800 tabular-nums">
                                {{ $financeWatch['pending_withdrawals_count'] }} pengajuan <span class="text-slate-400 font-normal">({{ 'Rp ' . number_format($financeWatch['pending_withdrawals_amount'], 0, ',', '.') }})</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 mt-2">
                    <a href="{{ route('superadmin.banks.index') }}" class="text-xs font-medium text-slate-500 hover:text-emerald-700 transition flex items-center justify-between">
                        <span>Master Rekening Bank</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Kolom 2: Peringatan Tata Kelola Sistem -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-slate-900 text-sm">Tata Kelola & Antrean</h4>
                        <a href="{{ route('superadmin.parameters.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Parameter &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 mt-2">
                        <!-- 1. Wilayah Tanpa Admin -->
                        <div class="py-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Wilayah Tanpa Admin Tugas</span>
                            @if ($unassignedRegions->isNotEmpty())
                                <div class="flex items-center gap-2">
                                    <span class="text-rose-700 font-semibold">{{ $unassignedRegions->pluck('name')->join(', ') }}</span>
                                    <a href="{{ route('superadmin.admins.index') }}" 
                                       class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                                        Tugaskan
                                    </a>
                                </div>
                            @else
                                <span class="text-slate-400">Nihil</span>
                            @endif
                        </div>

                        <!-- 2. Komplain Belum Ditangani -->
                        <div class="py-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Komplain Belum Ditangani</span>
                            <span class="font-semibold {{ $pendingComplaintsCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' }}">
                                {{ $pendingComplaintsCount }} tiket
                            </span>
                        </div>

                        <!-- 3. Verifikasi Mitra Tertunda -->
                        <div class="py-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Verifikasi Mitra Baru</span>
                            <span class="font-semibold {{ $pendingVerificationsCount > 0 ? 'text-blue-700 font-bold' : 'text-slate-400' }}">
                                {{ $pendingVerificationsCount }} pengajuan
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 mt-2">
                    <a href="{{ route('superadmin.admins.index') }}" class="text-xs font-medium text-slate-500 hover:text-emerald-700 transition flex items-center justify-between">
                        <span>Penugasan Admin Wilayah</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. LOG AUDIT TERBARU -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-bold text-slate-900 text-sm">Log Audit Terbaru</h4>
                <a href="{{ route('superadmin.audit-logs.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                    Semua Log &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/70 text-[11px] font-semibold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 font-medium">Waktu</th>
                            <th class="px-5 py-3 font-medium">Aktor</th>
                            <th class="px-5 py-3 font-medium">Modul</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                            <th class="px-5 py-3 font-medium text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentAuditLogs as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-500 font-mono" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $log->created_at->diffForHumans(null, true) }} lalu
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs font-medium text-slate-800">
                                    {{ $log->user->name ?? 'Sistem' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                    {{ $log->module_label ?? ucfirst($log->module) }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                        {{ $log->action_label ?? ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <a href="{{ route('superadmin.audit-logs.show', $log) }}" class="text-xs font-medium text-emerald-700 hover:underline">
                                        Lihat &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada catatan aktivitas log audit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Script Inisialisasi Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Line Chart: Tren Transaksi Nasional dengan Filter Range (7d, 30d, all)
            const trendData = @json($trendChartData ?? []);
            let trendChartInstance = null;

            window.switchTrendRange = function(range) {
                if (!trendData[range] || !trendChartInstance) return;

                ['7d', '30d', 'all'].forEach(function(r) {
                    const btn = document.getElementById('btn-trend-' + r);
                    if (btn) {
                        if (r === range) {
                            btn.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold transition bg-white text-slate-900 shadow-xs';
                        } else {
                            btn.className = 'px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 transition';
                        }
                    }
                });

                trendChartInstance.data.labels = trendData[range].labels;
                trendChartInstance.data.datasets[0].data = trendData[range].orders;
                trendChartInstance.update();
            };

            const ctxTrend = document.getElementById('nationalTrendChart');
            if (ctxTrend && trendData['7d']) {
                trendChartInstance = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: trendData['7d'].labels,
                        datasets: [{
                            label: 'Pesanan Selesai',
                            data: trendData['7d'].orders,
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#059669',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 }, color: '#64748b' }
                            },
                            y: {
                                beginAtZero: true,
                                border: { dash: [4, 4] },
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                    font: { size: 11 },
                                    color: '#64748b'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
