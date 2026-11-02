<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LegalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalDocumentController extends Controller
{
    /**
     * Display list of legal documents & version status (SA-05).
     */
    public function index(): View
    {
        $documents = LegalDocument::with('updater')->get();

        return view('superadmin.legal-documents.index', compact('documents'));
    }

    /**
     * Show edit form for a legal document.
     */
    public function edit(LegalDocument $document): View
    {
        return view('superadmin.legal-documents.edit', compact('document'));
    }

    /**
     * Update legal document content, increment version, and record audit log.
     */
    public function update(Request $request, LegalDocument $document): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:20'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'min:20'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Catatan alasan pembaruan dokumen legal wajib diisi demi kepatuhan audit.',
        ]);

        $oldValues = [
            'version' => $document->version,
            'title' => $document->title,
            'summary' => $document->summary,
        ];

        $document->update([
            'title' => $validated['title'],
            'version' => $validated['version'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'updated_by' => $request->user()->id,
        ]);

        AuditLog::record([
            'user_id' => $request->user()->id,
            'module' => 'legal',
            'action' => 'update',
            'auditable_type' => LegalDocument::class,
            'auditable_id' => $document->id,
            'old_values' => $oldValues,
            'new_values' => [
                'version' => $document->version,
                'title' => $document->title,
                'summary' => $document->summary,
            ],
            'reason' => $validated['reason'],
        ]);

        return redirect()->route('superadmin.legal-documents.index')
            ->with('status', "Dokumen legal '{$document->title}' berhasil diperbarui ke versi {$document->version}.");
    }
}
