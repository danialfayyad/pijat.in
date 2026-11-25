<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth provider.
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('error', 'Google Sign-In belum dikonfigurasi. Harap masukkan GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada berkas .env.');
        }

        // Store intended role (defaults to pelanggan)
        $role = $request->query('role', Role::Pelanggan->value);
        if (! in_array($role, [Role::Pelanggan->value, Role::Mitra->value])) {
            $role = Role::Pelanggan->value;
        }

        session([
            'oauth_intended_role' => $role,
        ]);

        $state = base64_encode(json_encode([
            'role' => $role,
            'token' => Str::random(16),
        ]));

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->stateless()->with(['state' => $state])->redirect();
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Login Google dibatalkan atau terjadi kesalahan.');
        }

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = $driver->stateless()->user();
        } catch (Throwable $e) {
            Log::error('Google OAuth callback error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('login')->with('error', 'Gagal memproses autentikasi Google: '.$e->getMessage());
        }

        if (! $googleUser || ! $googleUser->getEmail()) {
            return redirect()->route('login')->with('error', 'Email dari akun Google tidak ditemukan.');
        }

        $intendedRole = session()->pull('oauth_intended_role');

        if (! $intendedRole && $request->has('state')) {
            try {
                $decoded = json_decode(base64_decode($request->query('state')), true);
                if (is_array($decoded) && isset($decoded['role']) && in_array($decoded['role'], [Role::Pelanggan->value, Role::Mitra->value])) {
                    $intendedRole = $decoded['role'];
                }
            } catch (Throwable) {
                // Ignore parse errors
            }
        }

        $intendedRole ??= Role::Pelanggan->value;

        // 1. Cari user berdasarkan google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        // 2. Jika belum ada dengan google_id, cari berdasarkan email
        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Tautkan google_id ke akun yang sudah ada
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            }
        }

        // 3. Jika user baru pertama kali mendaftar via Google
        if (! $user) {
            $isMitra = ($intendedRole === Role::Mitra->value);

            $user = User::create([
                'name' => $googleUser->getName() ?: 'Pengguna Google',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => null,
                'role' => $isMitra ? Role::Mitra : Role::Pelanggan,
                'status' => $isMitra ? UserStatus::BelumLengkap : UserStatus::Terverifikasi,
            ]);

            event(new Registered($user));
        }

        // 4. Periksa apakah akun diblokir atau ditangguhkan
        if ($user->isBlocked() || Blacklist::where('user_id', $user->id)->orWhere('phone', $user->phone)->exists()) {
            return redirect()->route('login')->with('error', 'Akun Anda telah diblokir permanen karena pelanggaran ketentuan layanan.');
        }

        if ($user->isSuspended()) {
            $until = $user->suspended_until?->format('d M Y H:i') ?? 'jangka waktu tertentu';
            $reason = $user->status_reason ? " Alasan: {$user->status_reason}" : '';

            return redirect()->route('login')->with('error', "Akun Anda sedang ditangguhkan hingga {$until}.{$reason}");
        }

        Auth::login($user, true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
