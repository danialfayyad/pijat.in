<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'role' => Role::Superadmin,
        'status' => UserStatus::Terverifikasi,
    ]);

    $this->admin = User::factory()->create([
        'role' => Role::Admin,
        'status' => UserStatus::Terverifikasi,
    ]);

    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
        'status' => UserStatus::Terverifikasi,
    ]);
});

test('superadmin can access voucher index and see metrics', function () {
    Voucher::create([
        'code' => 'TEST20',
        'name' => 'Diskon 20 Persen',
        'type' => Voucher::TYPE_PERCENTAGE,
        'amount' => 20,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.vouchers.index'));

    $response->assertOk()
        ->assertSee('Master Voucher & Kupon Diskon')
        ->assertSee('TEST20')
        ->assertSee('Diskon 20 Persen');
});

test('regular admin and customers cannot access superadmin vouchers', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.vouchers.index'))
        ->assertForbidden();

    $this->actingAs($this->customer)
        ->get(route('superadmin.vouchers.index'))
        ->assertForbidden();
});

test('superadmin can create percentage discount voucher', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.vouchers.store'), [
            'code' => 'gajian25',
            'name' => 'Promo Gajian 25%',
            'type' => Voucher::TYPE_PERCENTAGE,
            'amount' => 25,
            'max_discount_amount' => 30000,
            'min_order_amount' => 50000,
            'quota' => 100,
            'per_user_limit' => 2,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('superadmin.vouchers.index'));

    $this->assertDatabaseHas('vouchers', [
        'code' => 'GAJIAN25',
        'name' => 'Promo Gajian 25%',
        'type' => 'percentage',
        'amount' => 25.00,
        'max_discount_amount' => 30000.00,
        'min_order_amount' => 50000.00,
        'quota' => 100,
        'per_user_limit' => 2,
        'is_active' => true,
    ]);
});

test('superadmin can create fixed nominal discount voucher', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.vouchers.store'), [
            'code' => 'POTONG15RB',
            'name' => 'Potongan 15 Ribu',
            'type' => Voucher::TYPE_FIXED,
            'amount' => 15000,
            'min_order_amount' => 60000,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('superadmin.vouchers.index'));

    $this->assertDatabaseHas('vouchers', [
        'code' => 'POTONG15RB',
        'type' => 'fixed',
        'amount' => 15000.00,
    ]);
});

test('percentage discount cannot exceed 100 percent', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.vouchers.store'), [
            'code' => 'LEBAY150',
            'name' => 'Diskon 150%',
            'type' => Voucher::TYPE_PERCENTAGE,
            'amount' => 150,
            'per_user_limit' => 1,
        ]);

    $response->assertSessionHasErrors(['amount']);
});

test('superadmin can update voucher', function () {
    $voucher = Voucher::create([
        'code' => 'UPDATECODE',
        'name' => 'Nama Lama',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 10000,
        'per_user_limit' => 1,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.vouchers.update', $voucher), [
            'code' => 'UPDATECODE',
            'name' => 'Nama Baru Diperbarui',
            'type' => Voucher::TYPE_FIXED,
            'amount' => 12000,
            'per_user_limit' => 2,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('superadmin.vouchers.index'));

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'name' => 'Nama Baru Diperbarui',
        'amount' => 12000.00,
        'per_user_limit' => 2,
    ]);
});

test('superadmin can toggle voucher status', function () {
    $voucher = Voucher::create([
        'code' => 'TOGGLE1',
        'name' => 'Toggle Me',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 5000,
        'per_user_limit' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.vouchers.toggle', $voucher));

    $response->assertStatus(302);
    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'is_active' => false,
    ]);
});

test('superadmin can delete unused voucher', function () {
    $voucher = Voucher::create([
        'code' => 'DELETEUS',
        'name' => 'Hapus Aku',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 5000,
        'per_user_limit' => 1,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('superadmin.vouchers.destroy', $voucher));

    $response->assertRedirect(route('superadmin.vouchers.index'));
    $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
});

test('voucher with existing usages is deactivated instead of hard deleted', function () {
    $voucher = Voucher::create([
        'code' => 'USEDVOUCHER',
        'name' => 'Voucher Sudah Dipakai',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 10000,
        'per_user_limit' => 1,
        'is_active' => true,
        'used_count' => 1,
    ]);

    VoucherUsage::create([
        'voucher_id' => $voucher->id,
        'user_id' => $this->customer->id,
        'discount_amount' => 10000,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('superadmin.vouchers.destroy', $voucher));

    $response->assertStatus(302);
    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'is_active' => false,
    ]);
});

test('voucher calculation and validation logic handles all conditions correctly', function () {
    // 1. Percentage with cap
    $voucherPercent = Voucher::create([
        'code' => 'HEMAT30',
        'name' => 'Diskon 30%',
        'type' => Voucher::TYPE_PERCENTAGE,
        'amount' => 30,
        'max_discount_amount' => 20000,
        'min_order_amount' => 50000,
        'is_active' => true,
        'per_user_limit' => 1,
    ]);

    // Order 40.000 (below min order 50.000)
    $valMin = $voucherPercent->validateFor($this->customer, 40000);
    expect($valMin['valid'])->toBeFalse();

    // Order 100.000 (30% = 30.000, but capped at 20.000)
    $valCapped = $voucherPercent->validateFor($this->customer, 100000);
    expect($valCapped['valid'])->toBeTrue()
        ->and($valCapped['discount_amount'])->toBe(20000.0);

    // 2. Per-user limit reached
    VoucherUsage::create([
        'voucher_id' => $voucherPercent->id,
        'user_id' => $this->customer->id,
        'discount_amount' => 20000,
    ]);

    $valLimit = $voucherPercent->validateFor($this->customer, 100000);
    expect($valLimit['valid'])->toBeFalse()
        ->and($valLimit['message'])->toContain('mencapai batas');

    // 3. Expired voucher
    $expiredVoucher = Voucher::create([
        'code' => 'EXPIRED99',
        'name' => 'Expired',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 10000,
        'is_active' => true,
        'expires_at' => Carbon::now()->subDay(),
    ]);

    $valExpired = $expiredVoucher->validateFor($this->customer, 100000);
    expect($valExpired['valid'])->toBeFalse()
        ->and($valExpired['message'])->toContain('kedaluwarsa');
});
