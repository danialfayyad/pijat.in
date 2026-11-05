<?php

namespace App\Http\Controllers\Mitra;

use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MitraComplaintController extends Controller
{
    /**
     * Show incident / complaint submission form for an order (MIT-19).
     */
    public function create(Request $request, Order $order): View|RedirectResponse
    {
        $user = $request->user();

        if ($order->mitra_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        $existingComplaint = Complaint::where('order_id', $order->id)->first();

        if ($existingComplaint) {
            return redirect()->route('mitra.orders.show', $order)
                ->with('info', 'Pesanan ini telah memiliki laporan kendala/aduan dengan nomor tiket '.$existingComplaint->complaint_code.'.');
        }

        $categories = ComplaintCategory::where('is_active', true)->orderBy('name')->get();

        return view('mitra.complaints.create', compact('order', 'categories'));
    }

    /**
     * Store incident report / complaint submitted by partner (MIT-19).
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if ($order->mitra_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        $existingComplaint = Complaint::where('order_id', $order->id)->first();

        if ($existingComplaint) {
            return redirect()->route('mitra.orders.show', $order)
                ->with('error', 'Pesanan ini telah memiliki laporan kendala/aduan.');
        }

        $validated = $request->validate([
            'complaint_category_id' => ['nullable', 'exists:complaint_categories,id'],
            'title' => ['required', 'string', 'min:5', 'max:100'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'is_emergency' => ['nullable', 'boolean'],
            'evidence_photos' => ['nullable', 'array', 'max:3'],
            'evidence_photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'title.required' => 'Judul laporan kendala wajib diisi.',
            'title.min' => 'Judul laporan minimal 5 karakter.',
            'description.required' => 'Deskripsi kejadian wajib diisi.',
            'description.min' => 'Deskripsi kejadian minimal 10 karakter.',
            'evidence_photos.max' => 'Maksimal 3 foto bukti yang dapat diunggah.',
        ]);

        $photoPaths = [];
        if ($request->hasFile('evidence_photos')) {
            foreach ($request->file('evidence_photos') as $photo) {
                $photoPaths[] = $photo->store('complaints/mitra', 'public');
            }
        }

        $isEmergency = $request->boolean('is_emergency');
        $priority = $isEmergency ? 'darurat' : 'tinggi';

        $complaint = Complaint::create([
            'order_id' => $order->id,
            'region_id' => $order->region_id,
            'complainant_id' => $user->id,
            'complainant_role' => 'mitra',
            'target_id' => $order->customer_id,
            'complaint_category_id' => $validated['complaint_category_id'] ?? null,
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'evidence_photos' => ! empty($photoPaths) ? $photoPaths : null,
            'status' => ComplaintStatus::Menunggu,
            'priority' => $priority,
        ]);

        $msg = $isEmergency
            ? 'Peringatan darurat dan laporan insiden tiket '.$complaint->complaint_code.' telah diteruskan ke Admin Wilayah dengan status prioritas darurat.'
            : 'Laporan kendala pesanan dengan nomor tiket '.$complaint->complaint_code.' berhasil dikirim ke Admin Wilayah.';

        return redirect()->route('mitra.orders.show', $order)->with('status', $msg);
    }
}
