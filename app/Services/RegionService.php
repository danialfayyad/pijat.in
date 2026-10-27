<?php

namespace App\Services;

use App\Models\AdminRegionAssignment;
use App\Models\Region;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class RegionService
{
    /**
     * Create a new region with spatial boundaries.
     *
     * @param  array<mixed>|string  $boundary  [[lng, lat], ...] or GeoJSON geometry array or WKT string
     */
    public function createRegion(
        string $name,
        array|string $boundary,
        string $timezone = 'Asia/Jakarta',
        string $status = 'nonaktif'
    ): Region {
        if (is_string($boundary)) {
            $polygonWkt = $boundary;
        } elseif (isset($boundary['type'])) {
            $polygonWkt = Region::geoJsonToWkt($boundary);
        } else {
            $polygonWkt = Region::coordinatesToWkt($boundary);
        }

        if (Region::hasOverlappingRegion($polygonWkt)) {
            throw new DomainException('Batas wilayah beririsan atau tumpang tindih dengan wilayah lain yang sudah ada.');
        }

        if ($status === 'aktif') {
            throw new DomainException('Wilayah baru harus berstatus awal nonaktif sebelum memiliki admin dan admin siaga.');
        }

        $regionId = DB::transaction(function () use ($name, $polygonWkt, $timezone, $status) {
            DB::statement(
                "INSERT INTO regions (name, boundaries, timezone, status, created_at, updated_at) 
                 VALUES (?, ST_GeomFromText(?, 4326, 'axis-order=long-lat'), ?, ?, NOW(), NOW())",
                [$name, $polygonWkt, $timezone, $status]
            );

            return DB::getPdo()->lastInsertId();
        });

        return Region::findOrFail($regionId);
    }

    /**
     * Check if a region meets activation criteria.
     */
    public function canActivate(Region $region): bool
    {
        return $region->adminAssignments()->exists();
    }

    /**
     * Activate a region if requirements are met (SA-06, BRD 4.16, 4.18).
     */
    public function activateRegion(Region $region): void
    {
        if (! $this->canActivate($region)) {
            throw new DomainException(
                'Wilayah baru hanya dapat diaktifkan jika memiliki sedikitnya satu akun Admin yang bertugas.'
            );
        }

        $region->update(['status' => 'aktif']);
    }

    /**
     * Deactivate a region.
     */
    public function deactivateRegion(Region $region): void
    {
        $region->update(['status' => 'nonaktif']);
    }

    /**
     * Assign an admin to a region (automatically includes emergency handling duty).
     */
    public function assignAdmin(User $admin, Region $region, bool $canHandleEmergency = true): AdminRegionAssignment
    {
        if (! $admin->isAdmin()) {
            throw new DomainException('Pengguna harus memiliki peran Admin.');
        }

        return AdminRegionAssignment::updateOrCreate(
            ['user_id' => $admin->id, 'region_id' => $region->id],
            ['can_handle_emergency' => $canHandleEmergency]
        );
    }

    /**
     * Revoke admin from a region with protection for active regions (SA-02, BRD 6.6).
     */
    public function revokeAdmin(User $admin, Region $region): void
    {
        $assignment = AdminRegionAssignment::where('user_id', $admin->id)
            ->where('region_id', $region->id)
            ->first();

        if (! $assignment) {
            return;
        }

        if ($region->status === 'aktif') {
            $totalAdmins = $region->adminAssignments()->count();
            if ($totalAdmins <= 1) {
                throw new DomainException('Sistem menolak pencabutan admin terakhir pada wilayah yang berstatus Aktif.');
            }
        }

        $assignment->delete();
    }

    /**
     * Find active region containing given coordinates (SYS-10).
     */
    public function findActiveRegionByCoordinates(float $latitude, float $longitude): ?Region
    {
        return Region::active()
            ->containsPoint($latitude, $longitude)
            ->first();
    }

    /**
     * Find any region (active or inactive) containing coordinates.
     */
    public function findRegionByCoordinates(float $latitude, float $longitude): ?Region
    {
        return Region::containsPoint($latitude, $longitude)->first();
    }
}
