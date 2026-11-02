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
        'name' => 'Pelanggan Jember',
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
        'name' => 'Mitra Terapis Pro',
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
            'mitra_id' => $this->mitra->id,
            'region_id' => $this->region->id,
            'service_id' => $this->service->id,
            'service_package_id' => $this->package->id,
            'service_name' => $this->service->name,
            'duration_minutes' => 60,
            'total_price' => 100000,
            'recipient_name' => 'Budi Pelanggan',
            'recipient_gender' => 'pria',
            'order_type' => OrderType::Instan,
            'status' => OrderStatus::Tiba,
            'service_address_text' => $this->address->address_text,
            'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        ], $overrides));
    };
});

test('mitra regular cancellation incurs 15 penalty points and returns order to bursa (MIT-12)', function () {
    $order = ($this->createOrder)([
        'order_type' => OrderType::Terjadwal,
        'status' => OrderStatus::Dijadwalkan,
        'scheduled_start_time' => Carbon::now()->addHours(5),
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.cancel', $order), [
            'cancellation_type' => 'regular',
            'reason' => 'Kendaraan mogok mendadak dan tidak dapat melanjutkan',
        ]);

    $response->assertRedirect(route('mitra.orders.scheduled'));
    $order->refresh();

    // Order returns to bursa, mitra_id null
    expect($order->status)->toBe(OrderStatus::MencariMitra)
        ->and($order->mitra_id)->toBeNull();

    // Mitra gets 15 demerit points
    expect($this->mitra->fresh()->penalty_points)->toBe(15);
});

test('mitra special cancellation customer not present at Tiba gives 50% compensation and 0 penalty to mitra (MIT-11)', function () {
    $order = ($this->createOrder)([
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::Tiba,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.cancel', $order), [
            'cancellation_type' => 'customer_not_present',
            'reason' => 'Sudah menunggu 20 menit di depan rumah, telepon tidak diangkat',
        ]);

    $response->assertRedirect(route('mitra.orders.show', $order));
    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(50000.0) // refund 50%
        ->and((float) $this->mitraWallet->fresh()->balance)->toEqual(50000.0);   // kompensasi mitra 50%

    // Mitra has ZERO demerit points
    expect($this->mitra->fresh()->penalty_points)->toBe(0);

    // Customer gets 15 demerit points for no-show
    expect($this->customer->fresh()->penalty_points)->toBe(15);
});

test('mitra special cancellation gender mismatch at Tiba gives 50% compensation and 0 penalty to mitra (MIT-11)', function () {
    $order = ($this->createOrder)([
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::Tiba,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.cancel', $order), [
            'cancellation_type' => 'gender_mismatch',
            'reason' => 'Penerima layanan di lokasi ternyata pria padahal preferensi wanita',
        ]);

    $response->assertRedirect(route('mitra.orders.show', $order));
    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->mitraWallet->fresh()->balance)->toEqual(50000.0);

    expect($this->mitra->fresh()->penalty_points)->toBe(0);
});

test('mitra special cancellation medical condition gives 100% refund and 0 penalty to mitra (DEC-03, MIT-11)', function () {
    $order = ($this->createOrder)([
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::Tiba,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.cancel', $order), [
            'cancellation_type' => 'medical_condition',
            'reason' => 'Pelanggan sedang demam 39C dan luka bakar terbuka di bagian punggung',
        ]);

    $response->assertRedirect(route('mitra.orders.show', $order));
    $order->refresh();

    // 100% refund to customer, mitra free from penalty (DEC-03)
    expect($order->status)->toBe(OrderStatus::Dibatalkan)
        ->and((float) $this->customerWallet->fresh()->balance)->toEqual(100000.0);

    expect($this->mitra->fresh()->penalty_points)->toBe(0);
});
