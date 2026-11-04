<?php

namespace App\Http\Controllers\Mitra;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PartnerRelocationRequest;
use App\Models\Region;
use App\Services\NotificationService;
use App\Services\RegionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartnerRelocationController extends Controller
{
    public function __construct(
        protected RegionService $regionService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display the partner relocation request form (MIT-22).
     */
    public function create(Request $request): View
    {
        $user = $request->user()->load('partnerProfile.region');
        $profile = $user->partnerProfile;

        // Check active / scheduled orders count (Prerequisite)
        $activeOrdersCount = Order::where('mitra_id', $user->id)
            ->whereIn('status', [
                OrderStatus::Dijadwalkan,
                OrderStatus::MenujuLokasi,
                OrderStatus::Tiba,
                OrderStatus::SedangBerlangsung,
            ])
            ->count();

        $hasActiveOrders = $activeOrdersCount > 0;
        $pendingRequest = $user->pendingRelocationRequest();
        $currentRegion = $profile?->region;

        $activeRegions = Region::active()
            ->where('id', '!=', $profile?->region_id ?? 0)
            ->get(['id', 'name', 'timezone']);

        return view('mitra.relocation.create', compact(
            'user',
            'profile',
            'hasActiveOrders',
            'activeOrdersCount',
            'pendingRequest',
            'currentRegion',
            'activeRegions'
        ));
    }

    /**
     * Submit a partner relocation request (MIT-22).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user()->load('partnerProfile.region');
        $profile = $user->partnerProfile;

        if (! $profile) {
            return redirect()->route('mitra.dashboard')
                ->with('error', 'Profil kemitraan Anda tidak ditemukan.');
        }

        // 1. Strict prerequisite: No active or scheduled orders
        $activeOrdersCount = Order::where('mitra_id', $user->id)
            ->whereIn('status', [
                OrderStatus::Dijadwalkan,
                OrderStatus::MenujuLokasi,
                OrderStatus::Tiba,
                OrderStatus::SedangBerlangsung,
            ])
            ->count();

        if ($activeOrdersCount > 0) {
            return redirect()->route('mitra.relocation.create')
                ->with('error', "Permohonan pindah wilayah ditolak. Anda masih memiliki {$activeOrdersCount} pesanan aktif atau yang telah dijadwalkan.");
        }

        // 2. Prevent concurrent pending relocation requests
        if ($user->hasPendingRelocation()) {
            return redirect()->route('mitra.relocation.create')
                ->with('error', 'Anda masih memiliki permohonan perpindahan domisili yang sedang menunggu peninjauan admin wilayah tujuan.');
        }

        $validated = $request->validate([
            'address_text' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'address_text.required' => 'Alamat domisili baru wajib diisi lengkap.',
            'latitude.required' => 'Titik koordinat domisili baru pada peta wajib disematkan.',
            'longitude.required' => 'Titik koordinat domisili baru pada peta wajib disematkan.',
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        // 3. Verify destination coordinates belong to an active operating region
        $destinationRegion = $this->regionService->findActiveRegionByCoordinates($lat, $lng);
        if (! $destinationRegion) {
            return back()->withInput()->withErrors([
                'latitude' => 'Titik koordinat yang Anda tentukan berada di luar batas wilayah operasional aktif Pijat.In.',
            ]);
        }

        if ($destinationRegion->id === $profile->region_id) {
            return back()->withInput()->withErrors([
                'latitude' => "Titik koordinat masih berada di wilayah Anda saat ini ({$profile->region->name}). Untuk memperbarui alamat dalam wilayah yang sama, silakan gunakan menu Profil.",
            ]);
        }

        DB::transaction(function () use ($user, $profile, $destinationRegion, $validated, $lat, $lng) {
            $pointWkt = sprintf('POINT(%F %F)', $lng, $lat);

            $relocation = PartnerRelocationRequest::create([
                'partner_id' => $user->id,
                'from_region_id' => $profile->region_id,
                'to_region_id' => $destinationRegion->id,
                'address_text' => $validated['address_text'],
                'latitude' => $lat,
                'longitude' => $lng,
                'coordinate' => DB::raw("ST_GeomFromText('{$pointWkt}', 4326, 'axis-order=long-lat')"),
                'status' => PartnerRelocationRequest::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Freeze online status while pending destination admin review
            $profile->update(['is_active' => false]);

            // Notify Regional Admins of destination region
            $this->notificationService->sendToRegionAdmins(
                $destinationRegion->id,
                'partner_relocation_pending',
                'Permohonan Relokasi Mitra Masuk',
                "Mitra {$user->name} mengajukan perpindahan domisili dari {$profile->region->name} ke {$destinationRegion->name}.",
                [
                    'relocation_id' => $relocation->id,
                    'partner_id' => $user->id,
                    'to_region_id' => $destinationRegion->id,
                ],
                'high'
            );
        });

        return redirect()->route('mitra.relocation.create')
            ->with('status', 'Permohonan perpindahan domisili berhasil diajukan! Akun Anda dinonaktifkan sementara dan penarikan saldo ditahan hingga verifikasi Admin Wilayah Tujuan selesai.');
    }

    /**
     * Cancel an unreviewed relocation request (MIT-22).
     */
    public function cancel(Request $request, PartnerRelocationRequest $relocationRequest): RedirectResponse
    {
        $user = $request->user();

        if ($relocationRequest->partner_id !== $user->id || ! $relocationRequest->isPending()) {
            abort(403, 'Anda tidak memiliki hak untuk membatalkan permohonan ini.');
        }

        $relocationRequest->update([
            'status' => PartnerRelocationRequest::STATUS_CANCELLED,
        ]);

        return redirect()->route('mitra.relocation.create')
            ->with('status', 'Permohonan perpindahan domisili telah berhasil dibatalkan.');
    }
}
