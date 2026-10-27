<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PartnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region_id',
        'address_text',
        'coordinate',
        'ktp_path',
        'selfie_path',
        'skck_path',
        'is_active',
        'rejection_code',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
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
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Set the spatial coordinate attribute.
     */
    public function setCoordinateAttribute(array|string|null $value): void
    {
        if (is_array($value) && isset($value['lat'], $value['lng'])) {
            $wkt = sprintf('POINT(%F %F)', (float) $value['lng'], (float) $value['lat']);
            $this->attributes['coordinate'] = DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')");
        } elseif (is_string($value)) {
            $this->attributes['coordinate'] = DB::raw("ST_GeomFromText('{$value}', 4326, 'axis-order=long-lat')");
        }
    }
}
