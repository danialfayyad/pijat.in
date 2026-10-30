<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\PenaltyLedger;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\OrderRatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create(['status' => 'aktif']);

    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
        'status' => UserStatus::Terverifikasi,
    ]);

    $this->mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 10,
    ]);

    $this->service = Service::factory()->create();
    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 150000,
        'is_active' => true,
    ]);

    $this->order = Order::create([
        'order_code' => 'ORD-20261030-0001',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 150000,
        'commission_percent' => 15,
        'recipient_name' => 'Budi',
        'recipient_gender' => 'pria',
        'recipient_phone' => '081234567890',
        'service_address_text' => 'Jl. Pemuda No. 1',
        'service_coordinate' => ['lat' => -7.25, 'lng' => 112.75],
        'scheduled_start_time' => now(),
        'status' => OrderStatus::Selesai,
    ]);
});

test('5-star rating on completed order automatically redeems 1 penalty point for mitra', function () {
    $service = app(OrderRatingService::class);

    $result = $service->submitRating($this->order, $this->customer, 5);

    expect($result['rating'])->toBeInstanceOf(OrderRating::class)
        ->and($result['rating']->score)->toBe(5)
        ->and($result['redemptionLedger'])->toBeInstanceOf(PenaltyLedger::class)
        ->and($result['redemptionLedger']->points)->toBe(-1);

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(9);

    $this->assertDatabaseHas('penalty_ledgers', [
        'user_id' => $this->mitra->id,
        'order_id' => $this->order->id,
        'type' => 'merit_redemption',
        'points' => -1,
        'balance_before' => 10,
        'balance_after' => 9,
    ]);
});

test('rating below 5 does not redeem penalty points', function () {
    $service = app(OrderRatingService::class);

    $result = $service->submitRating($this->order, $this->customer, 4);

    expect($result['rating']->score)->toBe(4)
        ->and($result['redemptionLedger'])->toBeNull();

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(10);

    $this->assertDatabaseMissing('penalty_ledgers', [
        'order_id' => $this->order->id,
        'type' => 'merit_redemption',
    ]);
});

test('5-star rating when mitra has 0 points does not deduct into negative', function () {
    $this->mitra->update(['penalty_points' => 0]);

    $service = app(OrderRatingService::class);

    $result = $service->submitRating($this->order, $this->customer, 5);

    expect($result['redemptionLedger'])->toBeNull();

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(0);
});

test('rating on non-completed order throws exception', function () {
    $this->order->update(['status' => OrderStatus::MencariMitra]);

    $service = app(OrderRatingService::class);

    expect(fn () => $service->submitRating($this->order, $this->customer, 5))
        ->toThrow(InvalidArgumentException::class);
});
