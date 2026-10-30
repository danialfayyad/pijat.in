<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ComplaintCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'penalty_points',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'penalty_points' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ComplaintCategory $category) {
            if (empty($category->slug)) {
                $baseSlug = Str::slug($category->name, '_');
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}_{$counter}";
                    $counter++;
                }
                $category->slug = $slug;
            }
        });
    }

    /**
     * Scope active categories.
     *
     * @param  Builder<ComplaintCategory>  $query
     * @return Builder<ComplaintCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get complaints under this category.
     *
     * @return HasMany<Complaint, $this>
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complaint_category_id');
    }
}
