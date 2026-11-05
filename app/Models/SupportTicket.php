<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_BERLANGSUNG = 'berlangsung';

    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'ticket_code',
        'user_id',
        'user_role',
        'region_id',
        'admin_id',
        'subject',
        'status',
        'priority',
        'last_message_at',
        'inactivity_warning_at',
        'closed_at',
        'closed_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_message_at' => 'datetime',
        'inactivity_warning_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * @return HasMany<SupportMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'support_ticket_id');
    }

    /**
     * Generate unique ticket code (e.g. TIK-20261104-ABCDE).
     */
    public static function generateTicketCode(): string
    {
        do {
            $code = 'TIK-'.date('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Scope for tickets scoped to specific regions.
     *
     * @param  Builder<$this>  $query
     * @param  array<int, int>  $regionIds
     * @return Builder<$this>
     */
    public function scopeInRegions(Builder $query, array $regionIds): Builder
    {
        return $query->whereIn('region_id', $regionIds);
    }

    /**
     * Scope for open / active tickets.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_MENUNGGU, self::STATUS_BERLANGSUNG]);
    }
}
