<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\OrderDispatch;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InstantOrderMatchingService
{
    public const OFFER_DURATION_SECONDS = 60;

    public const MAX_RADIUS_KM = 5.0;

    /**
     * Check if there are any online, eligible partners within radius (CUS-08).
     */
    public function hasAvailablePartners(
        float $lat,
        float $lng,
        string $preferredGender = 'bebas',
        int $regionId = 0,
        ?int $serviceId = null
    ): bool {
        $partners = $this->queryEligiblePartners($lat, $lng, $preferredGender, $regionId, $serviceId, []);

        return $partners->isNotEmpty();
    }

    /**
     * Get candidate partners sorted by distance from service location (SYS-01).
     *
     * @param  array<int, int>  $excludePartnerIds
     * @return Collection<int, object>
     */
    public function findEligiblePartners(Order $order, array $excludePartnerIds = []): Collection
    {
        $lat = 0.0;
        $lng = 0.0;

        if (is_array($order->service_coordinate)) {
            $lat = (float) ($order->service_coordinate['lat'] ?? 0);
            $lng = (float) ($order->service_coordinate['lng'] ?? 0);
        }

        if ($lat == 0.0 && $lng == 0.0) {
            $order->loadMissing('customerAddress');
            $lat = (float) ($order->customerAddress?->latitude ?? 0);
            $lng = (float) ($order->customerAddress?->longitude ?? 0);
        }

        $preferredGender = (string) ($order->preferred_gender ?: 'bebas');

        return $this->queryEligiblePartners(
            $lat,
            $lng,
            $preferredGender,
            (int) $order->region_id,
            $order->service_id,
            $excludePartnerIds
        );
    }

    /**
     * Internal query to find online eligible partners within radius 5 km.
     *
     * @param  array<int, int>  $excludePartnerIds
     * @return Collection<int, object>
     */
    protected function queryEligiblePartners(
        float $lat,
        float $lng,
        string $genderPreference,
        int $regionId,
        ?int $serviceId,
        array $excludePartnerIds = []
    ): Collection {
        $normalizedPref = strtolower(trim($genderPreference));
        $originWkt = sprintf('POINT(%F %F)', $lng, $lat);

        // Native MySQL spherical distance in kilometers
        $distanceSql = "(ST_Distance_Sphere(
            ST_GeomFromText('{$originWkt}', 4326, 'axis-order=long-lat'),
            COALESCE(pp.current_coordinate, pp.coordinate)
        ) / 1000.0)";

        $query = DB::table('users as u')
            ->join('partner_profiles as pp', 'pp.user_id', '=', 'u.id')
            ->where('u.role', Role::Mitra->value)
            ->where('u.status', UserStatus::Terverifikasi->value)
            ->where('pp.region_id', $regionId)
            ->where('pp.is_active', true)
            ->whereNotNull('pp.coordinate');

        if (in_array($normalizedPref, ['pria', 'wanita'], true)) {
            $query->whereRaw('LOWER(u.gender) = ?', [$normalizedPref]);
        }

        if (! empty($excludePartnerIds)) {
            $query->whereNotIn('u.id', $excludePartnerIds);
        }

        // If service is specified, check partner service preference
        if ($serviceId) {
            $query->whereExists(function ($sub) use ($serviceId) {
                $sub->select(DB::raw(1))
                    ->from('partner_service_preferences as psp')
                    ->whereColumn('psp.partner_profile_id', 'pp.id')
                    ->where('psp.service_id', $serviceId)
                    ->where('psp.is_enabled', true);
            });
        }

        // Exclude partners who are currently busy with an active order (Dijadwalkan, MenujuLokasi, MulaiLayanan)
        $query->whereNotExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('orders as o')
                ->whereColumn('o.mitra_id', 'u.id')
                ->whereIn('o.status', [
                    OrderStatus::Dijadwalkan->value,
                    OrderStatus::MenujuLokasi->value,
                    OrderStatus::Tiba->value,
                    OrderStatus::SedangBerlangsung->value,
                ]);
        });

        // Select with distance calculation
        $query->select(
            'u.id as partner_id',
            'u.name',
            'u.phone',
            'u.gender',
            'pp.id as partner_profile_id',
            'pp.region_id',
            DB::raw('ST_Latitude(COALESCE(pp.current_coordinate, pp.coordinate)) as latitude'),
            DB::raw('ST_Longitude(COALESCE(pp.current_coordinate, pp.coordinate)) as longitude'),
            DB::raw("{$distanceSql} as distance_km")
        );

        // Radius filter <= 5 km
        $query->having('distance_km', '<=', self::MAX_RADIUS_KM);
        $query->orderBy('distance_km', 'asc');

        return $query->get();
    }

    /**
     * Dispatch the next 60-second offer to the closest available partner (SYS-01 & MIT-05).
     */
    public function dispatchNextOffer(Order $order): ?OrderDispatch
    {
        return DB::transaction(function () use ($order) {
            // Lock order to check status
            $freshOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (! $freshOrder || $freshOrder->status !== OrderStatus::MencariMitra || $freshOrder->mitra_id !== null) {
                return null;
            }

            // Expire any existing active offer for this order
            OrderDispatch::where('order_id', $order->id)
                ->where('status', OrderDispatch::STATUS_OFFERED)
                ->update(['status' => OrderDispatch::STATUS_EXPIRED]);

            // Find partners already offered previously
            $alreadyOffered = OrderDispatch::where('order_id', $order->id)
                ->pluck('partner_id')
                ->toArray();

            $candidates = $this->findEligiblePartners($order, $alreadyOffered);
            $nextCandidate = $candidates->first();

            if (! $nextCandidate) {
                return null;
            }

            $now = Carbon::now();

            return OrderDispatch::create([
                'order_id' => $order->id,
                'partner_id' => $nextCandidate->partner_id,
                'offered_at' => $now,
                'expires_at' => $now->copy()->addSeconds(self::OFFER_DURATION_SECONDS),
                'status' => OrderDispatch::STATUS_OFFERED,
            ]);
        });
    }

    /**
     * Partner accepts the instant order offer (MIT-05).
     */
    public function acceptOffer(OrderDispatch $dispatch, User $partner): bool
    {
        return DB::transaction(function () use ($dispatch, $partner) {
            $lockedDispatch = OrderDispatch::where('id', $dispatch->id)->lockForUpdate()->first();
            if (! $lockedDispatch || $lockedDispatch->partner_id !== $partner->id || ! $lockedDispatch->isActive()) {
                return false;
            }

            $order = Order::where('id', $lockedDispatch->order_id)->lockForUpdate()->first();
            if (! $order || $order->status !== OrderStatus::MencariMitra || $order->mitra_id !== null) {
                $lockedDispatch->update(['status' => OrderDispatch::STATUS_EXPIRED]);

                return false;
            }

            // Accept dispatch
            $lockedDispatch->update([
                'status' => OrderDispatch::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            // Assign partner and update order status to Dijadwalkan
            $order->update([
                'mitra_id' => $partner->id,
                'status' => OrderStatus::Dijadwalkan,
            ]);

            // Log status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => OrderStatus::MencariMitra,
                'to_status' => OrderStatus::Dijadwalkan,
                'actor_id' => $partner->id,
                'actor_role' => 'mitra',
                'notes' => 'Mitra menerima pesanan instan dalam batas waktu 60 detik.',
            ]);

            return true;
        });
    }

    /**
     * Partner rejects the offer (bypasses to next partner) (MIT-05 & SYS-01).
     */
    public function rejectOffer(OrderDispatch $dispatch, User $partner, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($dispatch, $partner, $reason) {
            $lockedDispatch = OrderDispatch::where('id', $dispatch->id)->lockForUpdate()->first();
            if (! $lockedDispatch || $lockedDispatch->partner_id !== $partner->id || ! $lockedDispatch->isActive()) {
                return false;
            }

            $lockedDispatch->update([
                'status' => OrderDispatch::STATUS_REJECTED,
                'responded_at' => now(),
                'rejection_reason' => $reason ?? 'Mitra menolak tawaran.',
            ]);

            $order = Order::find($lockedDispatch->order_id);
            if ($order && $order->status === OrderStatus::MencariMitra) {
                // Dispatch next offer to the next nearest partner
                $this->dispatchNextOffer($order);
            }

            return true;
        });
    }

    /**
     * Check and expire pending offers and transition to next candidate (SYS-01).
     */
    public function processExpiredOffers(): int
    {
        $expiredCount = 0;

        $expiredDispatches = OrderDispatch::where('status', OrderDispatch::STATUS_OFFERED)
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredDispatches as $dispatch) {
            DB::transaction(function () use ($dispatch) {
                $locked = OrderDispatch::where('id', $dispatch->id)->lockForUpdate()->first();
                if ($locked && $locked->status === OrderDispatch::STATUS_OFFERED) {
                    $locked->update([
                        'status' => OrderDispatch::STATUS_EXPIRED,
                        'responded_at' => now(),
                    ]);

                    $order = Order::find($locked->order_id);
                    if ($order && $order->status === OrderStatus::MencariMitra) {
                        $this->dispatchNextOffer($order);
                    }
                }
            });
            $expiredCount++;
        }

        return $expiredCount;
    }
}
