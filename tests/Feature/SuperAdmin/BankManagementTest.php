<?php

use App\Enums\Role;
use App\Models\Bank;
use App\Models\User;

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'name' => 'Superadmin Master',
        'role' => Role::Superadmin,
        'email_verified_at' => now(),
    ]);

    $this->admin = User::factory()->create([
        'name' => 'Admin Regional',
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
});

test('non-superadmin cannot access bank management page', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.banks.index'))
        ->assertForbidden();
});

test('superadmin can view bank management page and see sidebar menu', function () {
    $bank = Bank::create([
        'name' => 'Bank Central Asia',
        'code' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'PT Pijat In Indonesia',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.banks.index'));

    $response->assertOk()
        ->assertSee('Master Rekening &amp; Bank Pencairan', false)
        ->assertSee('Master Rekening Bank')
        ->assertSee('Bank Central Asia')
        ->assertSee('BCA');
});

test('superadmin can create a new bank manually via form', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.banks.store'), [
            'name' => 'Bank Mandiri',
            'code' => 'mandiri',
            'account_number' => '9876543210',
            'account_holder_name' => 'PT Pijat In Mandiri',
            'notes' => 'Rekening operasional settlement',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('superadmin.banks.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('banks', [
        'name' => 'Bank Mandiri',
        'code' => 'MANDIRI',
        'account_number' => '9876543210',
        'account_holder_name' => 'PT Pijat In Mandiri',
        'is_active' => true,
    ]);
});

test('bank code must be unique and properly formatted', function () {
    Bank::create([
        'name' => 'Bank Rakyat Indonesia',
        'code' => 'BRI',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.banks.store'), [
            'name' => 'BRI Syariah',
            'code' => 'bri', // duplikat case-insensitive setelah dicek rule
        ]);

    $response->assertSessionHasErrors(['code']);
});

test('superadmin can update an existing bank', function () {
    $bank = Bank::create([
        'name' => 'Bank BNI',
        'code' => 'BNI',
        'account_number' => '111222333',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.banks.update', $bank), [
            'name' => 'Bank Negara Indonesia',
            'code' => 'BNI',
            'account_number' => '999888777',
            'account_holder_name' => 'PT Pijat In Cabang',
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('superadmin.banks.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('banks', [
        'id' => $bank->id,
        'name' => 'Bank Negara Indonesia',
        'account_number' => '999888777',
    ]);
});

test('superadmin can toggle active status of a bank', function () {
    $bank = Bank::create([
        'name' => 'Bank Permata',
        'code' => 'PERMATA',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->post(route('superadmin.banks.toggle', $bank));

    $response->assertRedirect(route('superadmin.banks.index'));

    expect($bank->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->superadmin)
        ->post(route('superadmin.banks.toggle', $bank));

    expect($bank->fresh()->is_active)->toBeTrue();
});

test('superadmin can delete bank without withdrawal history', function () {
    $bank = Bank::create([
        'name' => 'Bank Danamon',
        'code' => 'DANAMON',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('superadmin.banks.destroy', $bank));

    $response->assertRedirect(route('superadmin.banks.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('banks', [
        'id' => $bank->id,
    ]);
});
