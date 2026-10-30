<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Region;
use App\Services\PenaltyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function __construct(
        protected PenaltyService $penaltyService
    ) {}

    /**
     * Display a listing of complaints in assigned regions (ADM-08).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();

        // Scope to admin's assigned active regions, or all active regions for Super Admin
        $regionIds = $admin->isSuperadmin()
            ? Region::active()->pluck('id')->all()
            : $admin->assignedRegions()->where('status', 'aktif')->pluck('regions.id')->all();

        $query = Complaint::inRegions($regionIds)->with([
            'order',
            'region',
            'complainant',
            'target',
            'handledBy',
            'complaintCategory',
        ]);

        // Filter by region if requested
        if ($request->filled('region_id') && in_array((int) $request->input('region_id'), $regionIds, true)) {
            $query->where('region_id', (int) $request->input('region_id'));
        }

        // Filter by status tab
        $currentTab = $request->input('status', 'semua');
        if ($currentTab !== 'semua' && ComplaintStatus::tryFrom($currentTab)) {
            $query->where('status', $currentTab);
        }

        // Filter by category
        if ($request->filled('category') && ComplaintCategory::tryFrom($request->input('category'))) {
            $query->where('category', $request->input('category'));
        }

        // Search by code, title, order code, or complainant name
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('complaint_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_code', 'like', "%{$search}%"))
                    ->orWhereHas('complainant', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        // Aggregate statistics for active tabs
        $statsBase = Complaint::inRegions($regionIds);
        if ($request->filled('region_id') && in_array((int) $request->input('region_id'), $regionIds, true)) {
            $statsBase->where('region_id', (int) $request->input('region_id'));
        }

        $totalCount = (clone $statsBase)->count();
        $pendingCount = (clone $statsBase)->pending()->count();
        $inProcessCount = (clone $statsBase)->inProcess()->count();
        $resolvedCount = (clone $statsBase)->resolved()->count();
        $rejectedCount = (clone $statsBase)->rejected()->count();

        $complaints = $query->latest()->paginate(15)->withQueryString();

        $accessibleRegions = $admin->isSuperadmin()
            ? Region::active()->orderBy('name')->get()
            : $admin->assignedRegions()->where('status', 'aktif')->orderBy('name')->get();

        return view('admin.complaints.index', compact(
            'complaints',
            'currentTab',
            'totalCount',
            'pendingCount',
            'inProcessCount',
            'resolvedCount',
            'rejectedCount',
            'accessibleRegions'
        ));
    }

    /**
     * Display the specified complaint details (ADM-08).
     */
    public function show(Complaint $complaint, Request $request): View
    {
        $admin = $request->user();

        if (! $admin->isSuperadmin() && ! $admin->canAccessRegion($complaint->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses operasional untuk melihat aduan di luar wilayah tugas.');
        }

        $complaint->load([
            'order.service',
            'order.servicePackage',
            'order.customer',
            'order.mitra',
            'order.statusHistories.actor',
            'complainant',
            'target',
            'region',
            'handledBy',
            'complaintCategory',
        ]);

        return view('admin.complaints.show', compact('complaint'));
    }

    /**
     * Start processing / investigating a complaint.
     */
    public function process(Complaint $complaint, Request $request): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin->isSuperadmin() && ! $admin->canAccessRegion($complaint->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses operasional di wilayah aduan ini.');
        }

        if ($complaint->isResolved() || $complaint->isRejected()) {
            return back()->with('error', 'Aduan ini sudah selesai atau telah diputuskan sebelumnya.');
        }

        $complaint->update([
            'status' => ComplaintStatus::Diproses,
            'handled_by_id' => $complaint->handled_by_id ?? $admin->id,
            'handled_at' => $complaint->handled_at ?? Carbon::now(),
        ]);

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('status', 'Status aduan berhasil diperbarui menjadi Sedang Diproses. Silakan lakukan mediasi atau investigasi.');
    }

    /**
     * Resolve a complaint with decisions (e.g. refund, penalty points sanction, or peaceful settlement).
     */
    public function resolve(Complaint $complaint, Request $request): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin->isSuperadmin() && ! $admin->canAccessRegion($complaint->region_id)) {
            abort(403, 'Anda tidak berhak menyelesaikan aduan di luar wilayah tugas.');
        }

        if ($complaint->isResolved() || $complaint->isRejected()) {
            return back()->with('error', 'Aduan ini sudah dinyatakan selesai sebelumnya.');
        }

        $request->validate([
            'resolution_type' => ['required', 'in:refund_penuh,damai_tanpa_tindakan,terbukti_bersalah,rekomendasi_sp'],
            'manual_points' => ['nullable', 'integer', 'min:1', 'max:150'],
            'admin_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'resolution_type.required' => 'Bentuk putusan solusi wajib dipilih.',
            'manual_points.required' => 'Poin sanksi untuk kategori lainnya wajib diisi.',
            'manual_points.min' => 'Poin sanksi minimal 1.',
            'manual_points.max' => 'Poin sanksi maksimal 150.',
            'admin_notes.required' => 'Catatan hasil investigasi dan alasan putusan wajib diisi.',
            'admin_notes.min' => 'Catatan investigasi minimal 10 karakter.',
        ]);

        $resolutionType = $request->input('resolution_type');
        $pointsApplied = 0;

        // Apply penalty points if resolution is terbukti_bersalah or rekomendasi_sp (backward compatibility)
        if (in_array($resolutionType, ['terbukti_bersalah', 'rekomendasi_sp'], true)) {
            if ($complaint->isLainnyaCategory() && $request->filled('manual_points')) {
                $pointsApplied = (int) $request->input('manual_points');
            } else {
                $pointsApplied = $complaint->getDefaultPenaltyPoints();
            }

            // Apply points to target user if target exists
            if ($complaint->target) {
                $this->penaltyService->applyViolation(
                    $complaint->target,
                    $pointsApplied,
                    "Aduan {$complaint->complaint_code} terbukti bersalah: ".trim($request->input('admin_notes')),
                    $complaint,
                    $admin
                );
            }
        }

        $complaint->update([
            'status' => ComplaintStatus::Selesai,
            'resolution_type' => $resolutionType,
            'penalty_points_applied' => $pointsApplied,
            'admin_notes' => trim($request->input('admin_notes')),
            'handled_by_id' => $complaint->handled_by_id ?? $admin->id,
            'handled_at' => $complaint->handled_at ?? Carbon::now(),
            'resolved_at' => Carbon::now(),
        ]);

        $message = match ($resolutionType) {
            'refund_penuh' => 'Aduan disetujui dengan putusan Refund Penuh ke Pelanggan.',
            'terbukti_bersalah', 'rekomendasi_sp' => "Aduan terbukti bersalah. Sanksi sebesar +{$pointsApplied} poin telah otomatis diterapkan ke akun terlapor.",
            default => 'Aduan berhasil diselesaikan melalui mediasi damai.',
        };

        return redirect()->route('admin.complaints.show', $complaint)->with('status', $message);
    }

    /**
     * Reject or dismiss an unfounded complaint.
     */
    public function reject(Complaint $complaint, Request $request): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin->isSuperadmin() && ! $admin->canAccessRegion($complaint->region_id)) {
            abort(403, 'Anda tidak berhak menolak aduan di luar wilayah tugas.');
        }

        if ($complaint->isResolved() || $complaint->isRejected()) {
            return back()->with('error', 'Aduan ini sudah dinyatakan selesai atau ditolak sebelumnya.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'min:10', 'max:2000'],
            'is_false_complaint' => ['nullable', 'boolean'],
        ], [
            'admin_notes.required' => 'Alasan penolakan aduan wajib diisi.',
            'admin_notes.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        $notes = trim($request->input('admin_notes'));
        if ($request->boolean('is_false_complaint')) {
            $notes .= "\n[CATATAN ADMIN]: Laporan terindikasi aduan palsu / mengada-ada. Pelapor dapat dikenakan sanksi peringatan.";
        }

        $complaint->update([
            'status' => ComplaintStatus::Ditolak,
            'resolution_type' => 'ditolak',
            'admin_notes' => $notes,
            'handled_by_id' => $complaint->handled_by_id ?? $admin->id,
            'handled_at' => $complaint->handled_at ?? Carbon::now(),
            'resolved_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('status', 'Aduan berhasil ditolak / diabaikan dan dicatat dalam arsip.');
    }
}
