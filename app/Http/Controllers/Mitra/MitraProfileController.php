<?php

namespace App\Http\Controllers\Mitra;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Services\RegionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class MitraProfileController extends Controller
{
    public function __construct(
        protected RegionService $regionService
    ) {}

    /**
     * Show partner profile and settings (MIT-20).
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load('partnerProfile.region', 'primaryBankAccount');
        $activeRegions = Region::active()->get(['id', 'name', 'timezone']);

        return view('mitra.profile', compact('user', 'activeRegions'));
    }

    /**
     * Update partner profile information and handle re-verification safeguards (MIT-20).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->partnerProfile;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['nullable', 'string', 'max:150'],
            'address_text' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $requiresReverification = false;

        // Check if bank account changed
        $currentBank = $user->primaryBankAccount;
        if (! empty($validated['account_number']) && $currentBank && $validated['account_number'] !== $currentBank->account_number) {
            $currentBank->update([
                'bank_name' => $validated['bank_name'] ?? $currentBank->bank_name,
                'account_number' => $validated['account_number'],
                'account_holder_name' => $validated['account_holder_name'] ?? $currentBank->account_holder_name,
                'is_verified' => false,
            ]);
            $requiresReverification = true;
        }

        // Check if address coordinates changed
        if (! empty($validated['latitude']) && ! empty($validated['longitude'])) {
            $activeRegion = $this->regionService->findActiveRegionByCoordinates((float) $validated['latitude'], (float) $validated['longitude']);
            if (! $activeRegion) {
                return back()->withInput()->withErrors(['latitude' => 'Titik domisili baru berada di luar wilayah layanan aktif.']);
            }

            if ($profile) {
                $profile->update([
                    'region_id' => $activeRegion->id,
                    'address_text' => $validated['address_text'] ?? $profile->address_text,
                    'coordinate' => ['lat' => (float) $validated['latitude'], 'lng' => (float) $validated['longitude']],
                ]);
                $requiresReverification = true;
            }
        }

        if ($requiresReverification) {
            $userData['status'] = UserStatus::MenungguVerifikasi;
        }

        $user->update($userData);

        if ($requiresReverification) {
            return redirect()->route('mitra.status')
                ->with('status', 'Pembaruan data perbankan atau domisili Anda memerlukan verifikasi ulang oleh Admin Wilayah.');
        }

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}
