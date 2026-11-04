<?php

use App\Models\Parameter;
use App\Models\Region;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
    ]);

    $this->superadmin = User::factory()->superadmin()->create([
        'name' => 'Super Administrator',
    ]);

    $this->admin = User::factory()->admin()->create();

    $this->customer = User::factory()->customer()->create();
    $this->customerWallet = Wallet::create([
        'user_id' => $this->customer->id,
        'balance' => 100000.00,
    ]);
});

test('superadmin can view centralized floating balance monitoring and settlement (SA-04)', function () {
    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.finance.index'));

    $response->assertOk()
        ->assertSee('Monitoring Saldo Mengendap &amp; Settlement', false)
        ->assertSee('Total Saldo Pelanggan')
        ->assertSee('Sebaran Dana Mengendap per Wilayah')
        ->assertSee($this->region->name);
});

test('superadmin can view cross-region financial reports (SA-08)', function () {
    WalletTransaction::create([
        'wallet_id' => $this->customerWallet->id,
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'type' => 'credit',
        'amount' => 100000.00,
        'balance_before' => 0.00,
        'balance_after' => 100000.00,
        'reference_type' => 'topup',
        'description' => 'Top-up saldo dompet',
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.finance.cross-region', [
            'regions' => [$this->region->id],
        ]));

    $response->assertOk()
        ->assertSee('Laporan Keuangan Lintas Wilayah')
        ->assertSee($this->region->name)
        ->assertSee('Ringkasan Finansial per Wilayah');
});

test('superadmin can export cross-region report to CSV (SA-08)', function () {
    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.finance.export.csv', [
            'regions' => [$this->region->id],
        ]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('regular admin cannot access superadmin financial monitoring (SA-04)', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('superadmin.finance.index'));

    $response->assertForbidden();
});

test('superadmin finance view reflects dynamic commission percentage parameter', function () {
    Parameter::updateOrCreate(
        ['key' => 'platform_commission_percent'],
        [
            'value' => '25.00',
            'type' => 'decimal',
            'group' => 'finance',
            'label' => 'Persentase Komisi Platform',
        ]
    );
    Cache::forget('sys_param_platform_commission_percent');

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.finance.index'));

    $response->assertOk()
        ->assertSee('Pendapatan bersih (25%)');
});
