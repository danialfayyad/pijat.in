<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderDispatch;
use App\Models\OrderRating;
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

    // Saat belum berangkat (Dijadwalkan), UI full-page tanpa map latar belakang
    $response = $this->actingAs($this->mitra)->get(route('mitra.orders.show', $order));

    $response->assertOk();
    $response->assertSee('ORD-MAP-TEST-01');
    $response->assertSee('Danial Fayyad');
    $response->assertSee('Jl. Semeru No. 10, Sumbersari');
    $response->assertSee('Pagar Hitam Depan Masjid');
    $response->assertSee('Cedera otot pundak kanan');
    $response->assertDontSee('id="mitra-order-map"', false);
    $response->assertDontSee('Rute Pelanggan');
    $response->assertSee('Rincian Pesanan');
    $response->assertSee('Buka Rute Navigasi di Google Maps');
    $response->assertSee('Rp 90.000'); // 90% net earnings

    // Saat mitra berangkat (Menuju Lokasi), Leaflet map dan navigasi rute muncul
    $order->update(['status' => OrderStatus::MenujuLokasi]);
    $responseDeparted = $this->actingAs($this->mitra)->get(route('mitra.orders.show', $order));
    $responseDeparted->assertOk();
    $responseDeparted->assertSee('id="mitra-order-map"', false);
    $responseDeparted->assertSee('Rute Pelanggan');
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

    $viewResponse = $this->actingAs($this->mitra)
        ->withSession(['location_warning' => ['message' => 'Jarak Anda terdeteksi 1800 meter dari lokasi pelanggan.']])
        ->get(route('mitra.orders.show', $order));
    $viewResponse->assertSee('Anda Masih Jauh dari Titik Pelanggan');
    $viewResponse->assertSee('Jarak Anda terdeteksi 1800 meter dari lokasi pelanggan.');

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

test('mitra tidak dapat berangkat lebih cepat dari 45 menit sebelum jadwal pesanan terjadwal', function () {
    $order = Order::create([
        'order_code' => 'ORD-EARLY-DEPART-01',
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
        'scheduled_start_time' => Carbon::now()->addHours(3), // 3 jam lagi (misal jam 15:00 saat sekarang jam 12:00)
        'status' => OrderStatus::Dijadwalkan,
    ]);

    expect($order->canPartnerDepartNow(45))->toBeFalse();

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), ['status' => 'menuju_lokasi']);

    $response->assertSessionHas('error');
    expect($order->fresh()->status)->toBe(OrderStatus::Dijadwalkan);
});

test('mitra dapat berangkat jika berada dalam rentang 45 menit sebelum jadwal pesanan terjadwal', function () {
    $order = Order::create([
        'order_code' => 'ORD-VALID-DEPART-01',
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
        'scheduled_start_time' => Carbon::now()->addMinutes(30), // 30 menit lagi (di dalam window 45 menit)
        'status' => OrderStatus::Dijadwalkan,
    ]);

    expect($order->canPartnerDepartNow(45))->toBeTrue();

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), ['status' => 'menuju_lokasi']);

    $response->assertRedirect(route('mitra.orders.show', $order));
    expect($order->fresh()->status)->toBe(OrderStatus::MenujuLokasi);
});

test('mitra order details mask sensitive customer data when order is completed more than 24 hours ago', function () {
    $order = Order::create([
        'order_code' => 'ORD-ARCHIVE-TEST-MITRA',
        'order_type' => OrderType::Instan,
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
        'recipient_phone' => '082233445566',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Semeru Sangat Rahasia No. 10',
        'service_coordinate' => ['lat' => -8.1776, 'lng' => 113.7197],
        'medical_notes' => 'Riwayat cedera pinggang rahasia',
        'status' => OrderStatus::Selesai,
        'service_completed_at' => Carbon::now()->subHours(26),
    ]);

    expect($order->isHistoryArchived())->toBeTrue();

    $response = $this->actingAs($this->mitra)->get(route('mitra.orders.show', $order));

    $response->assertOk();
    $response->assertSee($order->masked_address);
    $response->assertDontSee('Jl. Semeru Sangat Rahasia No. 10');
    $response->assertSee($order->masked_phone);
    $response->assertDontSee('082233445566');
    $response->assertDontSee('Riwayat cedera pinggang rahasia');
});

test('mitra can see customer rating or new customer badge on order detail', function () {
    $order = Order::factory()->create([
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'service_id' => $this->service->id,
        'region_id' => $this->region->id,
        'service_package_id' => $this->package->id,
        'status' => OrderStatus::MenujuLokasi,
        'order_type' => OrderType::Instan,
        'recipient_name' => 'Danial Fayyad',
    ]);

    // Customer has no rating -> displays "Pelanggan Baru"
    $response = $this->actingAs($this->mitra)->get(route('mitra.orders.show', $order));
    $response->assertOk();
    $response->assertSee('Pelanggan Baru');

    // Add a review for customer from another partner
    $otherMitra = User::factory()->mitra()->create(['gender' => 'pria']);
    OrderRating::create([
        'order_id' => $order->id,
        'from_user_id' => $otherMitra->id,
        'to_user_id' => $this->customer->id,
        'score' => 5,
        'rating_type' => 'mitra_to_customer',
    ]);

    $responseWithRating = $this->actingAs($this->mitra)->get(route('mitra.orders.show', $order));
    $responseWithRating->assertOk();
    $responseWithRating->assertSee('5.0');
});

test('pending offer returns customer rating info for instant orders', function () {
    $order = Order::factory()->create([
        'customer_id' => $this->customer->id,
        'service_id' => $this->service->id,
        'region_id' => $this->region->id,
        'service_package_id' => $this->package->id,
        'status' => OrderStatus::MencariMitra,
        'order_type' => OrderType::Instan,
        'recipient_name' => 'Danial Fayyad',
        'customer_address_id' => $this->address->id,
    ]);

    OrderDispatch::create([
        'order_id' => $order->id,
        'partner_id' => $this->mitra->id,
        'status' => OrderDispatch::STATUS_OFFERED,
        'offered_at' => now(),
        'expires_at' => now()->addSeconds(60),
    ]);

    $res = $this->actingAs($this->mitra)->getJson(route('mitra.orders.pending-offer'));
    $res->assertOk();
    $res->assertJson([
        'has_offer' => true,
        'customer_name' => 'Danial Fayyad',
        'customer_rating' => null,
        'customer_ratings_count' => 0,
        'is_new_customer' => true,
    ]);

    // Give customer a 4-star rating from another mitra
    $otherMitra = User::factory()->mitra()->create(['gender' => 'pria']);
    OrderRating::create([
        'order_id' => $order->id,
        'from_user_id' => $otherMitra->id,
        'to_user_id' => $this->customer->id,
        'score' => 4,
        'rating_type' => 'mitra_to_customer',
    ]);

    $res2 = $this->actingAs($this->mitra)->getJson(route('mitra.orders.pending-offer'));
    $res2->assertOk();
    $res2->assertJson([
        'has_offer' => true,
        'customer_name' => 'Danial Fayyad',
        'customer_rating' => '4.0',
        'customer_ratings_count' => 1,
        'is_new_customer' => false,
    ]);
});
