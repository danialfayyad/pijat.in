<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use App\Services\PartnerVerificationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnerVerificationController extends Controller
{
    public function __construct(
        protected PartnerVerificationService $verificationService
    ) {}

    /**
     * Display a listing of pending partner applications scoped to the admin's regions (ADM-02).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();
        $assignedRegions = $admin->isSuperadmin()
            ? Region::active()->get()
            : $admin->assignedRegions()->where('status', 'aktif')->get();

        $selectedRegionId = $request->filled('region_id') ? (int) $request->input('region_id') : null;

        $pendingPartners = $this->verificationService->getPendingPartners($admin, $selectedRegionId);

        return view('admin.mitra.index', compact('pendingPartners', 'assignedRegions', 'selectedRegionId'));
    }

    /**
     * Show detail of a partner application for verification inspection (ADM-02, ADM-03).
     */
    public function show(User $mitra, Request $request): View|RedirectResponse
    {
        $admin = $request->user();
        $profile = $mitra->partnerProfile?->load(['region', 'secondaryRegion']);

        if (! $profile || ! $admin->canAccessRegion($profile->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses terhadap mitra di luar wilayah tugas Anda.');
        }

        $bankAccount = $mitra->primaryBankAccount;
        $standardReasons = PartnerVerificationService::STANDARD_REASONS;

        return view('admin.mitra.show', compact('mitra', 'profile', 'bankAccount', 'standardReasons'));
    }

    /**
     * Approve a partner application (ADM-02).
     */
    public function approve(User $mitra, Request $request): RedirectResponse
    {
        $admin = $request->user();

        try {
            $this->verificationService->approvePartner($admin, $mitra);

            return redirect()->route('admin.mitra.index')
                ->with('status', "Mitra {$mitra->name} berhasil diverifikasi dan akunnya kini aktif!");
        } catch (DomainException $e) {
            return back()->withErrors(['approval' => $e->getMessage()]);
        }
    }

    /**
     * Reject a partner application with standard reason code (ADM-02).
     */
    public function reject(User $mitra, Request $request): RedirectResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'rejection_code' => ['required', Rule::in(array_keys(PartnerVerificationService::STANDARD_REASONS))],
            'custom_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->verificationService->rejectPartner(
                $admin,
                $mitra,
                $validated['rejection_code'],
                $validated['custom_note'] ?? null
            );

            return redirect()->route('admin.mitra.index')
                ->with('status', "Pendaftaran mitra {$mitra->name} telah ditolak dengan alasan tercatat.");
        } catch (DomainException $e) {
            return back()->withErrors(['rejection' => $e->getMessage()]);
        }
    }
}
