<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use App\Services\RegionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAssignmentController extends Controller
{
    public function __construct(
        protected RegionService $regionService
    ) {}

    /**
     * Display a listing of admins and their region assignments.
     */
    public function index(): View
    {
        $admins = User::where('role', Role::Admin)
            ->with([
                'assignedRegions' => fn ($query) => $query->select('regions.id', 'regions.name', 'regions.status'),
                'menuPermissions',
            ])
            ->get();

        $regions = Region::select('id', 'name', 'status')->orderBy('name')->get();

        return view('superadmin.admins.index', compact('admins', 'regions'));
    }

    /**
     * Assign an admin to one or multiple regions simultaneously.
     */
    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['exists:regions,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
        ]);

        $admin = User::findOrFail($validated['user_id']);

        $regionIds = $validated['region_ids'] ?? [];
        if (! empty($validated['region_id'])) {
            $regionIds[] = $validated['region_id'];
        }
        $regionIds = array_unique(array_filter($regionIds));

        if (empty($regionIds)) {
            return back()->withErrors(['assignment' => 'Pilih sedikitnya satu wilayah tugas untuk admin.']);
        }

        try {
            DB::transaction(function () use ($admin, $regionIds) {
                foreach ($regionIds as $rId) {
                    $region = Region::findOrFail($rId);
                    $this->regionService->assignAdmin($admin, $region, true);
                }
            });

            $count = count($regionIds);

            return back()->with('status', "Admin {$admin->name} berhasil ditugaskan ke {$count} wilayah sekaligus.");
        } catch (DomainException $e) {
            return back()->withErrors(['assignment' => $e->getMessage()]);
        }
    }

    /**
     * Revoke an admin from a region.
     */
    public function revoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'region_id' => ['required', 'exists:regions,id'],
        ]);

        $admin = User::findOrFail($validated['user_id']);
        $region = Region::findOrFail($validated['region_id']);

        try {
            $this->regionService->revokeAdmin($admin, $region);

            return back()->with('status', "Penugasan admin {$admin->name} pada wilayah {$region->name} telah dicabut.");
        } catch (DomainException $e) {
            return back()->withErrors(['assignment' => $e->getMessage()]);
        }
    }
}
