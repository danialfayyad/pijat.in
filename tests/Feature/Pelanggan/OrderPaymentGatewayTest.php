<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\TopupInvoice;
use App\Models\User;
use App\Models\Wallet;
use App\Services\OrderCancellationService;
use App\Services\RegionService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->regionService = app(RegionService::class);
    $this->walletService = app(WalletService::class);

    // Active Region
    $this->region = $this->regionService->createRegion(
        'Wilayah Jogja',
        [[110.30, -7.70], [110.45, -7.70], [110.45, -7.85], [110.30, -7.85], [110.30, -7.70]]
    );
    $this->admin = User::factory()->create(['role' => Role::Admin]);
    $this->regionService->assignAdmin($this->admin, $this->region);
    $this->regionService->activateRegion($this->region);

    // Customer
    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
        'gender' => 'pria',
    ]);

    // Primary address inside region
    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Malioboro No. 10, Yogyakarta',
        'latitude' => -7.79,
        'longitude' => 110.36,
        'coordinate' => ['lat' => -7.79, 'lng' => 110.36],
        'is_primary' => true,
    ]);

    // Service & Package
    $this->service = Service::factory()->create(['name' => 'Pijat Tradisional']);
    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);
});

it('automatically pays order with wallet balance when sufficient and enters MencariMitra', function () {
    // Credit customer wallet with 150.000 (enough for 100.000 package)
    $this->walletService->credit($this->customer, 150000, 'topup', 'TOPUP-001', 'Deposit saldo', $this->region->id);

    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(4)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(4)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::MencariMitra);

    // Wallet balance should be decremented by 100.000 (remaining: 50.000)
    expect($this->walletService->getBalance($this->customer))->toBe(50000.0);

    // Redirects to orders.show
    $response->assertRedirect(route('pelanggan.orders.show', $order));
});

it('creates order in MenungguPembayaran, generates deficit invoice, and redirects to invoice page when balance is insufficient (CUS-09)', function () {
    // Customer has 30.000, needs 100.000 (deficit: 70.000)
    $this->walletService->credit($this->customer, 30000, 'topup', 'TOPUP-001', 'Deposit awal', $this->region->id);

    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(4)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(4)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::MenungguPembayaran);

    // Deficit invoice should be generated automatically
    $invoice = TopupInvoice::where('order_id', $order->id)->first();
    expect($invoice)->not->toBeNull()
        ->and((float) $invoice->amount)->toBe(70000.0)
        ->and($invoice->status)->toBe('PENDING')
        ->and($invoice->user_id)->toBe($this->customer->id)
        ->and($invoice->region_id)->toBe($this->region->id);

    // Opsi 1: Redirects directly to the Xendit checkout URL or invoice fallback
    if ($invoice->payment_url) {
        $response->assertRedirect($invoice->payment_url);
    } else {
        $response->assertRedirect(route('pelanggan.wallet.invoice', $invoice->invoice_number));
    }
});

it('allows customer to pay deficit invoice via simulatePay and atomically settles order into MencariMitra', function () {
    // Initial balance: 40.000, package: 100.000, deficit: 60.000
    $this->walletService->credit($this->customer, 40000, 'topup', 'TOPUP-001', 'Deposit awal', $this->region->id);

    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(5)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(5)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();
    $invoice = TopupInvoice::where('order_id', $order->id)->first();

    // Customer simulates payment of deficit invoice
    $payResponse = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.simulate-pay', $invoice->invoice_number));

    // Redirects back to order tracking page
    $payResponse->assertRedirect(route('pelanggan.orders.show', $order));

    $invoice->refresh();
    expect($invoice->status)->toBe('PAID');

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::MencariMitra);

    // Balance after: 40.000 + 60.000 (topup) - 100.000 (order payment) = 0
    expect($this->walletService->getBalance($this->customer))->toBe(0.0);
});

it('handles Xendit webhook settlement for order deficit invoice atomically and transitions order to MencariMitra', function () {
    // Initial balance: 0, package: 100.000, deficit: 100.000
    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(4)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(4)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();
    $invoice = TopupInvoice::where('order_id', $order->id)->first();

    // Incoming Xendit Webhook for invoice
    $webhookResponse = $this->postJson(route('api.webhooks.xendit.topup'), [
        'external_id' => $invoice->invoice_number,
        'status' => 'PAID',
        'id' => 'xendit_paid_998877',
    ]);

    $webhookResponse->assertOk()->assertJson(['success' => true]);

    $invoice->refresh();
    expect($invoice->status)->toBe('PAID')
        ->and($invoice->xendit_invoice_id)->toBe('xendit_paid_998877');

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::MencariMitra);

    // Remaining balance is 0.0 (100.000 credited then immediately debited for order)
    expect($this->walletService->getBalance($this->customer))->toBe(0.0);
});

it('automatically cancels unpaid order and expires deficit invoice after 15 minutes', function () {
    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(4)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(4)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();
    $invoice = TopupInvoice::where('order_id', $order->id)->first();

    // Fast-forward order and invoice created_at / expires_at to 16 minutes ago
    $past = now()->subMinutes(16);
    DB::table('orders')->where('id', $order->id)->update(['created_at' => $past]);
    DB::table('topup_invoices')->where('id', $invoice->id)->update(['created_at' => $past, 'expires_at' => $past]);

    // Run auto-cancellations command
    app(OrderCancellationService::class)->autoCancelStuckOrders();

    $order->refresh();
    $invoice->refresh();

    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and($order->cancellation_reason)->toContain('15 menit');

    expect($invoice->status)->toBe('EXPIRED');
});

it('displays deficit details and link on order show page when status is MenungguPembayaran', function () {
    $this->walletService->credit($this->customer, 25000, 'topup', 'TOPUP-001', 'Deposit awal', $this->region->id);

    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(4)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(4)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();

    $viewResponse = $this->actingAs($this->customer)->get(route('pelanggan.orders.show', $order));

    $viewResponse->assertOk();
    $viewResponse->assertSee('Konfirmasi Pembayaran Pesanan');
    $viewResponse->assertSee('Batas Waktu Pembayaran');
    $viewResponse->assertSee('Bayar via QRIS / Xendit');
    $viewResponse->assertSee('Rp 75.000'); // Deficit = 100.000 - 25.000
});

it('allows customer to check and sync payment status directly from Xendit API (No Ngrok needed)', function () {
    config(['services.xendit.secret_key' => 'xnd_development_dummy_key']);

    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => now($this->region->timezone)->addHours(4)->format('Y-m-d'),
        'scheduled_time' => now($this->region->timezone)->addHours(4)->format('H:i'),
        'risk_agreement' => true,
    ]);

    $order = Order::where('customer_id', $this->customer->id)->first();
    $invoice = TopupInvoice::where('order_id', $order->id)->first();

    // Fake HTTP call to Xendit returning PAID status
    Http::fake([
        'https://api.xendit.co/v2/invoices*' => Http::response([
            'id' => 'xendit_sync_123',
            'status' => 'PAID',
            'amount' => 100000,
        ], 200),
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.check-status', $invoice->invoice_number));

    $response->assertRedirect(route('pelanggan.orders.show', $order));

    $invoice->refresh();
    $order->refresh();

    expect($invoice->status)->toBe('PAID')
        ->and($order->status)->toBe(OrderStatus::MencariMitra);
});
