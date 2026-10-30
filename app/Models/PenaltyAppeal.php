<?php

namespace App\Models;

use App\Enums\AppealStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PenaltyAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'appeal_code',
        'user_id',
        'region_id',
        'complaint_id',
        'statement_letter_path',
        'evidence_photos',
        'reason',
        'status',
        'points_to_reverse',
        'reviewed_by_id',
        'reviewed_at',
        'admin_notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => AppealStatus::class,
        'points_to_reverse' => 'integer',
        'reviewed_at' => 'datetime',
        'evidence_photos' => 'array',
    ];

    /**
     * Boot model events to auto-generate unique appeal code.
     */
    protected static function booted(): void
    {
        static::creating(function (PenaltyAppeal $appeal) {
            if (empty($appeal->appeal_code)) {
                $appeal->appeal_code = self::generateCode();
            }
        });
    }

    /**
     * Generate unique appeal code: BND-YYYYMMDD-XXXX.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'BND-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (self::where('appeal_code', $code)->exists());

        return $code;
    }

    /**
     * Scope to appeals within given region IDs.
     *
     * @param  Builder<PenaltyAppeal>  $query
     * @param  array<int>  $regionIds
     * @return Builder<PenaltyAppeal>
     */
    public function scopeInRegions(Builder $query, array $regionIds): Builder
    {
        return $query->whereIn('region_id', $regionIds);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return BelongsTo<Complaint, $this>
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function isMenunggu(): bool
    {
        return $this->status === AppealStatus::Menunggu;
    }

    public function isDisetujui(): bool
    {
        return $this->status === AppealStatus::Disetujui;
    }

    public function isDitolak(): bool
    {
        return $this->status === AppealStatus::Ditolak;
    }

    public function isStatementLetterImage(): bool
    {
        $ext = strtolower(pathinfo($this->statement_letter_path ?? '', PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function isStatementLetterPdf(): bool
    {
        $ext = strtolower(pathinfo($this->statement_letter_path ?? '', PATHINFO_EXTENSION));

        return $ext === 'pdf';
    }
}
