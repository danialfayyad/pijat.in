<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;
    use LogsActivity;

    protected string $auditModule = 'catalog';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'images',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
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

    /**
     * URL foto utama layanan (untuk thumbnail).
     */
    public function getFirstImageUrlAttribute(): ?string
    {
        if (empty($this->images) || ! is_array($this->images)) {
            return null;
        }

        $first = $this->images[0] ?? null;
        if (! $first) {
            return null;
        }

        if (str_starts_with($first, 'http://') || str_starts_with($first, 'https://')) {
            return $first;
        }

        return asset('storage/'.$first);
    }

    /**
     * Seluruh URL foto layanan.
     *
     * @return array<int, string>
     */
    public function getImageUrlsAttribute(): array
    {
        if (empty($this->images) || ! is_array($this->images)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($path): ?string {
            if (! is_string($path) || empty($path)) {
                return null;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return asset('storage/'.$path);
        }, $this->images)));
    }
}
