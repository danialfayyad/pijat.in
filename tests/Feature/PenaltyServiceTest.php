<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Region;
use App\Models\User;
use App\Services\PenaltyService;

beforeEach(function () {
    $this->penaltyService = app(PenaltyService::class);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Uji Sanksi',
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 0,
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin Penegak',
        'role' => Role::Admin,
    ]);

    $this->region = Region::factory()->create();
    $this->order = Order::factory()->create([
        'region_id' => $this->region->id,
        'mitra_id' => $this->mitra->id,
    ]);
});

test('applying violation increments penalty points and records ledger', function () {
    $complaint = Complaint::factory()->create([
        'region_id' => $this->region->id,
        'order_id' => $this->order->id,
        'target_id' => $this->mitra->id,
    ]);

    $ledger = $this->penaltyService->applyViolation(
        $this->mitra,
        25,
        'Terbukti meminta tarif di luar aplikasi',
        $complaint,
        $this->admin
    );

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(25)
        ->and($ledger->balance_before)->toBe(0)
        ->and($ledger->balance_after)->toBe(25)
        ->and($ledger->points)->toBe(25)
        ->and($this->mitra->status)->toBe(UserStatus::Terverifikasi)
        ->and($this->mitra->isSuspended())->toBeFalse();
});

test('reaching 60 points triggers 3-day suspension automatically', function () {
    $this->penaltyService->applyViolation(
        $this->mitra,
        65,
        'Akumulasi pelanggaran mencapai batas 60 poin',
        null,
        $this->admin
    );

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(65)
        ->and($this->mitra->status)->toBe(UserStatus::Ditangguhkan)
        ->and($this->mitra->isSuspended())->toBeTrue()
        ->and($this->mitra->suspended_until->isFuture())->toBeTrue();
});

test('reaching 150 points permanently bans the user', function () {
    $this->penaltyService->applyViolation(
        $this->mitra,
        155,
        'Akumulasi poin melebihi ambang batas maksimal 150',
        null,
        $this->admin
    );

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(155)
        ->and($this->mitra->status)->toBe(UserStatus::Diblokir);
});

test('redeem for five star decreases 1 penalty point down to zero floor', function () {
    $this->mitra->update(['penalty_points' => 2]);

    $ledger1 = $this->penaltyService->redeemForFiveStar($this->mitra, $this->order);
    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(1)
        ->and($ledger1->points)->toBe(-1);

    $ledger2 = $this->penaltyService->redeemForFiveStar($this->mitra, $this->order);
    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(0);

    // Further redemption returns null and does not go negative
    $ledger3 = $this->penaltyService->redeemForFiveStar($this->mitra, $this->order);
    expect($ledger3)->toBeNull()
        ->and($this->mitra->penalty_points)->toBe(0);
});

test('appeal reversal restores points and recovers suspended status', function () {
    $this->mitra->update([
        'penalty_points' => 70,
        'status' => UserStatus::Ditangguhkan,
        'suspended_until' => now()->addDays(3),
    ]);

    $this->penaltyService->reverseAppeal(
        $this->mitra,
        50,
        'Banding aduan diterima oleh admin wilayah',
        null,
        $this->admin
    );

    $this->mitra->refresh();
    expect($this->mitra->penalty_points)->toBe(20)
        ->and($this->mitra->status)->toBe(UserStatus::Terverifikasi)
        ->and($this->mitra->isSuspended())->toBeFalse();
});
