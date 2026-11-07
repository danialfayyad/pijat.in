<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

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

test('models using LogsActivity trait automatically record audit logs on crud', function () {
    $this->actingAs($this->superadmin);

    // 1. Create Bank
    $bank = Bank::create([
        'name' => 'Bank Mandiri Syariah',
        'code' => 'BMS',
        'is_active' => true,
    ]);

    $createLog = AuditLog::where('auditable_type', Bank::class)
        ->where('auditable_id', $bank->id)
        ->where('action', 'create')
        ->first();

    expect($createLog)->not->toBeNull()
        ->and($createLog->module)->toBe('bank')
        ->and($createLog->user_id)->toBe($this->superadmin->id)
        ->and($createLog->new_values['name'])->toBe('Bank Mandiri Syariah');

    // 2. Update Bank
    $bank->update([
        'name' => 'Bank Syariah Indonesia',
        'code' => 'BSI',
    ]);

    $updateLog = AuditLog::where('auditable_type', Bank::class)
        ->where('auditable_id', $bank->id)
        ->where('action', 'update')
        ->latest('id')
        ->first();

    expect($updateLog)->not->toBeNull()
        ->and($updateLog->old_values['name'])->toBe('Bank Mandiri Syariah')
        ->and($updateLog->new_values['name'])->toBe('Bank Syariah Indonesia');

    // 3. Delete Bank
    $bank->delete();

    $deleteLog = AuditLog::where('auditable_type', Bank::class)
        ->where('auditable_id', $bank->id)
        ->where('action', 'delete')
        ->latest('id')
        ->first();

    expect($deleteLog)->not->toBeNull()
        ->and($deleteLog->old_values['code'])->toBe('BSI');
});

test('auth login and logout events automatically record audit logs', function () {
    // Fire Login Event
    event(new Login('web', $this->admin, false));

    $loginLog = AuditLog::where('module', 'auth')
        ->where('action', 'login')
        ->where('user_id', $this->admin->id)
        ->latest('id')
        ->first();

    expect($loginLog)->not->toBeNull()
        ->and($loginLog->new_values['email'])->toBe($this->admin->email);

    // Fire Logout Event
    event(new Logout('web', $this->admin));

    $logoutLog = AuditLog::where('module', 'auth')
        ->where('action', 'logout')
        ->where('user_id', $this->admin->id)
        ->latest('id')
        ->first();

    expect($logoutLog)->not->toBeNull()
        ->and($logoutLog->new_values['email'])->toBe($this->admin->email);
});

test('cleanup command prunes audit logs older than retention period', function () {
    $oldDate = now()->subDays(100);
    $freshDate = now()->subDays(10);

    // Create old log
    $oldLog = AuditLog::create([
        'user_id' => $this->superadmin->id,
        'module' => 'bank',
        'action' => 'create',
        'created_at' => $oldDate,
    ]);

    // Create fresh log
    $freshLog = AuditLog::create([
        'user_id' => $this->superadmin->id,
        'module' => 'bank',
        'action' => 'create',
        'created_at' => $freshDate,
    ]);

    // Run cleanup command
    $this->artisan('app:cleanup-expired-data --days=30')
        ->assertSuccessful();

    // Old log pruned, fresh log kept
    expect(AuditLog::find($oldLog->id))->toBeNull();
    expect(AuditLog::find($freshLog->id))->not->toBeNull();
});
