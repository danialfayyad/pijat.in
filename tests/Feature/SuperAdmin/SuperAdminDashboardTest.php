<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\Region;
use App\Models\User;

test('superadmin can access national platform dashboard', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

    $response->assertOk()
        ->assertSee('Dashboard Platform Nasional')
        ->assertSee('Total GMV')
        ->assertSee('Tren Transaksi')
        ->assertSee('Wilayah Teraktif');
});

test('regular admin and other roles cannot access superadmin dashboard', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    $this->actingAs($admin)
        ->get(route('superadmin.dashboard'))
        ->assertForbidden();

    $this->actingAs($customer)
        ->get(route('superadmin.dashboard'))
        ->assertForbidden();
});

test('unauthenticated user is redirected to login', function () {
    $this->get(route('superadmin.dashboard'))
        ->assertRedirect(route('login'));
});

test('dashboard accurately presents completed order gmv and commission', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $region = Region::factory()->create(['status' => 'aktif']);

    Order::factory()->create([
        'region_id' => $region->id,
        'status' => OrderStatus::Selesai,
        'total_price' => 200000,
        'commission_percent' => 20,
    ]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

    $response->assertOk()
        ->assertSee('Rp 200.000')
        ->assertSee('Rp 40.000');
});

test('dashboard warns when active region has no assigned admin', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $orphanRegion = Region::factory()->create([
        'name' => 'Wilayah Khusus Belum Terisi',
        'status' => 'aktif',
    ]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

    $response->assertOk()
        ->assertSee('Wilayah Khusus Belum Terisi')
        ->assertSee('Tugaskan');
});
