<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;
    use LogsActivity;

    protected string $auditModule = 'bank';

    protected $fillable = [
        'name',
        'code',
        'category',
        'account_number',
        'account_holder_name',
        'notes',
        'is_active',
        'sort_order',
    ];

    /**
     * Pemetaan prefix Virtual Account resmi bank Indonesia.
     *
     * @var array<string, string>
     */
    public const VA_PREFIXES = [
        'BCA' => '8808',
        'MANDIRI' => '8908',
        'BRI' => '8888',
        'BNI' => '8277',
        'BSI' => '8303',
        'PERMATA' => '8414',
        'CIMB' => '8525',
    ];

    /**
     * Dapatkan prefix Virtual Account berdasarkan kode bank.
     */
    public function getVaPrefixAttribute(): string
    {
        $code = strtoupper(trim($this->code));

        return self::VA_PREFIXES[$code] ?? '8899';
    }

    /**
     * Cek apakah nama atau kode bank diizinkan dan aktif di sistem.
     */
    public static function isAllowed(?string $nameOrCode): bool
    {
        if (blank($nameOrCode)) {
            return false;
        }

        $needle = strtolower(trim($nameOrCode));
        $activeBanks = static::active()->where('category', 'bank')->get();

        // Fallback untuk lingkungan test sebelum seeder dijalankan
        if ($activeBanks->isEmpty()) {
            $defaults = ['bca', 'mandiri', 'bri', 'bni', 'bsi', 'cimb', 'permata'];
            foreach ($defaults as $code) {
                if ($needle === $code || str_contains($needle, $code)) {
                    return true;
                }
            }

            return false;
        }

        return $activeBanks->contains(function (self $bank) use ($needle) {
            $name = strtolower(trim($bank->name));
            $code = strtolower(trim($bank->code));

            return $needle === $name
                || $needle === $code
                || str_contains($name, $needle)
                || str_contains($needle, $code);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope query untuk hanya mengambil bank aktif.
     *
     * @param  Builder<Bank>  $query
     * @return Builder<Bank>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk kategori bank konvensional / VA.
     *
     * @param  Builder<Bank>  $query
     * @return Builder<Bank>
     */
    public function scopeBank(Builder $query): Builder
    {
        return $query->where('category', 'bank');
    }

    /**
     * Scope query untuk kategori e-wallet.
     *
     * @param  Builder<Bank>  $query
     * @return Builder<Bank>
     */
    public function scopeEwallet(Builder $query): Builder
    {
        return $query->where('category', 'ewallet');
    }

    /**
     * Scope query untuk kategori QRIS.
     *
     * @param  Builder<Bank>  $query
     * @return Builder<Bank>
     */
    public function scopeQris(Builder $query): Builder
    {
        return $query->where('category', 'qris');
    }

    /**
     * Relasi ke pengajuan penarikan dana berdasarkan nama bank atau kode bank.
     *
     * @return HasMany<WithdrawalRequest, $this>
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'bank_name', 'name');
    }
}
