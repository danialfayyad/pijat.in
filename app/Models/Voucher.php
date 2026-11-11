<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;
    use LogsActivity;

    protected string $auditModule = 'voucher';

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'amount',
        'max_discount_amount',
        'min_order_amount',
        'quota',
        'used_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'quota' => 'integer',
            'used_count' => 'integer',
            'per_user_limit' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke riwayat penggunaan voucher oleh pengguna.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /**
     * Relasi ke pesanan yang menerapkan voucher ini.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope untuk voucher aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk voucher yang sedang berlaku (aktif, masa berlaku valid, kuota tersedia).
     */
    public function scopeValid(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('quota')->orWhereColumn('used_count', '<', 'quota');
            });
    }

    /**
     * Cek apakah voucher sudah kedaluwarsa.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Cek apakah voucher sudah mulai berlaku.
     */
    public function isStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    /**
     * Cek ketersediaan kuota nasional.
     */
    public function hasQuota(): bool
    {
        return $this->quota === null || $this->used_count < $this->quota;
    }

    /**
     * Validasi kelayakan voucher untuk pengguna dan nominal transaksi tertentu.
     *
     * @return array{valid: bool, message: ?string, discount_amount: float}
     */
    public function validateFor(?User $user, float $orderAmount): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => 'Voucher saat ini sedang tidak aktif.', 'discount_amount' => 0];
        }

        if (! $this->isStarted()) {
            return [
                'valid' => false,
                'message' => 'Voucher belum dapat digunakan. Berlaku mulai '.($this->starts_at ? $this->starts_at->translatedFormat('d M Y H:i') : '').'.',
                'discount_amount' => 0,
            ];
        }

        if ($this->isExpired()) {
            return ['valid' => false, 'message' => 'Voucher telah kedaluwarsa.', 'discount_amount' => 0];
        }

        if (! $this->hasQuota()) {
            return ['valid' => false, 'message' => 'Kuota pemakaian voucher ini telah habis.', 'discount_amount' => 0];
        }

        if ($this->min_order_amount > 0 && $orderAmount < (float) $this->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Minimum transaksi untuk menggunakan voucher ini adalah Rp '.number_format((float) $this->min_order_amount, 0, ',', '.').'.',
                'discount_amount' => 0,
            ];
        }

        if ($user && $this->per_user_limit > 0) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->per_user_limit) {
                return [
                    'valid' => false,
                    'message' => 'Anda telah mencapai batas maksimum pemakaian voucher ini ('.$this->per_user_limit.'x).',
                    'discount_amount' => 0,
                ];
            }
        }

        $discount = $this->calculateDiscount($orderAmount);

        return [
            'valid' => true,
            'message' => null,
            'discount_amount' => $discount,
        ];
    }

    /**
     * Hitung besaran nominal diskon dari subtotal transaksi.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = ($orderAmount * (float) $this->amount) / 100;
            if ($this->max_discount_amount !== null && (float) $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = (float) $this->amount;
        }

        return max(0, min($discount, $orderAmount));
    }

    /**
     * Teks representasi potongan diskon (e.g. "20%" atau "Rp 25.000").
     */
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            $text = rtrim(rtrim(number_format((float) $this->amount, 2, ',', '.'), '0'), ',').'%';
            if ($this->max_discount_amount && (float) $this->max_discount_amount > 0) {
                $text .= ' (Maks. Rp '.number_format((float) $this->max_discount_amount, 0, ',', '.').')';
            }

            return $text;
        }

        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }

    /**
     * Label tipe diskon berbahasa Indonesia.
     */
    public function getFormattedTypeAttribute(): string
    {
        return $this->type === self::TYPE_PERCENTAGE ? 'Persentase (%)' : 'Nominal Tetap (Rp)';
    }
}
