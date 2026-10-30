<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDispatch extends Model
{
    public const STATUS_OFFERED = 'offered';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'order_id',
        'partner_id',
        'offered_at',
        'expires_at',
        'status',
        'responded_at',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * Scope to currently active and non-expired offers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OFFERED)
            ->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_OFFERED && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || ($this->status === self::STATUS_OFFERED && $this->expires_at->isPast());
    }

    public function timeRemainingSeconds(): int
    {
        if (! $this->isActive()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }
}
