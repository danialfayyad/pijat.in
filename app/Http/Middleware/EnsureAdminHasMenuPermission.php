<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $menuCode): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isSuperadmin()) {
            return $next($request);
        }

        if (! $user->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya untuk Admin atau Super Admin.');
        }

        if (! $user->canAccessMenu($menuCode)) {
            abort(403, "Akses ditolak. Anda tidak memiliki izin untuk mengakses menu {$menuCode}.");
        }

        return $next($request);
    }
}
