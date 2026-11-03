<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\AppealStatus;
use App\Http\Controllers\Controller;
use App\Models\PenaltyAppeal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPenaltyController extends Controller
{
    /**
     * Display customer penalty points, status, and history (CUS-20).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $ledgers = $user->penaltyLedgers()
            ->with(['order', 'complaint'])
            ->latest()
            ->paginate(10);

        $appeals = PenaltyAppeal::where('user_id', $user->id)
            ->latest()
            ->get();

        $pendingAppeal = $appeals->firstWhere('status', AppealStatus::Menunggu);

        return view('pelanggan.penalties.index', compact('user', 'ledgers', 'appeals', 'pendingAppeal'));
    }

    /**
     * Show penalty appeal form (CUS-20).
     */
    public function createAppeal(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->penalty_points <= 0) {
            return redirect()->route('pelanggan.penalties.index')
                ->with('info', 'Anda tidak memiliki catatan poin pelanggaran untuk diajukan banding.');
        }

        $existingPending = PenaltyAppeal::where('user_id', $user->id)
            ->where('status', AppealStatus::Menunggu)
            ->first();

        if ($existingPending) {
            return redirect()->route('pelanggan.penalties.index')
                ->with('warning', 'Anda sudah memiliki berkas banding yang sedang menunggu peninjauan oleh Admin Wilayah.');
        }

        return view('pelanggan.penalties.appeal', compact('user'));
    }

    /**
     * Store penalty appeal submission with statement letter (CUS-20).
     */
    public function storeAppeal(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->penalty_points <= 0) {
            return redirect()->route('pelanggan.penalties.index')
                ->with('error', 'Tidak ada poin pelanggaran aktif.');
        }

        $existingPending = PenaltyAppeal::where('user_id', $user->id)
            ->where('status', AppealStatus::Menunggu)
            ->first();

        if ($existingPending) {
            return redirect()->route('pelanggan.penalties.index')
                ->with('warning', 'Anda sudah memiliki pengajuan banding yang sedang diproses.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'statement_letter' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'evidence_photos' => ['nullable', 'array', 'max:3'],
            'evidence_photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'reason.required' => 'Alasan permohonan banding wajib diisi.',
            'reason.min' => 'Alasan permohonan banding minimal 10 karakter.',
            'statement_letter.required' => 'Surat pernyataan bermaterai/resmi wajib diunggah.',
            'statement_letter.mimes' => 'Format surat pernyataan harus berupa PDF, JPG, atau PNG.',
            'statement_letter.max' => 'Ukuran file surat pernyataan maksimal 2MB.',
            'evidence_photos.max' => 'Maksimal 3 foto bukti pendukung.',
        ]);

        $letterPath = $request->file('statement_letter')->store('appeals/statements', 'public');

        $evidencePaths = [];
        if ($request->hasFile('evidence_photos')) {
            foreach ($request->file('evidence_photos') as $photo) {
                $evidencePaths[] = $photo->store('appeals/evidence', 'public');
            }
        }

        $regionId = $user->region_id ?? $user->primaryAddress?->region_id;

        $appeal = PenaltyAppeal::create([
            'user_id' => $user->id,
            'region_id' => $regionId,
            'reason' => trim($validated['reason']),
            'statement_letter_path' => $letterPath,
            'evidence_photos' => ! empty($evidencePaths) ? $evidencePaths : null,
            'points_to_reverse' => $user->penalty_points,
            'status' => AppealStatus::Menunggu,
        ]);

        return redirect()->route('pelanggan.penalties.index')
            ->with('success', 'Berkas banding sanksi dengan nomor '.$appeal->appeal_code.' berhasil dikirim. Admin Wilayah akan meninjau dokumen surat pernyataan Anda.');
    }
}
