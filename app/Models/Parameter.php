<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'unit',
    ];

    /**
     * Get typed parameter value by key with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "sys_param_{$key}";

        return Cache::rememberForever($cacheKey, function () use ($key, $default) {
            $param = static::where('key', $key)->first();
            if (! $param) {
                return config("pijatin.{$key}", $default);
            }

            return $param->castValue($param->value);
        });
    }

    /**
     * Update parameter value, clear cache, and record audit log.
     */
    public static function set(string $key, mixed $value, ?string $reason = null, ?User $actor = null): static
    {
        $param = static::where('key', $key)->firstOrFail();
        $oldValue = $param->value;
        $newValue = (string) $value;

        if ($oldValue !== $newValue) {
            $param->update(['value' => $newValue]);

            Cache::forget("sys_param_{$key}");

            AuditLog::record([
                'user_id' => $actor?->id,
                'module' => 'parameter',
                'action' => 'update',
                'auditable_type' => static::class,
                'auditable_id' => $param->id,
                'old_values' => ['key' => $key, 'value' => $oldValue],
                'new_values' => ['key' => $key, 'value' => $newValue],
                'reason' => $reason ?? 'Perubahan parameter sistem operasional',
            ]);
        }

        return $param;
    }

    /**
     * Cast raw string value to native PHP type.
     */
    public function castValue(?string $val): mixed
    {
        if ($val === null) {
            return null;
        }

        return match ($this->type) {
            'integer' => (int) $val,
            'decimal' => (float) $val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            default => (string) $val,
        };
    }

    /**
     * Clear all parameter caches.
     */
    public static function clearCache(): void
    {
        static::cursor()->each(function (Parameter $param) {
            Cache::forget("sys_param_{$param->key}");
        });
    }
}
