<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use App\Services\PenaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenaltyMonitoringController extends Controller
{
    public function __construct(
        protected PenaltyService $penaltyService
    ) {}

    /**
     * Display a listing of penalized users (ADM-06).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();

        // Scope to admin's assigned active regions, or all active regions for Super Admin
        $regionIds = $admin->isSuperadmin()
            ? Region::active()->pluck('id')->all()
            : $admin->assignedRegions()->where('status', 'aktif')->pluck('regions.id')->all();

        $accessibleRegions = Region::whereIn('id', $regionIds)->orderBy('name')->get();

        // Base query: users with penalty points or sanctions
        $baseQuery = User::query()
            ->where(function ($q) {
                $q->where('penalty_points', '>', 0)
                    ->orWhereIn('status', [UserStatus::Ditangguhkan, UserStatus::Diblokir])
                    ->orWhereNotNull('suspended_until')
                    ->orWhereHas('penaltyLedgers');
            });

        // Scope by region
        if (! $admin->isSuperadmin()) {
            $baseQuery->where(function ($q) use ($regionIds) {
                $q->whereHas('partnerProfile', fn ($p) => $p->whereIn('region_id', $regionIds))
                    ->orWhereHas('customerAddresses', fn ($a) => $a->whereIn('region_id', $regionIds))
                    ->orWhereHas('complaintsAsTarget', fn ($c) => $c->whereIn('region_id', $regionIds));
            });
        }

        // Calculate counts for metrics & tabs
        $counts = [
            'semua' => (clone $baseQuery)->count(),
            'peringatan' => (clone $baseQuery)->where('penalty_points', '>=', PenaltyService::THRESHOLD_WARNING)
                ->where('penalty_points', '<', PenaltyService::THRESHOLD_SUSPEND_3_DAYS)
                ->count(),
            'tersuspend' => (clone $baseQuery)->where(function ($q) {
                $q->where('status', UserStatus::Ditangguhkan)
                    ->orWhere(function ($sub) {
                        $sub->where('penalty_points', '>=', PenaltyService::THRESHOLD_SUSPEND_3_DAYS)
                            ->where('penalty_points', '<', PenaltyService::THRESHOLD_BANNED);
                    });
            })->count(),
            'banned' => (clone $baseQuery)->where(function ($q) {
                $q->where('status', UserStatus::Diblokir)
                    ->orWhere('penalty_points', '>=', PenaltyService::THRESHOLD_BANNED);
            })->count(),
        ];

        // Specific region filter
        $query = clone $baseQuery;
        if ($request->filled('region_id') && in_array((int) $request->input('region_id'), $regionIds, true)) {
            $selectedRegionId = (int) $request->input('region_id');
            $query->where(function ($q) use ($selectedRegionId) {
                $q->whereHas('partnerProfile', fn ($p) => $p->where('region_id', $selectedRegionId))
                    ->orWhereHas('customerAddresses', fn ($a) => $a->where('region_id', $selectedRegionId))
                    ->orWhereHas('complaintsAsTarget', fn ($c) => $c->where('region_id', $selectedRegionId));
            });
        }

        // Filter tab
        $currentTab = $request->input('status', 'semua');
        if ($currentTab === 'peringatan') {
            $query->where('penalty_points', '>=', PenaltyService::THRESHOLD_WARNING)
                ->where('penalty_points', '<', PenaltyService::THRESHOLD_SUSPEND_3_DAYS);
        } elseif ($currentTab === 'tersuspend') {
            $query->where(function ($q) {
                $q->where('status', UserStatus::Ditangguhkan)
                    ->orWhere(function ($sub) {
                        $sub->where('penalty_points', '>=', PenaltyService::THRESHOLD_SUSPEND_3_DAYS)
                            ->where('penalty_points', '<', PenaltyService::THRESHOLD_BANNED);
                    });
            });
        } elseif ($currentTab === 'banned') {
            $query->where(function ($q) {
                $q->where('status', UserStatus::Diblokir)
                    ->orWhere('penalty_points', '>=', PenaltyService::THRESHOLD_BANNED);
            });
        }

        // Role filter
        if ($request->filled('role') && in_array($request->input('role'), ['mitra', 'pelanggan'], true)) {
            $query->where('role', $request->input('role'));
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['partnerProfile.region', 'customerAddresses.region'])
            ->orderByDesc('penalty_points')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.penalties.index', [
            'users' => $users,
            'counts' => $counts,
            'currentTab' => $currentTab,
            'accessibleRegions' => $accessibleRegions,
        ]);
    }

    /**
     * Display the specified user's penalty profile & ledger history (ADM-06).
     */
    public function show(Request $request, User $user): View
    {
        $this->authorizeAdminAccess($request->user(), $user);

        $user->load(['partnerProfile.region', 'customerAddresses.region']);

        $ledgers = $user->penaltyLedgers()
            ->with(['complaint.complaintCategory', 'order', 'actor'])
            ->latest()
            ->paginate(10);

        $recentComplaints = $user->complaintsAsTarget()
            ->with(['complaintCategory', 'region', 'complainant'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.penalties.show', [
            'user' => $user,
            'ledgers' => $ledgers,
            'recentComplaints' => $recentComplaints,
        ]);
    }

    /**
     * Adjust a user's penalty points manually (Super Admin / Regional Admin).
     */
    public function adjust(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdminAccess($request->user(), $user);

        $validated = $request->validate([
            'type' => ['required', 'in:add,deduct'],
            'points' => ['required', 'integer', 'min:1', 'max:150'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $delta = $validated['type'] === 'deduct' ? -$validated['points'] : $validated['points'];

        $this->penaltyService->manualAdjustment(
            $user,
            $delta,
            $validated['reason'],
            $request->user()
        );

        return back()->with('success', 'Penyesuaian poin sanksi berhasil dicatat.');
    }

    /**
     * Check if the admin is authorized to view or modify this user's sanctions.
     */
    protected function authorizeAdminAccess(User $admin, User $targetUser): void
    {
        if ($admin->isSuperadmin()) {
            return;
        }

        $regionIds = $admin->assignedRegions()->where('status', 'aktif')->pluck('regions.id')->all();

        $hasAccess = $targetUser->partnerProfile()->whereIn('region_id', $regionIds)->exists()
            || $targetUser->customerAddresses()->whereIn('region_id', $regionIds)->exists()
            || $targetUser->complaintsAsTarget()->whereIn('region_id', $regionIds)->exists();

        abort_unless($hasAccess, 403, 'Anda tidak memiliki hak akses untuk memantau pengguna di luar wilayah penugasan Anda.');
    }
}
