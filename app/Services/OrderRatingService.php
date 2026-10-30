<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\PenaltyLedger;
use App\Models\User;
use InvalidArgumentException;

class OrderRatingService
{
    public function __construct(
        protected PenaltyService $penaltyService
    ) {}

    /**
     * Submit rating for a completed order.
     *
     * @return array{rating: OrderRating, redemptionLedger: ?PenaltyLedger}
     */
    public function submitRating(Order $order, User $fromUser, int $score): array
    {
        if ($score < 1 || $score > 5) {
            throw new InvalidArgumentException('Nilai rating harus berada di antara 1 sampai 5.');
        }

        if ($order->status !== OrderStatus::Selesai) {
            throw new InvalidArgumentException('Rating hanya dapat diberikan pada pesanan yang sudah berstatus selesai.');
        }

        $isCustomer = $fromUser->id === $order->customer_id;
        $toUserId = $isCustomer ? $order->mitra_id : $order->customer_id;
        $ratingType = $isCustomer ? 'customer_to_mitra' : 'mitra_to_customer';

        $rating = OrderRating::updateOrCreate(
            [
                'order_id' => $order->id,
                'from_user_id' => $fromUser->id,
            ],
            [
                'to_user_id' => $toUserId,
                'rating_type' => $ratingType,
                'score' => $score,
            ]
        );

        $redemptionLedger = null;

        // If a customer gives a 5-star rating to a mitra, trigger performance-based point redemption
        if ($isCustomer && $score === 5 && $order->mitra_id) {
            $mitra = User::find($order->mitra_id);
            if ($mitra && $mitra->penalty_points > 0) {
                $redemptionLedger = $this->penaltyService->redeemForFiveStar($mitra, $order);
            }
        }

        return [
            'rating' => $rating,
            'redemptionLedger' => $redemptionLedger,
        ];
    }
}
