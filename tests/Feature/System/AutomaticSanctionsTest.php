<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\UserStatus;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\PenaltyLedger;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PenaltyService;
use Carbon\Carbon;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
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
        'name' => 'Customer Pelanggar',
        'penalty_points' => 0,
    ]);

    $this->customerWallet = Wallet::create([
        'user_id' => $this->customer->id,
        'balance' => 100000,
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Terapis Pelanggar',
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 0,
    ]);

    $this->mitraWallet = Wallet::create([
        'user_id' => $this->mitra->id,
        'balance' => 0,
    ]);

    CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Mastrip No. 15',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
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
            'recipient_name' => 'Penerima Uji',
            'recipient_gender' => 'pria',
            'order_type' => OrderType::Terjadwal,
            'status' => OrderStatus::Dijadwalkan,
            'scheduled_start_time' => Carbon::now()->addHours(4),
            'service_address_text' => 'Jl. Mastrip No. 15',
            'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        ], $overrides));
    };
});

test('partner unilateral cancel applies 15 penalty points automatically (SYS-04, MIT-12)', function () {
    $order = ($this->createOrder)();

    $response = $this->actingAs($this->mitra)->post(route('mitra.orders.cancel', $order), [
        'cancellation_type' => 'regular',
        'reason' => 'Saya berhalangan mendadak karena urusan darurat keluarga.',
    ]);

    $response->assertRedirect(route('mitra.orders.scheduled'));
    $response->assertSessionHas('warning');

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(15);

    $ledger = PenaltyLedger::where('user_id', $this->mitra->id)->first();
    expect($ledger)->not->toBeNull()
        ->and($ledger->points)->toBe(15)
        ->and($ledger->type)->toBe('violation');
});

test('customer no-show cancellation by partner applies 15 points to customer automatically (SYS-04, MIT-11)', function () {
    $order = ($this->createOrder)([
        'status' => OrderStatus::Tiba,
    ]);

    $response = $this->actingAs($this->mitra)->post(route('mitra.orders.cancel', $order), [
        'cancellation_type' => 'customer_not_present',
        'reason' => 'Sudah menunggu 15 menit di lokasi, pelanggan tidak keluar dan telepon tidak diangkat.',
    ]);

    $response->assertRedirect(route('mitra.orders.show', $order));
    $response->assertSessionHas('status');

    $this->customer->refresh();
    expect($this->customer->penalty_points)->toBe(15);

    $ledger = PenaltyLedger::where('user_id', $this->customer->id)->first();
    expect($ledger)->not->toBeNull()
        ->and($ledger->points)->toBe(15)
        ->and($ledger->type)->toBe('violation');

    // 50% compensation to partner wallet
    $this->mitraWallet->refresh();
    expect((int) $this->mitraWallet->balance)->toBe(50000);
});

test('automatic sanctions trigger 3-day suspend at 60 points, 7-day suspend at 100 points, and ban at 150 points (SYS-04)', function () {
    $penaltyService = app(PenaltyService::class);

    // Cross 60 points -> 3-day suspend
    $penaltyService->applyViolation($this->customer, 60, 'Akumulasi pelanggaran mencapai 60 poin');
    $this->customer->refresh();
    expect($this->customer->penalty_points)->toBe(60)
        ->and($this->customer->status)->toBe(UserStatus::Ditangguhkan)
        ->and($this->customer->isSuspended())->toBeTrue();

    // Cross 100 points -> 7-day suspend
    $penaltyService->applyViolation($this->customer, 40, 'Pelanggaran berulang mencapai 100 poin');
    $this->customer->refresh();
    expect($this->customer->penalty_points)->toBe(100)
        ->and($this->customer->status)->toBe(UserStatus::Ditangguhkan)
        ->and($this->customer->isSuspended())->toBeTrue();

    // Cross 150 points -> permanent ban
    $penaltyService->applyViolation($this->customer, 50, 'Mencapai batas 150 poin maksimal');
    $this->customer->refresh();
    expect($this->customer->penalty_points)->toBe(150)
        ->and($this->customer->status)->toBe(UserStatus::Diblokir);
});

test('completing 5-star rating redeems 1 penalty point down to zero (SYS-04)', function () {
    $penaltyService = app(PenaltyService::class);
    $order = ($this->createOrder)();

    $this->mitra->update(['penalty_points' => 2]);

    $penaltyService->redeemForFiveStar($this->mitra, $order);
    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(1);

    $penaltyService->redeemForFiveStar($this->mitra, $order);
    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(0);

    // Further 5-star does not go negative
    $penaltyService->redeemForFiveStar($this->mitra, $order);
    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(0);
});
