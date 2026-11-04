<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'held_balance',
        'currency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'held_balance' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<WalletTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    /**
     * Get formatted rupiah available balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->balance, 0, ',', '.');
    }

    /**
     * Get formatted rupiah held balance (24 jam).
     */
    public function getFormattedHeldBalanceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->held_balance, 0, ',', '.');
    }

    /**
     * Get combined total balance (available + held).
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->balance + (float) $this->held_balance;
    }

    /**
     * Get formatted combined total balance.
     */
    public function getFormattedTotalBalanceAttribute(): string
    {
        return 'Rp '.number_format($this->total_balance, 0, ',', '.');
    }
}
