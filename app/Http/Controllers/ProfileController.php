<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AccountDeletionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Show personal information edit form.
     */
    public function editPersonal(Request $request): View
    {
        return view('profile.personal', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Show security & password change form.
     */
    public function editSecurity(Request $request): View
    {
        return view('profile.security', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Show account deletion request page.
     */
    public function editDeleteAccount(Request $request): View
    {
        $user = $request->user();

        return view('profile.delete', [
            'user' => $user,
            'blockers' => $user->accountDeletionBlockers(ignorePendingRequest: true),
            'latestDeletionRequest' => $user->accountDeletionRequests()->latest()->first(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->back(fallback: route('profile.edit'))->with('status', 'profile-updated');
    }

    /**
     * Submit an account deletion request for admin approval.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $blockers = $user->accountDeletionBlockers();

        if ($blockers !== []) {
            return redirect()->back(fallback: route('profile.delete'))
                ->withErrors(['eligibility' => $blockers[0]], 'userDeletion');
        }

        AccountDeletionRequest::create([
            'user_id' => $user->id,
            'reason' => isset($validated['reason']) ? trim($validated['reason']) : null,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        return redirect()->back(fallback: route('profile.delete'))
            ->with('status', 'deletion-requested');
    }
}
