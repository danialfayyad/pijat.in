<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display centralized audit trail logs (SA-03).
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('module')) {
            $query->module($request->input('module'));
        }

        if ($request->filled('user_id')) {
            $query->actor((int) $request->input('user_id'));
        }

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->dateRange($request->input('start_date'), $request->input('end_date'));
        }

        $logs = $query->paginate(10)->withQueryString();

        $modules = AuditLog::select('module')->distinct()->pluck('module');
        $actors = User::whereIn('role', ['superadmin', 'admin'])->orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('superadmin.audit-logs.index', compact('logs', 'modules', 'actors'));
    }

    /**
     * Show detail of an audit log entry (JSON or modal).
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user');

        return response()->json([
            'id' => $auditLog->id,
            'actor' => $auditLog->user?->name ?? 'Sistem / Anonim',
            'actor_email' => $auditLog->user?->email ?? '-',
            'module' => $auditLog->module,
            'action' => $auditLog->action,
            'reason' => $auditLog->reason ?? 'Tidak ada catatan alasan',
            'old_values' => $auditLog->old_values,
            'new_values' => $auditLog->new_values,
            'ip_address' => $auditLog->ip_address ?? '-',
            'user_agent' => $auditLog->user_agent ?? '-',
            'created_at' => $auditLog->created_at->format('d M Y H:i:s'),
        ]);
    }
}
