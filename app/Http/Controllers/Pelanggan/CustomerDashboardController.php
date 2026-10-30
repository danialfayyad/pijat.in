<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    /**
     * Display the customer PWA home dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load(['primaryAddress.region', 'region']);

        $services = Service::active()
            ->with(['activePackages' => fn ($q) => $q->orderBy('duration_minutes')])
            ->get();

        $activeOrders = Order::where('customer_id', $user->id)
            ->whereNotIn('status', [OrderStatus::Selesai, OrderStatus::Dibatalkan, OrderStatus::Kedaluwarsa])
            ->with(['service', 'servicePackage'])
            ->orderBy('id', 'desc')
            ->get();

        return view('pelanggan.dashboard', compact('user', 'services', 'activeOrders'));
    }
}
