<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Operasional Wilayah') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN for Visual Operational Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

        <!-- Bar Informasi Wilayah & Pemilih Wilayah (ADM-15) -->
        <div class="bg-white rounded-2xl px-5 py-3.5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                @if ($selectedRegion)
                    <span class="font-semibold text-slate-800">Wilayah: {{ $selectedRegion->name }}</span>
                    <span class="text-slate-400">({{ $selectedRegion->timezone }})</span>
                @else
                    <span class="font-semibold text-slate-800">Semua Wilayah Tugas</span>
                    <span class="text-slate-400">({{ $assignedRegions->count() }} Wilayah Aktif)</span>
                @endif
            </div>

            @if ($assignedRegions->count() > 1 || auth()->user()->isSuperadmin())
                <!-- Region Selector Dropdown bagi Admin dengan Multi-Wilayah Tugas -->
                <div class="flex items-center gap-2 shrink-0">
                    <label for="region_selector" class="text-xs text-slate-500 font-medium shrink-0">Pilih:</label>
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="inline-block">
                        <select id="region_selector" name="region_id" onchange="this.form.submit()"
                                class="rounded-xl border-slate-200 bg-white text-slate-800 font-medium text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500 py-1.5 pl-3 pr-8 cursor-pointer">
                            <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                            @foreach ($assignedRegions as $r)
                                <option value="{{ $r->id }}" {{ (request('region_id') == $r->id || (isset($selectedRegion) && $selectedRegion?->id == $r->id)) ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        <!-- 1. HANYA 4 KARTU METRIK UTAMA (Core Essential KPIs) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Pesanan Aktif Berjalan -->
            <a href="{{ route('admin.orders.index', ['tab' => 'aktif']) }}" 
               class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1 hover:border-slate-300 transition group block">
                <div class="text-xs font-medium text-slate-500">
                    Pesanan Berjalan
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $metrics['orders_in_progress'] }}</div>
                <div class="text-[11px] text-blue-600 font-medium flex items-center justify-between">
                    <span>Sedang ditangani terapis</span>
                    <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
                </div>
            </a>

            <!-- 2. Mitra Siaga Online -->
            <a href="{{ route('admin.mitra.index') }}" 
               class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1 hover:border-slate-300 transition group block">
                <div class="text-xs font-medium text-slate-500">
                    Mitra Siaga Online
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $metrics['active_partners_count'] }}</div>
                <div class="text-[11px] text-emerald-600 font-medium flex items-center justify-between">
                    <span>Siap terima panggilan</span>
                    <span class="group-hover:translate-x-0.5 transition">&rarr;</span>
                </div>
            </a>

            <!-- 3. Omzet Selesai Hari Ini (GMV) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="text-xs font-medium text-slate-500">
                    Omzet Selesai Hari Ini
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    Rp {{ number_format($metrics['daily_revenue'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400">
                    Komisi: Rp {{ number_format($metrics['daily_commission'], 0, ',', '.') }}
                </div>
            </div>

            <!-- 4. Tingkat Keberhasilan Layanan (Completion Rate) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="text-xs font-medium text-slate-500">
                    Tingkat Penyelesaian
                </div>
                <div class="text-2xl font-bold {{ $metrics['completion_rate'] >= 80 ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $metrics['completion_rate'] }}%
                </div>
                <div class="text-[11px] text-slate-400">
                    {{ $metrics['daily_completed_count'] }} sukses / {{ $metrics['daily_cancelled_count'] }} batal hari ini
                </div>
            </div>
        </div>

        <!-- 2. DUA GRAFIK VISUAL OPERASIONAL (Visual Trend & Distribution) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik 1: Tren Pesanan & Pendapatan 7 Hari Terakhir (Line / Area Chart) -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Tren Pesanan Wilayah</h4>
                        <p id="trendSubtitle" class="text-xs text-slate-400 mt-0.5">Aktivitas volume transaksi pesanan 7 hari terakhir</p>
                    </div>
                    <!-- Filter Rentang Waktu (7 Hari, 1 Bulan, All Time) -->
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
                    <canvas id="orderTrendChart"></canvas>
                </div>
            </div>

            <!-- Grafik 2: Distribusi Layanan Terpopuler (Donut Chart) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">
                            {{ $isServiceFallback ? 'Distribusi Status' : 'Distribusi Layanan' }}
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $isServiceFallback ? 'Ringkasan status pesanan wilayah' : 'Paket pijat paling diminati' }}
                        </p>
                    </div>
                </div>

                @if (empty($serviceChartData) || array_sum($serviceChartData) === 0)
                    <div class="h-64 flex flex-col items-center justify-center text-center p-6 text-slate-400">
                        <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        <p class="text-xs font-semibold text-slate-600">Belum Ada Transaksi</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Distribusi akan muncul saat pesanan masuk</p>
                    </div>
                @else
                    <div class="relative w-full h-64 flex items-center justify-center">
                        <canvas id="serviceDonutChart"></canvas>
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. RADAR PESANAN BERJALAN & PANEL TUGAS PRIORITAS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri (2/3): Radar Pesanan Real-Time -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h4 class="font-bold text-slate-900 text-sm">Pesanan Berlangsung</h4>
                        <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Semua Pesanan &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/70 text-[11px] font-semibold text-slate-500 border-b border-slate-100">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Kode</th>
                                    <th class="px-4 py-3 font-medium">Waktu</th>
                                    <th class="px-4 py-3 font-medium">Layanan</th>
                                    <th class="px-4 py-3 font-medium">Pelanggan</th>
                                    <th class="px-4 py-3 font-medium">Terapis</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($liveOrders as $order)
                                    <tr class="hover:bg-slate-50/70 transition cursor-pointer {{ $order->isStuck() ? 'bg-amber-50/30' : '' }}"
                                        onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <span class="font-mono text-xs font-bold text-slate-900 hover:text-emerald-700">
                                                {{ $order->order_code }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 font-mono" title="{{ $order->created_at->translatedFormat('d M Y, H:i') }}">
                                            {{ $order->created_at->format('H:i') }}
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                            {{ $order->service_name ?? 'Layanan Pijat' }} · {{ $order->duration_minutes }}m
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-slate-800">
                                            {{ $order->customer->name ?? $order->recipient_name }}
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                            @if ($order->mitra)
                                                <span class="text-slate-800">{{ $order->mitra->name }}</span>
                                            @else
                                                <span class="text-slate-400 italic">Belum ada</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $order->status->dotClass() }}"></span>
                                                <span>{{ $order->status->label() }}</span>
                                                @if ($order->isStuck())
                                                    <span class="text-[10px] font-semibold text-amber-700">(Tertahan)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-semibold text-slate-900 tabular-nums">
                                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                            Belum ada pesanan aktif atau transaksi terbaru di wilayah ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan (1/3): Antrean Tugas & Prioritas Operasional -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
                <div>
                    <div class="border-b border-slate-100 pb-3">
                        <h4 class="font-bold text-slate-900 text-sm">Tugas Perlu Tindakan</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Antrean operasional wilayah</p>
                    </div>

                    <div class="divide-y divide-slate-100 mt-2">
                        <!-- 1. Pesanan Tertahan (Macet) -->
                        <a href="{{ route('admin.orders.index', ['tab' => 'macet']) }}" 
                           class="flex items-center justify-between py-3 px-1.5 rounded-lg hover:bg-slate-50 transition group">
                            <span class="text-xs font-medium {{ $stuckOrders->isNotEmpty() ? 'text-slate-900 font-semibold' : 'text-slate-600' }}">
                                Pesanan Butuh Terapis
                            </span>
                            @if ($stuckOrders->isNotEmpty())
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-900 text-white">
                                    {{ $stuckOrders->count() }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">0</span>
                            @endif
                        </a>

                        <!-- 2. Tiket Komplain / Aduan -->
                        <a href="{{ route('admin.complaints.index') }}" 
                           class="flex items-center justify-between py-3 px-1.5 rounded-lg hover:bg-slate-50 transition group">
                            <span class="text-xs font-medium {{ $urgentComplaints->isNotEmpty() ? 'text-slate-900 font-semibold' : 'text-slate-600' }}">
                                Aduan Pelanggan
                            </span>
                            @if ($urgentComplaints->isNotEmpty())
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-900 text-white">
                                    {{ $urgentComplaints->count() }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">0</span>
                            @endif
                        </a>

                        <!-- 3. Verifikasi Mitra Baru -->
                        <a href="{{ route('admin.mitra.index') }}" 
                           class="flex items-center justify-between py-3 px-1.5 rounded-lg hover:bg-slate-50 transition group">
                            <span class="text-xs font-medium {{ $pendingVerifications > 0 ? 'text-slate-900 font-semibold' : 'text-slate-600' }}">
                                Verifikasi Mitra Baru
                            </span>
                            @if ($pendingVerifications > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-900 text-white">
                                    {{ $pendingVerifications }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">0</span>
                            @endif
                        </a>

                        <!-- 4. Banding Poin Sanksi -->
                        <a href="{{ route('admin.appeals.index') }}" 
                           class="flex items-center justify-between py-3 px-1.5 rounded-lg hover:bg-slate-50 transition group">
                            <span class="text-xs font-medium {{ $pendingAppeals > 0 ? 'text-slate-900 font-semibold' : 'text-slate-600' }}">
                                Banding Poin Sanksi
                            </span>
                            @if ($pendingAppeals > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-900 text-white">
                                    {{ $pendingAppeals }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">0</span>
                            @endif
                        </a>
                    </div>

                    @php
                        $totalPendingTasks = $stuckOrders->count() + $urgentComplaints->count() + $pendingVerifications + $pendingAppeals;
                    @endphp
                    @if ($totalPendingTasks === 0)
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs text-emerald-600 font-medium">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Semua antrean tertangani</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 mt-6">
                    <a href="{{ route('admin.notifications.index') }}" class="text-xs font-medium text-slate-500 hover:text-emerald-700 transition flex items-center justify-between">
                        <span>Pusat Notifikasi</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Script Inisialisasi Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Line/Area Chart: Tren Pesanan dengan Filter Range (7d, 30d, all)
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

                const subtitle = document.getElementById('trendSubtitle');
                if (subtitle && trendData[range].title) {
                    subtitle.textContent = 'Aktivitas volume transaksi pesanan ' + trendData[range].title.toLowerCase();
                }

                trendChartInstance.data.labels = trendData[range].labels;
                trendChartInstance.data.datasets[0].data = trendData[range].orders;
                trendChartInstance.update();
            };

            const ctxTrend = document.getElementById('orderTrendChart');
            if (ctxTrend && trendData['7d']) {
                trendChartInstance = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: trendData['7d'].labels,
                        datasets: [{
                            label: 'Volume Pesanan',
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

            // 2. Donut Chart: Distribusi Layanan
            const ctxDonut = document.getElementById('serviceDonutChart');
            if (ctxDonut) {
                const serviceLabels = @json($serviceChartLabels);
                const serviceData = @json($serviceChartData);

                new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: serviceLabels,
                        datasets: [{
                            data: serviceData,
                            backgroundColor: [
                                '#059669', // Emerald
                                '#0284c7', // Sky
                                '#d97706', // Amber
                                '#64748b', // Slate
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 12,
                                    font: { size: 11, weight: '500' },
                                    color: '#334155'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
