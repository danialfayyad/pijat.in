<?php

use App\Enums\Role;
use App\Models\TopupInvoice;
use App\Models\User;
use App\Services\CustomerAddressService;
use App\Services\RegionService;
use App\Services\TopupService;
use App\Services\WalletService;

beforeEach(function () {
    $this->regionService = app(RegionService::class);

    // Create and activate Bandung Region
    $this->bandung = $this->regionService->createRegion(
        'Bandung Kota',
        [[107.55, -6.85], [107.70, -6.85], [107.70, -6.98], [107.55, -6.98], [107.55, -6.85]]
    );
    $admin = User::factory()->create(['role' => Role::Admin]);
    $this->regionService->assignAdmin($admin, $this->bandung);
    $this->regionService->activateRegion($this->bandung);

    // Create Customer
    $this->customer = User::factory()->create(['role' => Role::Pelanggan]);
});

test('customer can view wallet page and sees 0 initial balance and empty transactions', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.wallet.index'));

    $response->assertOk()
        ->assertSee('Saldo Dompet Anda')
        ->assertSee('Rp 0')
        ->assertSee('Belum Ada Riwayat Transaksi');
});

test('customer cannot create topup invoice without primary address (DEC-02)', function () {
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.store'), [
            'amount' => 50000,
            'payment_method' => 'QRIS',
        ]);

    $response->assertSessionHasErrors(['amount']);
    expect(TopupInvoice::count())->toBe(0);
});

test('customer with primary address can create topup invoice with 15-minute expiration (CUS-16)', function () {
    // Add primary address
    app(CustomerAddressService::class)->createAddress($this->customer, [
        'label' => 'Rumah',
        'address_text' => 'Jl. Asia Afrika No. 10',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.store'), [
            'amount' => 100000,
            'payment_method' => 'QRIS',
        ]);

    $invoice = TopupInvoice::where('user_id', $this->customer->id)->first();
    expect($invoice)->not->toBeNull()
        ->and((float) $invoice->amount)->toBe(100000.0)
        ->and($invoice->status)->toBe('PENDING')
        ->and($invoice->region_id)->toBe($this->bandung->id)
        ->and($invoice->expires_at->gt(now()->addMinutes(14)))->toBeTrue();

    $response->assertRedirect(route('pelanggan.wallet.invoice', $invoice->invoice_number));
});

test('customer can simulate paying invoice and wallet balance is credited with ledger transaction', function () {
    app(CustomerAddressService::class)->createAddress($this->customer, [
        'label' => 'Rumah',
        'address_text' => 'Jl. Asia Afrika No. 10',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    $topupService = app(TopupService::class);
    $invoice = $topupService->createInvoice($this->customer, 75000, 'QRIS');

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.wallet.simulate-pay', $invoice->invoice_number));

    $response->assertRedirect(route('pelanggan.wallet.index'))
        ->assertSessionHas('status');

    $invoice->refresh();
    expect($invoice->status)->toBe('PAID')
        ->and($invoice->paid_at)->not->toBeNull();

    $wallet = $this->customer->getOrCreateWallet();
    expect((float) $wallet->balance)->toBe(75000.0);

    $tx = $wallet->transactions()->first();
    expect($tx)->not->toBeNull()
        ->and($tx->type)->toBe('credit')
        ->and((float) $tx->amount)->toBe(75000.0)
        ->and((float) $tx->balance_before)->toBe(0.0)
        ->and((float) $tx->balance_after)->toBe(75000.0)
        ->and($tx->region_id)->toBe($this->bandung->id);
});

test('wallet debit decrements balance atomically and prevents negative balance (SYS-05)', function () {
    $walletService = app(WalletService::class);

    // Initial credit of 50.000
    $walletService->credit($this->customer, 50000, 'topup', 'TEST-CREDIT', 'Isi saldo awal', $this->bandung->id);
    expect($walletService->getBalance($this->customer))->toBe(50000.0);

    // Valid debit of 30.000
    $txDebit = $walletService->debit($this->customer, 30000, 'order_payment', 'ORDER-001', 'Bayar pesanan pijat');
    expect($walletService->getBalance($this->customer))->toBe(20000.0)
        ->and((float) $txDebit->balance_before)->toBe(50000.0)
        ->and((float) $txDebit->balance_after)->toBe(20000.0);

    // Attempt debit of 25.000 (exceeds balance of 20.000)
    expect(fn () => $walletService->debit($this->customer, 25000, 'order_payment', 'ORDER-002', 'Bayar pesanan lebih'))
        ->toThrow(DomainException::class, 'Saldo dompet tidak mencukupi untuk melakukan transaksi ini.');

    // Balance must remain strictly 20.000
    expect($walletService->getBalance($this->customer))->toBe(20000.0);
});

test('xendit webhook can mark invoice paid and credit wallet balance (SYS-05)', function () {
    app(CustomerAddressService::class)->createAddress($this->customer, [
        'label' => 'Rumah',
        'address_text' => 'Jl. Asia Afrika No. 10',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    $topupService = app(TopupService::class);
    $invoice = $topupService->createInvoice($this->customer, 150000, 'QRIS');

    $response = $this->postJson(route('api.webhooks.xendit.topup'), [
        'external_id' => $invoice->invoice_number,
        'status' => 'PAID',
        'id' => 'xendit_inv_123456789',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $invoice->refresh();
    expect($invoice->status)->toBe('PAID')
        ->and($invoice->xendit_invoice_id)->toBe('xendit_inv_123456789');

    expect(app(WalletService::class)->getBalance($this->customer))->toBe(150000.0);
});
