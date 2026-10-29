<?php

use App\Enums\Role;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create(['role' => Role::Pelanggan]);

    // Create Active Service 1 with 3 packages (60, 90, 120 mins)
    $this->service1 = Service::create([
        'name' => 'Full Body Massage',
        'slug' => 'full-body-massage',
        'description' => 'Pijatan menyeluruh untuk meredakan ketegangan otot dan stres.',
        'is_active' => true,
    ]);
    ServicePackage::create(['service_id' => $this->service1->id, 'duration_minutes' => 60, 'price' => 100000, 'is_active' => true]);
    ServicePackage::create(['service_id' => $this->service1->id, 'duration_minutes' => 90, 'price' => 140000, 'is_active' => true]);
    ServicePackage::create(['service_id' => $this->service1->id, 'duration_minutes' => 120, 'price' => 180000, 'is_active' => true]);

    // Create Active Service 2 with 1 active and 1 inactive package
    $this->service2 = Service::create([
        'name' => 'Refleksi Kaki',
        'slug' => 'refleksi-kaki',
        'description' => 'Titik akupresur untuk menyegarkan organ vital.',
        'is_active' => true,
    ]);
    ServicePackage::create(['service_id' => $this->service2->id, 'duration_minutes' => 60, 'price' => 80000, 'is_active' => true]);
    ServicePackage::create(['service_id' => $this->service2->id, 'duration_minutes' => 90, 'price' => 120000, 'is_active' => false]);

    // Create Inactive Service
    $this->inactiveService = Service::create([
        'name' => 'Layanan Arsip Nonaktif',
        'slug' => 'layanan-arsip-nonaktif',
        'description' => 'Layanan yang sedang tidak beroperasi.',
        'is_active' => false,
    ]);
    ServicePackage::create(['service_id' => $this->inactiveService->id, 'duration_minutes' => 60, 'price' => 70000, 'is_active' => true]);
});

test('customer can view active services and starting prices on dashboard', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.dashboard'));

    $response->assertOk()
        ->assertSee('Full Body Massage')
        ->assertSee('Pijatan menyeluruh')
        ->assertSee('Refleksi Kaki')
        ->assertSee('Rp 100.000')
        ->assertSee('Rp 80.000')
        ->assertDontSee('Layanan Arsip Nonaktif');
});

test('customer can view full catalog index page with active services and packages (CUS-03)', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.catalog.index'));

    $response->assertOk()
        ->assertSee('Pilihan Layanan Pijat')
        ->assertSee('Full Body Massage')
        ->assertSee('60 Menit')
        ->assertSee('90 Menit')
        ->assertSee('120 Menit')
        ->assertSee('Rp 100.000')
        ->assertSee('Rp 140.000')
        ->assertSee('Rp 180.000')
        ->assertSee('Refleksi Kaki')
        ->assertDontSee('Layanan Arsip Nonaktif');
});

test('inactive service packages are excluded from catalog display', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.catalog.index'));

    $response->assertOk()
        // 90 minutes package for service2 was inactive (Rp 120.000)
        ->assertDontSee('Rp 120.000');
});

test('customer can view service detail page with duration packages and fixed prices (CUS-03)', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.catalog.show', $this->service1));

    $response->assertOk()
        ->assertSee('Full Body Massage')
        ->assertSee('Pijatan menyeluruh untuk meredakan ketegangan otot dan stres.')
        ->assertSee('60 Menit')
        ->assertSee('90 Menit')
        ->assertSee('120 Menit')
        ->assertSee('Rp 100.000')
        ->assertSee('Rp 140.000')
        ->assertSee('Rp 180.000')
        ->assertSee('Bebas Ongkir')
        ->assertSee('Protokol Gender')
        ->assertSee('Tanpa Add-on');
});

test('accessing inactive service detail returns 404', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('pelanggan.catalog.show', $this->inactiveService));

    $response->assertNotFound();

});
