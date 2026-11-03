<?php

namespace App\Services;

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
     * Settle completed order earnings and commissions atomically (SYS-03).
     */
    public function settle(Order $order): ?WalletTransaction
    {
        if (! $order->mitra_id) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            // Check idempotency: prevent double credit
            $alreadyCredited = WalletTransaction::where('user_id', $order->mitra_id)
                ->where('reference_type', 'order_earning')
                ->where('reference_id', (string) $order->id)
                ->exists();

            if ($alreadyCredited) {
                return null;
            }

            $commissionPercent = (float) ($order->commission_percent ?? Parameter::get('platform_commission_percent', 20.0));
            $totalPrice = (float) $order->total_price;
            $partnerEarning = round($totalPrice * (1 - ($commissionPercent / 100)), 2);

            $mitra = User::find($order->mitra_id);
            if (! $mitra || $partnerEarning <= 0) {
                return null;
            }

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
                'order_earning_released',
                'Pendapatan Layanan Masuk',
                "Pendapatan sebesar Rp {$formattedEarning} dari pesanan #{$order->order_code} telah masuk ke saldo dompet Anda.",
                ['order_id' => $order->id, 'amount' => $partnerEarning, 'earning' => $partnerEarning],
                'high'
            );

            return $transaction;
        });
    }
}
