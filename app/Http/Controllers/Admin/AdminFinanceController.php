<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFinanceController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display Regional Finance Dashboard, Withdrawal Queue, & Cash Ledger (ADM-11).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $assignedRegions = $user->isSuperadmin()
            ? Region::where('status', 'aktif')->get()
            : $user->assignedRegions;

        $assignedRegionIds = $assignedRegions->pluck('id')->toArray();

        // Selected region filter
        $selectedRegionId = $request->filled('region_id') && in_array((int) $request->input('region_id'), $assignedRegionIds, true)
            ? (int) $request->input('region_id')
            : null;

        $targetRegionIds = $selectedRegionId ? [$selectedRegionId] : $assignedRegionIds;

        $tab = $request->input('tab', 'withdrawals');

        // Cash Flow Metrics
        $totalInflow = (float) WalletTransaction::whereIn('region_id', $targetRegionIds)
            ->where('type', 'credit')
            ->whereIn('reference_type', ['topup', 'order_earning'])
            ->sum('amount');

        $totalOutflow = (float) WalletTransaction::whereIn('region_id', $targetRegionIds)
            ->where('type', 'debit')
            ->whereIn('reference_type', ['withdrawal_request', 'customer_withdrawal', 'order_refund', 'order_payment'])
            ->sum('amount');

        $pendingWithdrawalCount = WithdrawalRequest::whereIn('region_id', $targetRegionIds)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->count();

        $totalApprovedDisbursement = (float) WithdrawalRequest::whereIn('region_id', $targetRegionIds)
            ->where('status', WithdrawalRequest::STATUS_APPROVED)
            ->sum('amount');

        // 1. Tab: Withdrawal Requests Queue
        $withdrawalStatus = $request->input('status', 'all');
        $withdrawalQuery = WithdrawalRequest::with(['user', 'region', 'processedBy'])
            ->whereIn('region_id', $targetRegionIds);

        if ($withdrawalStatus === 'pending') {
            $withdrawalQuery->pending();
        } elseif ($withdrawalStatus === 'approved') {
            $withdrawalQuery->approved();
        } elseif ($withdrawalStatus === 'rejected') {
            $withdrawalQuery->rejected();
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $withdrawalQuery->where(function ($q) use ($search) {
                $q->where('withdrawal_code', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('account_holder_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $withdrawals = $withdrawalQuery->latest()->paginate(15, ['*'], 'wd_page')->withQueryString();

        // 2. Tab: Regional Ledger Transactions
        $ledgerQuery = WalletTransaction::with(['user', 'region'])
            ->whereIn('region_id', $targetRegionIds);

        if ($request->filled('ref_type')) {
            $ledgerQuery->where('reference_type', $request->input('ref_type'));
        }

        if ($request->filled('start_date')) {
            $ledgerQuery->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $ledgerQuery->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $ledgerTransactions = $ledgerQuery->latest()->paginate(20, ['*'], 'tx_page')->withQueryString();

        return view('admin.finance.index', compact(
            'assignedRegions',
            'selectedRegionId',
            'tab',
            'totalInflow',
            'totalOutflow',
            'pendingWithdrawalCount',
            'totalApprovedDisbursement',
            'withdrawalStatus',
            'withdrawals',
            'ledgerTransactions'
        ));
    }

    /**
     * Approve withdrawal request and confirm payout transfer (ADM-11).
     */
    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperadmin() && ! $user->canAccessRegion($withdrawal->region_id)) {
            abort(403, 'Anda tidak memiliki otorisasi untuk memproses penarikan di wilayah ini.');
        }

        if (! $withdrawal->isPending()) {
            return back()->with('error', 'Permohonan penarikan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'transfer_reference' => ['nullable', 'string', 'max:100'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('withdrawals/proofs', 'public');
        }

        DB::transaction(function () use ($withdrawal, $user, $validated, $proofPath) {
            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_APPROVED,
                'transfer_reference' => $validated['transfer_reference'] ?? null,
                'proof_path' => $proofPath ?? $withdrawal->proof_path,
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            // Notify user
            $targetUser = $withdrawal->user;
            if ($targetUser) {
                $amountFormatted = number_format($withdrawal->net_amount, 0, ',', '.');
                $this->notificationService->sendToUser(
                    $targetUser,
                    'withdrawal_approved',
                    'Penarikan Dana Berhasil',
                    "Dana penarikan senilai Rp {$amountFormatted} telah ditransfer ke rekening {$withdrawal->bank_name} ({$withdrawal->account_number}).",
                    ['withdrawal_id' => $withdrawal->id, 'withdrawal_code' => $withdrawal->withdrawal_code],
                    'high'
                );
            }
        });

        return back()->with('status', "Penarikan {$withdrawal->withdrawal_code} berhasil disetujui dan status pencairan telah diperbarui.");
    }

    /**
     * Reject withdrawal request with mandatory reason and return funds to user wallet (ADM-11).
     */
    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isSuperadmin() && ! $user->canAccessRegion($withdrawal->region_id)) {
            abort(403, 'Anda tidak memiliki otorisasi untuk memproses penarikan di wilayah ini.');
        }

        if (! $withdrawal->isPending()) {
            return back()->with('error', 'Permohonan penarikan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($withdrawal, $user, $validated) {
            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_REJECTED,
                'rejection_reason' => $validated['rejection_reason'],
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            // Refund balance atomically back to user's wallet
            $targetUser = $withdrawal->user;
            if ($targetUser) {
                $this->walletService->credit(
                    $targetUser,
                    (float) $withdrawal->amount,
                    'withdrawal_refund',
                    (string) $withdrawal->id,
                    "Pengembalian dana penarikan {$withdrawal->withdrawal_code} yang ditolak: {$validated['rejection_reason']}",
                    $withdrawal->region_id
                );

                // Notify user
                $this->notificationService->sendToUser(
                    $targetUser,
                    'withdrawal_rejected',
                    'Permohonan Penarikan Ditolak',
                    "Pengajuan penarikan dana {$withdrawal->withdrawal_code} senilai Rp ".number_format($withdrawal->amount, 0, ',', '.')." ditolak: {$validated['rejection_reason']}. Saldo telah dikembalikan ke dompet Anda.",
                    ['withdrawal_id' => $withdrawal->id, 'withdrawal_code' => $withdrawal->withdrawal_code, 'reason' => $validated['rejection_reason']],
                    'high'
                );
            }
        });

        return back()->with('status', "Penarikan {$withdrawal->withdrawal_code} berhasil ditolak dan saldo Rp ".number_format($withdrawal->amount, 0, ',', '.').' telah dikembalikan ke dompet pengguna.');
    }

    /**
     * Export regional financial ledger to downloadable CSV (ADM-11).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $assignedRegions = $user->isSuperadmin()
            ? Region::where('status', 'aktif')->get()
            : $user->assignedRegions;

        $assignedRegionIds = $assignedRegions->pluck('id')->toArray();
        $selectedRegionId = $request->filled('region_id') ? (int) $request->input('region_id') : null;
        $targetRegionIds = $selectedRegionId && in_array($selectedRegionId, $assignedRegionIds, true)
            ? [$selectedRegionId]
            : $assignedRegionIds;

        $query = WalletTransaction::with(['user', 'region'])
            ->whereIn('region_id', $targetRegionIds);

        if ($request->filled('ref_type')) {
            $query->where('reference_type', $request->input('ref_type'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $transactions = $query->latest()->get();

        $filename = 'laporan-arus-kas-wilayah-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Transaksi',
                'Tanggal & Waktu',
                'Wilayah',
                'Pengguna',
                'Role Pengguna',
                'Tipe Mutasi',
                'Nominal (Rp)',
                'Saldo Sebelum (Rp)',
                'Saldo Sesudah (Rp)',
                'Jenis Referensi',
                'ID Referensi',
                'Keterangan Transaksi',
            ]);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->id,
                    $tx->created_at->format('Y-m-d H:i:s'),
                    $tx->region?->name ?? 'Pusat / Netral',
                    $tx->user?->name ?? 'Sistem / Anonim',
                    $tx->user?->role?->label() ?? '-',
                    strtoupper($tx->type),
                    $tx->amount,
                    $tx->balance_before,
                    $tx->balance_after,
                    $tx->reference_type,
                    $tx->reference_id ?? '-',
                    $tx->description,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Clean printable financial summary view (ADM-11).
     */
    public function exportPrint(Request $request): View
    {
        $user = $request->user();
        $assignedRegions = $user->isSuperadmin()
            ? Region::where('status', 'aktif')->get()
            : $user->assignedRegions;

        $assignedRegionIds = $assignedRegions->pluck('id')->toArray();
        $selectedRegionId = $request->filled('region_id') ? (int) $request->input('region_id') : null;
        $targetRegionIds = $selectedRegionId && in_array($selectedRegionId, $assignedRegionIds, true)
            ? [$selectedRegionId]
            : $assignedRegionIds;

        $regionName = $selectedRegionId ? Region::find($selectedRegionId)?->name : 'Semua Wilayah Penugasan';

        $transactions = WalletTransaction::with(['user', 'region'])
            ->whereIn('region_id', $targetRegionIds)
            ->latest()
            ->take(200)
            ->get();

        $totalInflow = (float) WalletTransaction::whereIn('region_id', $targetRegionIds)
            ->where('type', 'credit')
            ->sum('amount');

        $totalOutflow = (float) WalletTransaction::whereIn('region_id', $targetRegionIds)
            ->where('type', 'debit')
            ->sum('amount');

        return view('admin.finance.print', compact(
            'transactions',
            'regionName',
            'totalInflow',
            'totalOutflow'
        ));
    }
}
