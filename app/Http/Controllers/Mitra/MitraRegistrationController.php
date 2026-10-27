<?php

namespace App\Http\Controllers\Mitra;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class MitraRegistrationController extends Controller
{
    /**
     * Display the mitra registration view (MIT-01).
     */
    public function create(): View
    {
        return view('auth.register-mitra');
    }

    /**
     * Handle an incoming mitra registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:25'],
            'gender' => ['required', Rule::in(['laki-laki', 'perempuan', 'L', 'P'])],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'gender' => in_array($validated['gender'], ['L', 'laki-laki']) ? 'laki-laki' : 'perempuan',
            'password' => Hash::make($validated['password']),
            'role' => Role::Mitra,
            'status' => UserStatus::BelumLengkap,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('mitra.onboarding');
    }
}
