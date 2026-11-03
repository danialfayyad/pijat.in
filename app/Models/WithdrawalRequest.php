<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WithdrawalRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'withdrawal_code',
        'user_id',
        'bank_account_id',
        'region_id',
        'amount',
        'fee',
        'net_amount',
        'bank_name',
        'account_number',
        'account_holder_name',
        'status',
        'notes',
        'rejection_reason',
        'proof_path',
        'transfer_reference',
        'processed_by',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * User who requested withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Associated bank account if any.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Operating region for permanent financial attribution.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Admin who processed this request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope query to specific region IDs.
     *
     * @param  Builder<WithdrawalRequest>  $query
     * @param  array<int>  $regionIds
     * @return Builder<WithdrawalRequest>
     */
    public function scopeInRegions(Builder $query, array $regionIds): Builder
    {
        return $query->whereIn('region_id', $regionIds);
    }

    /**
     * Scope query to pending status.
     *
     * @param  Builder<WithdrawalRequest>  $query
     * @return Builder<WithdrawalRequest>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to approved status.
     *
     * @param  Builder<WithdrawalRequest>  $query
     * @return Builder<WithdrawalRequest>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope query to rejected status.
     *
     * @param  Builder<WithdrawalRequest>  $query
     * @return Builder<WithdrawalRequest>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Generate unique withdrawal code.
     */
    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return "WD-{$date}-{$random}";
    }
}
