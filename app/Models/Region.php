<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'boundaries',
        'timezone',
        'status',
    ];

    /**
     * @return HasMany<AdminRegionAssignment, $this>
     */
    public function adminAssignments(): HasMany
    {
        return $this->hasMany(AdminRegionAssignment::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_region_assignments')
            ->withPivot('can_handle_emergency')
            ->withTimestamps();
    }

    /**
     * Scope for active regions.
     *
     * @param  Builder<Region>  $query
     * @return Builder<Region>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope to find regions containing a given lat/lng point.
     *
     * @param  Builder<Region>  $query
     * @return Builder<Region>
     */
    public function scopeContainsPoint(Builder $query, float $latitude, float $longitude): Builder
    {
        $pointWkt = sprintf('POINT(%F %F)', $longitude, $latitude);

        return $query->whereRaw(
            "ST_Contains(boundaries, ST_GeomFromText(?, 4326, 'axis-order=long-lat'))",
            [$pointWkt]
        );
    }

    /**
     * Check if a given polygon WKT intersects with this region.
     */
    public static function hasOverlappingRegion(string $polygonWkt, ?int $excludeRegionId = null): bool
    {
        $query = static::query();

        if ($excludeRegionId) {
            $query->where('id', '!=', $excludeRegionId);
        }

        return $query->whereRaw(
            "ST_Intersects(boundaries, ST_GeomFromText(?, 4326, 'axis-order=long-lat'))",
            [$polygonWkt]
        )->exists();
    }

    /**
     * Format array of [lng, lat] coordinate pairs into WKT POLYGON string.
     *
     * @param  array<int, array{0: float, 1: float}>  $coordinates
     */
    public static function coordinatesToWkt(array $coordinates): string
    {
        if (count($coordinates) < 3) {
            throw new \InvalidArgumentException('A polygon must have at least 3 coordinates.');
        }

        // Ensure closed ring
        $first = $coordinates[0];
        $last = end($coordinates);
        if ($first[0] !== $last[0] || $first[1] !== $last[1]) {
            $coordinates[] = $first;
        }

        $points = array_map(function ($coord) {
            return sprintf('%F %F', $coord[0], $coord[1]);
        }, $coordinates);

        return sprintf('POLYGON((%s))', implode(', ', $points));
    }

    /**
     * Convert GeoJSON geometry (Polygon or MultiPolygon) to WKT format.
     *
     * @param  array{type: string, coordinates: array<mixed>}  $geometry
     */
    public static function geoJsonToWkt(array $geometry): string
    {
        $type = strtoupper($geometry['type'] ?? '');
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'POLYGON') {
            $rings = [];
            foreach ($coordinates as $ring) {
                if (empty($ring)) {
                    continue;
                }
                // Ensure ring is closed
                if ($ring[0] !== end($ring)) {
                    $ring[] = $ring[0];
                }
                $points = array_map(fn ($pt) => sprintf('%F %F', $pt[0], $pt[1]), $ring);
                $rings[] = '('.implode(', ', $points).')';
            }

            return 'POLYGON('.implode(', ', $rings).')';
        }

        if ($type === 'MULTIPOLYGON') {
            $polygons = [];
            foreach ($coordinates as $polygon) {
                $rings = [];
                foreach ($polygon as $ring) {
                    if (empty($ring)) {
                        continue;
                    }
                    if ($ring[0] !== end($ring)) {
                        $ring[] = $ring[0];
                    }
                    $points = array_map(fn ($pt) => sprintf('%F %F', $pt[0], $pt[1]), $ring);
                    $rings[] = '('.implode(', ', $points).')';
                }
                if (! empty($rings)) {
                    $polygons[] = '('.implode(', ', $rings).')';
                }
            }

            return 'MULTIPOLYGON('.implode(', ', $polygons).')';
        }

        throw new \InvalidArgumentException("Tipe geometri tidak didukung: {$type}");
    }
}
