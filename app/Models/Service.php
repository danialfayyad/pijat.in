<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Service $service): void {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    /**
     * @return HasMany<ServicePackage, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class)->orderBy('duration_minutes');
    }

    /**
     * @return HasMany<ServicePackage, $this>
     */
    public function activePackages(): HasMany
    {
        return $this->hasMany(ServicePackage::class)
            ->where('is_active', true)
            ->orderBy('duration_minutes');
    }

    /**
     * Scope for active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
