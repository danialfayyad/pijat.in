<?php

use App\Enums\Role;
use App\Models\AdminMenuPermission;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin without KATALOG permission cannot access service catalog and receives 403 Forbidden', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->get(route('admin.catalog.index'));

    $response->assertForbidden();
});

test('admin with KATALOG permission can access and view service catalog', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $service = Service::create([
        'name' => 'Refleksi Kaki Relaksasi',
        'description' => 'Pijat refleksi titik saraf kaki.',
        'is_active' => true,
    ]);

    ServicePackage::create([
        'service_id' => $service->id,
        'duration_minutes' => 60,
        'price' => 85000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.catalog.index'));

    $response->assertOk();
    $response->assertSee('Katalog Layanan Pijat');
    $response->assertSee('Refleksi Kaki Relaksasi');
    $response->assertSee('Rp 85.000');
});

test('superadmin can access service catalog automatically without explicit permission row', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    $response = $this->actingAs($superadmin)->get(route('admin.catalog.index'));

    $response->assertOk();
    $response->assertSee('Katalog Layanan Pijat');
});

test('unauthenticated users, customers, and partners are rejected from catalog management', function () {
    $customer = User::factory()->create(['role' => Role::Pelanggan]);
    $mitra = User::factory()->create(['role' => Role::Mitra]);

    // Unauthenticated -> redirect to login
    $this->get(route('admin.catalog.index'))->assertRedirect(route('login'));

    // Customer -> 403
    $this->actingAs($customer)->get(route('admin.catalog.index'))->assertForbidden();

    // Mitra -> 403
    $this->actingAs($mitra)->get(route('admin.catalog.index'))->assertForbidden();
});

test('admin with KATALOG permission can create a new service with duration packages', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $payload = [
        'name' => 'Shiatsu Massage Tradisional',
        'description' => 'Pijat akupresur ala Jepang tanpa minyak.',
        'packages' => [
            ['duration_minutes' => 60, 'price' => 125000],
            ['duration_minutes' => 90, 'price' => 175000],
            ['duration_minutes' => 120, 'price' => 220000],
        ],
    ];

    $response = $this->actingAs($admin)->post(route('admin.catalog.store'), $payload);

    $response->assertRedirect(route('admin.catalog.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('services', [
        'name' => 'Shiatsu Massage Tradisional',
        'slug' => 'shiatsu-massage-tradisional',
        'is_active' => true,
    ]);

    $service = Service::where('slug', 'shiatsu-massage-tradisional')->first();

    $this->assertDatabaseHas('service_packages', [
        'service_id' => $service->id,
        'duration_minutes' => 60,
        'price' => 125000,
    ]);
    $this->assertDatabaseHas('service_packages', [
        'service_id' => $service->id,
        'duration_minutes' => 90,
        'price' => 175000,
    ]);
    $this->assertDatabaseHas('service_packages', [
        'service_id' => $service->id,
        'duration_minutes' => 120,
        'price' => 220000,
    ]);
});

test('admin can update service details and package prices', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $service = Service::create([
        'name' => 'Lulur & Body Scrub',
        'description' => 'Scrub tradisional bengkoang.',
        'is_active' => true,
    ]);

    $pkg = ServicePackage::create([
        'service_id' => $service->id,
        'duration_minutes' => 60,
        'price' => 95000,
        'is_active' => true,
    ]);

    $payload = [
        'name' => 'Lulur & Body Scrub Rempah',
        'description' => 'Scrub herbal rempah alami Yogyakarta.',
        'is_active' => 1,
        'packages' => [
            [
                'id' => $pkg->id,
                'duration_minutes' => 60,
                'price' => 110000,
                'is_active' => 1,
            ],
            [
                'id' => null,
                'duration_minutes' => 90,
                'price' => 150000,
                'is_active' => 1,
            ],
        ],
    ];

    $response = $this->actingAs($admin)->put(route('admin.catalog.update', $service), $payload);

    $response->assertRedirect(route('admin.catalog.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Lulur & Body Scrub Rempah',
    ]);

    $this->assertDatabaseHas('service_packages', [
        'id' => $pkg->id,
        'price' => 110000,
    ]);

    $this->assertDatabaseHas('service_packages', [
        'service_id' => $service->id,
        'duration_minutes' => 90,
        'price' => 150000,
    ]);
});

test('admin can toggle active status of a service and an individual duration package', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $service = Service::create([
        'name' => 'Ear Candle Therapy',
        'is_active' => true,
    ]);

    $pkg = ServicePackage::create([
        'service_id' => $service->id,
        'duration_minutes' => 45,
        'price' => 60000,
        'is_active' => true,
    ]);

    // Toggle service status
    $this->actingAs($admin)->post(route('admin.catalog.toggle', $service))->assertRedirect(route('admin.catalog.index'));
    expect($service->fresh()->is_active)->toBeFalse();

    // Toggle package status
    $this->actingAs($admin)->post(route('admin.catalog.packages.toggle', $pkg))->assertRedirect(route('admin.catalog.index'));
    expect($pkg->fresh()->is_active)->toBeFalse();
});

test('admin can delete a service and cascade delete its duration packages', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $service = Service::create([
        'name' => 'Layanan Sementara',
        'is_active' => true,
    ]);

    $pkg = ServicePackage::create([
        'service_id' => $service->id,
        'duration_minutes' => 60,
        'price' => 50000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.catalog.destroy', $service));

    $response->assertRedirect(route('admin.catalog.index'));
    $this->assertDatabaseMissing('services', ['id' => $service->id]);
    $this->assertDatabaseMissing('service_packages', ['id' => $pkg->id]);
});

test('admin can filter catalog by status=nonaktif to view inactive services', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $activeService = Service::create([
        'name' => 'Pijat Refleksi Aktif',
        'is_active' => true,
    ]);

    $inactiveService = Service::create([
        'name' => 'Lulur Herbal Nonaktif',
        'is_active' => false,
    ]);

    // Active view (default)
    $activeResponse = $this->actingAs($admin)->get(route('admin.catalog.index'));
    $activeResponse->assertOk();
    $activeResponse->assertSee('Pijat Refleksi Aktif');
    $activeResponse->assertDontSee('Lulur Herbal Nonaktif');
    $activeResponse->assertSee('Lihat Katalog Nonaktif');

    // Inactive view (?status=nonaktif)
    $inactiveResponse = $this->actingAs($admin)->get(route('admin.catalog.index', ['status' => 'nonaktif']));
    $inactiveResponse->assertOk();
    $inactiveResponse->assertSee('Lulur Herbal Nonaktif');
    $inactiveResponse->assertDontSee('Pijat Refleksi Aktif');
    $inactiveResponse->assertSee('Kembali ke Katalog Aktif');
});

test('admin toggling active service moves it to inactive view and updates counts', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $service = Service::create([
        'name' => 'Aromaterapi Relaksasi',
        'is_active' => true,
    ]);

    // Initially active in viewData
    $initialView = $this->actingAs($admin)->get(route('admin.catalog.index'));
    expect($initialView->viewData('services')->pluck('name'))->toContain('Aromaterapi Relaksasi');
    expect($initialView->viewData('activeCount'))->toBe(1);
    expect($initialView->viewData('inactiveCount'))->toBe(0);

    // Toggle to inactive
    $this->actingAs($admin)->from(route('admin.catalog.index'))->post(route('admin.catalog.toggle', $service));
    expect($service->fresh()->is_active)->toBeFalse();

    // Now absent from active view
    $activeView = $this->actingAs($admin)->get(route('admin.catalog.index'));
    expect($activeView->viewData('services')->pluck('name'))->not->toContain('Aromaterapi Relaksasi');
    expect($activeView->viewData('activeCount'))->toBe(0);
    expect($activeView->viewData('inactiveCount'))->toBe(1);

    // Present in inactive view
    $inactiveView = $this->actingAs($admin)->get(route('admin.catalog.index', ['status' => 'nonaktif']));
    expect($inactiveView->viewData('services')->pluck('name'))->toContain('Aromaterapi Relaksasi');
    expect($inactiveView->viewData('activeCount'))->toBe(0);
    expect($inactiveView->viewData('inactiveCount'))->toBe(1);
});

test('validation rejects invalid duration or negative prices', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    AdminMenuPermission::create([
        'user_id' => $admin->id,
        'menu_code' => 'KATALOG',
    ]);

    $payload = [
        'name' => '',
        'packages' => [
            ['duration_minutes' => 5, 'price' => -50000],
        ],
    ];

    $response = $this->actingAs($admin)->post(route('admin.catalog.store'), $payload);

    $response->assertSessionHasErrors(['name', 'packages.0.duration_minutes', 'packages.0.price']);
});
