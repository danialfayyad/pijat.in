<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    /**
     * Display the customer PWA home dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load(['primaryAddress.region', 'region']);

        $services = Service::active()
            ->with(['activePackages' => fn ($q) => $q->orderBy('duration_minutes')])
            ->get();

        return view('pelanggan.dashboard', compact('user', 'services'));
    }
}
