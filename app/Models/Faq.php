<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_audience',
        'category',
        'question',
        'answer',
        'order_index',
        'is_active',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active FAQs.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific audience (mitra/pelanggan/semua).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForAudience(Builder $query, string $audience): Builder
    {
        if ($audience === 'semua') {
            return $query;
        }

        return $query->whereIn('target_audience', [$audience, 'semua']);
    }
}
