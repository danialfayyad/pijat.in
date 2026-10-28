<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region_id',
        'label',
        'address_text',
        'benchmark',
        'latitude',
        'longitude',
        'coordinate',
        'is_primary',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
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
     * Scope query to only primary address.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
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
