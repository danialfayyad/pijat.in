<?php

namespace App\Http\Controllers\Mitra;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MitraOrderController extends Controller
{
    public const MAX_SCHEDULED_RADIUS_KM = 10.0;

    /**
     * Display available scheduled orders for partner (MIT-06).
     */
    public function scheduled(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('partnerProfile');
        $profile = $user->partnerProfile;

        if (! $profile || ! $user->isTerverifikasi()) {
            return redirect()->route('mitra.status')
                ->with('error', 'Akun kemitraan Anda belum diverifikasi untuk mengambil pesanan.');
        }

        $enabledServiceIds = $profile->getEnabledServiceIds();

        $partnerCoords = DB::table('partner_profiles')
            ->selectRaw('ST_Latitude(COALESCE(current_coordinate, coordinate)) as lat, ST_Longitude(COALESCE(current_coordinate, coordinate)) as lng')
            ->where('user_id', $user->id)
            ->first();

        $partnerLat = $partnerCoords?->lat ? (float) $partnerCoords->lat : null;
        $partnerLng = $partnerCoords?->lng ? (float) $partnerCoords->lng : null;

        // 1. Base query for available scheduled orders
        $candidateOrders = Order::query()
            ->select('orders.*')
            ->selectRaw('ST_Latitude(orders.service_coordinate) as service_lat, ST_Longitude(orders.service_coordinate) as service_lng')
            ->where('order_type', 'terjadwal')
            ->whereNull('mitra_id')
            ->whereIn('status', [OrderStatus::MencariMitra, OrderStatus::Dijadwalkan])
            ->whereIn('region_id', $profile->getOperatingRegionIds())
            ->where(function ($q) use ($user) {
                $q->where('preferred_gender', 'bebas')
                    ->orWhereNull('preferred_gender')
                    ->orWhere('preferred_gender', $user->gender);
            })
            ->whereIn('service_id', $enabledServiceIds)
            ->whereNotNull('scheduled_start_time')
            ->where('scheduled_start_time', '>', now())
            ->with(['service', 'region', 'customerAddress'])
            ->orderBy('scheduled_start_time', 'asc')
            ->get();

        // 2. Filter out orders that have schedule clash or exceed 10 km radius
        $availableOrders = $candidateOrders->map(function (Order $order) use ($partnerLat, $partnerLng) {
            $orderLat = $order->service_lat !== null ? (float) $order->service_lat : ($order->customerAddress?->latitude ? (float) $order->customerAddress->latitude : null);
            $orderLng = $order->service_lng !== null ? (float) $order->service_lng : ($order->customerAddress?->longitude ? (float) $order->customerAddress->longitude : null);

            if ($partnerLat !== null && $partnerLng !== null && $orderLat !== null && $orderLng !== null) {
                $order->distance_km = $this->calculateDistance($partnerLat, $partnerLng, $orderLat, $orderLng);
            } else {
                $order->distance_km = null;
            }

            return $order;
        })->filter(function (Order $order) use ($user) {
            if ($this->hasScheduleClash($user, $order)) {
                return false;
            }
            if ($order->distance_km !== null && $order->distance_km > self::MAX_SCHEDULED_RADIUS_KM) {
                return false;
            }

            return true;
        });

        return view('mitra.orders.scheduled', compact('availableOrders', 'profile'));
    }

    /**
     * Take a scheduled order via First Come First Served (MIT-06).
     */
    public function take(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user()->load('partnerProfile');
        $profile = $user->partnerProfile;

        if (! $profile || ! $user->isTerverifikasi()) {
            return redirect()->route('mitra.orders.scheduled')
                ->with('error', 'Akun kemitraan Anda belum aktif atau belum diverifikasi.');
        }

        try {
            DB::transaction(function () use ($order, $user, $profile, $request) {
                // Lock row for update to prevent race conditions
                $lockedOrder = Order::where('id', $order->id)
                    ->select('orders.*')
                    ->selectRaw('ST_Latitude(orders.service_coordinate) as service_lat, ST_Longitude(orders.service_coordinate) as service_lng')
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check if already taken
                if ($lockedOrder->mitra_id !== null || ! in_array($lockedOrder->status, [OrderStatus::MencariMitra, OrderStatus::Dijadwalkan])) {
                    throw new \RuntimeException('Pesanan ini telah diambil oleh mitra lain.');
                }

                // Verify operating region
                if (! in_array($lockedOrder->region_id, $profile->getOperatingRegionIds(), true)) {
                    throw new \RuntimeException('Pesanan ini berada di luar wilayah operasional Anda.');
                }

                // Verify distance limit (10 km)
                $partnerCoords = DB::table('partner_profiles')
                    ->selectRaw('ST_Latitude(COALESCE(current_coordinate, coordinate)) as lat, ST_Longitude(COALESCE(current_coordinate, coordinate)) as lng')
                    ->where('user_id', $user->id)
                    ->first();

                $partnerLat = $request->filled('latitude') ? (float) $request->input('latitude') : ($partnerCoords?->lat ? (float) $partnerCoords->lat : null);
                $partnerLng = $request->filled('longitude') ? (float) $request->input('longitude') : ($partnerCoords?->lng ? (float) $partnerCoords->lng : null);

                if ($request->filled(['latitude', 'longitude'])) {
                    $reqLat = (float) $request->input('latitude');
                    $reqLng = (float) $request->input('longitude');
                    if ($reqLat != 0.0 && $reqLng != 0.0) {
                        DB::table('partner_profiles')
                            ->where('id', $profile->id)
                            ->update([
                                'current_coordinate' => DB::raw("ST_SRID(POINT({$reqLng}, {$reqLat}), 4326)"),
                                'last_active_at' => now(),
                            ]);
                    }
                }

                $orderLat = $lockedOrder->service_lat !== null ? (float) $lockedOrder->service_lat : ($lockedOrder->customerAddress?->latitude ? (float) $lockedOrder->customerAddress->latitude : null);
                $orderLng = $lockedOrder->service_lng !== null ? (float) $lockedOrder->service_lng : ($lockedOrder->customerAddress?->longitude ? (float) $lockedOrder->customerAddress->longitude : null);

                if ($partnerLat !== null && $partnerLng !== null && $orderLat !== null && $orderLng !== null) {
                    $distance = $this->calculateDistance($partnerLat, $partnerLng, $orderLat, $orderLng);
                    if ($distance > self::MAX_SCHEDULED_RADIUS_KM) {
                        throw new \RuntimeException("Jarak lokasi Anda saat ini ({$distance} km) melebihi batas maksimal 10 km dari alamat pelanggan.");
                    }
                }

                // Gender preference check (Opsi A)
                $pref = $lockedOrder->preferred_gender ?? 'bebas';
                if ($pref !== 'bebas' && strtolower($pref) !== strtolower((string) $user->gender)) {
                    throw new \RuntimeException('Pesanan ini memerlukan mitra terapis '.$pref.'.');
                }

                // Verify partner service preference
                if (! $profile->isServiceEnabled($lockedOrder->service_id)) {
                    throw new \RuntimeException('Layanan ini dinonaktifkan dalam preferensi layanan Anda.');
                }

                // Verify schedule clash
                if ($this->hasScheduleClash($user, $lockedOrder)) {
                    throw new \RuntimeException('Jadwal pesanan ini bentrok dengan jadwal pesanan Anda yang lain.');
                }

                $previousStatus = $lockedOrder->status;
                $lockedOrder->update([
                    'mitra_id' => $user->id,
                    'status' => OrderStatus::Dijadwalkan,
                ]);

                $lockedOrder->statusHistories()->create([
                    'from_status' => $previousStatus->value,
                    'to_status' => OrderStatus::Dijadwalkan->value,
                    'actor_id' => $user->id,
                    'actor_role' => 'mitra',
                    'note' => 'Pesanan terjadwal berhasil diambil oleh mitra (FCFS)',
                ]);
            });

            return redirect()->route('mitra.orders.my-schedule')
                ->with('status', 'Berhasil mengambil pesanan! Pesanan telah masuk ke daftar Jadwal Saya.');
        } catch (\RuntimeException $e) {
            return redirect()->route('mitra.orders.scheduled')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display partner's accepted scheduled orders with medical notes (MIT-07).
     */
    public function mySchedule(Request $request): View
    {
        $user = $request->user();

        $myOrders = Order::query()
            ->where('mitra_id', $user->id)
            ->whereIn('status', [
                OrderStatus::Dijadwalkan,
                OrderStatus::MenujuLokasi,
                OrderStatus::Tiba,
                OrderStatus::SedangBerlangsung,
            ])
            ->with(['service', 'region'])
            ->orderBy('scheduled_start_time', 'asc')
            ->get();

        return view('mitra.orders.my-schedule', compact('myOrders'));
    }

    /**
     * Check if a candidate order clashes with any of partner's existing orders.
     */
    public function hasScheduleClash(User $partner, Order $targetOrder): bool
    {
        if (! $targetOrder->scheduled_start_time) {
            return false;
        }

        $targetStart = Carbon::parse($targetOrder->scheduled_start_time);
        $targetEnd = $targetStart->copy()->addMinutes($targetOrder->duration_minutes);

        $existingOrders = Order::query()
            ->where('mitra_id', $partner->id)
            ->whereIn('status', [
                OrderStatus::Dijadwalkan,
                OrderStatus::MenujuLokasi,
                OrderStatus::Tiba,
                OrderStatus::SedangBerlangsung,
            ])
            ->where('id', '!=', $targetOrder->id)
            ->whereNotNull('scheduled_start_time')
            ->get();

        foreach ($existingOrders as $existing) {
            $existingStart = Carbon::parse($existing->scheduled_start_time);
            $existingEnd = $existingStart->copy()->addMinutes($existing->duration_minutes);

            // Overlap condition: (StartA < EndB) and (EndA > StartB)
            if ($existingStart->lt($targetEnd) && $existingEnd->gt($targetStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display detailed order view with interactive map, customer location & distance.
     */
    public function show(Request $request, Order $order): View|RedirectResponse
    {
        $user = $request->user()->load('partnerProfile');
        $profile = $user->partnerProfile;

        // Authorization check: partner owns this order, or order is available in bursa
        $isAssigned = $order->mitra_id === $user->id;
        $isAvailableInBursa = $order->order_type === OrderType::Terjadwal
            && $order->mitra_id === null
            && in_array($order->status, [OrderStatus::MencariMitra, OrderStatus::Dijadwalkan], true)
            && $profile
            && in_array($order->region_id, $profile->getOperatingRegionIds(), true);

        if (! $isAssigned && ! $isAvailableInBursa) {
            abort(403, 'Akses tidak sah untuk melihat pesanan ini.');
        }

        $order->load(['customer', 'customerAddress', 'service', 'servicePackage', 'region', 'statusHistories']);

        $orderCoords = DB::table('orders')
            ->selectRaw('ST_Latitude(service_coordinate) as lat, ST_Longitude(service_coordinate) as lng')
            ->where('id', $order->id)
            ->first();

        // Customer coordinate from service_coordinate or customerAddress or fallback
        $customerLat = (float) ($orderCoords?->lat ?? $order->customerAddress?->latitude ?? -8.1720);
        $customerLng = (float) ($orderCoords?->lng ?? $order->customerAddress?->longitude ?? 113.7000);

        // Partner coordinate from current_coordinate or base coordinate
        $profileCoords = DB::table('partner_profiles')
            ->selectRaw('ST_Latitude(COALESCE(current_coordinate, coordinate)) as lat, ST_Longitude(COALESCE(current_coordinate, coordinate)) as lng')
            ->where('user_id', $user->id)
            ->first();

        $mitraLat = $profileCoords?->lat ? (float) $profileCoords->lat : $customerLat;
        $mitraLng = $profileCoords?->lng ? (float) $profileCoords->lng : $customerLng;

        $distanceKm = null;
        if ($profileCoords?->lat && $profileCoords?->lng) {
            $distanceKm = $this->calculateDistance($mitraLat, $mitraLng, $customerLat, $customerLng);
        }

        $maxRadiusKm = self::MAX_SCHEDULED_RADIUS_KM;

        $myRating = OrderRating::where('order_id', $order->id)
            ->where('from_user_id', $user->id)
            ->first();

        return view('mitra.orders.show', compact(
            'order',
            'profile',
            'isAssigned',
            'customerLat',
            'customerLng',
            'mitraLat',
            'mitraLng',
            'distanceKm',
            'maxRadiusKm',
            'myRating'
        ));
    }

    /**
     * Update order progress status by assigned partner.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if ($order->mitra_id !== $user->id) {
            abort(403, 'Anda bukan mitra penanggung jawab untuk pesanan ini.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:menuju_lokasi,tiba,sedang_berlangsung,selesai',
        ]);

        $targetStatus = OrderStatus::from($validated['status']);
        $currentStatus = $order->status;

        // Idempotency: If order is already in the target status or has progressed past it (SYS-09)
        if ($currentStatus === $targetStatus) {
            return redirect()->route('mitra.orders.show', $order)
                ->with('status', 'Status pesanan sudah '.$targetStatus->label().'.');
        }

        $progressionWeights = [
            OrderStatus::Dijadwalkan->value => 1,
            OrderStatus::MenujuLokasi->value => 2,
            OrderStatus::Tiba->value => 3,
            OrderStatus::SedangBerlangsung->value => 4,
            OrderStatus::Selesai->value => 5,
        ];

        if (($progressionWeights[$currentStatus->value] ?? 0) > ($progressionWeights[$targetStatus->value] ?? 0)) {
            return redirect()->route('mitra.orders.show', $order)
                ->with('status', 'Pesanan telah melewati tahapan '.$targetStatus->label().'.');
        }

        // Valid status progression:
        // Dijadwalkan -> MenujuLokasi -> Tiba -> SedangBerlangsung -> Selesai
        $allowedTransitions = [
            OrderStatus::Dijadwalkan->value => [OrderStatus::MenujuLokasi],
            OrderStatus::MenujuLokasi->value => [OrderStatus::Tiba],
            OrderStatus::Tiba->value => [OrderStatus::SedangBerlangsung],
            OrderStatus::SedangBerlangsung->value => [OrderStatus::Selesai],
        ];

        $validNextStatuses = $allowedTransitions[$currentStatus->value] ?? [];

        if (! in_array($targetStatus, $validNextStatuses, true)) {
            return redirect()->back()
                ->with('error', 'Transisi status tidak valid dari '.$currentStatus->label().' ke '.$targetStatus->label().'.');
        }

        // MIT-10: Geofence radius validation (50 meters) on arrival with graceful fallback
        if ($targetStatus === OrderStatus::Tiba && ! $request->boolean('force_arrived')) {
            $orderCoords = DB::table('orders')
                ->selectRaw('ST_Latitude(service_coordinate) as lat, ST_Longitude(service_coordinate) as lng')
                ->where('id', $order->id)
                ->first();

            $custLat = (float) ($orderCoords?->lat ?? $order->customerAddress?->latitude ?? 0);
            $custLng = (float) ($orderCoords?->lng ?? $order->customerAddress?->longitude ?? 0);

            $partnerLat = $request->filled('latitude') ? (float) $request->input('latitude') : null;
            $partnerLng = $request->filled('longitude') ? (float) $request->input('longitude') : null;

            if (! $partnerLat || ! $partnerLng) {
                $profileCoords = DB::table('partner_profiles')
                    ->selectRaw('ST_Latitude(COALESCE(current_coordinate, coordinate)) as lat, ST_Longitude(COALESCE(current_coordinate, coordinate)) as lng')
                    ->where('user_id', $user->id)
                    ->first();
                $partnerLat = (float) ($profileCoords?->lat ?? 0);
                $partnerLng = (float) ($profileCoords?->lng ?? 0);
            }

            if ($custLat && $custLng && $partnerLat && $partnerLng) {
                $distanceMeters = $this->calculateDistance($partnerLat, $partnerLng, $custLat, $custLng) * 1000;
                if ($distanceMeters > 50) {
                    return redirect()->route('mitra.orders.show', $order)
                        ->with('location_warning', [
                            'distance_meters' => round($distanceMeters),
                            'message' => 'Jarak Anda terdeteksi '.round($distanceMeters).' meter dari lokasi pelanggan. Mohon mendekat ke lokasi atau konfirmasi kedatangan manual.',
                        ]);
                }
            }
        }

        DB::transaction(function () use ($order, $targetStatus, $currentStatus, $user, $request) {
            $updatePayload = ['status' => $targetStatus];

            if ($targetStatus === OrderStatus::SedangBerlangsung && ! $order->service_started_at) {
                $updatePayload['service_started_at'] = now();
            }

            if ($targetStatus === OrderStatus::Selesai && ! $order->service_completed_at) {
                $updatePayload['service_completed_at'] = now();
            }

            $order->update($updatePayload);

            $forceNote = ($targetStatus === OrderStatus::Tiba && $request->boolean('force_arrived'))
                ? ' (Konfirmasi kedatangan manual / toleransi GPS)'
                : '';

            $notes = [
                OrderStatus::MenujuLokasi->value => 'Mitra sedang dalam perjalanan menuju lokasi pelanggan',
                OrderStatus::Tiba->value => 'Mitra telah tiba di lokasi pelanggan'.$forceNote,
                OrderStatus::SedangBerlangsung->value => 'Mitra memulai sesi layanan pijat',
                OrderStatus::Selesai->value => 'Sesi layanan telah selesai dilaksanakan',
            ];

            $order->statusHistories()->create([
                'from_status' => $currentStatus->value,
                'to_status' => $targetStatus->value,
                'actor_id' => $user->id,
                'actor_role' => 'mitra',
                'note' => $notes[$targetStatus->value] ?? 'Status pesanan diperbarui oleh mitra',
            ]);
        });

        $successMessages = [
            OrderStatus::MenujuLokasi->value => 'Status diperbarui: Anda sedang dalam perjalanan ke lokasi pelanggan.',
            OrderStatus::Tiba->value => 'Status diperbarui: Anda telah tiba di lokasi pelanggan.',
            OrderStatus::SedangBerlangsung->value => 'Status diperbarui: Sesi layanan pijat dimulai.',
            OrderStatus::Selesai->value => 'Layanan selesai! Terima kasih atas dedikasi Anda.',
        ];

        return redirect()->route('mitra.orders.show', $order)
            ->with('status', $successMessages[$targetStatus->value] ?? 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Calculate spherical distance in kilometers using Haversine formula.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 1);
    }
}
