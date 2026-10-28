<?php

use App\Enums\Role;
use App\Models\Region;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('superadmin can create a region with spatial coordinates and initial nonaktif status', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    // Bandung coordinates polygon
    $coords = [
        [107.55, -6.95],
        [107.65, -6.95],
        [107.65, -6.85],
        [107.55, -6.85],
        [107.55, -6.95],
    ];

    $response = $this->actingAs($superadmin)->post(route('superadmin.regions.store'), [
        'name' => 'Kota Bandung',
        'coordinates' => json_encode($coords),
        'timezone' => 'Asia/Jakarta',
    ]);

    $response->assertRedirect(route('superadmin.regions.index'));
    $this->assertDatabaseHas('regions', [
        'name' => 'Kota Bandung',
        'status' => 'nonaktif',
        'timezone' => 'Asia/Jakarta',
    ]);
});

test('system rejects overlapping region boundaries (SA-06)', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $regionService = app(RegionService::class);

    // Create Region 1
    $coords1 = [
        [107.0, -6.0],
        [107.5, -6.0],
        [107.5, -5.5],
        [107.0, -5.5],
        [107.0, -6.0],
    ];
    $regionService->createRegion('Wilayah A', $coords1, 'Asia/Jakarta', 'nonaktif');

    // Attempt to create Region 2 overlapping with Region 1
    $coords2 = [
        [107.2, -6.2],
        [107.7, -6.2],
        [107.7, -5.7],
        [107.2, -5.7],
        [107.2, -6.2],
    ];

    $response = $this->actingAs($superadmin)->post(route('superadmin.regions.store'), [
        'name' => 'Wilayah B (Overlap)',
        'coordinates' => json_encode($coords2),
        'timezone' => 'Asia/Jakarta',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseMissing('regions', ['name' => 'Wilayah B (Overlap)']);
});

test('system prevents activation of a region without at least 1 assigned admin (SA-06)', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $regionService = app(RegionService::class);

    $coords = [
        [108.0, -7.0],
        [108.5, -7.0],
        [108.5, -6.5],
        [108.0, -6.5],
        [108.0, -7.0],
    ];
    $region = $regionService->createRegion('Wilayah Cirebon', $coords);

    // Attempt activation with 0 admins
    $response = $this->actingAs($superadmin)->post(route('superadmin.regions.activate', $region));
    $response->assertSessionHasErrors('activation');
    expect($region->fresh()->status)->toBe('nonaktif');

    // Assign 1 admin
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $region);

    // Verify admin automatically has emergency responsibility
    expect($admin->canHandleEmergency($region->id))->toBeTrue();

    // Now activation succeeds!
    $response = $this->actingAs($superadmin)->post(route('superadmin.regions.activate', $region));
    $response->assertRedirect();
    expect($region->fresh()->status)->toBe('aktif');
});

test('superadmin can assign and revoke admin from regions with safeguards (SA-02)', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService = app(RegionService::class);

    $coords = [
        [109.0, -7.0],
        [109.5, -7.0],
        [109.5, -6.5],
        [109.0, -6.5],
        [109.0, -7.0],
    ];
    $region = $regionService->createRegion('Wilayah Semarang', $coords);

    // Assign admin with emergency permission
    $this->actingAs($superadmin)->post(route('superadmin.admins.assign'), [
        'user_id' => $admin->id,
        'region_id' => $region->id,
        'can_handle_emergency' => 1,
    ])->assertRedirect();

    $this->assertDatabaseHas('admin_region_assignments', [
        'user_id' => $admin->id,
        'region_id' => $region->id,
        'can_handle_emergency' => true,
    ]);

    // Activate region
    $regionService->activateRegion($region);

    // Attempt to revoke the ONLY admin on active region (must be rejected)
    $response = $this->actingAs($superadmin)->post(route('superadmin.admins.revoke'), [
        'user_id' => $admin->id,
        'region_id' => $region->id,
    ]);
    $response->assertSessionHasErrors('assignment');
    $this->assertDatabaseHas('admin_region_assignments', ['user_id' => $admin->id, 'region_id' => $region->id]);

    // Add a second admin with emergency duty
    $admin2 = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin2, $region, canHandleEmergency: true);

    // Now revoking first admin succeeds
    $this->actingAs($superadmin)->post(route('superadmin.admins.revoke'), [
        'user_id' => $admin->id,
        'region_id' => $region->id,
    ])->assertRedirect();

    $this->assertDatabaseMissing('admin_region_assignments', ['user_id' => $admin->id, 'region_id' => $region->id]);
});

test('superadmin can batch revoke multiple regions from an admin simultaneously', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService = app(RegionService::class);

    $coordsA = [
        [110.0, -7.0],
        [110.4, -7.0],
        [110.4, -6.6],
        [110.0, -6.6],
        [110.0, -7.0],
    ];

    $coordsB = [
        [111.0, -7.0],
        [111.4, -7.0],
        [111.4, -6.6],
        [111.0, -6.6],
        [111.0, -7.0],
    ];

    $regionA = $regionService->createRegion('Region A', $coordsA);
    $regionB = $regionService->createRegion('Region B', $coordsB);

    $regionService->assignAdmin($admin, $regionA);
    $regionService->assignAdmin($admin, $regionB);

    expect($admin->fresh()->assignedRegions)->toHaveCount(2);

    $this->actingAs($superadmin)->post(route('superadmin.admins.revoke'), [
        'user_id' => $admin->id,
        'region_ids' => [$regionA->id, $regionB->id],
    ])->assertRedirect();

    expect($admin->fresh()->assignedRegions)->toHaveCount(0);
});

test('admin with assigned region can view dashboard and filter by region (ADM-01, ADM-15)', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService = app(RegionService::class);

    $coords1 = [
        [110.0, -7.0],
        [110.4, -7.0],
        [110.4, -6.6],
        [110.0, -6.6],
        [110.0, -7.0],
    ];
    $coords2 = [
        [110.5, -7.0],
        [110.9, -7.0],
        [110.9, -6.6],
        [110.5, -6.6],
        [110.5, -7.0],
    ];

    $regionA = $regionService->createRegion('Solo', $coords1);
    $regionB = $regionService->createRegion('Yogya', $coords2);

    $regionService->assignAdmin($admin, $regionA, true);
    $regionService->assignAdmin($admin, $regionB, false);

    // View dashboard (all assigned regions)
    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertOk();
    $response->assertSee('Solo');
    $response->assertSee('Yogya');

    // Filter by Solo
    $responseSolo = $this->actingAs($admin)->get(route('admin.dashboard', ['region_id' => $regionA->id]));
    $responseSolo->assertOk();
    $responseSolo->assertSee('Wilayah: Solo');
});

test('admin without assigned region is forbidden on dashboard (ADM-12, SYS-12)', function () {
    $adminWithoutRegion = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($adminWithoutRegion)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin cannot access data for a region they are not assigned to (ADM-12, SYS-12)', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService = app(RegionService::class);

    $coords1 = [
        [111.0, -7.0],
        [111.4, -7.0],
        [111.4, -6.6],
        [111.0, -6.6],
        [111.0, -7.0],
    ];
    $coords2 = [
        [111.5, -7.0],
        [111.9, -7.0],
        [111.9, -6.6],
        [111.5, -6.6],
        [111.5, -7.0],
    ];

    $assignedRegion = $regionService->createRegion('Wilayah Milik Admin', $coords1);
    $unauthorizedRegion = $regionService->createRegion('Wilayah Lain', $coords2);

    $regionService->assignAdmin($admin, $assignedRegion);

    // Access unauthorized region parameter -> 403 Forbidden
    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['region_id' => $unauthorizedRegion->id]))
        ->assertForbidden();
});

test('spatial point in polygon correctly identifies containing region (SYS-10)', function () {
    $regionService = app(RegionService::class);

    // Bandung Box: Lng 107.50 to 107.70, Lat -6.95 to -6.85
    $coords = [
        [107.50, -6.95],
        [107.70, -6.95],
        [107.70, -6.85],
        [107.50, -6.85],
        [107.50, -6.95],
    ];
    $region = $regionService->createRegion('Bandung Metropolitan', $coords);
    $regionService->assignAdmin(User::factory()->create(['role' => Role::Admin]), $region, true);
    $regionService->activateRegion($region);

    // Inside point (Gedung Sate: -6.9003, 107.6186)
    $found = $regionService->findActiveRegionByCoordinates(latitude: -6.9003, longitude: 107.6186);
    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($region->id)
        ->and($found->name)->toBe('Bandung Metropolitan');

    // Outside point (Jakarta: -6.2088, 106.8456)
    $outside = $regionService->findActiveRegionByCoordinates(latitude: -6.2088, longitude: 106.8456);
    expect($outside)->toBeNull();
});

test('superadmin can fetch preset provinces and cities for all Indonesian regencies', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.regions.presets'));
    $response->assertOk()
        ->assertJsonStructure([
            'provinces',
            'cities',
        ]);

    $data = $response->json();
    expect(count($data['provinces']))->toBeGreaterThanOrEqual(30)
        ->and(count($data['cities']))->toBeGreaterThanOrEqual(500);
});

test('superadmin can create a region using official GeoJSON MultiPolygon boundary', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    // Sample GeoJSON MultiPolygon for Kota Surabaya
    $multiPolygon = [
        'type' => 'MultiPolygon',
        'coordinates' => [
            [
                [
                    [112.70, -7.20],
                    [112.80, -7.20],
                    [112.80, -7.30],
                    [112.70, -7.30],
                    [112.70, -7.20],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($superadmin)->post(route('superadmin.regions.store'), [
        'name' => 'KOTA SURABAYA',
        'geojson' => json_encode($multiPolygon),
        'timezone' => 'Asia/Jakarta',
    ]);

    $response->assertRedirect(route('superadmin.regions.index'));
    $this->assertDatabaseHas('regions', [
        'name' => 'KOTA SURABAYA',
        'status' => 'nonaktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    $region = Region::where('name', 'KOTA SURABAYA')->first();
    expect($region)->not->toBeNull();

    // Verify point-in-polygon works on the saved MultiPolygon
    $contains = Region::containsPoint(-7.25, 112.75)->where('id', $region->id)->exists();
    expect($contains)->toBeTrue();
});

test('superadmin can batch assign multiple regions to an admin simultaneously', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService = app(RegionService::class);

    $regionA = $regionService->createRegion('Kota Solo', [[110.8, -7.6], [110.9, -7.6], [110.9, -7.5], [110.8, -7.5], [110.8, -7.6]]);
    $regionB = $regionService->createRegion('Kota Boyolali', [[110.5, -7.6], [110.6, -7.6], [110.6, -7.5], [110.5, -7.5], [110.5, -7.6]]);
    $regionC = $regionService->createRegion('Kota Klaten', [[110.5, -7.8], [110.6, -7.8], [110.6, -7.7], [110.5, -7.7], [110.5, -7.8]]);

    $response = $this->actingAs($superadmin)->post(route('superadmin.admins.assign'), [
        'user_id' => $admin->id,
        'region_ids' => [$regionA->id, $regionB->id, $regionC->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('admin_region_assignments', ['user_id' => $admin->id, 'region_id' => $regionA->id]);
    $this->assertDatabaseHas('admin_region_assignments', ['user_id' => $admin->id, 'region_id' => $regionB->id]);
    $this->assertDatabaseHas('admin_region_assignments', ['user_id' => $admin->id, 'region_id' => $regionC->id]);
    expect($admin->assignedRegions()->count())->toBe(3);
});
