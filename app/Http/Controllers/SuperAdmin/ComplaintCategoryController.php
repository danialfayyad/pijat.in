<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComplaintCategoryController extends Controller
{
    /**
     * Display a listing of complaint categories.
     */
    public function index(Request $request): View
    {
        $query = ComplaintCategory::withCount('complaints');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        $totalCount = ComplaintCategory::count();
        $activeCount = ComplaintCategory::where('is_active', true)->count();
        $inactiveCount = ComplaintCategory::where('is_active', false)->count();

        return view('superadmin.complaint-categories.index', compact(
            'categories',
            'totalCount',
            'activeCount',
            'inactiveCount'
        ));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'penalty_points' => ['required', 'integer', 'min:1', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama kategori aduan wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'penalty_points.required' => 'Bobot poin sanksi wajib diisi.',
            'penalty_points.min' => 'Bobot poin minimal 1.',
            'penalty_points.max' => 'Bobot poin maksimal 150.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        $name = trim($request->input('name'));
        $baseSlug = Str::slug($name, '_');
        $slug = $baseSlug;
        $counter = 1;
        while (ComplaintCategory::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}_{$counter}";
            $counter++;
        }

        ComplaintCategory::create([
            'name' => $name,
            'slug' => $slug,
            'penalty_points' => (int) $request->input('penalty_points', 15),
            'description' => $request->filled('description') ? trim($request->input('description')) : null,
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.complaint-categories.index')
            ->with('status', "Kategori aduan '{$name}' berhasil ditambahkan.");
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, ComplaintCategory $complaintCategory): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'penalty_points' => ['required', 'integer', 'min:1', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama kategori aduan wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'penalty_points.required' => 'Bobot poin sanksi wajib diisi.',
            'penalty_points.min' => 'Bobot poin minimal 1.',
            'penalty_points.max' => 'Bobot poin maksimal 150.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        $complaintCategory->update([
            'name' => trim($request->input('name')),
            'penalty_points' => (int) $request->input('penalty_points', 15),
            'description' => $request->filled('description') ? trim($request->input('description')) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('superadmin.complaint-categories.index')
            ->with('status', "Kategori aduan '{$complaintCategory->name}' berhasil diperbarui.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggle(ComplaintCategory $complaintCategory): RedirectResponse
    {
        $complaintCategory->update([
            'is_active' => ! $complaintCategory->is_active,
        ]);

        $statusText = $complaintCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('superadmin.complaint-categories.index')
            ->with('status', "Status kategori '{$complaintCategory->name}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified category or deactivate if complaints exist.
     */
    public function destroy(ComplaintCategory $complaintCategory): RedirectResponse
    {
        if ($complaintCategory->complaints()->exists()) {
            $complaintCategory->update(['is_active' => false]);

            return redirect()->route('superadmin.complaint-categories.index')
                ->with('status', "Kategori '{$complaintCategory->name}' memiliki riwayat kasus aduan aktif. Kategori otomatis dinonaktifkan demi menjaga integritas data.");
        }

        $name = $complaintCategory->name;
        $complaintCategory->delete();

        return redirect()->route('superadmin.complaint-categories.index')
            ->with('status', "Kategori aduan '{$name}' berhasil dihapus.");
    }
}
