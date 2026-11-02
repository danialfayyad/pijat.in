<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Blacklist;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTestPartnerProfile(User $user, Region $region, array $attributes = []): PartnerProfile
{
    return PartnerProfile::create(array_merge([
        'user_id' => $user->id,
        'region_id' => $region->id,
        'address_text' => 'Jl. Kaliurang KM 5',
        'ktp_path' => 'ktp/dummy.jpg',
        'selfie_path' => 'selfie/dummy.jpg',
        'coordinate' => ['lat' => -7.78, 'lng' => 110.37],
        'is_active' => true,
    ], $attributes));
}

function createTestCustomerAddress(User $user, Region $region, array $attributes = []): CustomerAddress
{
    return CustomerAddress::create(array_merge([
        'user_id' => $user->id,
        'region_id' => $region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kaliurang KM 5, Sleman',
        'latitude' => -7.78,
        'longitude' => 110.37,
        'coordinate' => ['lat' => -7.78, 'lng' => 110.37],
        'is_primary' => true,
    ], $attributes));
}

beforeEach(function () {
    $this->regionA = Region::factory()->create(['name' => 'Wilayah Sleman', 'status' => 'aktif']);
    $this->regionB = Region::factory()->create(['name' => 'Wilayah Bantul', 'status' => 'aktif']);

    $this->admin = User::factory()->create([
        'role' => Role::Admin,
        'status' => UserStatus::Terverifikasi,
    ]);
    $this->admin->assignedRegions()->attach($this->regionA->id, ['can_handle_emergency' => false]);

    $this->superadmin = User::factory()->create([
        'role' => Role::Superadmin,
        'status' => UserStatus::Terverifikasi,
    ]);

    $this->service = Service::firstOrCreate(
        ['slug' => 'pijat-tradisional'],
        ['name' => 'Pijat Tradisional Sehat', 'description' => 'Pijat relaksasi', 'is_active' => true]
    );
});

test('regional admin can view partner listing scoped to assigned regions', function () {
    $mitraInRegion = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionA->id,
        'name' => 'Budi Terapis Sleman',
    ]);
    createTestPartnerProfile($mitraInRegion, $this->regionA);

    $mitraOutsideRegion = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionB->id,
        'name' => 'Joko Terapis Bantul',
    ]);
    createTestPartnerProfile($mitraOutsideRegion, $this->regionB);

    $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['tab' => 'partners']));

    $response->assertOk();
    $response->assertSee('Budi Terapis Sleman');
    $response->assertDontSee('Joko Terapis Bantul');
});

test('superadmin can view partners across all regions', function () {
    $mitraA = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionA->id,
        'name' => 'Mitra Wilayah A',
    ]);
    createTestPartnerProfile($mitraA, $this->regionA);

    $mitraB = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionB->id,
        'name' => 'Mitra Wilayah B',
    ]);
    createTestPartnerProfile($mitraB, $this->regionB);

    $response = $this->actingAs($this->superadmin)->get(route('admin.users.index', ['tab' => 'partners']));

    $response->assertOk();
    $response->assertSee('Mitra Wilayah A');
    $response->assertSee('Mitra Wilayah B');
});

test('regional admin can view customer listing scoped to assigned regions', function () {
    $customerInRegion = User::factory()->create([
        'role' => Role::Pelanggan,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionA->id,
        'name' => 'Pelanggan Sleman Asli',
    ]);
    createTestCustomerAddress($customerInRegion, $this->regionA);

    $customerOutsideRegion = User::factory()->create([
        'role' => Role::Pelanggan,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionB->id,
        'name' => 'Pelanggan Bantul Jauh',
    ]);
    createTestCustomerAddress($customerOutsideRegion, $this->regionB);

    $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['tab' => 'customers']));

    $response->assertOk();
    $response->assertSee('Pelanggan Sleman Asli');
    $response->assertDontSee('Pelanggan Bantul Jauh');
});

test('admin can view partner detail with rating metrics and customer reviews', function () {
    $mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionA->id,
        'name' => 'Agus Terapis Berbakat',
    ]);
    createTestPartnerProfile($mitra, $this->regionA, ['address_text' => 'Jl. Gejayan No. 10']);

    $customer = User::factory()->create([
        'role' => Role::Pelanggan,
        'status' => UserStatus::Terverifikasi,
        'name' => 'Rina Pelanggan Puas',
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'mitra_id' => $mitra->id,
        'region_id' => $this->regionA->id,
        'service_id' => $this->service->id,
    ]);

    OrderRating::create([
        'order_id' => $order->id,
        'from_user_id' => $customer->id,
        'to_user_id' => $mitra->id,
        'rating_type' => 'customer_to_mitra',
        'score' => 5,
        'review' => 'Pijatan mantap sekali, sangat profesional dan tepat waktu!',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.users.partners.show', $mitra));

    $response->assertOk();
    $response->assertSee('Agus Terapis Berbakat');
    $response->assertSee('5.0');
    $response->assertSee('Pijatan mantap sekali, sangat profesional dan tepat waktu!');
    $response->assertSee('Rina Pelanggan Puas');
});

test('admin cannot view partner detail from unassigned region', function () {
    $mitraOther = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionB->id,
    ]);
    createTestPartnerProfile($mitraOther, $this->regionB);

    $response = $this->actingAs($this->admin)->get(route('admin.users.partners.show', $mitraOther));
    $response->assertForbidden();
});

test('admin can suspend a user account with duration and mandatory reason', function () {
    $mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionA->id,
    ]);
    createTestPartnerProfile($mitra, $this->regionA);

    $response = $this->actingAs($this->admin)->post(route('admin.users.update-status', $mitra), [
        'action' => 'suspend',
        'suspend_days' => 7,
        'reason' => 'Keterlambatan berturut-turut tanpa konfirmasi pada 3 pesanan.',
    ]);

    $response->assertRedirect();
    $mitra->refresh();

    expect($mitra->status)->toBe(UserStatus::Ditangguhkan);
    expect($mitra->suspended_until)->not->toBeNull();
    expect($mitra->isSuspended())->toBeTrue();
    expect($mitra->status_reason)->toBe('Keterlambatan berturut-turut tanpa konfirmasi pada 3 pesanan.');
});

test('admin can reactivate a suspended user account', function () {
    $mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'suspended_until' => now()->addDays(5),
        'region_id' => $this->regionA->id,
    ]);
    createTestPartnerProfile($mitra, $this->regionA);

    $response = $this->actingAs($this->admin)->post(route('admin.users.update-status', $mitra), [
        'action' => 'activate',
        'reason' => 'Telah mengikuti pembinaan SOP ulang dan menandatangani komitmen perbaikan.',
    ]);

    $response->assertRedirect();
    $mitra->refresh();

    expect($mitra->status)->toBe(UserStatus::Terverifikasi);
    expect($mitra->suspended_until)->toBeNull();
    expect($mitra->isSuspended())->toBeFalse();
    expect($mitra->status_reason)->toBe('Telah mengikuti pembinaan SOP ulang dan menandatangani komitmen perbaikan.');
});

test('admin can permanently block user and records into blacklists table', function () {
    $customer = User::factory()->create([
        'role' => Role::Pelanggan,
        'status' => UserStatus::Terverifikasi,
        'region_id' => $this->regionA->id,
        'phone' => '081299887766',
        'device_id' => 'device-xyz-123',
    ]);
    createTestCustomerAddress($customer, $this->regionA);

    $response = $this->actingAs($this->admin)->post(route('admin.users.update-status', $customer), [
        'action' => 'block',
        'reason' => 'Terbukti melakukan pelecehan verbal berat kepada mitra terapis.',
    ]);

    $response->assertRedirect();
    $customer->refresh();

    expect($customer->status)->toBe(UserStatus::Diblokir);
    expect($customer->isBlocked())->toBeTrue();

    // Verify blacklist record exists
    $blacklist = Blacklist::where('user_id', $customer->id)->first();
    expect($blacklist)->not->toBeNull();
    expect($blacklist->phone)->toBe('081299887766');
    expect($blacklist->device_id)->toBe('device-xyz-123');
    expect($blacklist->reason)->toBe('Terbukti melakukan pelecehan verbal berat kepada mitra terapis.');
    expect($blacklist->blocked_by)->toBe($this->admin->id);
});

test('permanently blocked user cannot authenticate', function () {
    $user = User::factory()->create([
        'email' => 'banned@pijat.in',
        'password' => bcrypt('password123'),
        'status' => UserStatus::Diblokir,
        'phone' => '081233445566',
    ]);

    $response = $this->post(route('login'), [
        'email' => 'banned@pijat.in',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('suspended user cannot authenticate during active suspension', function () {
    $user = User::factory()->create([
        'email' => 'suspended@pijat.in',
        'password' => bcrypt('password123'),
        'status' => UserStatus::Ditangguhkan,
        'suspended_until' => now()->addDays(3),
        'phone' => '081277889900',
    ]);

    $response = $this->post(route('login'), [
        'email' => 'suspended@pijat.in',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
