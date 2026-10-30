<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\AdminRegionAssignment;
use App\Models\PenaltyLedger;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPartnerProfile(User $user, Region $region): void
{
    $user->partnerProfile()->create([
        'region_id' => $region->id,
        'address_text' => 'Alamat '.$region->name,
        'coordinate' => ['lat' => -7.25, 'lng' => 112.75],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
    ]);
}

beforeEach(function () {
    $this->region1 = Region::factory()->create([
        'name' => 'Surabaya',
        'status' => 'aktif',
    ]);

    $this->region2 = Region::factory()->create([
        'name' => 'Malang',
        'status' => 'aktif',
    ]);

    $this->superadmin = User::factory()->create([
        'role' => Role::Superadmin,
        'status' => UserStatus::Terverifikasi,
    ]);

    $this->adminRegion1 = User::factory()->create([
        'role' => Role::Admin,
        'status' => UserStatus::Terverifikasi,
    ]);

    AdminRegionAssignment::create([
        'user_id' => $this->adminRegion1->id,
        'region_id' => $this->region1->id,
    ]);
});

test('superadmin can view penalties monitoring dashboard with all users', function () {
    $mitra1 = User::factory()->create([
        'name' => 'Budi Terapis',
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 35,
    ]);
    createPartnerProfile($mitra1, $this->region1);

    $mitra2 = User::factory()->create([
        'name' => 'Siti Terapis',
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 65,
    ]);
    createPartnerProfile($mitra2, $this->region2);

    $response = $this->actingAs($this->superadmin)->get(route('admin.penalties.index'));

    $response->assertOk()
        ->assertSee('Monitoring Poin Sanksi')
        ->assertSee('Budi Terapis')
        ->assertSee('Siti Terapis')
        ->assertSee('35 Poin')
        ->assertSee('65 Poin');
});

test('regional admin only sees penalized users within assigned region', function () {
    $mitraSurabaya = User::factory()->create([
        'name' => 'Mitra Surabaya',
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 40,
    ]);
    createPartnerProfile($mitraSurabaya, $this->region1);

    $mitraMalang = User::factory()->create([
        'name' => 'Mitra Malang',
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 50,
    ]);
    createPartnerProfile($mitraMalang, $this->region2);

    $response = $this->actingAs($this->adminRegion1)->get(route('admin.penalties.index'));

    $response->assertOk()
        ->assertSee('Mitra Surabaya')
        ->assertDontSee('Mitra Malang');
});

test('penalties index filters by status tabs correctly', function () {
    $warningUser = User::factory()->create([
        'name' => 'User Peringatan',
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 35,
    ]);
    createPartnerProfile($warningUser, $this->region1);

    $suspendedUser = User::factory()->create([
        'name' => 'User Tersuspend',
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 75,
    ]);
    createPartnerProfile($suspendedUser, $this->region1);

    // Check warning tab
    $responseWarning = $this->actingAs($this->adminRegion1)->get(route('admin.penalties.index', ['status' => 'peringatan']));
    $responseWarning->assertOk()
        ->assertSee('User Peringatan')
        ->assertDontSee('User Tersuspend');

    // Check suspend tab
    $responseSuspend = $this->actingAs($this->adminRegion1)->get(route('admin.penalties.index', ['status' => 'tersuspend']));
    $responseSuspend->assertOk()
        ->assertSee('User Tersuspend')
        ->assertDontSee('User Peringatan');
});

test('admin can view user penalty detail and audit ledgers', function () {
    $mitra = User::factory()->create([
        'name' => 'Agus Santoso',
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 60,
    ]);
    createPartnerProfile($mitra, $this->region1);

    PenaltyLedger::create([
        'user_id' => $mitra->id,
        'type' => 'violation',
        'points' => 60,
        'balance_before' => 0,
        'balance_after' => 60,
        'reason' => 'Pelanggaran SOP keterlambatan berulang',
    ]);

    $response = $this->actingAs($this->adminRegion1)->get(route('admin.penalties.show', $mitra));

    $response->assertOk()
        ->assertSee('Audit Poin Sanksi: Agus Santoso')
        ->assertSee('Pelanggaran SOP keterlambatan berulang')
        ->assertSee('60 / 150 Poin');
});

test('admin cannot view penalty detail of user outside assigned region', function () {
    $mitraMalang = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 60,
    ]);
    createPartnerProfile($mitraMalang, $this->region2);

    $response = $this->actingAs($this->adminRegion1)->get(route('admin.penalties.show', $mitraMalang));

    $response->assertForbidden();
});

test('admin can manually adjust user penalty points', function () {
    $mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Terverifikasi,
        'penalty_points' => 20,
    ]);
    createPartnerProfile($mitra, $this->region1);

    $response = $this->actingAs($this->adminRegion1)->post(route('admin.penalties.adjust', $mitra), [
        'type' => 'add',
        'points' => 15,
        'reason' => 'Penalti khusus pelanggaran etika berpakaian saat melayani pelanggan',
    ]);

    $response->assertRedirect();

    $mitra->refresh();
    expect($mitra->penalty_points)->toBe(35);

    $this->assertDatabaseHas('penalty_ledgers', [
        'user_id' => $mitra->id,
        'type' => 'admin_adjustment',
        'points' => 15,
        'balance_before' => 20,
        'balance_after' => 35,
        'actor_id' => $this->adminRegion1->id,
    ]);
});
