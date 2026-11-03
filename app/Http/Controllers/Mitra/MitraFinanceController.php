<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Parameter;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MitraFinanceController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display partner wallet, balance summary, and income history (MIT-15).
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('partnerProfile');
        $wallet = $user->getOrCreateWallet();

        $range = $request->input('range', '7_days');

        $query = WalletTransaction::where('wallet_id', $wallet->id);

        if ($range === '7_days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
        } elseif ($range === '30_days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        // Income & withdrawal statistics
        $currentMonthIncome = (float) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'credit')
            ->where('reference_type', 'order_earning')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $totalWithdrawn = (float) WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_APPROVED)
            ->sum('amount');

        $pendingWithdrawal = (float) WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->sum('amount');

        $withdrawalRequests = WithdrawalRequest::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $bankAccount = BankAccount::where('user_id', $user->id)
            ->where('is_verified', true)
            ->first();

        return view('mitra.finance.index', compact(
            'wallet',
            'range',
            'transactions',
            'currentMonthIncome',
            'totalWithdrawn',
            'pendingWithdrawal',
            'withdrawalRequests',
            'bankAccount'
        ));
    }

    /**
     * Display withdrawal request form (MIT-16).
     */
    public function withdraw(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('partnerProfile');
        $wallet = $user->getOrCreateWallet();

        $bankAccount = BankAccount::where('user_id', $user->id)
            ->where('is_verified', true)
            ->first();

        if (! $bankAccount) {
            return redirect()->route('mitra.finance.index')
                ->with('error', 'Anda belum memiliki rekening bank terverifikasi. Silakan hubungi admin atau perbarui rekening di profil Anda.');
        }

        $minAmount = (float) Parameter::get('min_withdrawal_amount', 50000);
        $maxAmount = (float) Parameter::get('max_withdrawal_amount', 5000000);
        $fee = (float) Parameter::get('withdrawal_fee_amount', 0);

        $hasPending = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->exists();

        return view('mitra.finance.withdraw', compact(
            'wallet',
            'bankAccount',
            'minAmount',
            'maxAmount',
            'fee',
            'hasPending'
        ));
    }

    /**
     * Store new withdrawal request (MIT-16).
     */
    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $user = $request->user()->load('partnerProfile');
        $wallet = $user->getOrCreateWallet();

        if ($user->isSuspended()) {
            return redirect()->route('mitra.finance.index')
                ->with('error', 'Akun Anda sedang dalam masa penangguhan (suspend). Pengajuan penarikan dana tidak diizinkan.');
        }

        $bankAccount = BankAccount::where('user_id', $user->id)
            ->where('is_verified', true)
            ->first();

        if (! $bankAccount) {
            return redirect()->route('mitra.finance.index')
                ->with('error', 'Rekening bank Anda belum diverifikasi oleh admin wilayah.');
        }

        // Prevent concurrent pending withdrawals
        $hasPending = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return redirect()->route('mitra.finance.index')
                ->with('error', 'Anda masih memiliki permohonan penarikan dana yang sedang menunggu peninjauan admin.');
        }

        $minAmount = (float) Parameter::get('min_withdrawal_amount', 50000);
        $maxAmount = (float) Parameter::get('max_withdrawal_amount', 5000000);
        $fee = (float) Parameter::get('withdrawal_fee_amount', 0);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', "min:{$minAmount}", "max:{$maxAmount}"],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.min' => 'Nominal penarikan minimal Rp '.number_format($minAmount, 0, ',', '.').'.',
            'amount.max' => 'Nominal penarikan maksimal Rp '.number_format($maxAmount, 0, ',', '.').'.',
        ]);

        $amount = (float) $validated['amount'];

        if ($amount > (float) $wallet->balance) {
            return back()->withInput()->withErrors([
                'amount' => 'Saldo dompet Anda tidak mencukupi (Tersedia: Rp '.number_format($wallet->balance, 0, ',', '.').').',
            ]);
        }

        $netAmount = max(0, $amount - $fee);
        $regionId = $user->partnerProfile?->region_id ?? $user->region_id;

        DB::transaction(function () use ($user, $amount, $fee, $netAmount, $bankAccount, $regionId, $validated) {
            $withdrawal = WithdrawalRequest::create([
                'withdrawal_code' => WithdrawalRequest::generateCode(),
                'user_id' => $user->id,
                'bank_account_id' => $bankAccount->id,
                'region_id' => $regionId,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_holder_name' => $bankAccount->account_holder_name,
                'status' => WithdrawalRequest::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Deduct from partner's wallet immediately
            $this->walletService->debit(
                $user,
                $amount,
                'withdrawal_request',
                (string) $withdrawal->id,
                "Pengajuan penarikan dana ke {$bankAccount->bank_name} ({$withdrawal->withdrawal_code})",
                $regionId
            );

            // Notify Regional Admins
            if ($regionId) {
                $this->notificationService->sendToRegionAdmins(
                    $regionId,
                    'partner_withdrawal_pending',
                    'Antrean Penarikan Dana Mitra',
                    "Mitra {$user->name} mengajukan penarikan dana sebesar Rp ".number_format($amount, 0, ',', '.')." ({$withdrawal->withdrawal_code}).",
                    ['withdrawal_id' => $withdrawal->id, 'withdrawal_code' => $withdrawal->withdrawal_code, 'amount' => $amount],
                    'high'
                );
            }
        });

        return redirect()->route('mitra.finance.index')
            ->with('status', 'Permohonan penarikan dana berhasil diajukan dan sedang dalam antrean verifikasi admin wilayah.');
    }
}
