<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Services\RegionService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function __construct(
        protected RegionService $regionService
    ) {}

    /**
     * Display a listing of all regions.
     */
    public function index(): View
    {
        $regions = Region::withCount([
            'adminAssignments',
            'adminAssignments as emergency_admins_count' => function ($query) {
                $query->where('can_handle_emergency', true);
            },
        ])->get();

        return view('superadmin.regions.index', compact('regions'));
    }

    /**
     * Get presets list (provinces and summary of 514 cities/regencies).
     */
    public function presets(): JsonResponse
    {
        $provincesPath = storage_path('app/geojson/provinces.json');
        $citiesPath = storage_path('app/geojson/cities_summary.json');

        $provinces = file_exists($provincesPath) ? json_decode(file_get_contents($provincesPath), true) : [];
        $cities = file_exists($citiesPath) ? json_decode(file_get_contents($citiesPath), true) : [];

        return response()->json([
            'provinces' => $provinces,
            'cities' => $cities,
        ]);
    }

    /**
     * Get official GeoJSON boundary for a specific city.
     */
    public function cityBoundary(string $cityId): JsonResponse
    {
        // Sanitize cityId to alphanumeric and underscore
        $cityId = preg_replace('/[^a-zA-Z0-9_]/', '', $cityId);
        $path = storage_path("app/geojson/boundaries/{$cityId}.json");

        if (! file_exists($path)) {
            return response()->json(['error' => 'Data batas wilayah kota tidak ditemukan.'], 404);
        }

        $data = json_decode(file_get_contents($path), true);

        return response()->json($data);
    }

    /**
     * Store a newly created region with spatial boundary polygon or GeoJSON.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'coordinates' => ['nullable', 'json'],
            'geojson' => ['nullable', 'json'],
            'timezone' => ['required', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
        ]);

        $boundary = null;

        if (! empty($validated['geojson'])) {
            $boundary = json_decode($validated['geojson'], true);
            if (! is_array($boundary) || empty($boundary['type'])) {
                return back()->withInput()->withErrors([
                    'geojson' => 'Format GeoJSON batas wilayah tidak valid.',
                ]);
            }
        } elseif (! empty($validated['coordinates'])) {
            $rawCoords = json_decode($validated['coordinates'], true);
            if (! is_array($rawCoords) || count($rawCoords) < 3) {
                return back()->withInput()->withErrors([
                    'coordinates' => 'Batas poligon harus memiliki minimal 3 titik koordinat yang valid.',
                ]);
            }
            $boundary = $rawCoords;
        } else {
            return back()->withInput()->withErrors([
                'coordinates' => 'Silakan pilih Kota/Kabupaten resmi atau gambar batas poligon.',
            ]);
        }

        try {
            $this->regionService->createRegion(
                name: $validated['name'],
                boundary: $boundary,
                timezone: $validated['timezone'],
                status: 'nonaktif'
            );

            return redirect()->route('superadmin.regions.index')
                ->with('status', 'Wilayah berhasil dibuat dengan status awal Nonaktif.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['name' => $e->getMessage()]);
        }
    }

    /**
     * Activate a region if minimum admin conditions are satisfied.
     */
    public function activate(Region $region): RedirectResponse
    {
        try {
            $this->regionService->activateRegion($region);

            return back()->with('status', "Wilayah {$region->name} berhasil diaktifkan!");
        } catch (DomainException $e) {
            return back()->withErrors(['activation' => $e->getMessage()]);
        }
    }

    /**
     * Deactivate a region.
     */
    public function deactivate(Region $region): RedirectResponse
    {
        $this->regionService->deactivateRegion($region);

        return back()->with('status', "Wilayah {$region->name} telah dinonaktifkan.");
    }
}
