<?php

use App\Models\CustomerAddress;
use App\Models\Region;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    $this->admin = User::factory()->admin()->create();
    $this->admin->assignedRegions()->attach($this->region->id);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Ahmad Pelanggan',
    ]);

    $this->wallet = Wallet::create([
        'user_id' => $this->customer->id,
        'balance' => 250000.00,
        'currency' => 'IDR',
    ]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Mastrip No. 5',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);
});

test('customer can view withdrawal form when primary address exists (CUS-17)', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.wallet.withdraw'));

    $response->assertOk()
        ->assertSee('Tarik Saldo')
        ->assertSee('Saldo Dompet Saat Ini')
        ->assertSee('Ahmad Pelanggan');
});

test('customer cannot access withdrawal form without primary address (Solusi B.2)', function () {
    $this->address->delete();

    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.wallet.withdraw'));

    $response->assertRedirect(route('pelanggan.addresses.index'))
        ->assertSessionHas('error');
});

test('customer can submit withdrawal request and wallet is debited immediately (CUS-17)', function () {
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.withdraw.store'), [
            'bank_name' => 'BCA',
            'account_number' => '0987654321',
            'account_holder_name' => 'Ahmad Pelanggan',
            'amount' => 100000,
            'notes' => 'Tarik sisa saldo',
        ]);

    $response->assertRedirect(route('pelanggan.wallet.index'))
        ->assertSessionHas('status');

    // Balance deducted from 250.000 to 150.000
    $this->wallet->refresh();
    expect((float) $this->wallet->balance)->toBe(150000.00);

    // Withdrawal request exists
    $wd = WithdrawalRequest::where('user_id', $this->customer->id)->first();
    expect($wd)->not->toBeNull()
        ->and((float) $wd->amount)->toBe(100000.00)
        ->and($wd->bank_name)->toBe('BCA')
        ->and($wd->account_holder_name)->toBe('Ahmad Pelanggan')
        ->and($wd->region_id)->toBe($this->region->id)
        ->and($wd->status)->toBe(WithdrawalRequest::STATUS_PENDING);

    // Ledger record
    $tx = WalletTransaction::where('user_id', $this->customer->id)
        ->where('reference_type', 'customer_withdrawal')
        ->first();

    expect($tx)->not->toBeNull()
        ->and((float) $tx->amount)->toBe(100000.00)
        ->and($tx->type)->toBe('debit');

    // Admin notified
    $notification = SystemNotification::where('user_id', $this->admin->id)
        ->where('event_code', 'customer_withdrawal_pending')
        ->first();

    expect($notification)->not->toBeNull();
});

test('customer cannot withdraw amount exceeding wallet balance (CUS-17)', function () {
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.withdraw.store'), [
            'bank_name' => 'BCA',
            'account_number' => '0987654321',
            'account_holder_name' => 'Ahmad Pelanggan',
            'amount' => 500000,
        ]);

    $response->assertSessionHasErrors(['amount']);
    expect(WithdrawalRequest::count())->toBe(0);
});
