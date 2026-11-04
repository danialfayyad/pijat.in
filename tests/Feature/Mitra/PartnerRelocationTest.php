<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\PartnerProfile;
use App\Models\PartnerRelocationRequest;
use App\Models\PenaltyLedger;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RegionService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->regionService = app(RegionService::class);

    // Region A: Jember [Lng 113.60 to 113.80, Lat -8.30 to -8.10]
    $this->regionA = $this->regionService->createRegion(
        'Wilayah Jember',
        [[113.60, -8.10], [113.80, -8.10], [113.80, -8.30], [113.60, -8.30], [113.60, -8.10]]
    );

    // Region B: Bondowoso [Lng 113.81 to 114.00, Lat -8.30 to -8.10]
    $this->regionB = $this->regionService->createRegion(
        'Wilayah Bondowoso',
        [[113.81, -8.10], [114.00, -8.10], [114.00, -8.30], [113.81, -8.30], [113.81, -8.10]]
    );

    $this->adminA = User::factory()->create(['role' => Role::Admin]);
    $this->regionService->assignAdmin($this->adminA, $this->regionA);
    $this->regionService->activateRegion($this->regionA);

    $this->adminB = User::factory()->create(['role' => Role::Admin]);
    $this->regionService->assignAdmin($this->adminB, $this->regionB);
    $this->regionService->activateRegion($this->regionB);

    // Create Partner in Region A
    $this->mitra = User::factory()->mitra()->create([
        'region_id' => $this->regionA->id,
        'status' => UserStatus::Terverifikasi,
    ]);

    $this->partnerProfile = PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->regionA->id,
        'address_text' => 'Jl. Kalimantan No. 1, Jember',
        'latitude' => -8.20,
        'longitude' => 113.70,
        'coordinate' => DB::raw("ST_GeomFromText('POINT(113.70 -8.20)', 4326, 'axis-order=long-lat')"),
        'ktp_path' => 'ktp/dummy.jpg',
        'selfie_path' => 'selfie/dummy.jpg',
        'verified_at' => now(),
        'is_active' => true,
    ]);

    $this->mitraWallet = Wallet::create([
        'user_id' => $this->mitra->id,
        'balance' => 500000,
        'held_balance' => 0,
    ]);

    $this->customer = User::factory()->customer()->create([
        'region_id' => $this->regionA->id,
    ]);

    $this->customerAddress = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->regionA->id,
        'label' => 'Kantor',
        'address_text' => 'Jl. Jawa No. 5',
        'latitude' => -8.19,
        'longitude' => 113.71,
        'coordinate' => ['lat' => -8.19, 'lng' => 113.71],
        'is_primary' => true,
    ]);

    $this->service = Service::factory()->create(['name' => 'Pijat Capek']);
    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);
});

it('allows partner with no active orders to view relocation page', function () {
    $response = $this->actingAs($this->mitra)->get(route('mitra.relocation.create'));

    $response->assertOk();
    $response->assertSee('Pilih Titik Domisili Baru');
    $response->assertSee('Wilayah Jember');
});

it('blocks partner from submitting relocation if active or scheduled orders exist', function () {
    // Create an active order for this partner
    Order::factory()->create([
        'order_code' => 'ORD-ACTIVE-01',
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::MenujuLokasi,
    ]);

    // View should show locked message
    $viewResponse = $this->actingAs($this->mitra)->get(route('mitra.relocation.create'));
    $viewResponse->assertOk();
    $viewResponse->assertSee('Pindah Wilayah Terkunci');
    $viewResponse->assertSee('Selesaikan Pesanan Terlebih Dahulu');

    // Post submit should be redirected with error
    $submitResponse = $this->actingAs($this->mitra)->post(route('mitra.relocation.store'), [
        'address_text' => 'Jl. Raya Bondowoso No. 10',
        'latitude' => -8.20,
        'longitude' => 113.90, // Inside Region B
    ]);

    $submitResponse->assertRedirect(route('mitra.relocation.create'));
    $submitResponse->assertSessionHas('error');
    expect(PartnerRelocationRequest::count())->toBe(0);
});

it('validates destination coordinate must belong to an active region different from current region', function () {
    // 1. Coordinate outside any active region
    $responseOutside = $this->actingAs($this->mitra)->post(route('mitra.relocation.store'), [
        'address_text' => 'Samudera Hindia',
        'latitude' => -10.0,
        'longitude' => 110.0,
    ]);
    $responseOutside->assertSessionHasErrors(['latitude']);

    // 2. Coordinate still in Region A (same region)
    $responseSame = $this->actingAs($this->mitra)->post(route('mitra.relocation.store'), [
        'address_text' => 'Jl. Mastrip, Jember',
        'latitude' => -8.22,
        'longitude' => 113.72,
    ]);
    $responseSame->assertSessionHasErrors(['latitude']);
});

it('allows partner to submit relocation request and freezes online & withdrawal access', function () {
    $response = $this->actingAs($this->mitra)->post(route('mitra.relocation.store'), [
        'address_text' => 'Jl. PB Sudirman No. 88, Bondowoso',
        'latitude' => -8.20,
        'longitude' => 113.90, // Valid Region B coordinate
        'notes' => 'Pindah rumah ke Bondowoso',
    ]);

    $response->assertRedirect(route('mitra.relocation.create'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('partner_relocation_requests', [
        'partner_id' => $this->mitra->id,
        'from_region_id' => $this->regionA->id,
        'to_region_id' => $this->regionB->id,
        'status' => PartnerRelocationRequest::STATUS_PENDING,
        'notes' => 'Pindah rumah ke Bondowoso',
    ]);

    // Profile is_active should be frozen to false
    expect($this->mitra->fresh()->partnerProfile->is_active)->toBeFalse();

    // Toggle active should fail with error
    $toggleResponse = $this->actingAs($this->mitra)->post(route('mitra.toggle-active'), ['is_active' => true]);
    $toggleResponse->assertRedirect();
    $toggleResponse->assertSessionHasErrors(['is_active']);
    expect($this->mitra->fresh()->partnerProfile->is_active)->toBeFalse();

    // Withdrawal submission should fail with error
    $withdrawResponse = $this->actingAs($this->mitra)->post(route('mitra.finance.withdraw.store'), [
        'amount' => 100000,
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Terapis',
    ]);
    $withdrawResponse->assertRedirect(route('mitra.finance.index'));
    $withdrawResponse->assertSessionHas('error');
});

it('allows partner to cancel a pending relocation request', function () {
    $relocation = PartnerRelocationRequest::create([
        'partner_id' => $this->mitra->id,
        'from_region_id' => $this->regionA->id,
        'to_region_id' => $this->regionB->id,
        'address_text' => 'Jl. PB Sudirman No. 88, Bondowoso',
        'latitude' => -8.20,
        'longitude' => 113.90,
        'coordinate' => DB::raw("ST_GeomFromText('POINT(113.90 -8.20)', 4326, 'axis-order=long-lat')"),
        'status' => PartnerRelocationRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->mitra)->post(route('mitra.relocation.cancel', $relocation));

    $response->assertRedirect(route('mitra.relocation.create'));
    $response->assertSessionHas('status');
    expect($relocation->fresh()->status)->toBe(PartnerRelocationRequest::STATUS_CANCELLED);
});

it('allows destination admin to view pending relocations scoped to their region', function () {
    $relocation = PartnerRelocationRequest::create([
        'partner_id' => $this->mitra->id,
        'from_region_id' => $this->regionA->id,
        'to_region_id' => $this->regionB->id,
        'address_text' => 'Jl. PB Sudirman No. 88, Bondowoso',
        'latitude' => -8.20,
        'longitude' => 113.90,
        'coordinate' => DB::raw("ST_GeomFromText('POINT(113.90 -8.20)', 4326, 'axis-order=long-lat')"),
        'status' => PartnerRelocationRequest::STATUS_PENDING,
    ]);

    // Destination admin (Admin B) can see it
    $responseB = $this->actingAs($this->adminB)->get(route('admin.mitra.index', ['tab' => 'relokasi']));
    $responseB->assertOk();
    $responseB->assertSee($this->mitra->name);
    $responseB->assertSee('Jl. PB Sudirman No. 88, Bondowoso');

    // Origin admin (Admin A) cannot see it on relocation tab for Region B
    $responseA = $this->actingAs($this->adminA)->get(route('admin.mitra.index', ['tab' => 'relokasi']));
    $responseA->assertOk();
    $responseA->assertDontSee($this->mitra->name);
});

it('approves partner relocation and preserves historical orders, ratings, and penalty ledger (ADM-16)', function () {
    // Setup historical completed order in Region A
    $pastOrder = Order::factory()->create([
        'order_code' => 'ORD-PAST-01',
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->regionA->id,
        'status' => OrderStatus::Selesai,
    ]);

    OrderRating::create([
        'order_id' => $pastOrder->id,
        'from_user_id' => $this->customer->id,
        'to_user_id' => $this->mitra->id,
        'score' => 5,
        'review' => 'Pelayanan luar biasa rapi dan bersih.',
        'rating_type' => 'customer_to_mitra',
    ]);

    // Setup historical demerit point
    PenaltyLedger::create([
        'user_id' => $this->mitra->id,
        'type' => 'violation',
        'points' => 10,
        'balance_before' => 0,
        'balance_after' => 10,
        'reason' => 'Keterlambatan 15 menit',
        'assigned_by' => $this->adminA->id,
    ]);
    $this->mitra->update(['penalty_points' => 10]);

    // Create relocation request to Region B
    $relocation = PartnerRelocationRequest::create([
        'partner_id' => $this->mitra->id,
        'from_region_id' => $this->regionA->id,
        'to_region_id' => $this->regionB->id,
        'address_text' => 'Jl. Diponegoro No. 77, Bondowoso',
        'latitude' => -8.21,
        'longitude' => 113.91,
        'coordinate' => DB::raw("ST_GeomFromText('POINT(113.91 -8.21)', 4326, 'axis-order=long-lat')"),
        'status' => PartnerRelocationRequest::STATUS_PENDING,
    ]);

    // Admin B approves the relocation
    $response = $this->actingAs($this->adminB)->post(route('admin.mitra.relocation.approve', $relocation));

    $response->assertRedirect(route('admin.mitra.index', ['tab' => 'relokasi']));
    $response->assertSessionHas('status');

    expect($relocation->fresh()->status)->toBe(PartnerRelocationRequest::STATUS_APPROVED)
        ->and($relocation->fresh()->reviewed_by)->toBe($this->adminB->id);

    // Verify Partner Region and Profile were updated to Region B
    $freshMitra = $this->mitra->fresh();
    $freshProfile = $this->partnerProfile->fresh();

    expect($freshMitra->region_id)->toBe($this->regionB->id)
        ->and($freshMitra->status)->toBe(UserStatus::Terverifikasi)
        ->and($freshProfile->region_id)->toBe($this->regionB->id)
        ->and($freshProfile->address_text)->toBe('Jl. Diponegoro No. 77, Bondowoso');

    // Verify historical audit records are preserved completely (ADM-16)
    expect(Order::where('mitra_id', $this->mitra->id)->count())->toBe(1)
        ->and(OrderRating::where('to_user_id', $this->mitra->id)->count())->toBe(1)
        ->and(PenaltyLedger::where('user_id', $this->mitra->id)->count())->toBe(1)
        ->and($freshMitra->penalty_points)->toBe(10);
});

it('rejects partner relocation with reason and retains partner in original region', function () {
    $relocation = PartnerRelocationRequest::create([
        'partner_id' => $this->mitra->id,
        'from_region_id' => $this->regionA->id,
        'to_region_id' => $this->regionB->id,
        'address_text' => 'Jl. Diponegoro No. 77, Bondowoso',
        'latitude' => -8.21,
        'longitude' => 113.91,
        'coordinate' => DB::raw("ST_GeomFromText('POINT(113.91 -8.21)', 4326, 'axis-order=long-lat')"),
        'status' => PartnerRelocationRequest::STATUS_PENDING,
    ]);

    // Admin B rejects relocation
    $response = $this->actingAs($this->adminB)->post(route('admin.mitra.relocation.reject', $relocation), [
        'rejection_reason' => 'Kuota mitra di Bondowoso sedang penuh.',
    ]);

    $response->assertRedirect(route('admin.mitra.index', ['tab' => 'relokasi']));
    $response->assertSessionHas('status');

    $freshRelocation = $relocation->fresh();
    expect($freshRelocation->status)->toBe(PartnerRelocationRequest::STATUS_REJECTED)
        ->and($freshRelocation->rejection_reason)->toBe('Kuota mitra di Bondowoso sedang penuh.')
        ->and($freshRelocation->reviewed_by)->toBe($this->adminB->id);

    // Partner remains in Region A
    $freshMitra = $this->mitra->fresh();
    expect($freshMitra->region_id)->toBe($this->regionA->id)
        ->and($freshMitra->status)->toBe(UserStatus::Terverifikasi)
        ->and($this->partnerProfile->fresh()->region_id)->toBe($this->regionA->id);
});
