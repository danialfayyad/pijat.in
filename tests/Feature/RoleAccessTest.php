<?php

use App\Enums\Role;
use App\Models\Region;
use App\Models\User;

test('each role is redirected to its own dashboard after login', function (Role $role, string $routeName) {
    $user = User::factory()->create(['role' => $role]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route($routeName, absolute: false));
})->with([
    'superadmin' => [Role::Superadmin, 'superadmin.dashboard'],
    'admin' => [Role::Admin, 'admin.dashboard'],
    'pelanggan' => [Role::Pelanggan, 'pelanggan.dashboard'],
    'mitra' => [Role::Mitra, 'mitra.dashboard'],
]);

test('users can only access dashboards allowed for their role', function () {
    $region = Region::factory()->create();
    $admin = User::factory()->create(['role' => Role::Admin]);
    $admin->assignedRegions()->attach($region->id);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('superadmin.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('mitra.dashboard'))->assertForbidden();
});

test('public registration always creates a customer account', function () {
    $this->post('/register', [
        'nik' => '9876543210987654',
        'name' => 'New Customer',
        'gender' => 'perempuan',
        'phone' => '089876543210',
        'email' => 'customer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => Role::Superadmin->value,
    ])->assertRedirect(route('pelanggan.dashboard', absolute: false));

    expect(User::where('email', 'customer@example.com')->first()->role)->toBe(Role::Pelanggan);
});
