<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the operational dashboard for assigned regions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $assignedRegions = $user->assignedRegions()->get();

        if ($assignedRegions->isEmpty() && ! $user->isSuperadmin()) {
            abort(403, 'Akun admin Anda belum memiliki wilayah tugas.');
        }

        $selectedRegionId = $request->input('region_id');
        $selectedRegion = null;

        if ($selectedRegionId) {
            $selectedRegion = $assignedRegions->firstWhere('id', $selectedRegionId);
            if (! $selectedRegion && ! $user->isSuperadmin()) {
                abort(403, 'Anda tidak memiliki hak akses operasional untuk wilayah ini.');
            }
            if ($user->isSuperadmin() && ! $selectedRegion) {
                $selectedRegion = Region::find($selectedRegionId);
            }
        }

        // Operational metrics (ADM-01)
        $metrics = [
            'active_partners_count' => 0,
            'orders_in_progress' => 0,
            'stuck_orders_count' => 0,
            'new_complaints_count' => 0,
            'daily_revenue' => 0,
        ];

        return view('admin.dashboard', compact(
            'assignedRegions',
            'selectedRegion',
            'metrics'
        ));
    }
}
