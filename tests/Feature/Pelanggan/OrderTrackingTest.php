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
        'name' => 'Bambang Pelanggan',
        'gender' => 'pria',
    ]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kaliurang No. 10',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Supriadi Terapis',
        'phone' => '081234567899',
        'gender' => 'pria',
        'nik' => '3509123456780001',
    ]);

    $this->profile = PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Mastrip No. 1',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);
});

test('CUS-10: saat status mencari mitra pada pesanan instan, hitung mundur batas waktu pencarian tampil', function () {
    $order = Order::create([
        'order_code' => 'ORD-TRACK-SEARCH',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Bambang',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Kaliurang No. 10',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.orders.show', $order));

    $response->assertOk();
    $response->assertSee('ORD-TRACK-SEARCH');
    $response->assertSee('Mencari Terapis Instan');
    $response->assertSee('Batas Waktu Pencarian');
});

test('CUS-10: saat status mencari mitra pada pesanan terjadwal, bursa jadwal mitra tampil', function () {
    $order = Order::create([
        'order_code' => 'ORD-TRACK-SCHED-SEARCH',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Bambang',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Kaliurang No. 10',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => Carbon::now()->addHours(5),
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.orders.show', $order));

    $response->assertOk();
    $response->assertSee('ORD-TRACK-SCHED-SEARCH');
    $response->assertSee('Bursa Jadwal Mitra');
    $response->assertSee('Menunggu Konfirmasi Terapis');
});

test('CUS-10: saat status dijadwalkan, profil mitra tampil tanpa data sensitif NIK', function () {
    $order = Order::create([
        'order_code' => 'ORD-TRACK-SCHEDULED',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'customer_address_id' => $this->address->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Bambang',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Kaliurang No. 10',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => Carbon::now()->addHours(5),
        'status' => OrderStatus::Dijadwalkan,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.orders.show', $order));

    $response->assertOk();
    $response->assertSee('Terapis Penanggung Jawab');
    $response->assertSee('Supriadi Terapis');
    $response->assertSee('Terapis pria');
    $response->assertSee('Hubungi Terapis');
    // Pastikan data sensitif NIK mitra TIDAK bocor ke customer
    $response->assertDontSee('3509123456780001');
});

test('CUS-10: saat status menuju lokasi, estimasi kedatangan dan tracking GPS tampil', function () {
    $order = Order::create([
        'order_code' => 'ORD-TRACK-OTW',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'customer_address_id' => $this->address->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Bambang',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Kaliurang No. 10',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => Carbon::now()->addHours(1),
        'status' => OrderStatus::MenujuLokasi,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.orders.show', $order));

    $response->assertOk();
    $response->assertSee('Terapis Dalam Perjalanan');
    $response->assertSee('Estimasi Kedatangan');
    $response->assertSee('~15 - 20 Menit');
    $response->assertSee('Posisi diperbarui setiap 30 detik');
});
