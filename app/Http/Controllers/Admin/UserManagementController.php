<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of partners and customers in admin's assigned regions (ADM-05).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();
        $assignedRegions = $admin->isSuperadmin()
            ? Region::active()->get()
            : $admin->assignedRegions()->where('status', 'aktif')->get();

        if ($assignedRegions->isEmpty() && ! $admin->isSuperadmin()) {
            abort(403, 'Akun admin Anda belum memiliki wilayah tugas.');
        }

        $selectedRegionId = $request->filled('region_id') ? (int) $request->input('region_id') : null;
        if ($selectedRegionId && ! $admin->canAccessRegion($selectedRegionId)) {
            abort(403, 'Anda tidak memiliki hak akses terhadap wilayah tugas ini.');
        }

        $regionIds = $selectedRegionId
            ? [$selectedRegionId]
            : $assignedRegions->pluck('id')->all();

        $activeTab = $request->input('tab', 'partners');
        if (! in_array($activeTab, ['partners', 'customers'], true)) {
            $activeTab = 'partners';
        }

        $statusFilter = $request->input('status', 'semua');
        $search = trim((string) $request->input('search', ''));

        if ($activeTab === 'partners') {
            // Base Mitra Query scoped to regions
            $basePartnerQuery = User::where('role', Role::Mitra)
                ->where(function ($query) use ($regionIds) {
                    $query->whereIn('region_id', $regionIds)
                        ->orWhereHas('partnerProfile', function ($sub) use ($regionIds) {
                            $sub->whereIn('region_id', $regionIds);
                        });
                });

            // Counters for partners
            $totalMitraCount = (clone $basePartnerQuery)->count();
            $verifiedMitraCount = (clone $basePartnerQuery)->where('status', UserStatus::Terverifikasi)->count();
            $suspendedMitraCount = (clone $basePartnerQuery)->where('status', UserStatus::Ditangguhkan)->count();
            $blockedMitraCount = (clone $basePartnerQuery)->where('status', UserStatus::Diblokir)->count();

            // Filter status
            $partnerQuery = clone $basePartnerQuery;
            if ($statusFilter !== 'semua') {
                $partnerQuery->where('status', $statusFilter);
            }

            if ($search !== '') {
                $partnerQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $users = $partnerQuery->with(['partnerProfile.region', 'ratingsReceived'])
                ->latest()
                ->paginate(15)
                ->withQueryString();

            return view('admin.users.index', [
                'activeTab' => 'partners',
                'users' => $users,
                'assignedRegions' => $assignedRegions,
                'selectedRegionId' => $selectedRegionId,
                'statusFilter' => $statusFilter,
                'search' => $search,
                'stats' => [
                    'total' => $totalMitraCount,
                    'verified' => $verifiedMitraCount,
                    'suspended' => $suspendedMitraCount,
                    'blocked' => $blockedMitraCount,
                ],
            ]);
        }

        // Base Customer Query scoped to regions
        $baseCustomerQuery = User::where('role', Role::Pelanggan)
            ->where(function ($query) use ($regionIds) {
                $query->whereIn('region_id', $regionIds)
                    ->orWhereHas('customerAddresses', function ($sub) use ($regionIds) {
                        $sub->where('is_primary', true)->whereIn('region_id', $regionIds);
                    });
            });

        // Counters for customers
        $totalCustomerCount = (clone $baseCustomerQuery)->count();
        $verifiedCustomerCount = (clone $baseCustomerQuery)->where('status', UserStatus::Terverifikasi)->count();
        $suspendedCustomerCount = (clone $baseCustomerQuery)->where('status', UserStatus::Ditangguhkan)->count();
        $blockedCustomerCount = (clone $baseCustomerQuery)->where('status', UserStatus::Diblokir)->count();

        // Filter status
        $customerQuery = clone $baseCustomerQuery;
        if ($statusFilter !== 'semua') {
            $customerQuery->where('status', $statusFilter);
        }

        if ($search !== '') {
            $customerQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $customerQuery->with(['primaryAddress.region'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'activeTab' => 'customers',
            'users' => $users,
            'assignedRegions' => $assignedRegions,
            'selectedRegionId' => $selectedRegionId,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'stats' => [
                'total' => $totalCustomerCount,
                'verified' => $verifiedCustomerCount,
                'suspended' => $suspendedCustomerCount,
                'blocked' => $blockedCustomerCount,
            ],
        ]);
    }

    /**
     * Display full partner profile, rating metrics, review breakdown, and order history.
     */
    public function showPartner(Request $request, User $user): View
    {
        if (! $user->isMitra()) {
            abort(404, 'Pengguna bukan mitra terapis.');
        }

        $admin = $request->user();
        $partnerRegionId = $user->region_id ?? $user->partnerProfile?->region_id;

        if ($partnerRegionId && ! $admin->canAccessRegion($partnerRegionId)) {
            abort(403, 'Anda tidak memiliki hak akses terhadap wilayah tugas mitra ini.');
        }

        $user->load([
            'partnerProfile.region',
            'primaryBankAccount',
            'penaltyLedgers' => fn ($q) => $q->latest()->limit(10),
        ]);

        // Ratings breakdown and reviews
        $ratingScore = $user->partnerRating();
        $ratingsCount = $user->partnerRatingsCount();
        $distribution = $user->partnerRatingDistribution();

        $reviews = $user->ratingsReceived()
            ->where('rating_type', 'customer_to_mitra')
            ->with(['fromUser', 'order'])
            ->latest()
            ->paginate(10, ['*'], 'review_page');

        // Order performance
        $completedOrdersCount = $user->ordersAsMitra()->where('status', OrderStatus::Selesai)->count();
        $cancelledOrdersCount = $user->ordersAsMitra()->where('status', OrderStatus::Dibatalkan)->count();

        return view('admin.users.show_partner', [
            'user' => $user,
            'partnerProfile' => $user->partnerProfile,
            'bankAccount' => $user->primaryBankAccount,
            'ratingScore' => $ratingScore,
            'ratingsCount' => $ratingsCount,
            'distribution' => $distribution,
            'reviews' => $reviews,
            'completedOrdersCount' => $completedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
        ]);
    }

    /**
     * Display customer profile, addresses, order history, and moderation options.
     */
    public function showCustomer(Request $request, User $user): View
    {
        if (! $user->isPelanggan()) {
            abort(404, 'Pengguna bukan customer.');
        }

        $admin = $request->user();
        $customerRegionId = $user->region_id ?? $user->primaryAddress?->region_id;

        if ($customerRegionId && ! $admin->canAccessRegion($customerRegionId)) {
            abort(403, 'Anda tidak memiliki hak akses terhadap wilayah tugas pelanggan ini.');
        }

        $user->load([
            'customerAddresses.region',
            'wallet',
            'ordersAsCustomer' => fn ($q) => $q->latest()->limit(10),
        ]);

        $completedOrdersCount = $user->ordersAsCustomer()->where('status', OrderStatus::Selesai)->count();
        $cancelledOrdersCount = $user->ordersAsCustomer()->where('status', OrderStatus::Dibatalkan)->count();

        return view('admin.users.show_customer', [
            'user' => $user,
            'primaryAddress' => $user->primaryAddress,
            'addresses' => $user->customerAddresses,
            'wallet' => $user->wallet,
            'completedOrdersCount' => $completedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'recentOrders' => $user->ordersAsCustomer,
        ]);
    }

    /**
     * Moderate user account status: activate, suspend, or permanently block (with blacklist).
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        // Prevent modifying privileged admin accounts
        if ($user->isAdmin() || $user->isSuperadmin()) {
            abort(403, 'Tidak dapat memoderasi akun berwenang.');
        }

        $regionId = $user->region_id ?? $user->partnerProfile?->region_id ?? $user->primaryAddress?->region_id;
        if ($regionId && ! $admin->canAccessRegion($regionId)) {
            abort(403, 'Anda tidak memiliki wewenang pada wilayah akun ini.');
        }

        $validated = $request->validate([
            'action' => 'required|in:activate,suspend,block',
            'reason' => 'required|string|min:5|max:1000',
            'suspend_days' => 'required_if:action,suspend|nullable|integer|min:1|max:365',
            'device_id' => 'nullable|string|max:120',
        ], [
            'reason.required' => 'Alasan tindakan moderasi wajib diisi.',
            'reason.min' => 'Alasan minimal 5 karakter.',
            'suspend_days.required_if' => 'Durasi penangguhan wajib ditentukan.',
        ]);

        $action = $validated['action'];
        $reason = $validated['reason'];

        if ($action === 'activate') {
            $user->update([
                'status' => UserStatus::Terverifikasi,
                'status_reason' => $reason,
                'suspended_until' => null,
            ]);

            return back()->with('success', "Akun {$user->name} berhasil diaktifkan kembali.");
        }

        if ($action === 'suspend') {
            $days = (int) ($validated['suspend_days'] ?? 3);
            $user->update([
                'status' => UserStatus::Ditangguhkan,
                'status_reason' => $reason,
                'suspended_until' => now()->addDays($days),
            ]);

            return back()->with('success', "Akun {$user->name} berhasil ditangguhkan selama {$days} hari.");
        }

        // Block action: Permanent ban & record into blacklists
        $deviceId = $validated['device_id'] ?? $user->device_id;

        $user->update([
            'status' => UserStatus::Diblokir,
            'status_reason' => $reason,
            'suspended_until' => null,
            'device_id' => $deviceId,
        ]);

        Blacklist::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'device_id' => $deviceId,
            'reason' => $reason,
            'blocked_by' => $admin->id,
        ]);

        return back()->with('success', "Akun {$user->name} telah diblokir permanen dan nomor HP/perangkat dimasukkan ke daftar hitam.");
    }
}

