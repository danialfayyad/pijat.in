<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the national platform overview dashboard for Superadmin.
     */
    public function index(Request $request): View
    {
        // 1. National Core Metrics (All-time & Platform-wide)
        $totalGmv = (float) Order::where('status', OrderStatus::Selesai)->sum('total_price');
        $totalPlatformCommission = (float) Order::where('status', OrderStatus::Selesai)
            ->selectRaw('SUM(total_price * (commission_percent / 100)) as commission')
            ->value('commission');

        $totalCompletedOrders = Order::where('status', OrderStatus::Selesai)->count();
        $totalActiveOrders = Order::active()->count();

        $activeMitraCount = PartnerProfile::where('is_active', true)
            ->whereNotNull('verified_at')
            ->count();
        $totalMitraCount = PartnerProfile::count();
        $totalCustomerCount = User::where('role', 'pelanggan')->count();

        $activeRegionsCount = Region::where('status', 'aktif')->count();
        $totalRegionsCount = Region::count();

        $metrics = [
            'total_gmv' => $totalGmv,
            'platform_commission' => $totalPlatformCommission,
            'completed_orders_count' => $totalCompletedOrders,
            'active_orders_count' => $totalActiveOrders,
            'active_mitra_count' => $activeMitraCount,
            'total_mitra_count' => $totalMitraCount,
            'total_customer_count' => $totalCustomerCount,
            'active_regions_count' => $activeRegionsCount,
            'total_regions_count' => $totalRegionsCount,
        ];

        // 2. Chart 1: National Order & Revenue Trend (7d, 30d, all)
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

        // Single aggregation query for daily counts in last 30 days
        $dailyCounts = Order::where('status', OrderStatus::Selesai)
            ->where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as total_orders, SUM(total_price) as total_volume')
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date');

        $orders7d = [];
        $gmv7d = [];
        foreach ($trend7Dates as $date) {
            $key = $date->toDateString();
            $row = $dailyCounts->get($key);
            $orders7d[] = (int) ($row->total_orders ?? 0);
            $gmv7d[] = (float) ($row->total_volume ?? 0);
        }

        $orders30d = [];
        $gmv30d = [];
        foreach ($trend30Dates as $date) {
            $key = $date->toDateString();
            $row = $dailyCounts->get($key);
            $orders30d[] = (int) ($row->total_orders ?? 0);
            $gmv30d[] = (float) ($row->total_volume ?? 0);
        }

        // 2c. All Time (12 Bulan Terakhir)
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::today()->subMonths($i));
        }
        $labelsAll = $months->map(fn ($m) => $m->translatedFormat('M y'))->toArray();

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthSql = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyStats = Order::where('status', OrderStatus::Selesai)
            ->where('created_at', '>=', Carbon::today()->subMonths(11)->startOfMonth())
            ->selectRaw("{$monthSql} as order_month, COUNT(*) as total_orders, SUM(total_price) as total_volume")
            ->groupBy('order_month')
            ->get()
            ->keyBy('order_month');

        $ordersAll = [];
        $gmvAll = [];
        foreach ($months as $m) {
            $key = $m->format('Y-m');
            $row = $monthlyStats->get($key);
            $ordersAll[] = (int) ($row->total_orders ?? 0);
            $gmvAll[] = (float) ($row->total_volume ?? 0);
        }

        $trendChartData = [
            '7d' => [
                'labels' => $labels7d,
                'orders' => $orders7d,
                'gmv' => $gmv7d,
                'title' => '7 Hari Terakhir',
            ],
            '30d' => [
                'labels' => $labels30d,
                'orders' => $orders30d,
                'gmv' => $gmv30d,
                'title' => '30 Hari Terakhir',
            ],
            'all' => [
                'labels' => $labelsAll,
                'orders' => $ordersAll,
                'gmv' => $gmvAll,
                'title' => 'Semua',
            ],
        ];

        $trendLabels = $labels7d;
        $trendOrders = $orders7d;
        $trendGmv = $gmv7d;

        // 3. Top 5 Active Regions by Completed Order Volume & Revenue
        $topRegions = Region::where('status', 'aktif')
            ->withCount(['orders' => fn ($q) => $q->where('status', OrderStatus::Selesai)])
            ->withSum(['orders as total_revenue' => fn ($q) => $q->where('status', OrderStatus::Selesai)], 'total_price')
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();

        $regionChartLabels = $topRegions->pluck('name')->toArray();
        $regionChartData = $topRegions->pluck('orders_count')->toArray();

        // 4. Financial Liquidity & Liabilities Watch (SA-04)
        $floatingPartnerBalance = (float) Wallet::whereHas('user', fn ($q) => $q->where('role', 'mitra'))->sum('balance');
        $floatingCustomerBalance = (float) Wallet::whereHas('user', fn ($q) => $q->where('role', 'pelanggan'))->sum('balance');
        $totalHeldEscrow = (float) Wallet::sum('held_balance');
        $totalFloatingLiability = $floatingPartnerBalance + $floatingCustomerBalance;

        $pendingWithdrawalsCount = WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count();
        $pendingWithdrawalsAmount = (float) WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->sum('amount');

        $financeWatch = [
            'total_liability' => $totalFloatingLiability,
            'partner_balance' => $floatingPartnerBalance,
            'customer_balance' => $floatingCustomerBalance,
            'held_escrow' => $totalHeldEscrow,
            'pending_withdrawals_count' => $pendingWithdrawalsCount,
            'pending_withdrawals_amount' => $pendingWithdrawalsAmount,
        ];

        // 5. Governance & System Risk Alerts
        $unassignedRegions = Region::where('status', 'aktif')
            ->whereDoesntHave('admins')
            ->get(['id', 'name']);

        $pendingComplaintsCount = Complaint::where('status', 'menunggu')->count();
        $pendingVerificationsCount = PartnerProfile::whereNull('verified_at')
            ->whereNull('rejection_code')
            ->count();

        // 6. Recent Audit Logs (5 entries)
        $recentAuditLogs = AuditLog::with('user')
            ->latest('id')
            ->take(5)
            ->get();

        return view('superadmin.dashboard', compact(
            'metrics',
            'trendChartData',
            'trendLabels',
            'trendOrders',
            'trendGmv',
            'topRegions',
            'regionChartLabels',
            'regionChartData',
            'financeWatch',
            'unassignedRegions',
            'pendingComplaintsCount',
            'pendingVerificationsCount',
            'recentAuditLogs'
        ));
    }
}
