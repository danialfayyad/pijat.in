<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders scoped to the admin's assigned regions (ADM-04).
     */
    public function index(Request $request): View
    {
        $admin = $request->user();
        $assignedRegions = $admin->isSuperadmin()
            ? Region::active()->get()
            : $admin->assignedRegions()->where('status', 'aktif')->get();

        if ($assignedRegions->isEmpty() && ! $admin->isSuperadmin()) {
            abort(403, 'Akun admin Anda belum memiliki wilayah tugas.');
        }

        $selectedRegionId = $request->filled('region_id') ? (int) $request->input('region_id') : null;
        if ($selectedRegionId && ! $admin->canAccessRegion($selectedRegionId)) {
            abort(403, 'Anda tidak memiliki hak akses terhadap wilayah tugas ini.');
        }

        $regionIds = $selectedRegionId
            ? [$selectedRegionId]
            : $assignedRegions->pluck('id')->all();

        // Base scoped query
        $baseQuery = Order::inRegions($regionIds);

        // Calculate counts for badges
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->active()->count();
        $stuckCount = (clone $baseQuery)->stuck()->count();
        $completedCount = (clone $baseQuery)->where('status', OrderStatus::Selesai)->count();
        $cancelledCount = (clone $baseQuery)->where('status', OrderStatus::Dibatalkan)->count();

        // Status tab filter
        $tab = $request->input('tab', 'semua');
        $ordersQuery = (clone $baseQuery)
            ->with(['customer', 'mitra', 'region', 'service'])
            ->latest();

        match ($tab) {
            'aktif' => $ordersQuery->active(),
            'macet' => $ordersQuery->stuck(),
            'selesai' => $ordersQuery->where('status', OrderStatus::Selesai),
            'batal' => $ordersQuery->where('status', OrderStatus::Dibatalkan),
            default => null,
        };

        // Search filter
        if ($search = $request->input('search')) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('service_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('mitra', fn ($mq) => $mq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $ordersQuery->paginate(15)->withQueryString();

        return view('admin.orders.index', compact(
            'orders',
            'assignedRegions',
            'selectedRegionId',
            'tab',
            'totalCount',
            'activeCount',
            'stuckCount',
            'completedCount',
            'cancelledCount'
        ));
    }

    /**
     * Display the specified order detail with full status timeline (ADM-04).
     */
    public function show(Order $order, Request $request): View
    {
        $admin = $request->user();

        if (! $admin->canAccessRegion($order->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses terhadap pesanan di luar wilayah tugas Anda.');
        }

        $order->load([
            'customer',
            'mitra.partnerProfile',
            'region',
            'service',
            'servicePackage',
            'statusHistories.actor',
            'cancelledBy',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cancel a stuck or problematic order with a required reason (ADM-04, T-20).
     */
    public function cancel(Order $order, Request $request): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin->canAccessRegion($order->region_id)) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan pesanan di luar wilayah tugas Anda.');
        }

        if ($order->status->isTerminal()) {
            return back()->withErrors(['order' => 'Pesanan yang sudah selesai atau dibatalkan tidak dapat diintervensi lagi.']);
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => 'Alasan pembatalan pesanan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($order, $request, $admin) {
            $fromStatus = $order->status;
            $reason = trim($request->input('reason'));

            $order->update([
                'status' => OrderStatus::Dibatalkan,
                'cancellation_reason' => $reason,
                'cancelled_by_id' => $admin->id,
                'cancelled_by_role' => $admin->role->value,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => OrderStatus::Dibatalkan,
                'actor_id' => $admin->id,
                'actor_role' => $admin->role->value,
                'note' => 'Pembatalan intervensi oleh Admin: '.$reason,
            ]);
        });

        return redirect()->route('admin.orders.show', $order)
            ->with('status', 'Pesanan berhasil dibatalkan oleh Admin. Dana customer 100% dialokasikan kembali.');
    }
}
