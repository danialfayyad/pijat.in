<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;
    use LogsActivity;

    protected string $auditModule = 'banner';

    /**
     * Preset rute halaman pelanggan yang sering dikunjungi untuk tautan aksi banner.
     *
     * @var array<string, array{label: string, default_text: string}>
     */
    public const ACTION_PRESETS = [
        '' => [
            'label' => 'Tanpa Tautan (Hanya Informasi)',
            'default_text' => '',
        ],
        '/pelanggan/catalog' => [
            'label' => 'Katalog Layanan Pijat',
            'default_text' => 'Lihat Katalog',
        ],
        '/pelanggan/orders/create?type=instan' => [
            'label' => 'Pemesanan Pijat Instan (Cepat)',
            'default_text' => 'Pesan Sekarang',
        ],
        '/pelanggan/orders/create?type=terjadwal' => [
            'label' => 'Jadwalkan Layanan Pijat',
            'default_text' => 'Jadwalkan',
        ],
        '/pelanggan/wallet/topup' => [
            'label' => 'Top Up Saldo Dompet',
            'default_text' => 'Top Up Saldo',
        ],
        '/pelanggan/wallet' => [
            'label' => 'Dompet & Riwayat Saldo',
            'default_text' => 'Buka Dompet',
        ],
        '/pelanggan/orders' => [
            'label' => 'Riwayat & Status Pesanan',
            'default_text' => 'Lihat Pesanan',
        ],
        '/pelanggan/bantuan' => [
            'label' => 'Pusat Bantuan & CS',
            'default_text' => 'Hubungi Bantuan',
        ],
        '/pelanggan/addresses' => [
            'label' => 'Kelola Alamat Pengantaran',
            'default_text' => 'Atur Alamat',
        ],
    ];

    protected $fillable = [
        'title',
        'type',
        'image_path',
        'badge_text',
        'subtitle',
        'description',
        'promo_code',
        'action_url',
        'action_text',
        'background_color',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Accessor untuk URL gambar banner.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Accessor untuk label tujuan aksi yang ramah pengguna.
     */
    public function getActionDestinationLabelAttribute(): ?string
    {
        if (empty($this->action_url)) {
            return null;
        }

        return self::ACTION_PRESETS[$this->action_url]['label'] ?? $this->action_url;
    }

    /**
     * Scope untuk banner yang aktif dan berada dalam rentang jadwal.
     *
     * @param  Builder<Banner>  $query
     * @return Builder<Banner>
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope untuk mengurutkan banner.
     *
     * @param  Builder<Banner>  $query
     * @return Builder<Banner>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}
