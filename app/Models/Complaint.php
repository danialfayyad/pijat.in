<?php

namespace App\Models;

use App\Enums\ComplaintCategory as ComplaintCategoryEnum;
use App\Enums\ComplaintStatus;
use App\Models\ComplaintCategory as ComplaintCategoryModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_code',
        'order_id',
        'region_id',
        'complainant_id',
        'complainant_role',
        'target_id',
        'complaint_category_id',
        'category',
        'title',
        'description',
        'evidence_photos',
        'status',
        'priority',
        'handled_by_id',
        'handled_at',
        'resolution_type',
        'penalty_points_applied',
        'admin_notes',
        'resolved_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ComplaintStatus::class,
        'penalty_points_applied' => 'integer',
        'evidence_photos' => 'array',
        'handled_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint) {
            if (empty($complaint->complaint_code)) {
                $complaint->complaint_code = 'ADU-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
            }
        });
    }

    /**
     * Get the master category definition.
     *
     * @return BelongsTo<ComplaintCategoryModel, $this>
     */
    public function complaintCategory(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategoryModel::class, 'complaint_category_id');
    }

    /**
     * Get the human-readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        if ($this->relationLoaded('complaintCategory') && $this->complaintCategory) {
            return $this->complaintCategory->name;
        }

        if ($this->complaint_category_id && $this->complaintCategory) {
            return $this->complaintCategory->name;
        }

        if ($this->category) {
            $val = is_string($this->category) ? $this->category : ($this->category->value ?? (string) $this->category);
            $enum = ComplaintCategoryEnum::tryFrom($val);
            if ($enum) {
                return $enum->label();
            }

            return ucfirst(str_replace('_', ' ', $val));
        }

        return 'Keluhan';
    }

    /**
     * Get default penalty points configured for this complaint's category.
     */
    public function getDefaultPenaltyPoints(): int
    {
        if ($this->relationLoaded('complaintCategory') && $this->complaintCategory) {
            return (int) $this->complaintCategory->penalty_points;
        }

        if ($this->complaint_category_id && $this->complaintCategory) {
            return (int) $this->complaintCategory->penalty_points;
        }

        return 15;
    }

    /**
     * Determine if this complaint falls under the flexible "Lainnya" category.
     */
    public function isLainnyaCategory(): bool
    {
        if ($this->relationLoaded('complaintCategory') && $this->complaintCategory) {
            return $this->complaintCategory->slug === 'lainnya';
        }

        if ($this->complaint_category_id && $this->complaintCategory) {
            return $this->complaintCategory->slug === 'lainnya';
        }

        return (string) $this->category === 'lainnya' || (string) $this->category === ComplaintCategoryEnum::Lainnya->value;
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_id');
    }

    /**
     * Scope complaints to specific region or array of region IDs.
     */
    public function scopeInRegions(Builder $query, array|int $regionIds): Builder
    {
        if (is_array($regionIds)) {
            return $query->whereIn('region_id', $regionIds);
        }

        return $query->where('region_id', $regionIds);
    }

    /**
     * Scope to pending (baru / menunggu) complaints.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ComplaintStatus::Menunggu);
    }

    /**
     * Scope to in-process complaints.
     */
    public function scopeInProcess(Builder $query): Builder
    {
        return $query->where('status', ComplaintStatus::Diproses);
    }

    /**
     * Scope to resolved complaints.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', ComplaintStatus::Selesai);
    }

    /**
     * Scope to rejected complaints.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', ComplaintStatus::Ditolak);
    }

    public function isPending(): bool
    {
        return $this->status === ComplaintStatus::Menunggu;
    }

    public function isInProcess(): bool
    {
        return $this->status === ComplaintStatus::Diproses;
    }

    public function isResolved(): bool
    {
        return $this->status === ComplaintStatus::Selesai;
    }

    public function isRejected(): bool
    {
        return $this->status === ComplaintStatus::Ditolak;
    }

    public function resolutionLabel(): ?string
    {
        return match ($this->resolution_type) {
            'refund_penuh' => 'Setujui dengan Refund Penuh ke Pelanggan',
            'terbukti_bersalah' => "Terbukti Bersalah (Sanksi +{$this->penalty_points_applied} Poin)",
            'rekomendasi_sp' => "Terbukti Bersalah (Sanksi +{$this->penalty_points_applied} Poin)",
            'damai_tanpa_tindakan' => 'Selesai Damai / Mediasi',
            'ditolak' => 'Aduan Ditolak / Tidak Berdasar',
            default => $this->resolution_type ? ucfirst(str_replace('_', ' ', $this->resolution_type)) : null,
        };
    }
}
