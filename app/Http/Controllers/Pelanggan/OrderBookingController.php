<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Services\OrderBookingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderBookingController extends Controller
{
    public function __construct(
        protected OrderBookingService $bookingService
    ) {}

    /**
     * Show booking checkout page.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Check if customer has at least one registered address
        $addresses = $user->customerAddresses()->with('region')->get();
        if ($addresses->isEmpty()) {
            return redirect()->route('pelanggan.addresses.create')
                ->with('warning', 'Silakan tentukan alamat layanan Anda terlebih dahulu sebelum memesan.');
        }

        $packageId = $request->query('package_id');
        $package = null;

        if ($packageId) {
            $package = ServicePackage::with('service')->where('is_active', true)->find($packageId);
        }

        if (! $package) {
            $package = ServicePackage::with('service')
                ->where('is_active', true)
                ->first();
        }

        if (! $package) {
            return redirect()->route('pelanggan.catalog.index')
                ->with('error', 'Layanan belum tersedia untuk dipesan saat ini.');
        }

        // Sibling packages for the same service
        $siblingPackages = ServicePackage::where('service_id', $package->service_id)
            ->where('is_active', true)
            ->orderBy('duration_minutes')
            ->get();

        // Default address is primary address or first
        $defaultAddress = $addresses->firstWhere('is_primary', true) ?? $addresses->first();

        $regionTz = $defaultAddress?->region?->timezone ?? 'Asia/Jakarta';
        $minScheduleDate = Carbon::now($regionTz)->addHours(3);
        $maxScheduleDate = Carbon::now($regionTz)->addDays(7);

        return view('pelanggan.orders.create', compact(
            'user',
            'package',
            'siblingPackages',
            'addresses',
            'defaultAddress',
            'minScheduleDate',
            'maxScheduleDate',
            'regionTz'
        ));
    }

    /**
     * Handle order submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:service_packages,id'],
            'customer_address_id' => ['required', 'exists:customer_addresses,id'],
            'recipient_type' => ['required', 'in:myself,other'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:25'],
            'recipient_gender' => ['nullable', 'in:pria,wanita'],
            'order_type' => ['required', 'in:instan,terjadwal'],
            'scheduled_date' => ['nullable', 'date_format:Y-m-d'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'risk_agreement' => ['required', 'accepted'],
        ], [
            'risk_agreement.accepted' => 'Anda wajib menyetujui syarat & ketentuan risiko layanan sebelum memesan.',
            'risk_agreement.required' => 'Anda wajib menyetujui syarat & ketentuan risiko layanan sebelum memesan.',
        ]);

        try {
            $order = $this->bookingService->createOrder($request->user(), $validated);

            return redirect()->route('pelanggan.orders.show', $order)
                ->with('success', 'Pesanan Anda berhasil dibuat! Nomor pesanan: '.$order->order_code);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Display order summary / detail.
     */
    public function show(Request $request, Order $order): View
    {
        // Must belong to this customer
        if ($order->customer_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        $order->load(['customerAddress', 'region', 'service', 'servicePackage']);

        return view('pelanggan.orders.show', compact('order'));
    }
}
