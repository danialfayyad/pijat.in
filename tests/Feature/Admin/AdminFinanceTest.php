<?php

use App\Models\Region;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->regionA = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
    ]);

    $this->regionB = Region::factory()->create([
        'name' => 'Wilayah Surabaya',
        'status' => 'aktif',
    ]);

    $this->adminA = User::factory()->admin()->create();
    $this->adminA->assignedRegions()->attach($this->regionA->id);

    $this->customerA = User::factory()->customer()->create([
        'name' => 'Customer Jember',
    ]);

    $this->customerWallet = Wallet::create([
        'user_id' => $this->customerA->id,
        'balance' => 50000.00,
    ]);

    $this->withdrawalA = WithdrawalRequest::create([
        'withdrawal_code' => 'WD-JEMBER-001',
        'user_id' => $this->customerA->id,
        'region_id' => $this->regionA->id,
        'amount' => 100000.00,
        'fee' => 0.00,
        'net_amount' => 100000.00,
        'bank_name' => 'BCA',
        'account_number' => '1122334455',
        'account_holder_name' => 'Customer Jember',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);
});

test('admin can view financial dashboard and withdrawal queue in assigned region (ADM-11)', function () {
    $response = $this->actingAs($this->adminA)
        ->get(route('admin.finance.index'));

    $response->assertOk()
        ->assertSee('Keuangan Wilayah &amp; Penarikan Dana', false)
        ->assertSee('WD-JEMBER-001')
        ->assertSee('Customer Jember')
        ->assertSee('BCA');
});

test('admin cannot process withdrawal outside their assigned region (ADM-11)', function () {
    $withdrawalB = WithdrawalRequest::create([
        'withdrawal_code' => 'WD-SBY-001',
        'user_id' => $this->customerA->id,
        'region_id' => $this->regionB->id,
        'amount' => 100000.00,
        'fee' => 0.00,
        'net_amount' => 100000.00,
        'bank_name' => 'Mandiri',
        'account_number' => '9988776655',
        'account_holder_name' => 'Customer Jember',
        'status' => WithdrawalRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.finance.withdrawals.approve', $withdrawalB), [
            'transfer_reference' => 'TRF-TEST-123',
        ]);

    $response->assertForbidden();
});

test('admin can approve withdrawal request and user is notified (ADM-11)', function () {
    Storage::fake('public');
    $proof = UploadedFile::fake()->image('proof.jpg');

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.finance.withdrawals.approve', $this->withdrawalA), [
            'transfer_reference' => 'TRF-BCA-987654',
            'proof_file' => $proof,
        ]);

    $response->assertRedirect();

    $this->withdrawalA->refresh();
    expect($this->withdrawalA->status)->toBe(WithdrawalRequest::STATUS_APPROVED)
        ->and($this->withdrawalA->transfer_reference)->toBe('TRF-BCA-987654')
        ->and($this->withdrawalA->proof_path)->not->toBeNull()
        ->and($this->withdrawalA->processed_by)->toBe($this->adminA->id)
        ->and($this->withdrawalA->processed_at)->not->toBeNull();

    // User receives notification
    $notification = SystemNotification::where('user_id', $this->customerA->id)
        ->where('event_code', 'withdrawal_approved')
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification->title)->toBe('Penarikan Dana Berhasil');
});

test('admin can reject withdrawal request and funds are refunded to user wallet (ADM-11)', function () {
    $initialBalance = (float) $this->customerWallet->balance; // 50.000

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.finance.withdrawals.reject', $this->withdrawalA), [
            'rejection_reason' => 'Nomor rekening tidak valid dan nama tidak sesuai',
        ]);

    $response->assertRedirect();

    $this->withdrawalA->refresh();
    expect($this->withdrawalA->status)->toBe(WithdrawalRequest::STATUS_REJECTED)
        ->and($this->withdrawalA->rejection_reason)->toBe('Nomor rekening tidak valid dan nama tidak sesuai')
        ->and($this->withdrawalA->processed_by)->toBe($this->adminA->id);

    // Balance refunded back: 50.000 + 100.000 = 150.000
    $this->customerWallet->refresh();
    expect((float) $this->customerWallet->balance)->toBe($initialBalance + 100000.00);

    // Refund ledger recorded
    $tx = WalletTransaction::where('user_id', $this->customerA->id)
        ->where('reference_type', 'withdrawal_refund')
        ->where('reference_id', (string) $this->withdrawalA->id)
        ->first();

    expect($tx)->not->toBeNull()
        ->and((float) $tx->amount)->toBe(100000.00)
        ->and($tx->type)->toBe('credit');

    // User notified
    $notification = SystemNotification::where('user_id', $this->customerA->id)
        ->where('event_code', 'withdrawal_rejected')
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification->title)->toBe('Permohonan Penarikan Ditolak');
});

test('admin can export regional cash ledger to CSV (ADM-11)', function () {
    WalletTransaction::create([
        'wallet_id' => $this->customerWallet->id,
        'user_id' => $this->customerA->id,
        'region_id' => $this->regionA->id,
        'type' => 'credit',
        'amount' => 50000.00,
        'balance_before' => 0.00,
        'balance_after' => 50000.00,
        'reference_type' => 'topup',
        'description' => 'Top-up saldo dompet',
    ]);

    $response = $this->actingAs($this->adminA)
        ->get(route('admin.finance.export.csv'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
