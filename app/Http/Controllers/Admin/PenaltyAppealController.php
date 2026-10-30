<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppealStatus;
use App\Http\Controllers\Controller;
use App\Models\PenaltyAppeal;
use App\Models\Region;
use App\Models\User;
use App\Services\PenaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenaltyAppealController extends Controller
{
    public function __construct(
        protected PenaltyService $penaltyService
    ) {}

    /**
     * Display a listing of penalty appeals (ADM-07).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();

        // Scope to admin's assigned active regions, or all active regions for Super Admin
        $regionIds = $admin->isSuperadmin()
            ? Region::active()->pluck('id')->all()
            : $admin->assignedRegions()->where('status', 'aktif')->pluck('regions.id')->all();

        $accessibleRegions = Region::whereIn('id', $regionIds)->orderBy('name')->get();

        $baseQuery = PenaltyAppeal::inRegions($regionIds)->with(['user', 'region', 'complaint.complaintCategory', 'reviewer']);

        // Calculate counts for tabs
        $counts = [
            'semua' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->where('status', AppealStatus::Menunggu)->count(),
            'disetujui' => (clone $baseQuery)->where('status', AppealStatus::Disetujui)->count(),
            'ditolak' => (clone $baseQuery)->where('status', AppealStatus::Ditolak)->count(),
        ];

        $query = clone $baseQuery;

        // Specific region filter
        if ($request->filled('region_id') && in_array((int) $request->input('region_id'), $regionIds, true)) {
            $query->where('region_id', (int) $request->input('region_id'));
        }

        // Filter tab
        $currentTab = $request->input('status', 'semua');
        if ($currentTab !== 'semua' && AppealStatus::tryFrom($currentTab)) {
            $query->where('status', $currentTab);
        }

        // Role filter
        if ($request->filled('role') && in_array($request->input('role'), ['mitra', 'pelanggan'], true)) {
            $role = $request->input('role');
            $query->whereHas('user', fn ($u) => $u->where('role', $role));
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('appeal_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $appeals = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.appeals.index', [
            'appeals' => $appeals,
            'counts' => $counts,
            'currentTab' => $currentTab,
            'accessibleRegions' => $accessibleRegions,
        ]);
    }

    /**
     * Display the specified penalty appeal (ADM-07).
     */
    public function show(Request $request, PenaltyAppeal $appeal): View
    {
        $this->authorizeAdminAccess($request->user(), $appeal);

        $appeal->load(['user.partnerProfile.region', 'user.customerAddresses.region', 'region', 'complaint.complaintCategory', 'reviewer']);

        return view('admin.appeals.show', [
            'appeal' => $appeal,
        ]);
    }

    /**
     * Approve the penalty appeal and reverse points (ADM-07).
     */
    public function approve(Request $request, PenaltyAppeal $appeal): RedirectResponse
    {
        $this->authorizeAdminAccess($request->user(), $appeal);

        if (! $appeal->isMenunggu()) {
            return back()->with('error', 'Permohonan banding ini telah diputuskan sebelumnya.');
        }

        $validated = $request->validate([
            'points_to_reverse' => ['required', 'integer', 'min:1', 'max:150'],
            'admin_notes' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->penaltyService->reverseAppeal(
            $appeal->user,
            $validated['points_to_reverse'],
            "Banding disetujui ({$appeal->appeal_code}): {$validated['admin_notes']}",
            $appeal->complaint,
            $request->user()
        );

        $appeal->update([
            'status' => AppealStatus::Disetujui,
            'points_to_reverse' => $validated['points_to_reverse'],
            'reviewed_by_id' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.appeals.show', $appeal)
            ->with('success', "Banding {$appeal->appeal_code} disetujui. Poin sanksi berhasil dikurangi sebesar {$validated['points_to_reverse']} poin.");
    }

    /**
     * Reject the penalty appeal (ADM-07).
     */
    public function reject(Request $request, PenaltyAppeal $appeal): RedirectResponse
    {
        $this->authorizeAdminAccess($request->user(), $appeal);

        if (! $appeal->isMenunggu()) {
            return back()->with('error', 'Permohonan banding ini telah diputuskan sebelumnya.');
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $appeal->update([
            'status' => AppealStatus::Ditolak,
            'reviewed_by_id' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.appeals.show', $appeal)
            ->with('success', "Banding {$appeal->appeal_code} ditolak. Sanksi poin tetap berlaku.");
    }

    /**
     * Check if admin has access to the appeal's region.
     */
    protected function authorizeAdminAccess(User $admin, PenaltyAppeal $appeal): void
    {
        if ($admin->isSuperadmin()) {
            return;
        }

        if (! $admin->canAccessRegion($appeal->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses untuk meninjau banding di luar wilayah penugasan Anda.');
        }
    }
}
