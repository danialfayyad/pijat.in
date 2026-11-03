<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParameterController extends Controller
{
    /**
     * Group definitions with labels and descriptions for display.
     */
    public const GROUPS = [
        'financial' => [
            'label' => 'Keuangan & Bagi Hasil',
            'desc' => 'Pengaturan persentase komisi platform, limit saldo dompet, serta batas minimal dan biaya penarikan dana mitra.',
        ],
        'operational' => [
            'label' => 'Operasional & Jangkauan',
            'desc' => 'Jam operasional layanan wilayah dan radius jangkauan penugasan mitra terapis.',
        ],
        'compliance' => [
            'label' => 'Privasi Data (UU PDP)',
            'desc' => 'Batas masa retensi penyimpanan riwayat percakapan chat dan catatan medis sebelum dihapus otomatis.',
        ],
    ];

    /**
     * Display a listing of configurable parameters.
     */
    public function index(Request $request): View
    {
        $parameters = Parameter::all()->groupBy('group');
        $groups = self::GROUPS;

        return view('superadmin.parameters.index', compact('parameters', 'groups'));
    }

    /**
     * Update parameter values with mandatory change reason and audit logging.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parameters' => ['required', 'array'],
            'parameters.*' => ['required'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Catatan alasan perubahan parameter wajib diisi demi akuntabilitas audit log.',
            'reason.min' => 'Alasan perubahan minimal terdiri dari 5 karakter.',
        ]);

        $actor = $request->user();
        $updatedCount = 0;

        foreach ($validated['parameters'] as $key => $newValue) {
            $param = Parameter::where('key', $key)->first();
            if ($param && (string) $param->value !== (string) $newValue) {
                Parameter::set($key, $newValue, $validated['reason'], $actor);
                $updatedCount++;
            }
        }

        if ($updatedCount === 0) {
            return back()->with('info', 'Tidak ada nilai parameter yang berubah.');
        }

        return redirect()->route('superadmin.parameters.index')
            ->with('status', "Berhasil memperbarui {$updatedCount} parameter operasional. Perubahan tercatat dalam log audit.");
    }
}
