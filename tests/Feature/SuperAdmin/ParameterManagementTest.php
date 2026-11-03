<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Parameter;
use App\Models\User;
use Database\Seeders\ParameterSeeder;

beforeEach(function () {
    $this->seed(ParameterSeeder::class);

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

test('non-superadmin cannot access system parameters page', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.parameters.index'))
        ->assertForbidden();
});

test('superadmin can view operational parameters list grouped by categories', function () {
    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.parameters.index'));

    $response->assertOk()
        ->assertSee('Manajemen Parameter Operasional')
        ->assertSee('Keuangan &amp; Bagi Hasil', false)
        ->assertSee('platform_commission_percent')
        ->assertSee('dispatch_max_radius_km');
});

test('superadmin can update parameters with mandatory audit reason', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.parameters.update'), [
            'parameters' => [
                'platform_commission_percent' => '25.00',
                'dispatch_max_radius_km' => '8',
            ],
            'reason' => 'Penyesuaian radius penugasan dan komisi platform sesuai evaluasi Q4',
        ]);

    $response->assertRedirect(route('superadmin.parameters.index'))
        ->assertSessionHas('status');

    expect(Parameter::get('platform_commission_percent'))->toEqual(25.00)
        ->and(Parameter::get('dispatch_max_radius_km'))->toEqual(8);

    // Verify audit log recorded
    $auditLogs = AuditLog::where('module', 'parameter')->get();
    expect($auditLogs)->toHaveCount(2)
        ->and($auditLogs->first()->user_id)->toBe($this->superadmin->id)
        ->and($auditLogs->first()->reason)->toContain('Penyesuaian radius penugasan');
});

test('parameter update rejected if reason is missing or too short', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.parameters.update'), [
            'parameters' => [
                'platform_commission_percent' => '30.00',
            ],
            'reason' => 'test', // < 5 chars
        ]);

    $response->assertSessionHasErrors('reason');
    expect(Parameter::get('platform_commission_percent'))->toEqual(20.00); // Unchanged
});

test('parameter get casts to correct native types', function () {
    expect(Parameter::get('dispatch_max_radius_km'))->toBeInt()
        ->and(Parameter::get('platform_commission_percent'))->toBeFloat()
        ->and(Parameter::get('operational_start_time'))->toBeString();
});
