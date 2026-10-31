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
use Carbon\Carbon;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember Kota',
        'status' => 'aktif',
    ]);

    $this->service = Service::factory()->create([
        'name' => 'Pijat Tradisional Relaksasi',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Danial Fayyad',
        'phone' => '082233445566',
        'gender' => 'pria',
    ]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Semeru No. 10, Sumbersari',
        'benchmark' => 'Pagar Hitam Depan Masjid',
        'latitude' => -8.1776,
        'longitude' => 113.7197,
        'coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'is_primary' => true,
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Budi Terapis',
        'phone' => '081234567899',
        'gender' => 'pria',
        'status' => 'terverifikasi',
    ]);

    $this->profile = PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Mastrip No. 1',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'current_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);
});

test('mitra dapat melihat detail pesanan, peta lokasi pelanggan, dan jarak terapis', function () {
    $order = Order::create([
        'order_code' => 'ORD-MAP-TEST-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Danial Fayyad',
        'recipient_phone' => '082233445566',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Semeru No. 10, Sumbersari',
        'service_coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'scheduled_start_time' => Carbon::now()->addHours(5),
        'medical_notes' => 'Cedera otot pundak kanan',
        'status' => OrderStatus::Dijadwalkan,
    ]);

    $response = $this->actingAs($this->mitra)->get(route('mitra.orders.show', $order));

    $response->assertOk();
    $response->assertSee('ORD-MAP-TEST-01');
    $response->assertSee('Danial Fayyad');
    $response->assertSee('Jl. Semeru No. 10, Sumbersari');
    $response->assertSee('Pagar Hitam Depan Masjid');
    $response->assertSee('Cedera otot pundak kanan');
    $response->assertSee('mitra-order-map');
    $response->assertSee('Jarak ke Pelanggan');
    $response->assertSee('Buka Rute Navigasi di Google Maps');
    $response->assertSee('Rp 90.000'); // 90% net earnings
});

test('mitra dapat memperbarui status pesanan secara bertahap menuju lokasi hingga selesai', function () {
    $order = Order::create([
        'order_code' => 'ORD-STATUS-FLOW-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Danial Fayyad',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Semeru No. 10',
        'service_coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'status' => OrderStatus::Dijadwalkan,
    ]);

    // 1. Dijadwalkan -> Menuju Lokasi
    $response1 = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), ['status' => 'menuju_lokasi']);
    $response1->assertRedirect(route('mitra.orders.show', $order));
    expect($order->fresh()->status)->toBe(OrderStatus::MenujuLokasi);

    // 2. Menuju Lokasi -> Tiba (dengan validasi koordinat / force arrived)
    $response2 = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), [
            'status' => 'tiba',
            'latitude' => -8.1776,
            'longitude' => 113.7197,
        ]);
    $response2->assertRedirect(route('mitra.orders.show', $order));
    expect($order->fresh()->status)->toBe(OrderStatus::Tiba);

    // 3. Tiba -> Sedang Berlangsung (mencatat service_started_at)
    $response3 = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), ['status' => 'sedang_berlangsung']);
    $response3->assertRedirect(route('mitra.orders.show', $order));
    $orderFresh = $order->fresh();
    expect($orderFresh->status)->toBe(OrderStatus::SedangBerlangsung);
    expect($orderFresh->service_started_at)->not->toBeNull();

    // 4. Sedang Berlangsung -> Selesai (mencatat service_completed_at)
    $response4 = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), ['status' => 'selesai']);
    $response4->assertRedirect(route('mitra.orders.show', $order));
    $orderDone = $order->fresh();
    expect($orderDone->status)->toBe(OrderStatus::Selesai);
    expect($orderDone->service_completed_at)->not->toBeNull();
});

test('mitra lain tidak dapat melihat detail pesanan mitra berbeda yang bukan di bursa', function () {
    $otherMitra = User::factory()->mitra()->create([
        'status' => 'terverifikasi',
    ]);
    PartnerProfile::create([
        'user_id' => $otherMitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Jawa No. 2',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);

    $order = Order::create([
        'order_code' => 'ORD-PRIVATE-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id, // Ditugaskan ke mitra pertama
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Danial',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Semeru No. 10',
        'service_coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'status' => OrderStatus::Dijadwalkan,
    ]);

    $response = $this->actingAs($otherMitra)->get(route('mitra.orders.show', $order));
    $response->assertForbidden();
});

test('mitra terhalang validasi radius 50m saat tiba jika jarak jauh tanpa konfirmasi manual', function () {
    $order = Order::create([
        'order_code' => 'ORD-GEOFENCE-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Danial',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Semeru No. 10',
        'service_coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'status' => OrderStatus::MenujuLokasi,
    ]);

    // Posisi terapis jauh (sekitar 2 km) tanpa force_arrived
    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), [
            'status' => 'tiba',
            'latitude' => -8.1900,
            'longitude' => 113.7300,
        ]);

    $response->assertSessionHas('location_warning');
    expect($order->fresh()->status)->toBe(OrderStatus::MenujuLokasi);

    // Dengan force_arrived (jalur cadangan/toleransi GPS)
    $responseFallback = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), [
            'status' => 'tiba',
            'latitude' => -8.1900,
            'longitude' => 113.7300,
            'force_arrived' => 1,
        ]);

    $responseFallback->assertSessionHas('status');
    expect($order->fresh()->status)->toBe(OrderStatus::Tiba);
});

test('pembaruan status bersifat idempoten terhadap pengiriman berulang saat sinyal buruk', function () {
    $order = Order::create([
        'order_code' => 'ORD-IDEMPOTENT-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Danial',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Semeru No. 10',
        'service_coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'status' => OrderStatus::MenujuLokasi,
    ]);

    // Request pertama
    $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), [
            'status' => 'tiba',
            'force_arrived' => 1,
        ]);
    expect($order->fresh()->status)->toBe(OrderStatus::Tiba);

    // Request kedua (duplikat karena retry sinyal buruk)
    $retry = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), [
            'status' => 'tiba',
            'force_arrived' => 1,
        ]);
    $retry->assertRedirect(route('mitra.orders.show', $order));
    expect($order->fresh()->status)->toBe(OrderStatus::Tiba);
});
