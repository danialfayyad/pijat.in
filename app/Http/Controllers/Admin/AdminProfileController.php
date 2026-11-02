<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    /**
     * Show admin profile details & change password form (ADM-14).
     */
    public function edit(Request $request): View
    {
        $admin = $request->user();
        $assignedRegions = $admin->isSuperadmin()
            ? Region::active()->get()
            : $admin->assignedRegions()->get();

        return view('admin.profile.edit', compact('admin', 'assignedRegions'));
    }

    /**
     * Update admin password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok dengan data kami.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::record([
            'user_id' => $admin->id,
            'module' => 'user',
            'action' => 'password_change',
            'auditable_type' => get_class($admin),
            'auditable_id' => $admin->id,
            'reason' => 'Perubahan kata sandi mandiri oleh admin',
        ]);

        return back()->with('status', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}
