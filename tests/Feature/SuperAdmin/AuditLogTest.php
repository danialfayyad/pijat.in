<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\User;

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'name' => 'Superadmin Test',
        'role' => Role::Superadmin,
        'email_verified_at' => now(),
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
});

test('non-superadmin cannot access centralized audit logs', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.audit-logs.index'))
        ->assertForbidden();
});

test('superadmin can view audit logs list and filter by module', function () {
    AuditLog::record([
        'user_id' => $this->superadmin->id,
        'module' => 'parameter',
        'action' => 'update',
        'reason' => 'Perubahan uji komisi',
    ]);

    AuditLog::record([
        'user_id' => $this->admin->id,
        'module' => 'region',
        'action' => 'activate',
        'reason' => 'Aktivasi wilayah Blitar',
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.audit-logs.index', ['module' => 'parameter']));

    $response->assertOk()
        ->assertSee('Log Audit Terpusat Sistem')
        ->assertSee('Perubahan uji komisi')
        ->assertDontSee('Aktivasi wilayah Blitar');
});

test('superadmin can view detail json of an audit log', function () {
    $log = AuditLog::record([
        'user_id' => $this->superadmin->id,
        'module' => 'parameter',
        'action' => 'update',
        'old_values' => ['val' => 20],
        'new_values' => ['val' => 25],
        'reason' => 'Catatan penting audit',
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.audit-logs.show', $log));

    $response->assertOk()
        ->assertJson([
            'id' => $log->id,
            'module' => 'parameter',
            'reason' => 'Catatan penting audit',
            'old_values' => ['val' => 20],
            'new_values' => ['val' => 25],
        ]);
});
