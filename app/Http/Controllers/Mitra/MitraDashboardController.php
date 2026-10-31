<?php

namespace App\Http\Controllers\Mitra;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispatch;
use App\Services\InstantOrderMatchingService;
use App\Services\PartnerOnboardingService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MitraDashboardController extends Controller
{
    public function __construct(
        protected PartnerOnboardingService $onboardingService,
        protected InstantOrderMatchingService $matchingService
    ) {}

    /**
     * Display the verified partner's main dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->isTerverifikasi()) {
            return redirect()->route('mitra.status');
        }

        $profile = $user->partnerProfile?->load(['region', 'secondaryRegion']);

        $tz = $profile?->region?->timezone ?? 'Asia/Jakarta';
        $todayStartUtc = Carbon::now($tz)->startOfDay()->utc();
        $todayEndUtc = Carbon::now($tz)->endOfDay()->utc();

        // 1. Fetch today's orders assigned to this partner (scheduled today or active instant)
        $todayOrders = Order::query()
            ->where('mitra_id', $user->id)
            ->whereIn('status', [
                OrderStatus::Dijadwalkan,
                OrderStatus::MenujuLokasi,
                OrderStatus::Tiba,
                OrderStatus::SedangBerlangsung,
                OrderStatus::Selesai,
            ])
            ->where(function ($q) use ($todayStartUtc, $todayEndUtc) {
                $q->whereBetween('scheduled_start_time', [$todayStartUtc, $todayEndUtc])
                    ->orWhere(function ($instant) use ($todayStartUtc, $todayEndUtc) {
                        $instant->whereNull('scheduled_start_time')
                            ->whereBetween('created_at', [$todayStartUtc, $todayEndUtc]);
                    });
            })
            ->with(['customer', 'customerAddress', 'service', 'region'])
            ->orderByRaw("FIELD(status, 'sedang_berlangsung', 'tiba', 'menuju_lokasi', 'dijadwalkan', 'selesai')")
            ->orderBy('scheduled_start_time', 'asc')
            ->get();

        // 2. Count available open orders in partner's operating regions
        $availableBursaCount = 0;
        if ($profile) {
            $operatingRegionIds = $profile->getOperatingRegionIds();
            $enabledServiceIds = $profile->getEnabledServiceIds();

            $availableBursaCount = Order::query()
                ->where('order_type', 'terjadwal')
                ->whereNull('mitra_id')
                ->whereIn('status', [OrderStatus::MencariMitra, OrderStatus::Dijadwalkan])
                ->whereIn('region_id', $operatingRegionIds)
                ->where(function ($q) use ($user) {
                    $q->where('preferred_gender', 'bebas')
                        ->orWhereNull('preferred_gender')
                        ->orWhere('preferred_gender', $user->gender);
                })
                ->whereIn('service_id', $enabledServiceIds)
                ->whereNotNull('scheduled_start_time')
                ->where('scheduled_start_time', '>', now())
                ->count();
        }

        return view('mitra.dashboard', compact('user', 'profile', 'todayOrders', 'availableBursaCount'));
    }

    /**
     * Toggle online/active receiving order status (MIT-04).
     */
    public function toggleActive(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);

        $lat = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $lng = $request->filled('longitude') ? (float) $request->input('longitude') : null;

        try {
            $this->onboardingService->toggleOnlineStatus($user, $isActive, $lat, $lng);

            $statusText = $isActive ? 'Aktif (Siap Menerima Order)' : 'Istirahat (Nonaktif)';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_active' => $isActive,
                    'message' => "Status kerja berhasil diubah menjadi {$statusText}.",
                ]);
            }

            return back()->with('status', "Status kerja Anda berhasil diubah menjadi: {$statusText}.");
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['is_active' => $e->getMessage()]);
        }
    }

    /**
     * Ping live location coordinates every 5 minutes while online (MIT-04).
     */
    public function pingLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user();
        $profile = $user->partnerProfile;

        if ($profile) {
            $pointWkt = sprintf('POINT(%F %F)', $validated['longitude'], $validated['latitude']);
            $profile->update([
                'current_coordinate' => DB::raw("ST_GeomFromText('{$pointWkt}', 4326, 'axis-order=long-lat')"),
                'last_active_at' => now(),
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Check pending instant order offer (MIT-05).
     */
    public function pendingOffer(Request $request): JsonResponse
    {
        $user = $request->user();

        $dispatch = OrderDispatch::with(['order.service', 'order.customerAddress'])
            ->where('partner_id', $user->id)
            ->where('status', OrderDispatch::STATUS_OFFERED)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $dispatch || ! $dispatch->order) {
            return response()->json(['has_offer' => false]);
        }

        $order = $dispatch->order;
        $orderLat = (float) ($order->customerAddress?->latitude ?? 0);
        $orderLng = (float) ($order->customerAddress?->longitude ?? 0);

        // Calculate distance from partner coordinate
        $profile = $user->partnerProfile;
        $distanceKm = 0.0;
        if ($orderLat != 0 && $orderLng != 0 && $profile) {
            $partnerLat = (float) ($profile->latitude ?? -8.1720);
            $partnerLng = (float) ($profile->longitude ?? 113.7000);

            // Haversine
            $earthRadius = 6371;
            $dLat = deg2rad($orderLat - $partnerLat);
            $dLon = deg2rad($orderLng - $partnerLng);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                cos(deg2rad($partnerLat)) * cos(deg2rad($orderLat)) *
                sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distanceKm = round($earthRadius * $c, 1);
        }

        return response()->json([
            'has_offer' => true,
            'dispatch_id' => $dispatch->id,
            'order_id' => $order->id,
            'service_name' => $order->service_name,
            'duration_minutes' => $order->duration_minutes,
            'total_price' => (float) $order->total_price,
            'formatted_price' => 'Rp '.number_format((float) $order->total_price, 0, ',', '.'),
            'distance_km' => $distanceKm > 0 ? "{$distanceKm} km" : '< 1 km',
            'remaining_seconds' => $dispatch->timeRemainingSeconds(),
        ]);
    }

    /**
     * Partner accepts the instant order offer (MIT-05).
     */
    public function acceptOffer(Request $request, OrderDispatch $dispatch): JsonResponse
    {
        $user = $request->user();
        $accepted = $this->matchingService->acceptOffer($dispatch, $user);

        if (! $accepted) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu penawaran telah habis atau pesanan sudah tidak tersedia.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diterima!',
            'redirect_url' => route('mitra.orders.my-schedule'),
        ]);
    }

    /**
     * Partner rejects the instant order offer (MIT-05).
     */
    public function rejectOffer(Request $request, OrderDispatch $dispatch): JsonResponse
    {
        $user = $request->user();
        $reason = $request->input('reason', 'Ditolak oleh mitra.');

        $rejected = $this->matchingService->rejectOffer($dispatch, $user, $reason);

        return response()->json([
            'success' => $rejected,
            'message' => 'Tawaran pesanan berhasil dilewati.',
        ]);
    }
}
