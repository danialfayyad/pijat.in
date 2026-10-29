<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use DomainException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Atomically credit funds into a user's wallet with ledger entry (SYS-05).
     */
    public function credit(
        User $user,
        float $amount,
        string $referenceType,
        ?string $referenceId,
        string $description,
        ?int $regionId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new DomainException('Nominal penambahan saldo harus lebih besar dari Rp 0.');
        }

        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $description, $regionId) {
            // Ensure wallet exists and acquire pessimistic write lock
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $wallet) {
                $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 0, 'currency' => 'IDR']);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->update(['balance' => $balanceAfter]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'region_id' => $regionId ?? $user->region_id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Atomically debit funds from a user's wallet with negative balance prevention (SYS-05).
     */
    public function debit(
        User $user,
        float $amount,
        string $referenceType,
        ?string $referenceId,
        string $description,
        ?int $regionId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new DomainException('Nominal pemotongan saldo harus lebih besar dari Rp 0.');
        }

        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $description, $regionId) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $wallet || (float) $wallet->balance < $amount) {
                throw new DomainException('Saldo dompet tidak mencukupi untuk melakukan transaksi ini.');
            }

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $wallet->update(['balance' => $balanceAfter]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'region_id' => $regionId ?? $user->region_id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Get user's current wallet balance fresh from database.
     */
    public function getBalance(User $user): float
    {
        return (float) ($user->wallet()->value('balance') ?? 0);
    }
}
