<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAdminRegionAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
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

        // If admin has zero assigned regions, they cannot view operational data (BRD 4.16, ADM-12)
        if ($user->assignedRegions()->count() === 0) {
            abort(403, 'Akun admin Anda belum memiliki wilayah tugas.');
        }

        // If a specific region_id is requested in route parameter or query string, enforce access
        $regionId = $request->route('region_id')
            ?? $request->route('region')
            ?? $request->input('region_id')
            ?? $request->header('X-Region-Id');

        if ($regionId) {
            $id = is_object($regionId) ? $regionId->id : (int) $regionId;

            if (! $user->canAccessRegion($id)) {
                abort(403, 'Anda tidak memiliki hak akses operasional untuk wilayah ini.');
            }
        }

        return $next($request);
    }
}
