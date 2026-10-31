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
use Illuminate\Support\Facades\Hash;
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
     * Create a new Admin account and optionally assign initial regions and permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['exists:regions,id'],
            'can_manage_catalog' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'email.required' => 'Email admin wajib diisi.',
            'email.unique' => 'Email sudah terdaftar untuk pengguna lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $admin = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'role' => Role::Admin,
                    'email_verified_at' => now(),
                ]);

                if (! empty($validated['region_ids'])) {
                    foreach ($validated['region_ids'] as $rId) {
                        $region = Region::findOrFail($rId);
                        $this->regionService->assignAdmin($admin, $region, true);
                    }
                }

                if (! empty($validated['can_manage_catalog'])) {
                    $admin->menuPermissions()->create(['menu_code' => 'KATALOG']);
                }
            });

            return redirect()->route('superadmin.admins.index')
                ->with('status', "Akun Admin {$validated['name']} berhasil dibuat dan siap bertugas.");
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['store_admin' => 'Gagal membuat akun admin: '.$e->getMessage()]);
        }
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
     * Revoke an admin from one or multiple regions simultaneously.
     */
    public function revoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['exists:regions,id'],
        ]);

        $admin = User::findOrFail($validated['user_id']);

        $regionIds = $validated['region_ids'] ?? [];
        if (! empty($validated['region_id'])) {
            $regionIds[] = $validated['region_id'];
        }
        $regionIds = array_unique(array_filter($regionIds));

        if (empty($regionIds)) {
            return back()->withErrors(['assignment' => 'Pilih sedikitnya satu wilayah yang ingin dicabut.']);
        }

        try {
            DB::transaction(function () use ($admin, $regionIds) {
                foreach ($regionIds as $rId) {
                    $region = Region::findOrFail($rId);
                    $this->regionService->revokeAdmin($admin, $region);
                }
            });

            $count = count($regionIds);

            return back()->with('status', "Berhasil mencabut {$count} wilayah tugas dari admin {$admin->name}.");
        } catch (DomainException $e) {
            return back()->withErrors(['assignment' => $e->getMessage()]);
        }
    }

    /**
     * Delete an admin account and clean up their assignments.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['admin_delete' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if (! $user->hasRole(Role::Admin)) {
            return back()->withErrors(['admin_delete' => 'Hanya akun Admin Wilayah yang dapat dihapus melalui menu ini.']);
        }

        $adminName = $user->name;

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()->route('superadmin.admins.index')
            ->with('status', "Akun Admin {$adminName} berhasil dihapus dari sistem.");
    }
}
