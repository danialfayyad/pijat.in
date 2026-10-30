<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'complaint_id',
        'order_id',
        'actor_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'reason',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Complaint, $this>
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
