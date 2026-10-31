<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\Role;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\InstantOrderMatchingService;
use App\Services\RegionService;

test('partner can match instant order from secondary region within 5 km', function () {
    $regionService = app(RegionService::class);
    $matchingService = app(InstantOrderMatchingService::class);

    // Primary region A: Jember
    $regionA = $regionService->createRegion(
        'Testing Jember',
        [[113.60, -8.10], [113.80, -8.10], [113.80, -8.30], [113.60, -8.30], [113.60, -8.10]]
    );
    // Secondary region B: Bondowoso
    $regionB = $regionService->createRegion(
        'Testing Bondowoso',
        [[113.81, -8.10], [114.00, -8.10], [114.00, -8.30], [113.81, -8.30], [113.81, -8.10]]
    );

    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $regionA);
    $regionService->assignAdmin($admin, $regionB);
    $regionService->activateRegion($regionA);
    $regionService->activateRegion($regionB);

    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    // Mitra located in Region A, close to border: lat -8.20, lng 113.80
    $mitra = User::factory()->mitra()->create(['gender' => 'laki-laki']);
    $mitra->partnerProfile()->create([
        'region_id' => $regionA->id,
        'secondary_region_id' => $regionB->id,
        'coordinate' => ['lat' => -8.20, 'lng' => 113.80],
        'current_coordinate' => ['lat' => -8.20, 'lng' => 113.80],
        'is_active' => true,
        'address_text' => 'Perbatasan Jember Bondowoso',
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    // Order placed in Region B (Bondowoso), 2 km from border: lat -8.20, lng 113.818
    $order = Order::create([
        'order_code' => 'ORD-SEC-001',
        'order_type' => OrderType::Instan->value,
        'customer_id' => $customer->id,
        'region_id' => $regionB->id,
        'service_id' => null,
        'service_package_id' => null,
        'service_name' => 'Pijat Tradisional',
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_type' => 'self',
        'recipient_name' => $customer->name,
        'recipient_gender' => 'laki-laki',
        'preferred_gender' => 'bebas',
        'recipient_phone' => '081234567890',
        'service_address_text' => 'Bondowoso Kota',
        'service_coordinate' => ['lat' => -8.20, 'lng' => 113.818],
        'status' => OrderStatus::MencariMitra,
    ]);

    $eligiblePartners = $matchingService->findEligiblePartners($order);

    expect($eligiblePartners)->toHaveCount(1)
        ->and($eligiblePartners->first()->partner_id)->toBe($mitra->id);
});

test('partner can view and take scheduled order from secondary region within 10 km', function () {
    $regionService = app(RegionService::class);

    // Primary region A
    $regionA = $regionService->createRegion(
        'Testing Region A',
        [[115.10, -8.10], [115.30, -8.10], [115.30, -8.30], [115.10, -8.30], [115.10, -8.10]]
    );
    // Secondary region B
    $regionB = $regionService->createRegion(
        'Testing Region B',
        [[115.31, -8.10], [115.50, -8.10], [115.50, -8.30], [115.31, -8.30], [115.31, -8.10]]
    );

    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $regionA);
    $regionService->assignAdmin($admin, $regionB);
    $regionService->activateRegion($regionA);
    $regionService->activateRegion($regionB);

    $customer = User::factory()->create(['role' => Role::Pelanggan]);
    $service = Service::create(['name' => 'Pijat Refleksi', 'slug' => 'pijat-refleksi-sec', 'is_active' => true]);
    $package = ServicePackage::create(['service_id' => $service->id, 'duration_minutes' => 90, 'price' => 150000, 'is_active' => true]);

    // Mitra in Region A: lat -8.20, lng 115.30
    $mitra = User::factory()->mitra()->create(['gender' => 'laki-laki']);
    $profile = $mitra->partnerProfile()->create([
        'region_id' => $regionA->id,
        'secondary_region_id' => $regionB->id,
        'coordinate' => ['lat' => -8.20, 'lng' => 115.30],
        'current_coordinate' => ['lat' => -8.20, 'lng' => 115.30],
        'is_active' => true,
        'address_text' => 'Perbatasan Region A',
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    // Scheduled order in Region B (4 km away): lat -8.20, lng 115.336
    $order = Order::create([
        'order_code' => 'ORD-SEC-SCHED-01',
        'order_type' => OrderType::Terjadwal->value,
        'customer_id' => $customer->id,
        'region_id' => $regionB->id,
        'service_id' => $service->id,
        'service_package_id' => $package->id,
        'service_name' => $service->name,
        'duration_minutes' => 90,
        'total_price' => 150000,
        'recipient_type' => 'self',
        'recipient_name' => $customer->name,
        'recipient_gender' => 'laki-laki',
        'preferred_gender' => 'bebas',
        'recipient_phone' => '081234567890',
        'service_address_text' => 'Alamat Region B',
        'service_coordinate' => ['lat' => -8.20, 'lng' => 115.336],
        'scheduled_start_time' => now()->addHours(5),
        'status' => OrderStatus::Dijadwalkan,
    ]);

    // 1. Mitra sees order from Region B in Bursa (scheduled orders list)
    $response = $this->actingAs($mitra)->get(route('mitra.orders.scheduled'));
    $response->assertOk()
        ->assertSee('ORD-SEC-SCHED-01')
        ->assertSee('Pijat Refleksi');

    // 2. Mitra takes scheduled order from Region B
    $takeResponse = $this->actingAs($mitra)->post(route('mitra.orders.take', $order), [
        'latitude' => -8.20,
        'longitude' => 115.30,
    ]);

    $takeResponse->assertRedirect(route('mitra.orders.my-schedule'));
    expect($order->fresh()->mitra_id)->toBe($mitra->id);
});
