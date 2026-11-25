<?php

use App\Enums\UserStatus;
use App\Models\BankAccount;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Terapis Testing',
        'status' => 'terverifikasi',
        'region_id' => $this->region->id,
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

    $this->bankAccount = BankAccount::create([
        'user_id' => $this->mitra->id,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Terapis Testing',
        'is_verified' => true,
    ]);

    $this->wallet = Wallet::create([
        'user_id' => $this->mitra->id,
        'balance' => 250000,
        'held_balance' => 160000,
        'currency' => 'IDR',
    ]);
});

test('partner can view wallet page with available and held balances displayed separately (MIT-15)', function () {
    $response = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.index'));

    $response->assertOk()
        ->assertSee('Saldo & Keuangan')
        ->assertSee('Saldo Tersedia')
        ->assertSee('250.000')
        ->assertSee('Saldo Tertahan (24 Jam)')
        ->assertSee('160.000')
        ->assertSee('BCA')
        ->assertSee('1234567890');
});

test('partner can filter transaction history by 7 days and 30 days (MIT-15)', function () {
    // Recent transaction (2 days ago)
    $tx1 = WalletTransaction::create([
        'wallet_id' => $this->wallet->id,
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'type' => 'credit',
        'amount' => 80000,
        'balance_before' => 170000,
        'balance_after' => 250000,
        'reference_type' => 'order_earning',
        'reference_id' => '101',
        'description' => 'Pendapatan pesanan ORD-RECENT',
    ]);
    DB::table('wallet_transactions')->where('id', $tx1->id)->update(['created_at' => now()->subDays(2)]);

    // Older transaction (15 days ago)
    $tx2 = WalletTransaction::create([
        'wallet_id' => $this->wallet->id,
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'type' => 'credit',
        'amount' => 70000,
        'balance_before' => 100000,
        'balance_after' => 170000,
        'reference_type' => 'order_earning',
        'reference_id' => '100',
        'description' => 'Pendapatan pesanan ORD-OLDER',
    ]);
    DB::table('wallet_transactions')->where('id', $tx2->id)->update(['created_at' => now()->subDays(15)]);

    // 7 days filter: only recent appears
    $response7 = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.index', ['range' => '7_days']));

    $response7->assertOk()
        ->assertSee('ORD-RECENT')
        ->assertDontSee('ORD-OLDER');

    // 30 days filter: both appear
    $response30 = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.index', ['range' => '30_days']));

    $response30->assertOk()
        ->assertSee('ORD-RECENT')
        ->assertSee('ORD-OLDER');
});

test('partner can filter transaction history and withdrawal requests by specific month', function () {
    // Transaction in September 2026
    $septTx = WalletTransaction::create([
        'wallet_id' => $this->wallet->id,
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'type' => 'credit',
        'amount' => 50000,
        'balance_before' => 100000,
        'balance_after' => 150000,
        'reference_type' => 'order_earning',
        'reference_id' => '201',
        'description' => 'Pendapatan pesanan ORD-SEPTEMBER',
    ]);
    DB::table('wallet_transactions')->where('id', $septTx->id)->update(['created_at' => Carbon::create(2026, 9, 15, 10, 0, 0)]);

    // Transaction in November 2026
    $novTx = WalletTransaction::create([
        'wallet_id' => $this->wallet->id,
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'type' => 'credit',
        'amount' => 75000,
        'balance_before' => 150000,
        'balance_after' => 225000,
        'reference_type' => 'order_earning',
        'reference_id' => '202',
        'description' => 'Pendapatan pesanan ORD-NOVEMBER',
    ]);
    DB::table('wallet_transactions')->where('id', $novTx->id)->update(['created_at' => Carbon::create(2026, 11, 5, 10, 0, 0)]);

    // Withdrawal request in November 2026
    $novWd = WithdrawalRequest::create([
        'withdrawal_code' => 'WD-NOV-2026',
        'user_id' => $this->mitra->id,
        'bank_account_id' => $this->bankAccount->id,
        'region_id' => $this->region->id,
        'amount' => 100000.00,
        'fee' => 0.00,
        'net_amount' => 100000.00,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Terapis Testing',
        'status' => WithdrawalRequest::STATUS_APPROVED,
    ]);
    DB::table('withdrawal_requests')->where('id', $novWd->id)->update(['created_at' => Carbon::create(2026, 11, 6, 10, 0, 0)]);

    // Filtering by September: must see September, must NOT see November transaction or November withdrawal
    $responseSept = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.index', ['month' => '2026-09']));

    $responseSept->assertOk()
        ->assertSee('ORD-SEPTEMBER')
        ->assertDontSee('ORD-NOVEMBER')
        ->assertDontSee('WD-NOV-2026');

    // Filtering by November: must see November, must NOT see September
    $responseNov = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.index', ['month' => '2026-11']));

    $responseNov->assertOk()
        ->assertSee('ORD-NOVEMBER')
        ->assertSee('WD-NOV-2026')
        ->assertDontSee('ORD-SEPTEMBER');
});

test('partner can view withdrawal form with verified bank account (MIT-16)', function () {
    $response = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.withdraw'));

    $response->assertOk()
        ->assertSee('Tarik Saldo')
        ->assertSee('BCA')
        ->assertSee('1234567890')
        ->assertSee('250.000');
});

test('partner sees pending withdrawal status and cancel button on withdraw page when pending exists', function () {
    $wd = WithdrawalRequest::create([
        'withdrawal_code' => 'WD-PARTNER-PENDING',
        'user_id' => $this->mitra->id,
        'bank_account_id' => $this->bankAccount->id,
        'region_id' => $this->region->id,
        'amount' => 100000.00,
        'fee' => 0.00,
        'net_amount' => 100000.00,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Terapis Testing',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->mitra)
        ->get(route('mitra.finance.withdraw'));

    $response->assertOk()
        ->assertSee('Menunggu Verifikasi Admin')
        ->assertSee('WD-PARTNER-PENDING')
        ->assertSee('Batalkan Pengajuan Penarikan');
});

test('partner cannot withdraw if account is suspended (MIT-16)', function () {
    $this->mitra->update([
        'status' => UserStatus::Ditangguhkan,
        'suspended_until' => now()->addDays(3),
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 100000,
        ]);

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('error');
});

test('partner cannot withdraw amount exceeding available balance (MIT-16)', function () {
    $response = $this->actingAs($this->mitra)
        ->from(route('mitra.finance.withdraw'))
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 500000, // Available is 250.000
        ]);

    $response->assertRedirect(route('mitra.finance.withdraw'))
        ->assertSessionHasErrors(['amount']);
});

test('partner can submit valid withdrawal request and balance is deducted immediately (MIT-16)', function () {
    $admin = User::factory()->admin()->create(['region_id' => $this->region->id]);
    $admin->assignedRegions()->attach($this->region->id);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 100000,
            'notes' => 'Tarik saldo mingguan',
        ]);

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('status');

    $this->wallet->refresh();
    // Available balance deducted from 250.000 to 150.000
    expect((float) $this->wallet->balance)->toBe(150000.00);

    // Withdrawal request recorded
    $wd = WithdrawalRequest::where('user_id', $this->mitra->id)->first();
    expect($wd)->not->toBeNull()
        ->and((float) $wd->amount)->toBe(100000.00)
        ->and($wd->status)->toBe(WithdrawalRequest::STATUS_PENDING)
        ->and($wd->bank_name)->toBe('BCA');

    // Admin receives notification
    $notification = SystemNotification::where('user_id', $admin->id)
        ->where('event_code', 'partner_withdrawal_pending')
        ->first();

    expect($notification)->not->toBeNull();
});

test('partner cannot submit multiple concurrent pending withdrawals (MIT-16)', function () {
    WithdrawalRequest::create([
        'withdrawal_code' => 'WD-TEST-001',
        'user_id' => $this->mitra->id,
        'bank_account_id' => $this->bankAccount->id,
        'region_id' => $this->region->id,
        'amount' => 100000,
        'net_amount' => 100000,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Terapis Testing',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.store'), [
            'amount' => 50000,
        ]);

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('error');
});

test('partner can cancel their pending withdrawal and balance is refunded (MIT-16)', function () {
    // Partner wallet starts at 250k, deduct 100k for pending withdrawal
    $this->wallet->update(['balance' => 150000.00]);

    $wd = WithdrawalRequest::create([
        'withdrawal_code' => WithdrawalRequest::generateCode(),
        'user_id' => $this->mitra->id,
        'bank_account_id' => $this->bankAccount->id,
        'region_id' => $this->region->id,
        'amount' => 100000.00,
        'fee' => 0.00,
        'net_amount' => 100000.00,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Terapis Testing',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.cancel', $wd));

    $response->assertRedirect(route('mitra.finance.index'))
        ->assertSessionHas('status');

    // Balance refunded to 250.000
    $this->wallet->refresh();
    expect((float) $this->wallet->balance)->toBe(250000.00);

    // Status updated to CANCELLED
    $wd->refresh();
    expect($wd->isCancelled())->toBeTrue();

    // Refund ledger entry created
    $refundTx = WalletTransaction::where('user_id', $this->mitra->id)
        ->where('reference_type', 'withdrawal_refund')
        ->where('reference_id', (string) $wd->id)
        ->first();

    expect($refundTx)->not->toBeNull()
        ->and((float) $refundTx->amount)->toBe(100000.00)
        ->and($refundTx->type)->toBe('credit');
});

test('partner cannot cancel another users withdrawal request', function () {
    $otherMitra = User::factory()->mitra()->create();

    $wd = WithdrawalRequest::create([
        'withdrawal_code' => WithdrawalRequest::generateCode(),
        'user_id' => $otherMitra->id,
        'region_id' => $this->region->id,
        'amount' => 50000.00,
        'fee' => 0.00,
        'net_amount' => 50000.00,
        'bank_name' => 'BRI',
        'account_number' => '9988776655',
        'account_holder_name' => 'Other Mitra',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.finance.withdraw.cancel', $wd));

    $response->assertForbidden();

    $wd->refresh();
    expect($wd->isPending())->toBeTrue();
});
