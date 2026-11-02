<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Wilayah Test',
        'role' => Role::Admin,
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]);

    $this->region = Region::factory()->create([
        'name' => 'KOTA BLITAR',
        'status' => 'aktif',
    ]);

    $this->admin->assignedRegions()->attach($this->region->id);
});

test('admin can view profile edit page and assigned regions', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.profile.edit'));

    $response->assertOk()
        ->assertSee('Profil Akun &amp; Kata Sandi', false)
        ->assertSee('Admin Wilayah Test')
        ->assertSee('KOTA BLITAR');
});

test('admin can update password successfully with correct current password', function () {
    $response = $this->actingAs($this->admin)
        ->put(route('admin.profile.password'), [
            'current_password' => 'password123',
            'password' => 'newPassword456!',
            'password_confirmation' => 'newPassword456!',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('status');

    $this->admin->refresh();
    expect(Hash::check('newPassword456!', $this->admin->password))->toBeTrue();

    // Verify audit log
    $log = AuditLog::where('module', 'user')->where('action', 'password_change')->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($this->admin->id);
});

test('password update fails if current password is incorrect', function () {
    $response = $this->actingAs($this->admin)
        ->put(route('admin.profile.password'), [
            'current_password' => 'wrongPassword',
            'password' => 'newPassword456!',
            'password_confirmation' => 'newPassword456!',
        ]);

    $response->assertSessionHasErrors('current_password');

    $this->admin->refresh();
    expect(Hash::check('password123', $this->admin->password))->toBeTrue();
});
