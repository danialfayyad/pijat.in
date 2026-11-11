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
     * Master list of supported official Indonesian banks for Virtual Account & withdrawal.
     */
    public const SUPPORTED_BANKS = [
        'BCA' => [
            'name' => 'Bank Central Asia (BCA)',
            'short_name' => 'BCA',
            'code' => 'bca',
            'va_prefix' => '8808',
            'color' => 'blue',
            'aliases' => ['BCA', 'Bank BCA', 'Bank Central Asia'],
        ],
        'Mandiri' => [
            'name' => 'Bank Mandiri',
            'short_name' => 'Mandiri',
            'code' => 'mandiri',
            'va_prefix' => '8908',
            'color' => 'amber',
            'aliases' => ['Mandiri', 'Bank Mandiri', 'Livin Mandiri'],
        ],
        'BRI' => [
            'name' => 'Bank Rakyat Indonesia (BRI)',
            'short_name' => 'BRI',
            'code' => 'bri',
            'va_prefix' => '8888',
            'color' => 'blue',
            'aliases' => ['BRI', 'Bank BRI', 'BRImo'],
        ],
        'BNI' => [
            'name' => 'Bank Negara Indonesia (BNI)',
            'short_name' => 'BNI',
            'code' => 'bni',
            'va_prefix' => '8277',
            'color' => 'teal',
            'aliases' => ['BNI', 'Bank BNI'],
        ],
        'BSI' => [
            'name' => 'Bank Syariah Indonesia (BSI)',
            'short_name' => 'BSI',
            'code' => 'bsi',
            'va_prefix' => '8455',
            'color' => 'emerald',
            'aliases' => ['BSI', 'Bank Syariah Indonesia'],
        ],
        'CIMB' => [
            'name' => 'CIMB Niaga',
            'short_name' => 'CIMB',
            'code' => 'cimb',
            'va_prefix' => '8059',
            'color' => 'rose',
            'aliases' => ['CIMB', 'CIMB Niaga', 'Bank CIMB Niaga'],
        ],
        'Permata' => [
            'name' => 'Bank Permata',
            'short_name' => 'Permata',
            'code' => 'permata',
            'va_prefix' => '8528',
            'color' => 'violet',
            'aliases' => ['Permata', 'Bank Permata'],
        ],
    ];

    /**
     * Get list of active bank keys configured by Superadmin.
     *
     * @return array<int, string>
     */
    public static function getAvailableBanks(): array
    {
        $banks = Bank::active()->orderBy('sort_order')->orderBy('name')->pluck('name')->all();

        return ! empty($banks) ? $banks : ['Bank Central Asia', 'Bank Mandiri', 'Bank Rakyat Indonesia', 'Bank Negara Indonesia'];
    }

    /**
     * Check if a given bank name/alias is allowed under Superadmin configuration.
     */
    public static function isBankAllowed(?string $bankName): bool
    {
        return Bank::isAllowed($bankName);
    }

    /**
     * Get details of active banks for payment & withdrawal interfaces.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getAvailableBankDetails(): array
    {
        $allowed = static::getAvailableBanks();
        $details = [];

        foreach ($allowed as $bankKey) {
            foreach (self::SUPPORTED_BANKS as $k => $info) {
                if (strcasecmp($k, $bankKey) === 0) {
                    $details[$k] = $info;
                    break;
                }
            }
        }

        if (empty($details)) {
            return [
                'BCA' => self::SUPPORTED_BANKS['BCA'],
                'Mandiri' => self::SUPPORTED_BANKS['Mandiri'],
                'BRI' => self::SUPPORTED_BANKS['BRI'],
                'BNI' => self::SUPPORTED_BANKS['BNI'],
            ];
        }

        return $details;
    }

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
