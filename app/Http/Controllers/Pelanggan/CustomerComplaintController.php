<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerComplaintController extends Controller
{
    /**
     * Display a listing of complaints filed by the customer.
     */
    public function index(Request $request): View
    {
        $complaints = Complaint::where('complainant_id', $request->user()->id)
            ->where('complainant_role', 'pelanggan')
            ->with(['order', 'complaintCategory'])
            ->latest()
            ->paginate(10);

        return view('pelanggan.complaints.index', compact('complaints'));
    }

    /**
     * Show complaint submission form for a completed order (CUS-19).
     */
    public function create(Request $request, Order $order): View|RedirectResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($order->status !== OrderStatus::Selesai) {
            return redirect()->route('pelanggan.orders.show', $order)
                ->with('error', 'Aduan hanya dapat diajukan setelah layanan selesai dilaksanakan.');
        }

        // CUS-19: Window maksimal 24 jam setelah layanan selesai
        $completedTime = $order->service_completed_at ?? $order->updated_at;
        if ($completedTime && $completedTime->lt(Carbon::now()->subHours(24))) {
            return redirect()->route('pelanggan.orders.show', $order)
                ->with('error', 'Batas waktu pengajuan aduan (maksimal 24 jam sejak layanan selesai) telah berakhir.');
        }

        $existingComplaint = Complaint::where('order_id', $order->id)
            ->where('complainant_id', $user->id)
            ->first();

        if ($existingComplaint) {
            return redirect()->route('pelanggan.complaints.show', $existingComplaint)
                ->with('info', 'Anda telah mengajukan aduan untuk pesanan ini.');
        }

        $categories = ComplaintCategory::where('is_active', true)->orderBy('name')->get();

        return view('pelanggan.complaints.create', compact('order', 'categories'));
    }

    /**
     * Store a newly created complaint in storage (CUS-19).
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($order->status !== OrderStatus::Selesai) {
            return back()->with('error', 'Aduan hanya dapat diajukan setelah layanan selesai.');
        }

        $completedTime = $order->service_completed_at ?? $order->updated_at;
        if ($completedTime && $completedTime->lt(Carbon::now()->subHours(24))) {
            return redirect()->route('pelanggan.orders.show', $order)
                ->with('error', 'Batas waktu pengajuan aduan (maksimal 24 jam sejak layanan selesai) telah berakhir.');
        }

        $existingComplaint = Complaint::where('order_id', $order->id)
            ->where('complainant_id', $user->id)
            ->first();

        if ($existingComplaint) {
            return redirect()->route('pelanggan.orders.show', $order)
                ->with('error', 'Anda telah mengajukan aduan untuk pesanan ini.');
        }

        $validated = $request->validate([
            'complaint_category_id' => ['required', 'exists:complaint_categories,id'],
            'title' => ['required', 'string', 'min:5', 'max:100'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'evidence_photos' => ['nullable', 'array', 'max:3'],
            'evidence_photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'complaint_category_id.required' => 'Pilih kategori aduan yang sesuai.',
            'title.required' => 'Judul ringkasan aduan wajib diisi.',
            'title.min' => 'Judul ringkasan aduan minimal 5 karakter.',
            'description.required' => 'Deskripsi keluhan wajib diisi dengan jelas.',
            'description.min' => 'Deskripsi keluhan minimal 10 karakter.',
            'evidence_photos.max' => 'Maksimal 3 foto bukti yang dapat diunggah.',
            'evidence_photos.*.image' => 'File bukti harus berupa format gambar (JPG/PNG).',
            'evidence_photos.*.max' => 'Ukuran foto maksimal 2MB per file.',
        ]);

        $photoPaths = [];
        if ($request->hasFile('evidence_photos')) {
            foreach ($request->file('evidence_photos') as $photo) {
                $photoPaths[] = $photo->store('complaints', 'public');
            }
        }

        $complaint = Complaint::create([
            'order_id' => $order->id,
            'region_id' => $order->region_id,
            'complainant_id' => $user->id,
            'complainant_role' => 'pelanggan',
            'target_id' => $order->mitra_id,
            'complaint_category_id' => $validated['complaint_category_id'],
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'evidence_photos' => ! empty($photoPaths) ? $photoPaths : null,
            'status' => ComplaintStatus::Menunggu,
            'priority' => 'normal',
        ]);

        return redirect()->route('pelanggan.complaints.show', $complaint)
            ->with('success', 'Laporan aduan Anda berhasil dikirim ke Admin Wilayah dengan nomor tiket '.$complaint->complaint_code.'. Admin akan menindaklanjuti dalam 1x24 jam.');
    }

    /**
     * Display complaint details.
     */
    public function show(Complaint $complaint, Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($complaint->complainant_id !== $user->id || $complaint->complainant_role !== 'pelanggan') {
            abort(403, 'Akses ditolak.');
        }

        $complaint->load(['order.service', 'complaintCategory', 'target']);

        return view('pelanggan.complaints.show', compact('complaint'));
    }
}
