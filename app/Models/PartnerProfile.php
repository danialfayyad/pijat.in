<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class PartnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region_id',
        'address_text',
        'coordinate',
        'current_coordinate',
        'ktp_path',
        'selfie_path',
        'skck_path',
        'is_active',
        'last_active_at',
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
            'last_active_at' => 'datetime',
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
     * @return HasMany<PartnerServicePreference, $this>
     */
    public function servicePreferences(): HasMany
    {
        return $this->hasMany(PartnerServicePreference::class);
    }

    /**
     * Check if a specific service is enabled for this partner.
     * Default is true (all active services enabled) if not explicitly set.
     */
    public function isServiceEnabled(int $serviceId): bool
    {
        $preference = $this->servicePreferences()->where('service_id', $serviceId)->first();

        return $preference ? (bool) $preference->is_enabled : true;
    }

    /**
     * Get list of enabled service IDs for this partner.
     *
     * @return array<int>
     */
    public function getEnabledServiceIds(): array
    {
        $allActiveIds = Service::where('is_active', true)->pluck('id')->all();
        $disabledIds = $this->servicePreferences()
            ->where('is_enabled', false)
            ->pluck('service_id')
            ->all();

        return array_values(array_diff($allActiveIds, $disabledIds));
    }

    /**
     * Set the spatial coordinate attribute.
     */
    public function setCoordinateAttribute(mixed $value): void
    {
        if ($value instanceof Expression) {
            $this->attributes['coordinate'] = $value;
        } elseif (is_array($value) && isset($value['lat'], $value['lng'])) {
            $wkt = sprintf('POINT(%F %F)', (float) $value['lng'], (float) $value['lat']);
            $this->attributes['coordinate'] = DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')");
        } elseif (is_string($value)) {
            $this->attributes['coordinate'] = DB::raw("ST_GeomFromText('{$value}', 4326, 'axis-order=long-lat')");
        }
    }

    /**
     * Set the spatial live coordinate attribute.
     */
    public function setCurrentCoordinateAttribute(mixed $value): void
    {
        if ($value instanceof Expression) {
            $this->attributes['current_coordinate'] = $value;
        } elseif (is_array($value) && isset($value['lat'], $value['lng'])) {
            $wkt = sprintf('POINT(%F %F)', (float) $value['lng'], (float) $value['lat']);
            $this->attributes['current_coordinate'] = DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')");
        } elseif (is_string($value)) {
            $this->attributes['current_coordinate'] = DB::raw("ST_GeomFromText('{$value}', 4326, 'axis-order=long-lat')");
        }
    }
}
