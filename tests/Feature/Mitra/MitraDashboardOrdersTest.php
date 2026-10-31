<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\Role;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

test('mitra dashboard displays today orders and bursa count correctly', function () {
    $now = Carbon::parse('2026-10-31 10:00:00');
    Carbon::setTestNow($now);

    $region = Region::create([
        'name' => 'Bantul Dashboard Test',
        'boundaries' => DB::raw("ST_GeomFromText('POLYGON((110.3 -7.8, 110.5 -7.8, 110.5 -8.0, 110.3 -8.0, 110.3 -7.8))', 4326, 'axis-order=long-lat')"),
        'timezone' => 'Asia/Jakarta',
        'status' => 'aktif',
    ]);

    $service = Service::create(['name' => 'Pijat Tradisional', 'slug' => 'pijat-trad-dash', 'is_active' => true]);
    $package = ServicePackage::create(['service_id' => $service->id, 'duration_minutes' => 60, 'price' => 100000, 'is_active' => true]);

    $mitra = User::factory()->mitra()->create(['gender' => 'pria']);
    $profile = $mitra->partnerProfile()->create([
        'region_id' => $region->id,
        'coordinate' => ['lat' => -7.88, 'lng' => 110.35],
        'current_coordinate' => ['lat' => -7.88, 'lng' => 110.35],
        'is_active' => true,
        'address_text' => 'Bantul',
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    // 1. Order assigned to mitra for today (6 PM)
    $todayOrder = Order::create([
        'order_code' => 'ORD-TODAY-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $customer->id,
        'mitra_id' => $mitra->id,
        'region_id' => $region->id,
        'service_id' => $service->id,
        'service_package_id' => $package->id,
        'service_name' => 'Pijat Tradisional (60 Menit)',
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Pelanggan Sore',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Bantul Km 5',
        'service_coordinate' => ['lat' => -7.88, 'lng' => 110.35],
        'scheduled_start_time' => Carbon::parse('2026-10-31 18:00:00', 'Asia/Jakarta')->utc(),
        'status' => OrderStatus::Dijadwalkan,
    ]);

    // 2. Open order in Bursa (for tomorrow)
    Order::create([
        'order_code' => 'ORD-BURSA-OPEN',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $customer->id,
        'region_id' => $region->id,
        'service_id' => $service->id,
        'service_package_id' => $package->id,
        'service_name' => 'Pijat Tradisional (60 Menit)',
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Pelanggan Besok',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Parangtritis',
        'service_coordinate' => ['lat' => -7.88, 'lng' => 110.35],
        'scheduled_start_time' => Carbon::parse('2026-11-01 10:00:00', 'Asia/Jakarta')->utc(),
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($mitra)->get(route('mitra.dashboard'));

    $response->assertOk();
    $response->assertSee('Pesanan Hari Ini');
    $response->assertSee('1 Pesanan');
    $response->assertSee('ORD-TODAY-01');
    $response->assertSee('Pijat Tradisional (60 Menit)');
    $response->assertSee('Buka Detail & Rute');

    // Check Bursa badge count
    $response->assertSee('Bursa Pesanan');
    $response->assertSee('1');

    Carbon::setTestNow();
});
