<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
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
        'name' => 'Pijat Refleksi',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Setia',
    ]);

    $this->customerWallet = Wallet::create([
        'user_id' => $this->customer->id,
        'balance' => 0,
    ]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kalimantan No. 12',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Terapis Handal',
        'status' => 'terverifikasi',
    ]);

    $this->mitraWallet = Wallet::create([
        'user_id' => $this->mitra->id,
        'balance' => 0,
    ]);

    PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Jawa No. 10',
        'coordinate' => ['lat' => -8.1725, 'lng' => 113.7005],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
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
            'status' => OrderStatus::MenungguPembayaran,
            'service_address_text' => $this->address->address_text,
            'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        ], $overrides));
    };
});

test('pelanggan can cancel order in MenungguPembayaran without refund', function () {
    $order = ($this->createOrder)([
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::MenungguPembayaran,
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.orders.cancel', $order), [
            'reason' => 'Ingin ganti alamat',
        ]);

    $response->assertRedirect(route('pelanggan.orders.index'));
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and($order->cancellation_reason)->toBe('Ingin ganti alamat')
        ->and($this->customerWallet->fresh()->balance)->toEqual(0);
});

test('pelanggan can cancel order in MencariMitra and receive 100% wallet refund (CUS-12)', function () {
    $order = ($this->createOrder)([
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.orders.cancel', $order), [
            'reason' => 'Menunggu terlalu lama',
        ]);

    $response->assertRedirect(route('pelanggan.orders.index'));
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(100000.0);
});

test('pelanggan cancelling Dijadwalkan order more than 3 hours before receives 100% refund (CUS-12)', function () {
    $scheduledTime = Carbon::now()->addHours(5);

    $order = ($this->createOrder)([
        'mitra_id' => $this->mitra->id,
        'order_type' => OrderType::Terjadwal,
        'status' => OrderStatus::Dijadwalkan,
        'scheduled_start_time' => $scheduledTime,
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.orders.cancel', $order), [
            'reason' => 'Ada keperluan mendadak',
        ]);

    $response->assertRedirect(route('pelanggan.orders.index'));
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(100000.0)
        ->and((float) $this->mitraWallet->fresh()->balance)->toEqual(0.0);
});

test('pelanggan cancelling Dijadwalkan order less than 3 hours before incurs 50% deduction to mitra compensation (CUS-12)', function () {
    $scheduledTime = Carbon::now()->addHours(2); // < 3 jam

    $order = ($this->createOrder)([
        'mitra_id' => $this->mitra->id,
        'order_type' => OrderType::Terjadwal,
        'status' => OrderStatus::Dijadwalkan,
        'scheduled_start_time' => $scheduledTime,
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.orders.cancel', $order), [
            'reason' => 'Batal mendadak',
        ]);

    $response->assertRedirect(route('pelanggan.orders.index'));
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(50000.0)
        ->and((float) $this->mitraWallet->fresh()->balance)->toEqual(50000.0);
});

test('pelanggan cannot cancel order in MenujuLokasi unless mitra is late over 30 mins (CUS-12)', function () {
    $order = ($this->createOrder)([
        'mitra_id' => $this->mitra->id,
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::MenujuLokasi,
    ]);

    // OTW baru 10 menit lalu
    $order->statusHistories()->create([
        'from_status' => OrderStatus::Dijadwalkan->value,
        'to_status' => OrderStatus::MenujuLokasi->value,
        'actor_id' => $this->mitra->id,
        'actor_role' => 'mitra',
        'created_at' => now()->subMinutes(10),
    ]);

    $response = $this->actingAs($this->customer)
        ->from(route('pelanggan.orders.show', $order))
        ->post(route('pelanggan.orders.cancel', $order));

    $response->assertRedirect(route('pelanggan.orders.show', $order));
    $response->assertSessionHas('error');
    expect($order->fresh()->status)->toBe(OrderStatus::MenujuLokasi);
});

test('pelanggan can reschedule scheduled order at least 3 hours before schedule (CUS-13)', function () {
    $currentSchedule = Carbon::now()->addHours(6);
    $newDate = Carbon::now()->addDays(2)->format('Y-m-d');

    $order = ($this->createOrder)([
        'mitra_id' => $this->mitra->id,
        'order_type' => OrderType::Terjadwal,
        'status' => OrderStatus::Dijadwalkan,
        'scheduled_start_time' => $currentSchedule,
        'reschedule_count' => 0,
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.orders.reschedule', $order), [
            'scheduled_date' => $newDate,
            'scheduled_time' => '14:00',
        ]);

    $response->assertRedirect(route('pelanggan.orders.show', $order));
    $order->refresh();

    // Order returns to MencariMitra bursa, mitra is detached, reschedule_count incremented to 1
    expect($order->status)->toBe(OrderStatus::MencariMitra)
        ->and($order->mitra_id)->toBeNull()
        ->and($order->reschedule_count)->toBe(1);
});

test('pelanggan cannot reschedule more than 2 times per order (CUS-13)', function () {
    $currentSchedule = Carbon::now()->addHours(6);

    $order = ($this->createOrder)([
        'mitra_id' => $this->mitra->id,
        'order_type' => OrderType::Terjadwal,
        'status' => OrderStatus::Dijadwalkan,
        'scheduled_start_time' => $currentSchedule,
        'reschedule_count' => 2, // sudah 2x
    ]);

    $response = $this->actingAs($this->customer)
        ->from(route('pelanggan.orders.show', $order))
        ->post(route('pelanggan.orders.reschedule', $order), [
            'scheduled_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'scheduled_time' => '14:00',
        ]);

    $response->assertRedirect(route('pelanggan.orders.show', $order));
    $response->assertSessionHas('error');
    expect($order->fresh()->reschedule_count)->toBe(2);
});
