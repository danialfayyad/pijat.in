<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Services\OrderCancellationService;
use App\Services\OrderSettlementService;
use App\Services\WalletService;
use Carbon\Carbon;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember Kota',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    $this->service = Service::factory()->create([
        'name' => 'Pijat Tradisional Relaksasi',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Budi Pelanggan',
        'email' => 'budi_voucher@example.com',
        'phone' => '081234567899',
        'gender' => 'pria',
    ]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kenanga No. 12',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);

    $this->partner = User::factory()->mitra()->create([
        'name' => 'Slamet Terapis',
        'gender' => 'pria',
    ]);

    app(WalletService::class)->credit(
        $this->customer,
        200000,
        'topup_balance',
        'TEST-INIT',
        'Initial balance',
        $this->region->id
    );
});

test('pelanggan dapat memvalidasi voucher diskon via endpoint ajax', function () {
    $voucher = Voucher::create([
        'code' => 'HEMAT20',
        'name' => 'Promo Diskon 20k',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 20000,
        'min_order_amount' => 50000,
        'is_active' => true,
        'per_user_limit' => 1,
    ]);

    $response = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.validate-voucher'), [
            'code' => 'HEMAT20',
            'package_id' => $this->package->id,
        ]);

    $response->assertOk()
        ->assertJson([
            'valid' => true,
            'code' => 'HEMAT20',
            'discount_amount' => 20000,
            'final_price' => 80000,
        ]);
});

test('endpoint validasi voucher menolak kode yang tidak aktif atau tidak memenuhi syarat minimum', function () {
    $voucher = Voucher::create([
        'code' => 'MIN150K',
        'name' => 'Diskon Minimal 150rb',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 25000,
        'min_order_amount' => 150000, // paket hanya 100k
        'is_active' => true,
        'per_user_limit' => 1,
    ]);

    $response = $this->actingAs($this->customer)
        ->postJson(route('pelanggan.orders.validate-voucher'), [
            'code' => 'MIN150K',
            'package_id' => $this->package->id,
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'valid' => false,
        ]);
});

test('pesanan yang dibuat dengan voucher memotong total_price dan mencatat voucher_usages', function () {
    $voucher = Voucher::create([
        'code' => 'PROMO15',
        'name' => 'Diskon 15k',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 15000,
        'min_order_amount' => 50000,
        'is_active' => true,
        'per_user_limit' => 1,
    ]);

    $validDate = Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d');

    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.orders.store'), [
            'package_id' => $this->package->id,
            'customer_address_id' => $this->address->id,
            'recipient_type' => 'myself',
            'order_type' => 'terjadwal',
            'scheduled_date' => $validDate,
            'scheduled_time' => '10:00',
            'preferred_gender' => 'bebas',
            'risk_agreement' => '1',
            'voucher_code' => 'PROMO15',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $order = Order::where('customer_id', $this->customer->id)->latest('id')->first();
    expect($order)->not->toBeNull()
        ->and((float) $order->discount_amount)->toBe(15000.0)
        ->and((float) $order->total_price)->toBe(85000.0)
        ->and($order->voucher_id)->toBe($voucher->id);

    // Cek record usage dan used_count bertambah
    expect(VoucherUsage::where('order_id', $order->id)->exists())->toBeTrue()
        ->and($voucher->fresh()->used_count)->toBe(1);

    // Saldo pelanggan terpotong sejumlah net total_price (85.000) dari 200.000 = 115.000
    expect((float) $this->customer->fresh()->wallet->balance)->toBe(115000.0);
});

test('mitra tetap menerima hak pendapatan penuh 80% dari harga kotor saat pesanan selesai', function () {
    $voucher = Voucher::create([
        'code' => 'SUPER20',
        'name' => 'Diskon 20k',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 20000,
        'min_order_amount' => 50000,
        'is_active' => true,
    ]);

    // Order seharga paket 100k, diskon 20k, net bayar customer 80k
    $order = Order::create([
        'order_code' => 'ORD-VOUCHER-TEST',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->partner->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => 'Pijat Tradisional',
        'duration_minutes' => 60,
        'total_price' => 80000, // net
        'discount_amount' => 20000, // voucher diskon
        'voucher_id' => $voucher->id,
        'commission_percent' => 20.00,
        'recipient_name' => 'Budi',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Kenanga No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::SedangBerlangsung,
    ]);

    $settlementService = app(OrderSettlementService::class);
    $settlementService->settle($order);

    // Pendapatan mitra harus 80% dari harga kotor (100.000) = Rp 80.000, bukan 80% dari 80.000
    $partnerBalance = (float) $this->partner->fresh()->wallet->balance;
    expect($partnerBalance)->toBe(80000.0);
});

test('pembatalan pesanan mengembalikan kuota voucher pelanggan', function () {
    $voucher = Voucher::create([
        'code' => 'BATAL10',
        'name' => 'Diskon 10k',
        'type' => Voucher::TYPE_FIXED,
        'amount' => 10000,
        'min_order_amount' => 50000,
        'is_active' => true,
        'per_user_limit' => 1,
        'used_count' => 1,
    ]);

    $futureTime = Carbon::now()->addHours(5);

    $order = Order::create([
        'order_code' => 'ORD-BATAL-TEST',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => null,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => 'Pijat Tradisional',
        'duration_minutes' => 60,
        'total_price' => 90000,
        'discount_amount' => 10000,
        'voucher_id' => $voucher->id,
        'commission_percent' => 20.00,
        'recipient_name' => 'Budi',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Kenanga No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $futureTime->copy()->utc(),
        'status' => OrderStatus::MencariMitra,
    ]);

    VoucherUsage::create([
        'voucher_id' => $voucher->id,
        'user_id' => $this->customer->id,
        'order_id' => $order->id,
        'discount_amount' => 10000,
    ]);

    $cancellationService = app(OrderCancellationService::class);
    $cancellationService->cancelByCustomer($order, $this->customer, 'Ingin ganti jadwal');

    // Usage record harus terhapus dan used_count berkurang
    expect(VoucherUsage::where('order_id', $order->id)->exists())->toBeFalse()
        ->and($voucher->fresh()->used_count)->toBe(0);
});
