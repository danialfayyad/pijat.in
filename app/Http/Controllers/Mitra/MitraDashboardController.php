<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Services\PartnerOnboardingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MitraDashboardController extends Controller
{
    public function __construct(
        protected PartnerOnboardingService $onboardingService
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

        $profile = $user->partnerProfile?->load('region');

        return view('mitra.dashboard', compact('user', 'profile'));
    }

    /**
     * Toggle online/active receiving order status (MIT-20).
     */
    public function toggleActive(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isActive = filter_var($request->input('is_active', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $this->onboardingService->toggleOnlineStatus($user, $isActive);

            $statusText = $isActive ? 'Aktif (Siap Menerima Order)' : 'Istirahat (Nonaktif)';

            return back()->with('status', "Status kerja Anda berhasil diubah menjadi: {$statusText}.");
        } catch (DomainException $e) {
            return back()->withErrors(['is_active' => $e->getMessage()]);
        }
    }
}
