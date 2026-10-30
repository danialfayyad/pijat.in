<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\PenaltyLedger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenaltyService
{
    /**
     * Threshold constants for sanctions.
     */
    public const THRESHOLD_WARNING = 30;

    public const THRESHOLD_SUSPEND_3_DAYS = 60;

    public const THRESHOLD_SUSPEND_7_DAYS = 100;

    public const THRESHOLD_BANNED = 150;

    /**
     * Apply penalty points to a user from a verified complaint violation.
     */
    public function applyViolation(
        User $targetUser,
        int $points,
        string $reason,
        ?Complaint $complaint = null,
        ?User $actor = null
    ): PenaltyLedger {
        return DB::transaction(function () use ($targetUser, $points, $reason, $complaint, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = $user->penalty_points;
            $balanceAfter = $balanceBefore + max(0, $points);

            $user->penalty_points = $balanceAfter;

            // Evaluate thresholds
            $this->evaluateSanctions($user, $balanceBefore, $balanceAfter);
            $user->save();

            return PenaltyLedger::create([
                'user_id' => $user->id,
                'complaint_id' => $complaint?->id,
                'order_id' => $complaint?->order_id,
                'actor_id' => $actor?->id,
                'type' => 'violation',
                'points' => $points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Deduct 1 penalty point upon completing an order with a 5-star rating.
     */
    public function redeemForFiveStar(User $mitra, Order $order): ?PenaltyLedger
    {
        return DB::transaction(function () use ($mitra, $order) {
            $user = User::where('id', $mitra->id)->lockForUpdate()->firstOrFail();

            if ($user->penalty_points <= 0) {
                return null;
            }

            $balanceBefore = $user->penalty_points;
            $balanceAfter = max(0, $balanceBefore - 1);

            $user->penalty_points = $balanceAfter;
            $user->save();

            return PenaltyLedger::create([
                'user_id' => $user->id,
                'complaint_id' => null,
                'order_id' => $order->id,
                'actor_id' => null,
                'type' => 'merit_redemption',
                'points' => -1,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => "Penebusan sanksi: Layanan bintang 5 selesai pada pesanan {$order->order_code}.",
            ]);
        });
    }

    /**
     * Reverse penalty points when an appeal is approved.
     */
    public function reverseAppeal(
        User $targetUser,
        int $pointsToReverse,
        string $reason,
        ?Complaint $complaint = null,
        ?User $actor = null
    ): PenaltyLedger {
        return DB::transaction(function () use ($targetUser, $pointsToReverse, $reason, $complaint, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = $user->penalty_points;
            $balanceAfter = max(0, $balanceBefore - abs($pointsToReverse));

            $user->penalty_points = $balanceAfter;

            // If points dropped below suspend thresholds, restore status if needed
            if (in_array($user->status, [UserStatus::Ditangguhkan, UserStatus::Diblokir], true) && $balanceAfter < self::THRESHOLD_BANNED) {
                if ($balanceAfter < self::THRESHOLD_SUSPEND_3_DAYS) {
                    $user->status = UserStatus::Terverifikasi;
                    $user->suspended_until = null;
                }
            }

            $user->save();

            return PenaltyLedger::create([
                'user_id' => $user->id,
                'complaint_id' => $complaint?->id,
                'order_id' => $complaint?->order_id,
                'actor_id' => $actor?->id,
                'type' => 'appeal_reversal',
                'points' => -abs($pointsToReverse),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Manual adjustment by Superadmin / Admin.
     */
    public function manualAdjustment(
        User $targetUser,
        int $pointsDelta,
        string $reason,
        User $actor
    ): PenaltyLedger {
        return DB::transaction(function () use ($targetUser, $pointsDelta, $reason, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = $user->penalty_points;
            $balanceAfter = max(0, $balanceBefore + $pointsDelta);

            $user->penalty_points = $balanceAfter;
            if ($pointsDelta > 0) {
                $this->evaluateSanctions($user, $balanceBefore, $balanceAfter);
            } elseif (in_array($user->status, [UserStatus::Ditangguhkan, UserStatus::Diblokir], true) && $balanceAfter < self::THRESHOLD_BANNED) {
                if ($balanceAfter < self::THRESHOLD_SUSPEND_3_DAYS) {
                    $user->status = UserStatus::Terverifikasi;
                    $user->suspended_until = null;
                }
            }
            $user->save();

            return PenaltyLedger::create([
                'user_id' => $user->id,
                'complaint_id' => null,
                'order_id' => null,
                'actor_id' => $actor->id,
                'type' => 'admin_adjustment',
                'points' => $pointsDelta,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Evaluate thresholds and trigger automated sanctions.
     */
    protected function evaluateSanctions(User $user, int $balanceBefore, int $balanceAfter): void
    {
        // 1. Permanent Banned
        if ($balanceAfter >= self::THRESHOLD_BANNED) {
            $user->status = UserStatus::Diblokir;
            $user->suspended_until = null;

            return;
        }

        // 2. Suspend 7 days
        if ($balanceAfter >= self::THRESHOLD_SUSPEND_7_DAYS) {
            $until = Carbon::now()->addDays(7);
            if (! $user->suspended_until || $user->suspended_until->lt($until)) {
                $user->suspended_until = $until;
            }
            $user->status = UserStatus::Ditangguhkan;

            return;
        }

        // 3. Suspend 3 days
        if ($balanceAfter >= self::THRESHOLD_SUSPEND_3_DAYS) {
            $until = Carbon::now()->addDays(3);
            if (! $user->suspended_until || $user->suspended_until->lt($until)) {
                $user->suspended_until = $until;
            }
            $user->status = UserStatus::Ditangguhkan;
        }
    }
}
