<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\PartnerProfile;
use App\Models\PenaltyLedger;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->regionA = Region::factory()->create([
        'name' => 'KABUPATEN JEMBER',
        'status' => 'aktif',
    ]);

    $this->regionB = Region::factory()->create([
        'name' => 'KOTA BLITAR',
        'status' => 'aktif',
    ]);

    // Admin B is assigned ONLY to Region B
    $this->adminB = User::factory()->create(['role' => Role::Admin]);
    $this->adminB->assignedRegions()->attach($this->regionB->id);
});

test('admin of new region can audit full historical orders, ratings, and penalty trail of relocated partner (ADM-16)', function () {
    // 1. Partner starts in Region A
    $partner = User::factory()->create([
        'role' => Role::Mitra,
        'region_id' => $this->regionA->id,
    ]);

    PartnerProfile::create([
        'user_id' => $partner->id,
        'address_text' => 'Alamat lama di Jember',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp/dummy.jpg',
        'selfie_path' => 'selfie/dummy.jpg',
        'region_id' => $this->regionA->id,
        'is_active' => true,
        'verified_at' => now(),
    ]);

    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    // Historical completed order in Region A
    $historicalOrder = Order::factory()->create([
        'order_code' => 'ORD-HIST-001',
        'customer_id' => $customer->id,
        'mitra_id' => $partner->id,
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::Selesai,
    ]);

    // Historical 5-star rating in Region A
    OrderRating::create([
        'order_id' => $historicalOrder->id,
        'from_user_id' => $customer->id,
        'to_user_id' => $partner->id,
        'score' => 5,
        'review' => 'Pelayanan sangat memuaskan dari wilayah asal!',
        'rating_type' => 'customer_to_mitra',
    ]);

    // Historical penalty ledger in Region A
    PenaltyLedger::create([
        'user_id' => $partner->id,
        'type' => 'violation',
        'points' => 15,
        'balance_before' => 0,
        'balance_after' => 15,
        'reason' => 'Keterlambatan tiba di lokasi layanan wilayah lama',
    ]);

    // 2. Partner relocates to Region B
    $partner->update(['region_id' => $this->regionB->id]);
    $partner->partnerProfile()->update(['region_id' => $this->regionB->id]);

    // 3. Admin of Region B visits partner profile
    $response = $this->actingAs($this->adminB)
        ->get(route('admin.users.partners.show', $partner));

    // Admin B must have full access and see the historical data
    $response->assertOk()
        ->assertSee('Pelayanan sangat memuaskan dari wilayah asal!')
        ->assertSee('Keterlambatan tiba di lokasi layanan wilayah lama')
        ->assertSee('5.0');
});

test('admin handling incident of out-of-region customer can access order case but is restricted from general user moderation (ADM-16)', function () {
    // Customer registered with primary address in Region A
    $customerA = User::factory()->create([
        'role' => Role::Pelanggan,
        'region_id' => $this->regionA->id,
    ]);

    CustomerAddress::create([
        'user_id' => $customerA->id,
        'region_id' => $this->regionA->id,
        'address_name' => 'Rumah Utama Jember',
        'address_text' => 'Jl. Jember No. 1',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'latitude' => -8.172,
        'longitude' => 113.700,
        'is_primary' => true,
    ]);

    // Customer travels and places order in Region B
    $orderInRegionB = Order::factory()->create([
        'order_code' => 'ORD-TRAVEL-002',
        'customer_id' => $customerA->id,
        'region_id' => $this->regionB->id,
        'status' => OrderStatus::MencariMitra,
    ]);

    // 1. Admin B can monitor the order within their jurisdiction (Region B)
    $orderResponse = $this->actingAs($this->adminB)
        ->get(route('admin.orders.show', $orderInRegionB));
    $orderResponse->assertOk()
        ->assertSee('ORD-TRAVEL-002');

    // 2. But Admin B CANNOT access full customer profile moderation (customer belongs to Region A)
    $customerModerationResponse = $this->actingAs($this->adminB)
        ->get(route('admin.users.customers.show', $customerA));
    $customerModerationResponse->assertForbidden();
});
