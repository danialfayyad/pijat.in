<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Region;
use App\Services\CustomerAddressService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function __construct(
        protected CustomerAddressService $addressService
    ) {}

    /**
     * Display a listing of customer addresses.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $addresses = $user->customerAddresses()
            ->with('region')
            ->orderByDesc('is_primary')
            ->latest()
            ->get();

        return view('pelanggan.addresses.index', compact('user', 'addresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $activeRegions = Region::active()
            ->selectRaw('id, name, timezone, ST_AsGeoJSON(boundaries) as geojson')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'timezone' => $r->timezone,
                'geojson' => $r->geojson ? json_decode($r->geojson, true) : null,
            ]);

        return view('pelanggan.addresses.create', compact('user', 'activeRegions'));
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'address_text' => ['required', 'string', 'max:500'],
            'benchmark' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'is_primary' => ['nullable', 'boolean'],
        ], [
            'label.required' => 'Label alamat (cth: Rumah, Kantor) wajib diisi.',
            'address_text.required' => 'Alamat lengkap wajib diisi.',
            'latitude.required' => 'Silakan sematkan pin lokasi alamat Anda pada peta.',
        ]);

        try {
            $this->addressService->createAddress($request->user(), [
                'label' => $validated['label'],
                'address_text' => $validated['address_text'],
                'benchmark' => $validated['benchmark'] ?? null,
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'is_primary' => (bool) ($validated['is_primary'] ?? false),
            ]);

            return redirect()->route('pelanggan.addresses.index')
                ->with('status', 'Alamat berhasil disimpan.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['latitude' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified address.
     */
    public function edit(Request $request, CustomerAddress $address): View
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $activeRegions = Region::active()
            ->selectRaw('id, name, timezone, ST_AsGeoJSON(boundaries) as geojson')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'timezone' => $r->timezone,
                'geojson' => $r->geojson ? json_decode($r->geojson, true) : null,
            ]);

        return view('pelanggan.addresses.edit', compact('address', 'activeRegions'));
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'address_text' => ['required', 'string', 'max:500'],
            'benchmark' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ], [
            'label.required' => 'Label alamat wajib diisi.',
            'address_text.required' => 'Alamat lengkap wajib diisi.',
            'latitude.required' => 'Silakan sematkan pin lokasi alamat Anda pada peta.',
        ]);

        try {
            $this->addressService->updateAddress($request->user(), $address, [
                'label' => $validated['label'],
                'address_text' => $validated['address_text'],
                'benchmark' => $validated['benchmark'] ?? null,
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
            ]);

            return redirect()->route('pelanggan.addresses.index')
                ->with('status', 'Alamat berhasil diperbarui.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['latitude' => $e->getMessage()]);
        }
    }

    /**
     * Set the address as primary address (CUS-23).
     */
    public function setPrimary(Request $request, CustomerAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->addressService->setPrimaryAddress($request->user(), $address);

            return redirect()->route('pelanggan.addresses.index')
                ->with('status', 'Alamat utama berhasil diperbarui. Wilayah layanan Anda telah dialihkan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(Request $request, CustomerAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->addressService->deleteAddress($request->user(), $address);

            return redirect()->route('pelanggan.addresses.index')
                ->with('status', 'Alamat berhasil dihapus.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
