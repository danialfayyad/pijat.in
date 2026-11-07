<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Services\ImageCompressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function __construct(
        protected ImageCompressionService $imageCompressionService
    ) {}

    /**
     * Display service catalog listing.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'aktif');
        if (! in_array($status, ['aktif', 'nonaktif'], true)) {
            $status = 'aktif';
        }

        $isActive = ($status === 'aktif');

        $services = Service::with('packages')
            ->where('is_active', $isActive)
            ->orderBy('name')
            ->get();

        $activeCount = Service::where('is_active', true)->count();
        $inactiveCount = Service::where('is_active', false)->count();

        return view('admin.catalog.index', [
            'services' => $services,
            'currentStatus' => $status,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
        ]);
    }

    /**
     * Store a newly created service with duration packages and optional photos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'packages' => ['required', 'array', 'min:1'],
            'packages.*.duration_minutes' => ['required', 'integer', 'min:15', 'max:360'],
            'packages.*.price' => ['required', 'numeric', 'min:0'],
        ], [
            'photos.max' => 'Maksimal 5 foto per layanan.',
            'photos.*.image' => 'Berkas harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus berupa JPG, PNG, atau WebP.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 10MB.',
        ]);

        $imagePaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $imagePaths[] = $this->imageCompressionService->compressAndStore(
                    file: $photo,
                    directory: 'services',
                    maxDimension: 1280,
                    quality: 80,
                    disk: 'public'
                );
            }
        }

        DB::transaction(function () use ($validated, $imagePaths): void {
            $service = Service::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'images' => ! empty($imagePaths) ? $imagePaths : null,
                'is_active' => true,
            ]);

            foreach ($validated['packages'] as $pkg) {
                ServicePackage::create([
                    'service_id' => $service->id,
                    'duration_minutes' => (int) $pkg['duration_minutes'],
                    'price' => (float) $pkg['price'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Layanan pijat baru berhasil ditambahkan ke katalog.');
    }

    /**
     * Update an existing service, its duration packages, and photos.
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'removed_images' => ['nullable', 'array'],
            'removed_images.*' => ['string'],
            'packages' => ['required', 'array', 'min:1'],
            'packages.*.id' => ['nullable', 'integer', 'exists:service_packages,id'],
            'packages.*.duration_minutes' => ['required', 'integer', 'min:15', 'max:360'],
            'packages.*.price' => ['required', 'numeric', 'min:0'],
            'packages.*.is_active' => ['nullable', 'boolean'],
        ], [
            'photos.max' => 'Maksimal 5 foto per layanan.',
            'photos.*.image' => 'Berkas harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus berupa JPG, PNG, atau WebP.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 10MB.',
        ]);

        $currentImages = is_array($service->images) ? $service->images : [];

        // Hapus foto yang ditandai untuk dihapus oleh pengguna
        if (! empty($validated['removed_images'])) {
            foreach ($validated['removed_images'] as $removedPath) {
                if (in_array($removedPath, $currentImages, true)) {
                    Storage::disk('public')->delete($removedPath);
                    $currentImages = array_values(array_diff($currentImages, [$removedPath]));
                }
            }
        }

        // Unggah dan kompres foto-foto baru jika ada
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if (count($currentImages) < 5) {
                    $currentImages[] = $this->imageCompressionService->compressAndStore(
                        file: $photo,
                        directory: 'services',
                        maxDimension: 1280,
                        quality: 80,
                        disk: 'public'
                    );
                }
            }
        }

        DB::transaction(function () use ($service, $validated, $currentImages): void {
            $service->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'images' => ! empty($currentImages) ? $currentImages : null,
                'is_active' => $validated['is_active'] ?? $service->is_active,
            ]);

            $existingIds = [];

            foreach ($validated['packages'] as $pkg) {
                $duration = (int) $pkg['duration_minutes'];
                $price = (float) $pkg['price'];
                $isActive = isset($pkg['is_active']) ? (bool) $pkg['is_active'] : true;

                if (! empty($pkg['id'])) {
                    $package = ServicePackage::where('id', $pkg['id'])
                        ->where('service_id', $service->id)
                        ->first();

                    if ($package) {
                        $package->update([
                            'duration_minutes' => $duration,
                            'price' => $price,
                            'is_active' => $isActive,
                        ]);
                        $existingIds[] = $package->id;

                        continue;
                    }
                }

                $package = ServicePackage::updateOrCreate(
                    [
                        'service_id' => $service->id,
                        'duration_minutes' => $duration,
                    ],
                    [
                        'price' => $price,
                        'is_active' => $isActive,
                    ]
                );
                $existingIds[] = $package->id;
            }

            // Remove any packages belonging to this service that were removed from the form
            $service->packages()->whereNotIn('id', $existingIds)->delete();
        });

        return redirect()->route('admin.catalog.index')
            ->with('success', 'Layanan katalog berhasil diperbarui.');
    }

    /**
     * Toggle active status of a service.
     */
    public function toggleStatus(Service $service): RedirectResponse
    {
        $service->is_active = ! $service->is_active;
        $service->save();

        $statusText = $service->is_active
            ? 'diaktifkan kembali dan masuk ke katalog aktif'
            : 'dinonaktifkan dan dipindahkan ke katalog tidak aktif';

        return redirect()->back(fallback: route('admin.catalog.index'))
            ->with('success', "Status layanan '{$service->name}' berhasil {$statusText}.");
    }

    /**
     * Toggle active status of an individual package.
     */
    public function togglePackageStatus(ServicePackage $package): RedirectResponse
    {
        $package->is_active = ! $package->is_active;
        $package->save();

        $statusText = $package->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back(fallback: route('admin.catalog.index'))
            ->with('success', "Paket {$package->duration_minutes} menit ({$package->service->name}) berhasil {$statusText}.");
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $name = $service->name;

        if (is_array($service->images)) {
            foreach ($service->images as $imgPath) {
                Storage::disk('public')->delete($imgPath);
            }
        }

        $service->delete();

        return redirect()->back(fallback: route('admin.catalog.index'))
            ->with('success', "Layanan '{$name}' dan seluruh pilihan durasinya berhasil dihapus.");
    }
}
