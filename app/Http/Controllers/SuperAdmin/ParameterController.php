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
     * Group definitions with labels and icons for display.
     */
    public const GROUPS = [
        'financial' => [
            'label' => 'Finansial & Bagi Hasil',
            'desc' => 'Persentase komisi platform, batas dompet, denda pembatalan, dan batas pencairan dana.',
        ],
        'operational_hours' => [
            'label' => 'Jam Operasional Layanan',
            'desc' => 'Jadwal jam buka dan jam tutup pemesanan layanan waktu setempat.',
        ],
        'dispatch' => [
            'label' => 'Algoritma Penugasan & Matching',
            'desc' => 'Radius penugasan, batas waktu respon mitra, dan jendela pemesanan terjadwal.',
        ],
        'order' => [
            'label' => 'Aturan Batasan Pesanan',
            'desc' => 'Batas maksimal pesanan aktif pelanggan dan kuota penjadwalan ulang (reschedule).',
        ],
        'location' => [
            'label' => 'GPS & Geofencing Lokasi',
            'desc' => 'Interval pelacakan posisi mitra, radius geofence tombol tiba, dan toleransi akurasi GPS.',
        ],
        'timeout' => [
            'label' => 'Batas Waktu & SLA Sistem',
            'desc' => 'Batas kadaluwarsa pembayaran, waktu tunggu pelanggan di lokasi, dan batas waktu pengajuan komplain.',
        ],
        'penalty' => [
            'label' => 'Kebijakan Sanksi & Banding',
            'desc' => 'Jeda waktu pengajuan ulang banding sanksi bagi mitra yang ditolak.',
        ],
        'privacy' => [
            'label' => 'Privasi & Retensi Data (UU PDP)',
            'desc' => 'Masa penyimpanan riwayat percakapan chat dan catatan medis sebelum dihapus otomatis.',
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
