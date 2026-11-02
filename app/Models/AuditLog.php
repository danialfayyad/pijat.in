<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Record an audit log event.
     *
     * @param  array<string, mixed>  $data
     */
    public static function record(array $data): static
    {
        $request = request();

        return static::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'module' => $data['module'] ?? 'general',
            'action' => $data['action'] ?? 'update',
            'auditable_type' => $data['auditable_type'] ?? null,
            'auditable_id' => $data['auditable_id'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'reason' => $data['reason'] ?? null,
            'ip_address' => $data['ip_address'] ?? $request?->ip(),
            'user_agent' => $data['user_agent'] ?? ($request ? substr((string) $request->userAgent(), 0, 500) : null),
            'created_at' => now(),
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope filter by module.
     */
    public function scopeModule(Builder $query, ?string $module): Builder
    {
        if (! $module) {
            return $query;
        }

        return $query->where('module', $module);
    }

    /**
     * Scope filter by user/actor.
     */
    public function scopeActor(Builder $query, ?int $userId): Builder
    {
        if (! $userId) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    /**
     * Scope filter by date range.
     */
    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query;
    }
}
