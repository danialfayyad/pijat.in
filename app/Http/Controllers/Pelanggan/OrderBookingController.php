<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispatch;
use App\Models\OrderStatusHistory;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Services\InstantOrderMatchingService;
use App\Services\OrderBookingService;
use App\Services\WalletService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderBookingController extends Controller
{
    public function __construct(
        protected OrderBookingService $bookingService,
        protected InstantOrderMatchingService $matchingService,
        protected WalletService $walletService
    ) {}

    /**
     * Display list of customer orders (active & history).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = Order::where('customer_id', $user->id)
            ->with(['service', 'servicePackage', 'mitra.partnerProfile'])
            ->orderBy('id', 'desc')
            ->get();

        $activeOrders = $orders->filter(fn (Order $o) => ! $o->status->isTerminal());
        $pastOrders = $orders->filter(fn (Order $o) => $o->status->isTerminal());

        return view('pelanggan.orders.index', compact('activeOrders', 'pastOrders'));
    }

    /**
     * Check instant order partner availability (CUS-08).
     */
    public function checkInstantAvailability(Request $request): JsonResponse
    {
        $user = $request->user();
        $addressId = $request->query('address_id');
        $preferredGender = strtolower(trim((string) $request->query('preferred_gender', $request->query('gender', 'bebas'))));
        if (! in_array($preferredGender, ['pria', 'wanita', 'bebas'], true)) {
            $preferredGender = 'bebas';
        }
        $packageId = $request->query('package_id');

        $address = $user->customerAddresses()->with('region')->find($addressId)
            ?? $user->primaryAddress
            ?? $user->customerAddresses()->first();

        if (! $address) {
            return response()->json([
                'available' => false,
                'message' => 'Alamat belum ditentukan. Silakan tambahkan alamat layanan terlebih dahulu.',
            ]);
        }

        $serviceId = null;
        if ($packageId) {
            $package = ServicePackage::find($packageId);
            $serviceId = $package?->service_id;
        }

        $available = $this->matchingService->hasAvailablePartners(
            (float) $address->latitude,
            (float) $address->longitude,
            $preferredGender,
            (int) $address->region_id,
            $serviceId
        );

        return response()->json([
            'available' => $available,
            'message' => $available
                ? 'Mitra terapis online tersedia di sekitar lokasi Anda.'
                : 'Saat ini tidak ada mitra terapis online yang tersedia di sekitar lokasi Anda (radius 5 km). Silakan beralih ke jadwal Terjadwal.',
        ]);
    }

    /**
     * Pay order using customer wallet balance (CUS-09).
     */
    public function payWithWallet(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($order->status !== OrderStatus::MenungguPembayaran) {
            return redirect()->route('pelanggan.orders.show', $order)
                ->with('info', 'Pesanan ini tidak lagi dalam status menunggu pembayaran.');
        }

        try {
            $this->walletService->debit(
                $user,
                (float) $order->total_price,
                'order_payment',
                (string) $order->id,
                'Pembayaran pesanan '.$order->order_code,
                $order->region_id
            );

            $order->update(['status' => OrderStatus::MencariMitra]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => OrderStatus::MenungguPembayaran->value,
                'to_status' => OrderStatus::MencariMitra->value,
                'actor_id' => $user->id,
                'actor_role' => 'pelanggan',
                'note' => 'Pembayaran lunas via Saldo Dompet. Pesanan masuk antrean pencarian mitra.',
            ]);

            if ($order->order_type === OrderType::Instan) {
                $this->matchingService->dispatchNextOffer($order);
            }

            return redirect()->route('pelanggan.orders.show', $order)
                ->with('success', 'Pembayaran berhasil dikonfirmasi! Sistem sedang mencarikan mitra terdekat.');
        } catch (DomainException $e) {
            return redirect()->route('pelanggan.orders.show', $order)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel an active order by customer (CUS-12).
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Akses tidak sah.');
        }

        if ($order->status->isTerminal()) {
            return back()->with('error', 'Pesanan ini sudah selesai atau telah dibatalkan sebelumnya.');
        }

        if (! in_array($order->status, [OrderStatus::MenungguPembayaran, OrderStatus::MencariMitra, OrderStatus::Dijadwalkan])) {
            return back()->with('error', 'Pesanan yang sedang dalam perjalanan atau sedang berlangsung tidak dapat dibatalkan mandiri.');
        }

        $reason = trim((string) $request->input('reason', 'Dibatalkan oleh pelanggan'));
        if (empty($reason)) {
            $reason = 'Dibatalkan oleh pelanggan';
        }

        DB::transaction(function () use ($order, $user, $reason) {
            $previousStatus = $order->status;

            if (in_array($previousStatus, [OrderStatus::MencariMitra, OrderStatus::Dijadwalkan])) {
                $this->walletService->credit(
                    $user,
                    (float) $order->total_price,
                    'order_refund',
                    (string) $order->id,
                    'Refund 100% pembatalan pesanan '.$order->order_code,
                    $order->region_id
                );
            }

            if ($order->order_type === OrderType::Instan) {
                OrderDispatch::where('order_id', $order->id)
                    ->where('status', OrderDispatch::STATUS_OFFERED)
                    ->update(['status' => OrderDispatch::STATUS_EXPIRED]);
            }

            $order->update([
                'status' => OrderStatus::Dibatalkan,
                'cancellation_reason' => $reason,
                'cancelled_by_id' => $user->id,
                'cancelled_by_role' => 'pelanggan',
                'mitra_id' => null,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previousStatus->value,
                'to_status' => OrderStatus::Dibatalkan->value,
                'actor_id' => $user->id,
                'actor_role' => 'pelanggan',
                'note' => 'Pembatalan oleh pelanggan: '.$reason,
            ]);
        });

        return redirect()->route('pelanggan.orders.index')
            ->with('success', 'Pesanan '.$order->order_code.' berhasil dibatalkan.');
    }

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
        $nowInRegion = Carbon::now($regionTz);
        $duration = (int) ($package?->duration_minutes ?? 60);

        // Opsi B: Lead time minimal 3 jam dari waktu sekarang
        $earliestBookingTime = $nowInRegion->copy()->addHours(3);

        // Batas maksimal jam mulai hari ini: 21:00 dikurangi durasi paket layanan
        $maxStartToday = $nowInRegion->copy()->setTime(21, 0, 0)->subMinutes($duration);

        // Cek apakah hari ini masih memungkinkan untuk dipesan
        $isTodayBookable = $earliestBookingTime->lte($maxStartToday);

        if (! $isTodayBookable) {
            // Hari ini sudah lewat batas operasional + 3 jam lead time, maka tanggal minimal mulai besok jam 08:00
            $minScheduleDate = $nowInRegion->copy()->addDay();
            $defaultScheduleDate = $minScheduleDate->format('Y-m-d');
            $defaultScheduleTime = '08:00';
            $minTimeToday = '08:00';
        } else {
            // Hari ini masih bisa dipesan
            $openingToday = $nowInRegion->copy()->setTime(8, 0, 0);
            $initialStart = $earliestBookingTime->lt($openingToday) ? $openingToday : $earliestBookingTime;

            // Bulatkan menit ke kelipatan 15 menit berikutnya agar rapi
            $minute = (int) $initialStart->minute;
            $roundedMinute = (int) (ceil($minute / 15) * 15);
            if ($roundedMinute >= 60) {
                $initialStart->addHour()->setMinute(0);
            } else {
                $initialStart->setMinute($roundedMinute);
            }

            $minScheduleDate = $nowInRegion;
            $defaultScheduleDate = $initialStart->format('Y-m-d');
            $defaultScheduleTime = $initialStart->format('H:i');
            $minTimeToday = $defaultScheduleTime;
        }

        $maxScheduleDate = $nowInRegion->copy()->addDays(7);
        $todayDate = $nowInRegion->format('Y-m-d');

        return view('pelanggan.orders.create', compact(
            'user',
            'package',
            'siblingPackages',
            'addresses',
            'defaultAddress',
            'minScheduleDate',
            'maxScheduleDate',
            'defaultScheduleDate',
            'defaultScheduleTime',
            'todayDate',
            'minTimeToday',
            'isTodayBookable',
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
            'recipient_gender' => ['nullable', 'in:pria,wanita,laki-laki,perempuan'],
            'preferred_gender' => ['nullable', 'in:pria,wanita,bebas'],
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

        $order->load(['customerAddress', 'region', 'service', 'servicePackage', 'mitra.partnerProfile']);

        return view('pelanggan.orders.show', compact('order'));
    }
}
