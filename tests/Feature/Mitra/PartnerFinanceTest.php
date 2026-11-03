<?php

use App\Models\BankAccount;
use App\Models\PartnerProfile;
use App\Models\Region;
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

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Terapis Santoso',
        'status' => 'terverifikasi',
    ]);

    $this->partnerProfile = PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Jawa No. 10',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp/test.jpg',
        'selfie_path' => 'selfie/test.jpg',
        'is_active' => true,
    ]);

    $this->wallet = Wallet::create([
        'user_id' => $this->mitra->id,
        'balance' => 300000.00,
        'currency' => 'IDR',
    ]);

    $this->bankAccount = BankAccount::create([
        'user_id' => $this->mitra->id,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Terapis Santoso',
        'is_verified' => true,
        'verified_at' => now(),
    ]);
});

test('partner can view finance page with balance and filtered history (MIT-15)', function () {
    // Add past transaction
    WalletTransaction::create([
        'wallet_id' => $this->wallet->id,
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'type' => 'credit',
        'amount' => 160000,
        'balance_before' => 140000,
        'balance_after' => 300000,
        'reference_type' => 'order_earning',
        'reference_id' => '1',
        'description' => 'Pendapatan layanan pesanan #ORD-001',
    ]);

    $response = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.index', ['range' => '7_days']));

    $response->assertOk()
        ->assertSee('Saldo &amp; Keuangan', false)
        ->assertSee('Rp 300.000')
        ->assertSee('BCA')
        ->assertSee('Pendapatan layanan pesanan #ORD-001');
});

test('partner can view withdrawal form if has verified bank account (MIT-16)', function () {
    $response = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.withdraw'));

    $response->assertOk()
        ->assertSee('Tarik Saldo')
        ->assertSee('BCA')
        ->assertSee('Terapis Santoso');
});

test('partner without verified bank account cannot access withdraw page', function () {
    $this->bankAccount->update(['is_verified' => false]);

    $response = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.withdraw'));

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('error');
});

test('partner can successfully submit withdrawal request and balance is deducted immediately (MIT-16)', function () {
    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 100000,
            'notes' => 'Penarikan mingguan',
        ]);

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('status');

    // Balance deducted from 300.000 to 200.000
    $this->wallet->refresh();
    expect((float) $this->wallet->balance)->toBe(200000.00);

    // Withdrawal request recorded
    $wd = WithdrawalRequest::where('user_id', $this->mitra->id)->first();
    expect($wd)->not->toBeNull()
        ->and((float) $wd->amount)->toBe(100000.00)
        ->and($wd->status)->toBe(WithdrawalRequest::STATUS_PENDING)
        ->and($wd->region_id)->toBe($this->region->id);

    // Ledger transaction exists
    $tx = WalletTransaction::where('user_id', $this->mitra->id)
        ->where('reference_type', 'withdrawal_request')
        ->first();

    expect($tx)->not->toBeNull()
        ->and((float) $tx->amount)->toBe(100000.00)
        ->and($tx->type)->toBe('debit');
});

test('partner cannot withdraw amount exceeding wallet balance (MIT-16)', function () {
    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 500000,
        ]);

    $response->assertSessionHasErrors(['amount']);
    expect(WithdrawalRequest::count())->toBe(0);
});

test('partner cannot submit withdrawal below minimum parameter (MIT-16)', function () {
    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 20000, // min is 50.000
        ]);

    $response->assertSessionHasErrors(['amount']);
    expect(WithdrawalRequest::count())->toBe(0);
});

test('partner cannot submit concurrent pending withdrawals', function () {
    WithdrawalRequest::create([
        'withdrawal_code' => 'WD-PENDING-1',
        'user_id' => $this->mitra->id,
        'bank_account_id' => $this->bankAccount->id,
        'region_id' => $this->region->id,
        'amount' => 100000,
        'fee' => 0,
        'net_amount' => 100000,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Terapis Santoso',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 100000,
        ]);

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('error');
});
