<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\AdminRegionAssignment;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\MasterWilayahSeeder;

test('MasterWilayahSeeder seeds key active regions with spatial boundaries', function () {
    $this->seed(MasterWilayahSeeder::class);

    $jember = Region::where('name', 'like', '%Jember%')->first();
    expect($jember)->not->toBeNull()
        ->and($jember->status)->toBe('aktif')
        ->and($jember->timezone)->toBe('Asia/Jakarta');

    $jogja = Region::where('name', 'like', '%Yogyakarta%')->first();
    expect($jogja)->not->toBeNull()
        ->and($jogja->status)->toBe('aktif');

    $surabaya = Region::where('name', 'like', '%Surabaya%')->first();
    expect($surabaya)->not->toBeNull()
        ->and($surabaya->status)->toBe('aktif');

    // Pastikan query spasial ST_Contains bekerja pada wilayah yang di-seed
    $containsJember = Region::containsPoint(-8.1720, 113.7000)->where('id', $jember->id)->exists();
    expect($containsJember)->toBeTrue();
});

test('AdminSeeder seeds superadmin and regional admins assigned to their regions', function () {
    $this->seed(MasterWilayahSeeder::class);
    $this->seed(AdminSeeder::class);

    // 1. Verifikasi Superadmin
    $superadmin = User::where('email', 'superadmin@pijatin.test')->first();
    expect($superadmin)->not->toBeNull()
        ->and($superadmin->role)->toBe(Role::Superadmin)
        ->and($superadmin->status)->toBe(UserStatus::Terverifikasi);

    // 2. Verifikasi Admin Jember
    $adminJember = User::where('email', 'admin@pijatin.test')->first();
    expect($adminJember)->not->toBeNull()
        ->and($adminJember->role)->toBe(Role::Admin)
        ->and($adminJember->status)->toBe(UserStatus::Terverifikasi);

    $assignmentJember = AdminRegionAssignment::where('user_id', $adminJember->id)->first();
    expect($assignmentJember)->not->toBeNull()
        ->and($assignmentJember->can_handle_emergency)->toBeTrue()
        ->and($assignmentJember->region->name)->toContain('Jember');

    // 3. Verifikasi Admin Yogyakarta
    $adminJogja = User::where('email', 'admin.jogja@pijatin.test')->first();
    expect($adminJogja)->not->toBeNull()
        ->and($adminJogja->role)->toBe(Role::Admin);

    $assignmentJogja = AdminRegionAssignment::where('user_id', $adminJogja->id)->first();
    expect($assignmentJogja)->not->toBeNull()
        ->and($assignmentJogja->region->name)->toContain('Yogyakarta');
});
