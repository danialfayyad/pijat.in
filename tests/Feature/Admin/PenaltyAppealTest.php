<?php

use App\Enums\AppealStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\AdminRegionAssignment;
use App\Models\PenaltyAppeal;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

test('admin can view appeals list scoped to their assigned region', function () {
    $mitraSurabaya = User::factory()->create([
        'name' => 'Mitra Surabaya',
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 60,
    ]);

    $appealSurabaya = PenaltyAppeal::create([
        'user_id' => $mitraSurabaya->id,
        'region_id' => $this->region1->id,
        'statement_letter_path' => 'appeals/letter1.pdf',
        'reason' => 'Saya terlambat karena kecelakaan motor di jalan.',
        'status' => AppealStatus::Menunggu,
    ]);

    $mitraMalang = User::factory()->create([
        'name' => 'Mitra Malang',
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 60,
    ]);

    $appealMalang = PenaltyAppeal::create([
        'user_id' => $mitraMalang->id,
        'region_id' => $this->region2->id,
        'statement_letter_path' => 'appeals/letter2.pdf',
        'reason' => 'Klarifikasi pesanan Malang.',
        'status' => AppealStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->adminRegion1)->get(route('admin.appeals.index'));

    $response->assertOk()
        ->assertSee('Tinjauan Banding Sanksi')
        ->assertSee($appealSurabaya->appeal_code)
        ->assertSee('Mitra Surabaya')
        ->assertDontSee($appealMalang->appeal_code)
        ->assertDontSee('Mitra Malang');
});

test('superadmin can view appeals across all regions', function () {
    $user1 = User::factory()->create(['name' => 'Pemohon 1']);
    $user2 = User::factory()->create(['name' => 'Pemohon 2']);

    $appeal1 = PenaltyAppeal::create([
        'user_id' => $user1->id,
        'region_id' => $this->region1->id,
        'statement_letter_path' => 'appeals/letter1.pdf',
        'reason' => 'Alasan 1',
        'status' => AppealStatus::Menunggu,
    ]);

    $appeal2 = PenaltyAppeal::create([
        'user_id' => $user2->id,
        'region_id' => $this->region2->id,
        'statement_letter_path' => 'appeals/letter2.pdf',
        'reason' => 'Alasan 2',
        'status' => AppealStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->superadmin)->get(route('admin.appeals.index'));

    $response->assertOk()
        ->assertSee($appeal1->appeal_code)
        ->assertSee($appeal2->appeal_code);
});

test('admin can view appeal detail in assigned region but forbidden for other regions', function () {
    $user1 = User::factory()->create(['name' => 'Mitra Surabaya']);
    $appealSurabaya = PenaltyAppeal::create([
        'user_id' => $user1->id,
        'region_id' => $this->region1->id,
        'statement_letter_path' => 'appeals/surabaya.pdf',
        'reason' => 'Surat pernyataan dan bukti pendukung',
        'status' => AppealStatus::Menunggu,
    ]);

    $user2 = User::factory()->create(['name' => 'Mitra Malang']);
    $appealMalang = PenaltyAppeal::create([
        'user_id' => $user2->id,
        'region_id' => $this->region2->id,
        'statement_letter_path' => 'appeals/malang.pdf',
        'reason' => 'Surat pernyataan Malang',
        'status' => AppealStatus::Menunggu,
    ]);

    // Assigned region OK
    $resSurabaya = $this->actingAs($this->adminRegion1)->get(route('admin.appeals.show', $appealSurabaya));
    $resSurabaya->assertOk()
        ->assertSee($appealSurabaya->appeal_code)
        ->assertSee('Surat pernyataan dan bukti pendukung');

    // Outside region FORBIDDEN
    $resMalang = $this->actingAs($this->adminRegion1)->get(route('admin.appeals.show', $appealMalang));
    $resMalang->assertForbidden();
});

test('admin can approve appeal to reverse penalty points and restore user status', function () {
    $mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 60,
    ]);

    $appeal = PenaltyAppeal::create([
        'user_id' => $mitra->id,
        'region_id' => $this->region1->id,
        'statement_letter_path' => 'appeals/statement.pdf',
        'reason' => 'Klarifikasi telah dilampirkan',
        'status' => AppealStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->adminRegion1)->post(route('admin.appeals.approve', $appeal), [
        'points_to_reverse' => 30,
        'admin_notes' => 'Bukti surat pernyataan dan klarifikasi diterima penuh oleh admin wilayah.',
    ]);

    $response->assertRedirect(route('admin.appeals.show', $appeal));

    $appeal->refresh();
    expect($appeal->status)->toBe(AppealStatus::Disetujui)
        ->and($appeal->points_to_reverse)->toBe(30)
        ->and($appeal->reviewed_by_id)->toBe($this->adminRegion1->id)
        ->and($appeal->admin_notes)->toContain('Bukti surat pernyataan');

    $mitra->refresh();
    // Points decreased from 60 to 30; since 30 < 60, suspended status should be restored to terverifikasi
    expect($mitra->penalty_points)->toBe(30)
        ->and($mitra->status)->toBe(UserStatus::Terverifikasi);

    $this->assertDatabaseHas('penalty_ledgers', [
        'user_id' => $mitra->id,
        'type' => 'appeal_reversal',
        'points' => -30,
        'balance_before' => 60,
        'balance_after' => 30,
        'actor_id' => $this->adminRegion1->id,
    ]);
});

test('admin can reject appeal without altering points', function () {
    $mitra = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::Ditangguhkan,
        'penalty_points' => 60,
    ]);

    $appeal = PenaltyAppeal::create([
        'user_id' => $mitra->id,
        'region_id' => $this->region1->id,
        'statement_letter_path' => 'appeals/statement.pdf',
        'reason' => 'Permohonan banding tidak lengkap',
        'status' => AppealStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->adminRegion1)->post(route('admin.appeals.reject', $appeal), [
        'admin_notes' => 'Bukti yang dilampirkan tidak sesuai dengan fakta lapangan.',
    ]);

    $response->assertRedirect(route('admin.appeals.show', $appeal));

    $appeal->refresh();
    expect($appeal->status)->toBe(AppealStatus::Ditolak)
        ->and($appeal->reviewed_by_id)->toBe($this->adminRegion1->id);

    $mitra->refresh();
    expect($mitra->penalty_points)->toBe(60)
        ->and($mitra->status)->toBe(UserStatus::Ditangguhkan);
});
