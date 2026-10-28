<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Region;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->regionService = app(RegionService::class);

    // Create 2 distinct regions
    $coordsA = [
        [110.0, -7.0],
        [110.4, -7.0],
        [110.4, -6.6],
        [110.0, -6.6],
        [110.0, -7.0],
    ];

    $coordsB = [
        [111.0, -7.0],
        [111.4, -7.0],
        [111.4, -6.6],
        [111.0, -6.6],
        [111.0, -7.0],
    ];

    $this->regionA = $this->regionService->createRegion('Wilayah Solo', $coordsA);
    $this->regionB = $this->regionService->createRegion('Wilayah Madiun', $coordsB);

    // Create Admin assigned to Region A only
    $this->adminA = User::factory()->create([
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
    $this->regionService->assignAdmin($this->adminA, $this->regionA, true);
    $this->regionService->activateRegion($this->regionA);

    // Also activate Region B by assigning a dummy admin
    $adminB = User::factory()->create([
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
    $this->regionService->assignAdmin($adminB, $this->regionB, true);
    $this->regionService->activateRegion($this->regionB);

    // Create Superadmin
    $this->superadmin = User::factory()->create([
        'role' => Role::Superadmin,
        'email_verified_at' => now(),
    ]);
});

test('guest or non-admin cannot access order monitoring', function () {
    $this->get(route('admin.orders.index'))->assertRedirect(route('login'));

    $customer = User::factory()->customer()->create();
    $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();
});

test('admin without assigned regions receives 403 on order monitoring', function () {
    $unassignedAdmin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($unassignedAdmin)
        ->get(route('admin.orders.index'))
        ->assertForbidden();
});

test('admin only sees orders within their assigned regions (ADM-04, ADM-15)', function () {
    $orderA = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-SOLO-101',
    ]);

    $orderB = Order::factory()->create([
        'region_id' => $this->regionB->id,
        'order_code' => 'ORD-MADIUN-202',
    ]);

    $response = $this->actingAs($this->adminA)->get(route('admin.orders.index'));

    $response->assertOk();
    $response->assertSee('ORD-SOLO-101');
    $response->assertDontSee('ORD-MADIUN-202');
});

test('admin with multiple regions can filter orders by specific region', function () {
    // Assign Region B to Admin A as well
    $this->regionService->assignAdmin($this->adminA, $this->regionB, false);

    $orderA = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-SOLO-001',
    ]);

    $orderB = Order::factory()->create([
        'region_id' => $this->regionB->id,
        'order_code' => 'ORD-MADIUN-002',
    ]);

    // View with Region A filter
    $responseA = $this->actingAs($this->adminA)
        ->get(route('admin.orders.index', ['region_id' => $this->regionA->id]));

    $responseA->assertOk();
    $responseA->assertSee('ORD-SOLO-001');
    $responseA->assertDontSee('ORD-MADIUN-002');

    // View with Region B filter
    $responseB = $this->actingAs($this->adminA)
        ->get(route('admin.orders.index', ['region_id' => $this->regionB->id]));

    $responseB->assertOk();
    $responseB->assertSee('ORD-MADIUN-002');
    $responseB->assertDontSee('ORD-SOLO-001');
});

test('admin cannot filter orders by unassigned region (ADM-15, 403)', function () {
    $this->actingAs($this->adminA)
        ->get(route('admin.orders.index', ['region_id' => $this->regionB->id]))
        ->assertForbidden();
});

test('superadmin can access all regions and filter freely', function () {
    $orderA = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-SOLO-999',
    ]);
    $orderB = Order::factory()->create([
        'region_id' => $this->regionB->id,
        'order_code' => 'ORD-MADIUN-999',
    ]);

    $response = $this->actingAs($this->superadmin)->get(route('admin.orders.index'));
    $response->assertOk();
    $response->assertSee('ORD-SOLO-999');
    $response->assertSee('ORD-MADIUN-999');
});

test('status tabs correctly filter active, stuck, completed, and cancelled orders', function () {
    // Active regular order
    $activeOrder = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-ACTIVE-01',
        'status' => OrderStatus::MenujuLokasi,
    ]);

    // Stuck instant order (> 30 minutes looking for partner)
    $stuckOrder = Order::factory()->stuck()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-STUCK-02',
    ]);

    // Completed order
    $completedOrder = Order::factory()->completed()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-DONE-03',
    ]);

    // Cancelled order
    $cancelledOrder = Order::factory()->cancelled('Batal oleh user')->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-CANCEL-04',
    ]);

    // Tab 'aktif'
    $resActive = $this->actingAs($this->adminA)->get(route('admin.orders.index', ['tab' => 'aktif']));
    $resActive->assertSee('ORD-ACTIVE-01');
    $resActive->assertDontSee('ORD-DONE-03');
    $resActive->assertDontSee('ORD-CANCEL-04');

    // Tab 'macet'
    $resStuck = $this->actingAs($this->adminA)->get(route('admin.orders.index', ['tab' => 'macet']));
    $resStuck->assertSee('ORD-STUCK-02');
    $resStuck->assertDontSee('ORD-DONE-03');

    // Tab 'selesai'
    $resDone = $this->actingAs($this->adminA)->get(route('admin.orders.index', ['tab' => 'selesai']));
    $resDone->assertSee('ORD-DONE-03');
    $resDone->assertDontSee('ORD-ACTIVE-01');

    // Tab 'batal'
    $resCancel = $this->actingAs($this->adminA)->get(route('admin.orders.index', ['tab' => 'batal']));
    $resCancel->assertSee('ORD-CANCEL-04');
    $resCancel->assertDontSee('ORD-ACTIVE-01');
});

test('order search filter matches order code, recipient name, or service name', function () {
    Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-FINDME-77',
        'recipient_name' => 'Budi Santoso',
    ]);

    Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-OTHER-88',
        'recipient_name' => 'Siti Nurhaliza',
    ]);

    $response = $this->actingAs($this->adminA)
        ->get(route('admin.orders.index', ['search' => 'FINDME']));

    $response->assertSee('ORD-FINDME-77');
    $response->assertDontSee('ORD-OTHER-88');

    $responseByName = $this->actingAs($this->adminA)
        ->get(route('admin.orders.index', ['search' => 'Siti Nurhaliza']));

    $responseByName->assertSee('ORD-OTHER-88');
    $responseByName->assertDontSee('ORD-FINDME-77');
});

test('admin can view order details and status timeline within assigned region (ADM-04)', function () {
    $order = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'order_code' => 'ORD-DETAIL-123',
        'status' => OrderStatus::MencariMitra,
    ]);

    OrderStatusHistory::create([
        'order_id' => $order->id,
        'from_status' => OrderStatus::MenungguPembayaran,
        'to_status' => OrderStatus::MencariMitra,
        'actor_id' => $order->customer_id,
        'actor_role' => 'customer',
        'note' => 'Pembayaran lunas via QRIS.',
    ]);

    $response = $this->actingAs($this->adminA)->get(route('admin.orders.show', $order));

    $response->assertOk();
    $response->assertSee('ORD-DETAIL-123');
    $response->assertSee('Pembayaran lunas via QRIS.');
    $response->assertSee('Mencari Mitra');
});

test('admin cannot view order details outside assigned region (403)', function () {
    $orderB = Order::factory()->create([
        'region_id' => $this->regionB->id,
        'order_code' => 'ORD-FORBIDDEN-99',
    ]);

    $this->actingAs($this->adminA)
        ->get(route('admin.orders.show', $orderB))
        ->assertForbidden();
});

test('admin can cancel active order with required reason (ADM-04, T-20)', function () {
    $order = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->adminA)->post(route('admin.orders.cancel', $order), [
        'reason' => 'Tidak ada mitra yang tersedia di area dalam waktu SLA.',
    ]);

    $response->assertRedirect(route('admin.orders.show', $order));
    $response->assertSessionHas('status');

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan);
    expect($order->cancellation_reason)->toBe('Tidak ada mitra yang tersedia di area dalam waktu SLA.');
    expect($order->cancelled_by_id)->toBe($this->adminA->id);
    expect($order->cancelled_by_role)->toBe(Role::Admin->value);

    // Verify history entry was written
    $history = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
    expect($history)->not->toBeNull();
    expect($history->to_status)->toBe(OrderStatus::Dibatalkan);
    expect($history->actor_id)->toBe($this->adminA->id);
    expect($history->note)->toContain('Tidak ada mitra yang tersedia di area dalam waktu SLA.');
});

test('admin cannot cancel order with missing or short reason (< 5 characters)', function () {
    $order = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->adminA)->post(route('admin.orders.cancel', $order), [
        'reason' => 'test',
    ]);

    $response->assertSessionHasErrors('reason');

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::MencariMitra);
});

test('admin cannot cancel already terminal order (completed or cancelled)', function () {
    $completedOrder = Order::factory()->completed()->create([
        'region_id' => $this->regionA->id,
    ]);

    $response = $this->actingAs($this->adminA)->post(route('admin.orders.cancel', $completedOrder), [
        'reason' => 'Mencoba batalkan pesanan selesai.',
    ]);

    $response->assertSessionHasErrors('order');

    $completedOrder->refresh();
    expect($completedOrder->status)->toBe(OrderStatus::Selesai);
});

test('admin cannot cancel order in unassigned region (403)', function () {
    $orderB = Order::factory()->create([
        'region_id' => $this->regionB->id,
        'status' => OrderStatus::MencariMitra,
    ]);

    $this->actingAs($this->adminA)->post(route('admin.orders.cancel', $orderB), [
        'reason' => 'Intervensi ilegal wilayah lain.',
    ])->assertForbidden();

    $orderB->refresh();
    expect($orderB->status)->toBe(OrderStatus::MencariMitra);
});

test('admin dashboard metrics reflect actual active and stuck orders', function () {
    // 2 active orders
    Order::factory()->create([
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::MenujuLokasi,
    ]);
    Order::factory()->create([
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::SedangBerlangsung,
    ]);

    // 1 stuck order
    Order::factory()->stuck()->create([
        'region_id' => $this->regionA->id,
    ]);

    // 1 completed order (not counted as active)
    Order::factory()->completed()->create([
        'region_id' => $this->regionA->id,
    ]);

    $response = $this->actingAs($this->adminA)->get(route('admin.dashboard'));
    $response->assertOk();

    // Check metrics passed to view
    $response->assertViewHas('metrics', function (array $metrics) {
        return $metrics['orders_in_progress'] === 3 // 2 active + 1 stuck (mencari mitra is also active)
            && $metrics['stuck_orders_count'] === 1;
    });
});
