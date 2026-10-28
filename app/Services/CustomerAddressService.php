<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class CustomerAddressService
{
    public function __construct(
        protected RegionService $regionService
    ) {}

    /**
     * Create a new customer address with spatial validation (CUS-05).
     *
     * @param  array{label: string, address_text: string, benchmark?: string|null, latitude: float, longitude: float, is_primary?: bool}  $data
     */
    public function createAddress(User $user, array $data): CustomerAddress
    {
        $lat = (float) $data['latitude'];
        $lng = (float) $data['longitude'];

        // Validate coordinates fall within an ACTIVE region
        $activeRegion = $this->regionService->findActiveRegionByCoordinates($lat, $lng);
        if (! $activeRegion) {
            throw new DomainException('Titik lokasi berada di luar jangkauan wilayah layanan aktif pijat.in.');
        }

        // The first saved address automatically becomes primary (CUS-05)
        $isFirstAddress = ! $user->customerAddresses()->exists();
        $isPrimary = $isFirstAddress || ! empty($data['is_primary']);

        return DB::transaction(function () use ($user, $data, $lat, $lng, $activeRegion, $isPrimary) {
            if ($isPrimary) {
                $user->customerAddresses()->update(['is_primary' => false]);
            }

            $address = new CustomerAddress([
                'user_id' => $user->id,
                'region_id' => $activeRegion->id,
                'label' => $data['label'] ?? 'Rumah',
                'address_text' => $data['address_text'],
                'benchmark' => $data['benchmark'] ?? null,
                'latitude' => $lat,
                'longitude' => $lng,
                'is_primary' => $isPrimary,
            ]);
            $address->coordinate = ['lat' => $lat, 'lng' => $lng];
            $address->save();

            if ($isPrimary) {
                $user->update(['region_id' => $activeRegion->id]);
            }

            return $address;
        });
    }

    /**
     * Update an existing address with spatial validation.
     *
     * @param  array{label: string, address_text: string, benchmark?: string|null, latitude: float, longitude: float}  $data
     */
    public function updateAddress(User $user, CustomerAddress $address, array $data): CustomerAddress
    {
        if ($address->user_id !== $user->id) {
            throw new DomainException('Anda tidak memiliki akses ke alamat ini.');
        }

        $lat = (float) $data['latitude'];
        $lng = (float) $data['longitude'];

        $activeRegion = $this->regionService->findActiveRegionByCoordinates($lat, $lng);
        if (! $activeRegion) {
            throw new DomainException('Titik lokasi berada di luar jangkauan wilayah layanan aktif pijat.in.');
        }

        return DB::transaction(function () use ($user, $address, $data, $lat, $lng, $activeRegion) {
            $address->region_id = $activeRegion->id;
            $address->label = $data['label'] ?? $address->label;
            $address->address_text = $data['address_text'];
            $address->benchmark = $data['benchmark'] ?? null;
            $address->latitude = $lat;
            $address->longitude = $lng;
            $address->coordinate = ['lat' => $lat, 'lng' => $lng];
            $address->save();

            if ($address->is_primary) {
                $user->update(['region_id' => $activeRegion->id]);
            }

            return $address;
        });
    }

    /**
     * Set an address as primary, switching customer region immediately (CUS-23).
     */
    public function setPrimaryAddress(User $user, CustomerAddress $address): void
    {
        if ($address->user_id !== $user->id) {
            throw new DomainException('Anda tidak memiliki akses ke alamat ini.');
        }

        DB::transaction(function () use ($user, $address) {
            $user->customerAddresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
            $address->update(['is_primary' => true]);
            $user->update(['region_id' => $address->region_id]);
        });
    }

    /**
     * Delete an address with primary re-assignment or region_id nullification (CUS-05, CUS-23).
     */
    public function deleteAddress(User $user, CustomerAddress $address): void
    {
        if ($address->user_id !== $user->id) {
            throw new DomainException('Anda tidak memiliki akses ke alamat ini.');
        }

        DB::transaction(function () use ($user, $address) {
            $wasPrimary = $address->is_primary;
            $address->delete();

            if ($wasPrimary) {
                $nextPrimary = $user->customerAddresses()->first();
                if ($nextPrimary) {
                    $nextPrimary->update(['is_primary' => true]);
                    $user->update(['region_id' => $nextPrimary->region_id]);
                } else {
                    $user->update(['region_id' => null]);
                }
            }
        });
    }
}
