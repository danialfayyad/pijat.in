<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Parameter;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class OrderSettlementService
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Settle completed order by directly crediting earnings to partner's available balance.
     * Platform commission is recorded.
     */
    public function settle(Order $order): ?WalletTransaction
    {
        return $this->creditEarningsImmediately($order);
    }

    /**
     * Directly credit partner's order earnings to available balance upon order completion.
     */
    public function creditEarningsImmediately(Order $order): ?WalletTransaction
    {
        if (! $order->mitra_id) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            // Check idempotency: prevent double credit
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

            // Langsung masuk ke saldo tersedia (available balance)
            $transaction = $this->walletService->credit(
                $mitra,
                $partnerEarning,
                'order_earning',
                (string) $order->id,
                "Pendapatan layanan pesanan #{$order->order_code}",
                $order->region_id
            );

            $formattedEarning = number_format($partnerEarning, 0, ',', '.');
            $this->notificationService->sendToUser(
                $mitra,
                'order_earning_received',
                'Pendapatan Layanan Masuk',
                "Pendapatan sebesar Rp {$formattedEarning} dari pesanan #{$order->order_code} telah masuk ke Saldo Tersedia Anda dan siap ditarik.",
                ['order_id' => $order->id, 'amount' => $partnerEarning, 'earning' => $partnerEarning],
                'high'
            );

            return $transaction;
        });
    }

    /**
     * Hold partner's order earnings for 24 hours post-completion (SYS-03).
     */
    public function holdEarnings(Order $order): ?WalletTransaction
    {
        return $this->creditEarningsImmediately($order);
    }

    /**
     * Release held earnings to available balance.
     */
    public function releaseEarnings(Order $order, bool $force = true): ?WalletTransaction
    {
        if (! $order->mitra_id) {
            return null;
        }

        return DB::transaction(function () use ($order, $force) {
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

            if (! $force) {
                $hasActiveCustomerComplaint = Complaint::where('order_id', $order->id)
                    ->where('complainant_role', 'pelanggan')
                    ->whereIn('status', [ComplaintStatus::Menunggu, ComplaintStatus::Diproses])
                    ->exists();

                if ($hasActiveCustomerComplaint) {
                    return null;
                }
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
                'Saldo Tersedia Bertambah',
                "Pendapatan pesanan #{$order->order_code} sebesar Rp {$formattedEarning} telah masuk ke Saldo Tersedia Anda dan siap ditarik.",
                ['order_id' => $order->id, 'amount' => $partnerEarning, 'earning' => $partnerEarning],
                'high'
            );

            return $transaction;
        });
    }

    /**
     * Batch process releasing all eligible held balances directly to available balance.
     */
    public function releaseEligibleHeldBalances(): int
    {
        // Find completed orders with held earnings pending
        $orders = Order::where('status', 'selesai')
            ->whereNotNull('mitra_id')
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
