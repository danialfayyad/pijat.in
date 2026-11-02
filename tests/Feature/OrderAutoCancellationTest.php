<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\TopupInvoice;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    $this->service = Service::factory()->create([
        'name' => 'Pijat Tradisional',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Auto Cancel',
    ]);

    $this->customerWallet = Wallet::create([
        'user_id' => $this->customer->id,
        'balance' => 0,
    ]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Mastrip No. 5',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);

    $this->createOrder = function (array $overrides = []) {
        return Order::create(array_merge([
            'order_code' => 'ORD-'.strtoupper(uniqid()),
            'customer_id' => $this->customer->id,
            'region_id' => $this->region->id,
            'service_id' => $this->service->id,
            'service_package_id' => $this->package->id,
            'service_name' => $this->service->name,
            'duration_minutes' => 60,
            'total_price' => 100000,
            'recipient_name' => 'Budi Pelanggan',
            'recipient_gender' => 'pria',
            'order_type' => OrderType::Instan,
            'status' => OrderStatus::MencariMitra,
            'service_address_text' => $this->address->address_text,
            'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        ], $overrides));
    };
});

test('auto cancel stuck instant orders after 30 mins with 100% refund (SYS-02)', function () {
    $order = ($this->createOrder)([
        'order_code' => 'ORD-AUTO-INSTANT',
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::MencariMitra,
    ]);
    $order->forceFill(['created_at' => Carbon::now()->subMinutes(35)])->save();

    $this->artisan('orders:process-cancellations')
        ->assertSuccessful();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(100000.0);
});

test('auto cancel scheduled orders without mitra at H-2 hours before start (SYS-02)', function () {
    $order = ($this->createOrder)([
        'order_code' => 'ORD-AUTO-SCHEDULED',
        'order_type' => OrderType::Terjadwal,
        'status' => OrderStatus::MencariMitra,
        'scheduled_start_time' => Carbon::now()->addHours(1)->addMinutes(30), // kurang dari H-2 jam
    ]);

    $this->artisan('orders:process-cancellations')
        ->assertSuccessful();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(100000.0);
});

test('auto cancel unpaid orders and expire topup invoices after 15 mins (SYS-02)', function () {
    $unpaidOrder = ($this->createOrder)([
        'order_code' => 'ORD-AUTO-UNPAID',
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::MenungguPembayaran,
    ]);
    $unpaidOrder->forceFill(['created_at' => Carbon::now()->subMinutes(20)])->save();

    $topupInvoice = TopupInvoice::create([
        'invoice_number' => 'TOPUP-EXP-001',
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'amount' => 50000,
        'status' => 'PENDING',
        'payment_method' => 'QRIS',
        'expires_at' => Carbon::now()->subMinutes(5),
    ]);

    $this->artisan('orders:process-cancellations')
        ->assertSuccessful();

    expect($unpaidOrder->fresh()->status)->toBe(OrderStatus::Dibatalkan)
        ->and($topupInvoice->fresh()->status)->toBe('EXPIRED');
});
