<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Parameter;
use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderSettlementService
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Settle completed order by holding 80% earnings in partner's held_balance (SYS-03, MIT-15).
     * Platform commission (20%) is recorded.
     */
    public function settle(Order $order): ?WalletTransaction
    {
        return $this->holdEarnings($order);
    }

    /**
     * Hold partner's order earnings for 24 hours post-completion (SYS-03).
     */
    public function holdEarnings(Order $order): ?WalletTransaction
    {
        if (! $order->mitra_id) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            // Check idempotency: prevent double hold or double credit
            $alreadyRecorded = WalletTransaction::where('user_id', $order->mitra_id)
                ->whereIn('reference_type', ['order_held_pending', 'order_earning'])
                ->where('reference_id', (string) $order->id)
                ->exists();

            if ($alreadyRecorded) {
                return null;
            }

            $commissionPercent = (float) ($order->commission_percent ?? Parameter::get('platform_commission_percent', 20.0));
            $totalPrice = (float) $order->total_price;
            $partnerEarning = round($totalPrice * (1 - ($commissionPercent / 100)), 2);

            $mitra = User::find($order->mitra_id);
            if (! $mitra || $partnerEarning <= 0) {
                return null;
            }

            // Put into held_balance (24h escrow safeguard)
            $transaction = $this->walletService->creditHeld(
                $mitra,
                $partnerEarning,
                'order_held_pending',
                (string) $order->id,
                "Pendapatan layanan pesanan #{$order->order_code} (Ditahan 24 Jam)",
                $order->region_id
            );

            $formattedEarning = number_format($partnerEarning, 0, ',', '.');
            $this->notificationService->sendToUser(
                $mitra,
                'order_earning_held',
                'Pendapatan Layanan Ditahan (24 Jam)',
                "Pendapatan sebesar Rp {$formattedEarning} dari pesanan #{$order->order_code} masuk ke Saldo Tertahan dan akan cair otomatis dalam 24 jam.",
                ['order_id' => $order->id, 'amount' => $partnerEarning, 'earning' => $partnerEarning],
                'normal'
            );

            return $transaction;
        });
    }

    /**
     * Release held earnings to available balance if 24 hours have passed and no active complaints (SYS-03).
     */
    public function releaseEarnings(Order $order): ?WalletTransaction
    {
        if (! $order->mitra_id) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            // 1. Must have an unreleased held earning
            $heldTransaction = WalletTransaction::where('user_id', $order->mitra_id)
                ->where('reference_type', 'order_held_pending')
                ->where('reference_id', (string) $order->id)
                ->first();

            if (! $heldTransaction) {
                return null;
            }

            // 2. Prevent double release
            $alreadyReleased = WalletTransaction::where('user_id', $order->mitra_id)
                ->where('reference_type', 'order_earning')
                ->where('reference_id', (string) $order->id)
                ->exists();

            if ($alreadyReleased) {
                return null;
            }

            // 3. Check for active/open customer complaints on this order
            $hasActiveComplaint = Complaint::where('order_id', $order->id)
                ->whereIn('status', [ComplaintStatus::Menunggu, ComplaintStatus::Diproses])
                ->exists();

            if ($hasActiveComplaint) {
                return null;
            }

            $partnerEarning = (float) $heldTransaction->amount;
            $mitra = User::find($order->mitra_id);
            if (! $mitra || $partnerEarning <= 0) {
                return null;
            }

            // Release from held_balance to available balance
            $transaction = $this->walletService->releaseHeld(
                $mitra,
                $partnerEarning,
                'order_earning',
                (string) $order->id,
                "Pelepasan saldo tertahan pesanan #{$order->order_code} ke saldo tersedia",
                $order->region_id
            );

            $formattedEarning = number_format($partnerEarning, 0, ',', '.');
            $this->notificationService->sendToUser(
                $mitra,
                'order_earning_released',
                'Saldo Siap Tarik Tersedia',
                "Pendapatan pesanan #{$order->order_code} sebesar Rp {$formattedEarning} telah cair dan siap ditarik.",
                ['order_id' => $order->id, 'amount' => $partnerEarning, 'earning' => $partnerEarning],
                'high'
            );

            return $transaction;
        });
    }

    /**
     * Batch process releasing all eligible held balances completed >= 24 hours ago (SYS-03).
     */
    public function releaseEligibleHeldBalances(): int
    {
        $threshold = Carbon::now()->subHours(24);

        // Find completed orders completed 24+ hours ago with held earnings pending
        $orders = Order::where('status', 'selesai')
            ->whereNotNull('mitra_id')
            ->where(function ($q) use ($threshold) {
                $q->where('service_completed_at', '<=', $threshold)
                    ->orWhere(function ($sub) use ($threshold) {
                        $sub->whereNull('service_completed_at')
                            ->where('updated_at', '<=', $threshold);
                    });
            })
            ->get();

        $releasedCount = 0;
        foreach ($orders as $order) {
            $result = $this->releaseEarnings($order);
            if ($result) {
                $releasedCount++;
            }
        }

        return $releasedCount;
    }
}
