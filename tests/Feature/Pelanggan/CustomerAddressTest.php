<?php

use App\Enums\Role;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\CustomerAddressService;
use App\Services\RegionService;

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

test('customer can view address list page and create form', function () {
    $this->actingAs($this->customer)
        ->get(route('pelanggan.addresses.index'))
        ->assertOk()
        ->assertSee('Daftar Alamat Tersimpan')
        ->assertSee('Belum Ada Alamat Tersimpan');

    $this->actingAs($this->customer)
        ->get(route('pelanggan.addresses.create'))
        ->assertOk()
        ->assertSee('Tambah Alamat Baru')
        ->assertSee('Bandung Kota');
});

test('customer can add address with valid coordinates within active region and first address automatically becomes primary (CUS-05)', function () {
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.addresses.store'), [
            'label' => 'Rumah Utama',
            'address_text' => 'Jl. Asia Afrika No. 10, RT 01 / RW 02',
            'benchmark' => 'Sebelah minimarket',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ]);

    $response->assertRedirect(route('pelanggan.addresses.index'))
        ->assertSessionHas('status', 'Alamat berhasil disimpan.');

    $address = CustomerAddress::where('user_id', $this->customer->id)->first();
    expect($address)->not->toBeNull()
        ->and($address->label)->toBe('Rumah Utama')
        ->and($address->region_id)->toBe($this->bandung->id)
        ->and($address->is_primary)->toBeTrue()
        ->and($address->latitude)->toBe(-6.9175)
        ->and($address->longitude)->toBe(107.6191);

    // Ensure customer account region_id is set to this region
    $this->customer->refresh();
    expect($this->customer->region_id)->toBe($this->bandung->id)
        ->and($this->customer->primaryAddress->id)->toBe($address->id);
});

test('customer cannot add address with coordinates outside active regions (spatial validation ST_Contains)', function () {
    // Coordinate in Jakarta (-6.2088, 106.8456) which is outside Bandung
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.addresses.store'), [
            'label' => 'Kantor Jakarta',
            'address_text' => 'Jl. MH Thamrin No. 1, Jakarta Pusat',
            'benchmark' => 'Dekat Bundaran HI',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

    $response->assertSessionHasErrors(['latitude']);
    expect(CustomerAddress::where('user_id', $this->customer->id)->count())->toBe(0);
});

test('second address added does not become primary by default unless explicitly specified', function () {
    // Add primary address
    $addr1 = app(CustomerAddressService::class)->createAddress($this->customer, [
        'label' => 'Rumah',
        'address_text' => 'Jl. Braga No. 1',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    // Add second address without is_primary
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.addresses.store'), [
            'label' => 'Kantor',
            'address_text' => 'Jl. Riau No. 50',
            'latitude' => -6.9100,
            'longitude' => 107.6200,
            'is_primary' => 0,
        ]);

    $response->assertRedirect(route('pelanggan.addresses.index'));

    $addr2 = CustomerAddress::where('label', 'Kantor')->first();
    expect($addr2)->not->toBeNull()
        ->and($addr2->is_primary)->toBeFalse();

    $addr1->refresh();
    expect($addr1->is_primary)->toBeTrue();
    expect($this->customer->refresh()->region_id)->toBe($this->bandung->id);
});

test('customer can switch primary address and customer region_id updates immediately (CUS-23)', function () {
    // Create second active region (Cimahi)
    $cimahi = $this->regionService->createRegion(
        'Cimahi Kota',
        [[107.45, -6.85], [107.54, -6.85], [107.54, -6.95], [107.45, -6.95], [107.45, -6.85]]
    );
    $admin = User::factory()->create(['role' => Role::Admin]);
    $this->regionService->assignAdmin($admin, $cimahi);
    $this->regionService->activateRegion($cimahi);

    $service = app(CustomerAddressService::class);
    $addrBandung = $service->createAddress($this->customer, [
        'label' => 'Rumah Bandung',
        'address_text' => 'Jl. Braga No. 1',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    $addrCimahi = $service->createAddress($this->customer, [
        'label' => 'Rumah Cimahi',
        'address_text' => 'Jl. Amir Machmud No. 12',
        'latitude' => -6.8800,
        'longitude' => 107.5000,
        'is_primary' => false,
    ]);

    expect($this->customer->refresh()->region_id)->toBe($this->bandung->id);

    // Switch primary address to Cimahi
    $response = $this->actingAs($this->customer)
        ->post(route('pelanggan.addresses.set-primary', $addrCimahi));

    $response->assertRedirect(route('pelanggan.addresses.index'))
        ->assertSessionHas('status');

    $addrCimahi->refresh();
    $addrBandung->refresh();
    $this->customer->refresh();

    expect($addrCimahi->is_primary)->toBeTrue()
        ->and($addrBandung->is_primary)->toBeFalse()
        ->and($this->customer->region_id)->toBe($cimahi->id);
});

test('customer can edit existing address', function () {
    $service = app(CustomerAddressService::class);
    $address = $service->createAddress($this->customer, [
        'label' => 'Rumah',
        'address_text' => 'Jl. Asli No. 1',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.addresses.edit', $address));
    $response->assertOk()
        ->assertSee('Jl. Asli No. 1');

    $updateResponse = $this->actingAs($this->customer)
        ->put(route('pelanggan.addresses.update', $address), [
            'label' => 'Rumah Baru',
            'address_text' => 'Jl. Baru No. 99',
            'benchmark' => 'Depan masjid',
            'latitude' => -6.9200,
            'longitude' => 107.6200,
        ]);

    $updateResponse->assertRedirect(route('pelanggan.addresses.index'));
    $address->refresh();
    expect($address->label)->toBe('Rumah Baru')
        ->and($address->address_text)->toBe('Jl. Baru No. 99')
        ->and($address->benchmark)->toBe('Depan masjid');
});

test('customer deleting primary address promotes next address or sets region_id to null', function () {
    $service = app(CustomerAddressService::class);
    $addr1 = $service->createAddress($this->customer, [
        'label' => 'Rumah 1',
        'address_text' => 'Jl. Satu No. 1',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);
    $addr2 = $service->createAddress($this->customer, [
        'label' => 'Rumah 2',
        'address_text' => 'Jl. Dua No. 2',
        'latitude' => -6.9100,
        'longitude' => 107.6100,
        'is_primary' => false,
    ]);

    // Delete primary addr1
    $response = $this->actingAs($this->customer)
        ->delete(route('pelanggan.addresses.destroy', $addr1));

    $response->assertRedirect(route('pelanggan.addresses.index'));
    expect(CustomerAddress::find($addr1->id))->toBeNull();

    $addr2->refresh();
    expect($addr2->is_primary)->toBeTrue()
        ->and($this->customer->refresh()->region_id)->toBe($this->bandung->id);

    // Delete remaining addr2
    $this->actingAs($this->customer)
        ->delete(route('pelanggan.addresses.destroy', $addr2));

    expect($this->customer->refresh()->region_id)->toBeNull();
});

test('customer cannot access, edit, or delete another customer address', function () {
    $otherCustomer = User::factory()->create(['role' => Role::Pelanggan]);
    $service = app(CustomerAddressService::class);
    $otherAddress = $service->createAddress($otherCustomer, [
        'label' => 'Alamat Rahasia',
        'address_text' => 'Jl. Rahasia No. 1',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
    ]);

    // Try to edit
    $this->actingAs($this->customer)
        ->get(route('pelanggan.addresses.edit', $otherAddress))
        ->assertForbidden();

    // Try to update
    $this->actingAs($this->customer)
        ->put(route('pelanggan.addresses.update', $otherAddress), [
            'label' => 'Hacked',
            'address_text' => 'Hacked',
            'latitude' => -6.9175,
            'longitude' => 107.6191,
        ])
        ->assertForbidden();

    // Try to delete
    $this->actingAs($this->customer)
        ->delete(route('pelanggan.addresses.destroy', $otherAddress))
        ->assertForbidden();
});
