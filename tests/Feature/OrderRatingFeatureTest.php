<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderRating;
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
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Budi Terapis',
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
        'order_code' => 'ORD-RATING-01',
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
        'status' => OrderStatus::Selesai,
    ]);
});

test('pelanggan dapat memberi rating bintang 1-5 kepada mitra setelah pesanan selesai', function () {
    $res = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.rating', $this->order), [
            'score' => 5,
        ]);

    $res->assertOk();
    $res->assertJsonFragment(['success' => true, 'score' => 5]);

    expect(OrderRating::count())->toBe(1);
    $rating = OrderRating::first();
    expect($rating->order_id)->toBe($this->order->id);
    expect($rating->from_user_id)->toBe($this->customer->id);
    expect($rating->score)->toBe(5);
    expect($rating->rating_type)->toBe('customer_to_mitra');
});

test('pelanggan memberi rating 3 ke bawah memunculkan flag prompt complaint', function () {
    $res = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.rating', $this->order), [
            'score' => 2,
        ]);

    $res->assertOk();
    $res->assertJsonFragment(['prompt_complaint' => true]);
});

test('mitra dapat memberi rating bintang 1-5 kepada pelanggan setelah pesanan selesai', function () {
    $res = $this->actingAs($this->mitra)
        ->postJson(route('mitra.orders.rating', $this->order), [
            'score' => 5,
        ]);

    $res->assertOk();
    $res->assertJsonFragment(['success' => true, 'score' => 5]);

    $rating = OrderRating::where('from_user_id', $this->mitra->id)->first();
    expect($rating)->not->toBeNull();
    expect($rating->rating_type)->toBe('mitra_to_customer');
});

test('rating ditolak jika status pesanan belum selesai', function () {
    $this->order->update(['status' => OrderStatus::SedangBerlangsung]);

    $res = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.rating', $this->order), [
            'score' => 5,
        ]);

    $res->assertStatus(422);
});

