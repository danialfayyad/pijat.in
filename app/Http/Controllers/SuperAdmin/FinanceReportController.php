<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Parameter;
use App\Models\Region;
use App\Models\TopupInvoice;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    /**
     * Centralized Floating Balance Monitoring & Settlement Reconciliation (SA-04).
     */
    public function index(Request $request): View
    {
        // 1. National Aggregate Balances
        $totalCustomerBalance = (float) Wallet::whereHas('user', fn ($q) => $q->where('role', 'pelanggan'))->sum('balance');
        $totalPartnerBalance = (float) Wallet::whereHas('user', fn ($q) => $q->where('role', 'mitra'))->sum('balance');

        $totalTopupSettled = (float) TopupInvoice::where('status', 'PAID')->sum('amount');
        $totalDisbursed = (float) WithdrawalRequest::where('status', WithdrawalRequest::STATUS_APPROVED)->sum('amount');

        $totalPlatformCommission = (float) Order::where('status', 'selesai')
            ->selectRaw('SUM(total_price * (commission_percent / 100)) as commission')
            ->value('commission');

        // Net platform floating liability
        $totalFloatingLiability = $totalCustomerBalance + $totalPartnerBalance;

        // 2. Per-Region Breakdown
        $regions = Region::where('status', 'aktif')->get();

        $regionStats = $regions->map(function ($region) {
            // Customer floating balance in region (based on primary address)
            $customerBalance = (float) Wallet::whereHas('user', function ($q) use ($region) {
                $q->where('role', 'pelanggan')
                    ->whereHas('primaryAddress', fn ($aq) => $aq->where('region_id', $region->id));
            })->sum('balance');

            // Partner floating balance in region (based on partner profile region)
            $partnerBalance = (float) Wallet::whereHas('user', function ($q) use ($region) {
                $q->where('role', 'mitra')
                    ->whereHas('partnerProfile', fn ($pq) => $pq->where('region_id', $region->id));
            })->sum('balance');

            // Regional order volume & commissions
            $ordersQuery = Order::where('region_id', $region->id)->where('status', 'selesai');
            $completedOrdersCount = $ordersQuery->count();
            $totalOrderVolume = (float) $ordersQuery->sum('total_price');
            $platformCommission = (float) Order::where('region_id', $region->id)
                ->where('status', 'selesai')
                ->selectRaw('SUM(total_price * (commission_percent / 100)) as comm')
                ->value('comm');

            // Regional Top-up & Withdrawal Volume
            $topupVolume = (float) TopupInvoice::where('region_id', $region->id)->where('status', 'PAID')->sum('amount');
            $withdrawalVolume = (float) WithdrawalRequest::where('region_id', $region->id)->where('status', 'APPROVED')->sum('amount');

            return [
                'region' => $region,
                'customer_balance' => $customerBalance,
                'partner_balance' => $partnerBalance,
                'total_floating' => $customerBalance + $partnerBalance,
                'completed_orders_count' => $completedOrdersCount,
                'total_order_volume' => $totalOrderVolume,
                'platform_commission' => $platformCommission,
                'topup_volume' => $topupVolume,
                'withdrawal_volume' => $withdrawalVolume,
            ];
        });

        $platformCommissionPercent = (float) Parameter::get('platform_commission_percent', 20.0);

        return view('superadmin.finance.index', compact(
            'totalCustomerBalance',
            'totalPartnerBalance',
            'totalFloatingLiability',
            'totalTopupSettled',
            'totalDisbursed',
            'totalPlatformCommission',
            'regionStats',
            'platformCommissionPercent'
        ));
    }

    /**
     * Cross-Region Financial Reports with Multi-Region Filtering (SA-08).
     */
    public function crossRegion(Request $request): View
    {
        $allRegions = Region::where('status', 'aktif')->get();

        $selectedRegionIds = $request->input('regions', []);
        if (empty($selectedRegionIds)) {
            $selectedRegionIds = $allRegions->pluck('id')->toArray();
        } else {
            $selectedRegionIds = array_map('intval', (array) $selectedRegionIds);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Transactions Query
        $txQuery = WalletTransaction::with(['user', 'region'])
            ->whereIn('region_id', $selectedRegionIds)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $totalTransactions = $txQuery->count();
        $totalInflow = (float) (clone $txQuery)->where('type', 'credit')->sum('amount');
        $totalOutflow = (float) (clone $txQuery)->where('type', 'debit')->sum('amount');

        $transactions = $txQuery->latest()->paginate(25)->withQueryString();

        // Summary per selected region
        $summaryPerRegion = Region::whereIn('id', $selectedRegionIds)->get()->map(function ($reg) use ($startDate, $endDate) {
            $inflow = (float) WalletTransaction::where('region_id', $reg->id)
                ->where('type', 'credit')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            $outflow = (float) WalletTransaction::where('region_id', $reg->id)
                ->where('type', 'debit')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            $commission = (float) Order::where('region_id', $reg->id)
                ->where('status', 'selesai')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('SUM(total_price * (commission_percent / 100)) as comm')
                ->value('comm');

            return [
                'region' => $reg,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'commission' => $commission,
                'net' => $inflow - $outflow,
            ];
        });

        return view('superadmin.finance.cross-region', compact(
            'allRegions',
            'selectedRegionIds',
            'startDate',
            'endDate',
            'totalTransactions',
            'totalInflow',
            'totalOutflow',
            'transactions',
            'summaryPerRegion'
        ));
    }

    /**
     * Export Cross-Region Financial Report to CSV (SA-08).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $allRegions = Region::where('status', 'aktif')->pluck('id')->toArray();
        $selectedRegionIds = $request->input('regions', []);
        $targetRegionIds = ! empty($selectedRegionIds) ? array_map('intval', (array) $selectedRegionIds) : $allRegions;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = WalletTransaction::with(['user', 'region'])
            ->whereIn('region_id', $targetRegionIds);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->latest()->get();

        $filename = 'laporan-lintas-wilayah-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Transaksi',
                'Tanggal',
                'Wilayah',
                'Pengguna',
                'Peran',
                'Tipe (Credit/Debit)',
                'Nominal (Rp)',
                'Saldo Sebelum (Rp)',
                'Saldo Sesudah (Rp)',
                'Tipe Referensi',
                'Keterangan',
            ]);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->id,
                    $tx->created_at->format('Y-m-d H:i:s'),
                    $tx->region?->name ?? 'Nasional',
                    $tx->user?->name ?? 'Sistem',
                    $tx->user?->role?->label() ?? '-',
                    strtoupper($tx->type),
                    $tx->amount,
                    $tx->balance_before,
                    $tx->balance_after,
                    $tx->reference_type,
                    $tx->description,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Printable View for Cross-Region Financial Report (SA-08).
     */
    public function exportPrint(Request $request): View
    {
        $allRegions = Region::where('status', 'aktif')->pluck('id')->toArray();
        $selectedRegionIds = $request->input('regions', []);
        $targetRegionIds = ! empty($selectedRegionIds) ? array_map('intval', (array) $selectedRegionIds) : $allRegions;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $summaryPerRegion = Region::whereIn('id', $targetRegionIds)->get()->map(function ($reg) use ($startDate, $endDate) {
            $inflow = (float) WalletTransaction::where('region_id', $reg->id)
                ->where('type', 'credit')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            $outflow = (float) WalletTransaction::where('region_id', $reg->id)
                ->where('type', 'debit')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            $commission = (float) Order::where('region_id', $reg->id)
                ->where('status', 'selesai')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('SUM(total_price * (commission_percent / 100)) as comm')
                ->value('comm');

            return [
                'region' => $reg,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'commission' => $commission,
                'net' => $inflow - $outflow,
            ];
        });

        $totalInflow = $summaryPerRegion->sum('inflow');
        $totalOutflow = $summaryPerRegion->sum('outflow');
        $totalCommission = $summaryPerRegion->sum('commission');

        return view('superadmin.finance.print', compact(
            'summaryPerRegion',
            'startDate',
            'endDate',
            'totalInflow',
            'totalOutflow',
            'totalCommission'
        ));
    }
}
