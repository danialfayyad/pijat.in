<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the full service catalog (CUS-03).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $services = Service::active()
            ->with(['activePackages' => fn ($q) => $q->orderBy('duration_minutes')])
            ->get();

        return view('pelanggan.catalog.index', compact('user', 'services'));
    }

    /**
     * Display a specific service with its duration packages (CUS-03).
     */
    public function show(Request $request, Service $service): View
    {
        if (! $service->is_active) {
            abort(404);
        }

        $user = $request->user();
        $service->load(['activePackages' => fn ($q) => $q->orderBy('duration_minutes')]);

        return view('pelanggan.catalog.show', compact('user', 'service'));
    }
}
