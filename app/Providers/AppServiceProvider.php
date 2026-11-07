<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            try {
                $user = $event->user;
                $request = request();

                AuditLog::create([
                    'user_id' => $user->getAuthIdentifier(),
                    'module' => 'auth',
                    'action' => 'login',
                    'auditable_type' => get_class($user),
                    'auditable_id' => $user->getAuthIdentifier(),
                    'old_values' => null,
                    'new_values' => [
                        'email' => $user->email ?? null,
                        'role' => isset($user->role) ? ($user->role->value ?? (string) $user->role) : null,
                    ],
                    'reason' => 'Pengguna berhasil masuk ke sistem',
                    'ip_address' => $request?->ip(),
                    'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if (! $event->user) {
                return;
            }

            try {
                $user = $event->user;
                $request = request();

                AuditLog::create([
                    'user_id' => $user->getAuthIdentifier(),
                    'module' => 'auth',
                    'action' => 'logout',
                    'auditable_type' => get_class($user),
                    'auditable_id' => $user->getAuthIdentifier(),
                    'old_values' => null,
                    'new_values' => [
                        'email' => $user->email ?? null,
                        'role' => isset($user->role) ? ($user->role->value ?? (string) $user->role) : null,
                    ],
                    'reason' => 'Pengguna keluar dari sistem',
                    'ip_address' => $request?->ip(),
                    'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
