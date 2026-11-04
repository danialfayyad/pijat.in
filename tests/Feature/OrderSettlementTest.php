<?php

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Complaint;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\OrderSettlementService;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    $this->service = Service::factory()->create([
        'name' => 'Pijat Tradisional',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 90,
        'price' => 200000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Budi Pelanggan',
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

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kalimantan No. 12',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);
});

test('partner receives 80% earnings into held_balance upon order completion (SYS-03, MIT-15)', function () {
    $order = Order::create([
        'order_code' => 'ORD-TEST-001',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 90,
        'total_price' => 200000,
        'commission_percent' => 20.0,
        'recipient_type' => 'myself',
        'recipient_name' => 'Budi Pelanggan',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'pria',
        'recipient_phone' => '08123456789',
        'service_address_text' => 'Jl. Kalimantan No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::SedangBerlangsung,
        'service_started_at' => now()->subMinutes(90),
    ]);

    $response = $this->actingAs($this->mitra)
        ->post(route('mitra.orders.update-status', $order), [
            'status' => 'selesai',
        ]);

    $response->assertRedirect(route('mitra.orders.show', $order));

    // Partner held_balance should be 80% of 200.000 = 160.000, while balance remains 0
    $wallet = Wallet::where('user_id', $this->mitra->id)->first();
    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->held_balance)->toBe(160000.00)
        ->and((float) $wallet->balance)->toBe(0.00);

    // Ledger transaction exists with proper attribution
    $tx = WalletTransaction::where('user_id', $this->mitra->id)
        ->where('reference_type', 'order_held_pending')
        ->where('reference_id', (string) $order->id)
        ->first();

    expect($tx)->not->toBeNull()
        ->and((float) $tx->amount)->toBe(160000.00)
        ->and($tx->region_id)->toBe($this->region->id);

    // Notification created for held earning
    $notification = SystemNotification::where('user_id', $this->mitra->id)
        ->where('event_code', 'order_earning_held')
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification->title)->toContain('Pendapatan Layanan Ditahan');
});

test('scheduler releases held balance to available balance after 24 hours without complaints (SYS-03)', function () {
    $order = Order::create([
        'order_code' => 'ORD-TEST-24H',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 90,
        'total_price' => 200000,
        'commission_percent' => 20.0,
        'recipient_type' => 'myself',
        'recipient_name' => 'Budi Pelanggan',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'pria',
        'recipient_phone' => '08123456789',
        'service_address_text' => 'Jl. Kalimantan No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::Selesai,
        'service_started_at' => now()->subHours(26),
        'service_completed_at' => now()->subHours(25),
    ]);

    $service = app(OrderSettlementService::class);
    $service->holdEarnings($order);

    $wallet = Wallet::where('user_id', $this->mitra->id)->first();
    expect((float) $wallet->held_balance)->toBe(160000.00)
        ->and((float) $wallet->balance)->toBe(0.00);

    // Run release scheduler command
    $this->artisan('app:release-held-balances')
        ->assertSuccessful();

    $wallet->refresh();
    expect((float) $wallet->held_balance)->toBe(0.00)
        ->and((float) $wallet->balance)->toBe(160000.00);

    // Available earning transaction created
    $tx = WalletTransaction::where('user_id', $this->mitra->id)
        ->where('reference_type', 'order_earning')
        ->where('reference_id', (string) $order->id)
        ->first();

    expect($tx)->not->toBeNull()
        ->and((float) $tx->amount)->toBe(160000.00);
});

test('scheduler does not release held balance if active complaint exists (SYS-03)', function () {
    $order = Order::create([
        'order_code' => 'ORD-TEST-COMPLAINT',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 90,
        'total_price' => 200000,
        'commission_percent' => 20.0,
        'recipient_type' => 'myself',
        'recipient_name' => 'Budi Pelanggan',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'pria',
        'recipient_phone' => '08123456789',
        'service_address_text' => 'Jl. Kalimantan No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::Selesai,
        'service_started_at' => now()->subHours(26),
        'service_completed_at' => now()->subHours(25),
    ]);

    $service = app(OrderSettlementService::class);
    $service->holdEarnings($order);

    // File an active complaint
    Complaint::create([
        'complaint_code' => 'CMP-TEST-001',
        'order_id' => $order->id,
        'region_id' => $this->region->id,
        'complainant_id' => $this->customer->id,
        'complainant_role' => 'pelanggan',
        'target_id' => $this->mitra->id,
        'title' => 'Layanan kurang bersih',
        'description' => 'Terapis terlambat dan tidak membawa perlengkapan.',
        'status' => ComplaintStatus::Menunggu,
        'priority' => 'sedang',
    ]);

    // Run release scheduler command
    $this->artisan('app:release-held-balances')
        ->assertSuccessful();

    $wallet = Wallet::where('user_id', $this->mitra->id)->first();
    // Balance remains held because complaint is still open
    expect((float) $wallet->held_balance)->toBe(160000.00)
        ->and((float) $wallet->balance)->toBe(0.00);
});

test('order settlement service is idempotent and prevents double crediting (SYS-03)', function () {
    $order = Order::create([
        'order_code' => 'ORD-TEST-002',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'customer_address_id' => $this->address->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'commission_percent' => 20.0,
        'recipient_type' => 'myself',
        'recipient_name' => 'Budi Pelanggan',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'pria',
        'recipient_phone' => '08123456789',
        'service_address_text' => 'Jl. Kalimantan No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::Selesai,
    ]);

    $service = app(OrderSettlementService::class);

    // First call settles into held
    $tx1 = $service->settle($order);
    expect($tx1)->not->toBeNull();

    $wallet = Wallet::where('user_id', $this->mitra->id)->first();
    expect((float) $wallet->held_balance)->toBe(80000.00);

    // Second call must return null and not change wallet balance
    $tx2 = $service->settle($order);
    expect($tx2)->toBeNull();

    $wallet->refresh();
    expect((float) $wallet->held_balance)->toBe(80000.00);

    $txCount = WalletTransaction::where('user_id', $this->mitra->id)
        ->where('reference_type', 'order_held_pending')
        ->where('reference_id', (string) $order->id)
        ->count();

    expect($txCount)->toBe(1);
});
