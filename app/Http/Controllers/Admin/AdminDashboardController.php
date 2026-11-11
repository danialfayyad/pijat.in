<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\PenaltyAppeal;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the operational dashboard for assigned regions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $assignedRegions = $user->assignedRegions()->get();

        if ($assignedRegions->isEmpty() && ! $user->isSuperadmin()) {
            abort(403, 'Akun admin Anda belum memiliki wilayah tugas.');
        }

        $selectedRegionId = $request->input('region_id');
        $selectedRegion = null;

        if ($selectedRegionId) {
            $selectedRegion = $assignedRegions->firstWhere('id', $selectedRegionId);
            if (! $selectedRegion && ! $user->isSuperadmin()) {
                abort(403, 'Anda tidak memiliki hak akses operasional untuk wilayah ini.');
            }
            if ($user->isSuperadmin() && ! $selectedRegion) {
                $selectedRegion = Region::find($selectedRegionId);
            }
        }

        $regionIds = $selectedRegion ? [$selectedRegion->id] : $assignedRegions->pluck('id')->all();

        $today = Carbon::today();

        // 1. Core Essential Metrics (ADM-01)
        $ordersInProgress = Order::inRegions($regionIds)->active()->count();
        $stuckOrdersCount = Order::inRegions($regionIds)->stuck()->count();
        $activePartnersCount = PartnerProfile::whereIn('region_id', $regionIds)->where('is_active', true)->count();
        $newComplaintsCount = Complaint::whereIn('region_id', $regionIds)->where('created_at', '>=', Carbon::now()->subDay())->count();

        // Today's completed and cancelled orders
        $dailyCompletedOrders = Order::inRegions($regionIds)
            ->where('status', OrderStatus::Selesai)
            ->where(function ($q) use ($today) {
                $q->whereDate('service_completed_at', $today)
                    ->orWhere(function ($sq) use ($today) {
                        $sq->whereNull('service_completed_at')->whereDate('updated_at', $today);
                    });
            });

        $dailyRevenue = (float) (clone $dailyCompletedOrders)->sum('total_price');
        $dailyCommission = (float) (clone $dailyCompletedOrders)->sum(DB::raw('total_price * commission_percent / 100'));
        $dailyCompletedCount = (clone $dailyCompletedOrders)->count();
        $dailyCancelledCount = Order::inRegions($regionIds)
            ->where('status', OrderStatus::Dibatalkan)
            ->whereDate('updated_at', $today)
            ->count();

        // Completion Rate
        $totalFinishedToday = $dailyCompletedCount + $dailyCancelledCount;
        $completionRate = $totalFinishedToday > 0
            ? round(($dailyCompletedCount / $totalFinishedToday) * 100, 1)
            : 100.0;

        $metrics = [
            'active_partners_count' => $activePartnersCount,
            'orders_in_progress' => $ordersInProgress,
            'stuck_orders_count' => $stuckOrdersCount,
            'new_complaints_count' => $newComplaintsCount,
            'daily_revenue' => $dailyRevenue,
            'daily_commission' => $dailyCommission,
            'daily_completed_count' => $dailyCompletedCount,
            'daily_cancelled_count' => $dailyCancelledCount,
            'completion_rate' => $completionRate,
        ];

        // 2. Chart 1: Order Trends (7 Days, 30 Days, All Time)
        // 2a. 7 Hari Terakhir
        $trend7Dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $trend7Dates->push(Carbon::today()->subDays($i));
        }
        $labels7d = $trend7Dates->map(fn ($d) => $d->translatedFormat('d M'))->toArray();

        // 2b. 30 Hari Terakhir
        $trend30Dates = collect();
        for ($i = 29; $i >= 0; $i--) {
            $trend30Dates->push(Carbon::today()->subDays($i));
        }
        $labels30d = $trend30Dates->map(fn ($d) => $d->translatedFormat('d M'))->toArray();

        // Query harian untuk rentang 30 hari dalam 1 kueri agregasi
        $dailyCounts = Order::inRegions($regionIds)
            ->where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as total')
            ->groupBy('order_date')
            ->pluck('total', 'order_date')
            ->toArray();

        $orders7d = [];
        foreach ($trend7Dates as $date) {
            $orders7d[] = (int) ($dailyCounts[$date->toDateString()] ?? 0);
        }

        $orders30d = [];
        foreach ($trend30Dates as $date) {
            $orders30d[] = (int) ($dailyCounts[$date->toDateString()] ?? 0);
        }

        // 2c. All Time / Sepanjang Waktu (Agregasi Bulanan 12 Bulan Terakhir)
        $trendAllMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $trendAllMonths->push(Carbon::today()->subMonths($i));
        }
        $labelsAll = $trendAllMonths->map(fn ($d) => $d->translatedFormat('M y'))->toArray();

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthSql = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyCounts = Order::inRegions($regionIds)
            ->where('created_at', '>=', Carbon::today()->subMonths(11)->startOfMonth())
            ->selectRaw("{$monthSql} as order_month, COUNT(*) as total")
            ->groupBy('order_month')
            ->pluck('total', 'order_month')
            ->toArray();

        $ordersAll = [];
        foreach ($trendAllMonths as $month) {
            $ordersAll[] = (int) ($monthlyCounts[$month->format('Y-m')] ?? 0);
        }

        $trendChartData = [
            '7d' => [
                'labels' => $labels7d,
                'orders' => $orders7d,
                'title' => '7 Hari Terakhir',
            ],
            '30d' => [
                'labels' => $labels30d,
                'orders' => $orders30d,
                'title' => '30 Hari Terakhir',
            ],
            'all' => [
                'labels' => $labelsAll,
                'orders' => $ordersAll,
                'title' => '12 Bulan Terakhir',
            ],
        ];

        $trendLabels = $labels7d;
        $trendOrders = $orders7d;
        $trendRevenue = [];

        // 3. Chart 2: Service Popularity Distribution (Top Services)
        $topServices = Order::inRegions($regionIds)
            ->whereNotNull('service_name')
            ->select('service_name', DB::raw('count(*) as total'))
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->take(4)
            ->get();

        $serviceChartLabels = $topServices->pluck('service_name')->toArray();
        $serviceChartData = $topServices->pluck('total')->toArray();
        $isServiceFallback = empty($serviceChartData);

        // Fallback if no service data yet (Status Distribution)
        if ($isServiceFallback) {
            $serviceChartLabels = ['Pesanan Aktif', 'Selesai', 'Dibatalkan'];
            $serviceChartData = [
                $ordersInProgress,
                Order::inRegions($regionIds)->where('status', OrderStatus::Selesai)->count(),
                Order::inRegions($regionIds)->where('status', OrderStatus::Dibatalkan)->count(),
            ];
        }

        // 4. Live Orders Feed (Recent 6 Active / Latest Orders)
        $liveOrders = Order::with(['customer', 'mitra', 'region'])
            ->inRegions($regionIds)
            ->latest()
            ->take(6)
            ->get();

        // 5. Urgent Operational Triage Alerts
        $stuckOrders = Order::with(['customer', 'region'])
            ->inRegions($regionIds)
            ->stuck()
            ->latest()
            ->take(3)
            ->get();

        $urgentComplaints = Complaint::with(['complainant', 'region'])
            ->whereIn('region_id', $regionIds)
            ->where('status', 'menunggu')
            ->latest()
            ->take(3)
            ->get();

        $pendingVerifications = PartnerProfile::whereIn('region_id', $regionIds)
            ->whereNull('verified_at')
            ->whereNull('rejection_code')
            ->count();

        $pendingAppeals = PenaltyAppeal::whereIn('region_id', $regionIds)
            ->where('status', 'menunggu')
            ->count();

        return view('admin.dashboard', compact(
            'assignedRegions',
            'selectedRegion',
            'metrics',
            'trendChartData',
            'trendLabels',
            'trendOrders',
            'trendRevenue',
            'serviceChartLabels',
            'serviceChartData',
            'isServiceFallback',
            'liveOrders',
            'stuckOrders',
            'urgentComplaints',
            'pendingVerifications',
            'pendingAppeals'
        ));
    }
}
