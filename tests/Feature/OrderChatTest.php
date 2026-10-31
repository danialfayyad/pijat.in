<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember Kota',
        'status' => 'aktif',
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
        'name' => 'Danial Pelanggan',
        'phone' => '082211223344',
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Budi Terapis',
        'phone' => '081233445566',
        'status' => 'terverifikasi',
    ]);

    PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Mastrip No. 1',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);

    $this->order = Order::create([
        'order_code' => 'ORD-CHAT-TEST-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
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
});

test('pelanggan dan mitra dapat berkirim pesan teks selama pesanan aktif', function () {
    // Mitra mengirim pesan
    $mitraRes = $this->actingAs($this->mitra)
        ->postJson(route('mitra.orders.chat.send', $this->order), [
            'message' => 'Halo Pak Danial, saya sudah dekat pertigaan Semeru.',
        ]);

    $mitraRes->assertOk();
    $mitraRes->assertJsonFragment(['success' => true]);

    expect(OrderMessage::count())->toBe(1);
    expect(OrderMessage::first()->message)->toBe('Halo Pak Danial, saya sudah dekat pertigaan Semeru.');

    // Pelanggan membalas pesan
    $custRes = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.chat.send', $this->order), [
            'message' => 'Baik mas, pagar hitam depan masjid ya.',
        ]);

    $custRes->assertOk();
    expect(OrderMessage::count())->toBe(2);

    // Ambil daftar pesan sebagai pelanggan
    $listRes = $this->actingAs($this->customer)
        ->getJson(route('pelanggan.orders.chat.messages', $this->order));

    $listRes->assertOk();
    $listRes->assertJsonCount(2, 'messages');
    $listRes->assertJsonPath('messages.0.sender_name', 'Mitra Terapis');
    $listRes->assertJsonPath('messages.1.sender_name', 'Saya');
});

test('fitur chat dinonaktifkan otomatis setelah pesanan selesai atau dibatalkan', function () {
    $this->order->update(['status' => OrderStatus::Selesai]);

    $res = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.chat.send', $this->order), [
            'message' => 'Pesan setelah selesai.',
        ]);

    $res->assertStatus(422);
    $res->assertJsonFragment(['error' => 'Sesi chat telah dinonaktifkan karena status pesanan telah Selesai.']);
});

test('pengguna lain yang tidak terafiliasi dengan pesanan dilarang mengakses chat', function () {
    $unrelated = User::factory()->customer()->create();

    $res = $this->actingAs($unrelated)
        ->getJson(route('pelanggan.orders.chat.messages', $this->order));

    $res->assertForbidden();
});
