<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageCompressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(
        protected ImageCompressionService $imageCompressionService
    ) {}

    /**
     * Tampilkan daftar banner promosi pelanggan.
     */
    public function index(Request $request): View
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('badge_text', 'like', "%{$search}%")
                    ->orWhere('promo_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('type')) {
            $type = $request->input('type');
            if (in_array($type, ['image', 'template'], true)) {
                $query->where('type', $type);
            }
        }

        $banners = $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $totalCount = Banner::count();
        $activeCount = Banner::where('is_active', true)->count();
        $inactiveCount = Banner::where('is_active', false)->count();
        $actionPresets = Banner::ACTION_PRESETS;

        return view('superadmin.banners.index', compact(
            'banners',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'actionPresets'
        ));
    }

    /**
     * Simpan banner baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:image,template'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'action_text' => ['nullable', 'string', 'max:50'],
            'background_color' => ['nullable', 'in:emerald,teal,slate,amber,indigo,rose'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'title.required' => 'Judul banner wajib diisi.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 10MB.',
            'ends_at.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageCompressionService->compressAndStore(
                file: $request->file('image'),
                directory: 'banners',
                maxDimension: 1280,
                quality: 85,
                disk: 'public'
            );
        }

        Banner::create([
            'title' => trim($validated['title']),
            'type' => $validated['type'],
            'image_path' => $imagePath,
            'badge_text' => ! empty($validated['badge_text']) ? trim($validated['badge_text']) : null,
            'subtitle' => ! empty($validated['subtitle']) ? trim($validated['subtitle']) : null,
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            'promo_code' => ! empty($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null,
            'action_url' => ! empty($validated['action_url']) ? trim($validated['action_url']) : null,
            'action_text' => ! empty($validated['action_text']) ? trim($validated['action_text']) : null,
            'background_color' => $validated['background_color'] ?? 'emerald',
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('superadmin.banners.index')
            ->with('status', "Banner '{$validated['title']}' berhasil ditambahkan.");
    }

    /**
     * Perbarui banner yang dipilih.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:image,template'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'remove_image' => ['nullable', 'boolean'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'action_text' => ['nullable', 'string', 'max:50'],
            'background_color' => ['nullable', 'in:emerald,teal,slate,amber,indigo,rose'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'title.required' => 'Judul banner wajib diisi.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 10MB.',
            'ends_at.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        $imagePath = $banner->image_path;

        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $imagePath = $this->imageCompressionService->compressAndStore(
                file: $request->file('image'),
                directory: 'banners',
                maxDimension: 1280,
                quality: 85,
                disk: 'public'
            );
        }

        $banner->update([
            'title' => trim($validated['title']),
            'type' => $validated['type'],
            'image_path' => $imagePath,
            'badge_text' => ! empty($validated['badge_text']) ? trim($validated['badge_text']) : null,
            'subtitle' => ! empty($validated['subtitle']) ? trim($validated['subtitle']) : null,
            'description' => ! empty($validated['description']) ? trim($validated['description']) : null,
            'promo_code' => ! empty($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null,
            'action_url' => ! empty($validated['action_url']) ? trim($validated['action_url']) : null,
            'action_text' => ! empty($validated['action_text']) ? trim($validated['action_text']) : null,
            'background_color' => $validated['background_color'] ?? 'emerald',
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', false),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('superadmin.banners.index')
            ->with('status', "Banner '{$banner->title}' berhasil diperbarui.");
    }

    /**
     * Alihkan status aktif/nonaktif banner.
     */
    public function toggle(Banner $banner): RedirectResponse
    {
        $banner->update([
            'is_active' => ! $banner->is_active,
        ]);

        $statusText = $banner->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('status', "Banner '{$banner->title}' berhasil {$statusText}.");
    }

    /**
     * Hapus banner dan file gambarnya.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        $title = $banner->title;

        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return redirect()->route('superadmin.banners.index')
            ->with('status', "Banner '{$title}' berhasil dihapus.");
    }
}
