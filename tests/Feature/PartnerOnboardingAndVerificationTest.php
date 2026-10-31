<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Region;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('mitra can register with basic data and receives BELUM_LENGKAP status (MIT-01)', function () {
    $response = $this->post(route('mitra.register'), [
        'name' => 'Budi Santoso',
        'email' => 'budi.mitra@pijatin.test',
        'phone' => '081234567890',
        'gender' => 'laki-laki',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('mitra.onboarding'));
    $this->assertAuthenticated();

    $user = User::where('email', 'budi.mitra@pijatin.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(Role::Mitra)
        ->and($user->status)->toBe(UserStatus::BelumLengkap)
        ->and($user->isBelumLengkap())->toBeTrue();
});

test('mitra cannot submit onboarding if coordinates are outside active service regions (MIT-02)', function () {
    $regionService = app(RegionService::class);
    // Create and activate Bandung Region
    $bandung = $regionService->createRegion(
        'Bandung Kota',
        [[107.55, -6.85], [107.70, -6.85], [107.70, -6.98], [107.55, -6.98], [107.55, -6.85]]
    );
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $bandung);
    $regionService->activateRegion($bandung);

    $mitra = User::factory()->mitra()->belumLengkap()->create();

    // Coordinate in Jakarta (-6.2088, 106.8456), outside Bandung
    $response = $this->actingAs($mitra)->post(route('mitra.onboarding.store'), [
        'nik' => '3273012345670001',
        'latitude' => -6.2088,
        'longitude' => 106.8456,
        'address_text' => 'Jl. Thamrin No. 10 Jakarta Pusat',
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => $mitra->name,
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_file' => UploadedFile::fake()->image('selfie.jpg'),
        'sop_agreement' => '1',
    ]);

    $response->assertSessionHasErrors('latitude');
    expect($mitra->fresh()->status)->toBe(UserStatus::BelumLengkap);
});

test('mitra can complete onboarding with KTP, selfie, bank account, and pin in active region (MIT-02, MIT-03)', function () {
    $regionService = app(RegionService::class);
    $bandung = $regionService->createRegion(
        'Bandung Kota',
        [[107.55, -6.85], [107.70, -6.85], [107.70, -6.98], [107.55, -6.98], [107.55, -6.85]]
    );
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $bandung);
    $regionService->activateRegion($bandung);

    $mitra = User::factory()->mitra()->belumLengkap()->create();

    // Coordinate inside Bandung (-6.9175, 107.6191)
    $response = $this->actingAs($mitra)->post(route('mitra.onboarding.store'), [
        'nik' => '3273012345670002',
        'latitude' => -6.9175,
        'longitude' => 107.6191,
        'address_text' => 'Jl. Asia Afrika No. 50 Bandung',
        'bank_name' => 'Bank Mandiri',
        'account_number' => '1300012345678',
        'account_holder_name' => $mitra->name,
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_file' => UploadedFile::fake()->image('selfie.jpg'),
        'sop_agreement' => '1',
    ]);

    $response->assertRedirect(route('mitra.status'));
    $freshMitra = $mitra->fresh();

    expect($freshMitra->status)->toBe(UserStatus::MenungguVerifikasi)
        ->and($freshMitra->nik)->toBe('3273012345670002')
        ->and($freshMitra->partnerProfile)->not->toBeNull()
        ->and($freshMitra->partnerProfile->region_id)->toBe($bandung->id)
        ->and($freshMitra->primaryBankAccount)->not->toBeNull()
        ->and($freshMitra->primaryBankAccount->bank_name)->toBe('Bank Mandiri')
        ->and($freshMitra->complianceAgreements()->count())->toBe(1);
});

test('mitra with MENUNGGU_VERIFIKASI is directed to status page and blocked from dashboard (MIT-03)', function () {
    $mitra = User::factory()->mitra()->menungguVerifikasi()->create();

    $response = $this->actingAs($mitra)->get(route('dashboard'));
    $response->assertRedirect(route('mitra.status'));

    $dashboardResponse = $this->actingAs($mitra)->get(route('mitra.dashboard'));
    $dashboardResponse->assertRedirect(route('mitra.status'));

    $statusResponse = $this->actingAs($mitra)->get(route('mitra.status'));
    $statusResponse->assertOk()
        ->assertSee('Berkas Anda Sedang Ditinjau')
        ->assertSee('1 x 24 Jam');
});

test('admin with assigned region can view pending partners within their region only (ADM-02, ADM-15)', function () {
    $regionService = app(RegionService::class);
    $bandung = $regionService->createRegion('Bandung', [[107.5, -6.8], [107.7, -6.8], [107.7, -7.0], [107.5, -7.0], [107.5, -6.8]]);
    $surabaya = $regionService->createRegion('Surabaya', [[112.6, -7.2], [112.8, -7.2], [112.8, -7.4], [112.6, -7.4], [112.6, -7.2]]);

    $adminBandung = User::factory()->create(['role' => Role::Admin]);
    $adminSurabaya = User::factory()->create(['role' => Role::Admin]);

    $regionService->assignAdmin($adminBandung, $bandung);
    $regionService->assignAdmin($adminSurabaya, $surabaya);

    $regionService->activateRegion($bandung);
    $regionService->activateRegion($surabaya);

    $mitraBandung = User::factory()->mitra()->menungguVerifikasi()->create(['name' => 'Mitra Bandung']);
    $mitraBandung->partnerProfile()->create([
        'region_id' => $bandung->id,
        'address_text' => 'Bandung Jl. Dago',
        'coordinate' => ['lat' => -6.9, 'lng' => 107.6],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    $mitraSurabaya = User::factory()->mitra()->menungguVerifikasi()->create(['name' => 'Mitra Surabaya']);
    $mitraSurabaya->partnerProfile()->create([
        'region_id' => $surabaya->id,
        'address_text' => 'Surabaya Jl. Pemuda',
        'coordinate' => ['lat' => -7.3, 'lng' => 112.7],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    // Admin Bandung should only see Mitra Bandung
    $responseBandung = $this->actingAs($adminBandung)->get(route('admin.mitra.index'));
    $responseBandung->assertOk()
        ->assertSee('Mitra Bandung')
        ->assertDontSee('Mitra Surabaya');

    // Admin Surabaya should only see Mitra Surabaya
    $responseSurabaya = $this->actingAs($adminSurabaya)->get(route('admin.mitra.index'));
    $responseSurabaya->assertOk()
        ->assertSee('Mitra Surabaya')
        ->assertDontSee('Mitra Bandung');
});

test('admin can approve partner application, setting status to TERVERIFIKASI (ADM-02, ADM-03)', function () {
    $regionService = app(RegionService::class);
    $region = $regionService->createRegion('Solo', [[110.8, -7.5], [110.9, -7.5], [110.9, -7.6], [110.8, -7.6], [110.8, -7.5]]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $region);
    $regionService->activateRegion($region);

    $mitra = User::factory()->mitra()->menungguVerifikasi()->create(['name' => 'Mitra Solo']);
    $mitra->partnerProfile()->create([
        'region_id' => $region->id,
        'address_text' => 'Solo Slamet Riyadi',
        'coordinate' => ['lat' => -7.55, 'lng' => 110.82],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);
    $bank = $mitra->bankAccounts()->create([
        'bank_name' => 'BCA',
        'account_number' => '888999000',
        'account_holder_name' => 'Mitra Solo',
        'is_verified' => false,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.mitra.approve', $mitra));
    $response->assertRedirect(route('admin.mitra.index'));

    $freshMitra = $mitra->fresh();
    expect($freshMitra->status)->toBe(UserStatus::Terverifikasi)
        ->and($freshMitra->isTerverifikasi())->toBeTrue()
        ->and($freshMitra->partnerProfile->verified_at)->not->toBeNull()
        ->and($freshMitra->partnerProfile->verified_by)->toBe($admin->id)
        ->and($bank->fresh()->is_verified)->toBeTrue();
});

test('admin cannot inspect or approve partner outside assigned region (authz safeguard)', function () {
    $regionService = app(RegionService::class);
    $regionA = $regionService->createRegion('Region A', [[100.1, -1.1], [100.2, -1.1], [100.2, -1.2], [100.1, -1.2], [100.1, -1.1]]);
    $regionB = $regionService->createRegion('Region B', [[100.5, -1.5], [100.6, -1.5], [100.6, -1.6], [100.5, -1.6], [100.5, -1.5]]);

    $adminA = User::factory()->create(['role' => Role::Admin]);
    $adminB = User::factory()->create(['role' => Role::Admin]);

    $regionService->assignAdmin($adminA, $regionA);
    $regionService->assignAdmin($adminB, $regionB);
    $regionService->activateRegion($regionA);
    $regionService->activateRegion($regionB);

    $mitraB = User::factory()->mitra()->menungguVerifikasi()->create();
    $mitraB->partnerProfile()->create([
        'region_id' => $regionB->id,
        'address_text' => 'Lokasi B',
        'coordinate' => ['lat' => -1.55, 'lng' => 100.55],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    // Admin A tries to access Mitra B
    $response = $this->actingAs($adminA)->get(route('admin.mitra.show', $mitraB));
    $response->assertForbidden();

    // Admin A tries to approve Mitra B
    $approveResponse = $this->actingAs($adminA)->post(route('admin.mitra.approve', $mitraB));
    $approveResponse->assertSessionHasErrors('approval');
    expect($mitraB->fresh()->status)->toBe(UserStatus::MenungguVerifikasi);
});

test('admin can reject partner application with standard reason code (ADM-02)', function () {
    $regionService = app(RegionService::class);
    $region = $regionService->createRegion('Malang', [[112.6, -7.9], [112.7, -7.9], [112.7, -8.0], [112.6, -8.0], [112.6, -7.9]]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $region);
    $regionService->activateRegion($region);

    $mitra = User::factory()->mitra()->menungguVerifikasi()->create();
    $mitra->partnerProfile()->create([
        'region_id' => $region->id,
        'address_text' => 'Malang Jl. Ijen',
        'coordinate' => ['lat' => -7.95, 'lng' => 112.62],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.mitra.reject', $mitra), [
        'rejection_code' => 'KTP_BLURRED',
        'custom_note' => 'Nama pada KTP buram dan nomor NIK tidak terbaca',
    ]);

    $response->assertRedirect(route('admin.mitra.index'));

    $freshMitra = $mitra->fresh();
    expect($freshMitra->status)->toBe(UserStatus::Ditolak)
        ->and($freshMitra->isDitolak())->toBeTrue()
        ->and($freshMitra->rejection_code)->toBe('KTP_BLURRED')
        ->and($freshMitra->rejection_reason)->toContain('KTP buram');
});

test('verified mitra can toggle active online status (MIT-20)', function () {
    $regionService = app(RegionService::class);
    $region = $regionService->createRegion('Semarang', [[110.3, -6.9], [110.5, -6.9], [110.5, -7.1], [110.3, -7.1], [110.3, -6.9]]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $region);
    $regionService->activateRegion($region);

    $mitra = User::factory()->mitra()->create(['status' => UserStatus::Terverifikasi]);
    $profile = $mitra->partnerProfile()->create([
        'region_id' => $region->id,
        'address_text' => 'Semarang Simpang Lima',
        'coordinate' => ['lat' => -7.0, 'lng' => 110.4],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => false,
    ]);

    // Activate online
    $response = $this->actingAs($mitra)->post(route('mitra.toggle-active'), ['is_active' => '1']);
    $response->assertRedirect();
    expect($profile->fresh()->is_active)->toBeTrue();

    // Toggle back to rest
    $responseRest = $this->actingAs($mitra)->post(route('mitra.toggle-active'), ['is_active' => '0']);
    $responseRest->assertRedirect();
    expect($profile->fresh()->is_active)->toBeFalse();
});

test('unverified mitra cannot toggle active online status', function () {
    $mitra = User::factory()->mitra()->menungguVerifikasi()->create();

    $response = $this->actingAs($mitra)->post(route('mitra.toggle-active'), ['is_active' => '1']);
    $response->assertSessionHasErrors('is_active');
});

test('updating bank account in mitra profile triggers re-verification (MIT-20)', function () {
    $regionService = app(RegionService::class);
    $region = $regionService->createRegion('Yogya', [[110.3, -7.7], [110.4, -7.7], [110.4, -7.9], [110.3, -7.9], [110.3, -7.7]]);
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $region);
    $regionService->activateRegion($region);

    $mitra = User::factory()->mitra()->create(['status' => UserStatus::Terverifikasi]);
    $mitra->partnerProfile()->create([
        'region_id' => $region->id,
        'address_text' => 'Yogya Malioboro',
        'coordinate' => ['lat' => -7.8, 'lng' => 110.35],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
    ]);
    $bank = $mitra->bankAccounts()->create([
        'bank_name' => 'BCA',
        'account_number' => '111222333',
        'account_holder_name' => $mitra->name,
        'is_verified' => true,
    ]);

    // Update bank account number
    $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
        'name' => $mitra->name,
        'phone' => $mitra->phone,
        'bank_name' => 'BCA',
        'account_number' => '999888777', // New account number
        'account_holder_name' => $mitra->name,
    ]);

    $response->assertRedirect(route('mitra.status'));
    expect($mitra->fresh()->status)->toBe(UserStatus::MenungguVerifikasi)
        ->and($bank->fresh()->is_verified)->toBeFalse();
});

test('mitra onboarding renders active cities list and rejects mismatch between selected region and pin coordinates', function () {
    $regionService = app(RegionService::class);
    $jember = $regionService->createRegion(
        'Kabupaten Jember',
        [[113.5, -8.4], [113.8, -8.4], [113.8, -8.1], [113.5, -8.1], [113.5, -8.4]]
    );
    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $jember);
    $regionService->activateRegion($jember);

    $surabaya = $regionService->createRegion(
        'Kota Surabaya',
        [[112.6, -7.2], [112.8, -7.2], [112.8, -7.4], [112.6, -7.4], [112.6, -7.2]]
    );
    $regionService->assignAdmin($admin, $surabaya);
    $regionService->activateRegion($surabaya);

    $mitra = User::factory()->mitra()->belumLengkap()->create();

    // 1. Check onboarding page renders available active cities
    $response = $this->actingAs($mitra)->get(route('mitra.onboarding'));
    $response->assertOk()
        ->assertSee('Kabupaten Jember')
        ->assertSee('Kota Surabaya')
        ->assertSee('Wilayah Layanan');

    // 2. Reject mismatch: selected region is Surabaya, but coordinates are in Jember (-8.2, 113.6)
    $mismatchResponse = $this->actingAs($mitra)->post(route('mitra.onboarding.store'), [
        'nik' => '3509012345670001',
        'region_id' => $surabaya->id,
        'latitude' => -8.25,
        'longitude' => 113.65, // In Jember, not Surabaya
        'address_text' => 'Jl. Kalimantan No. 1 Jember',
        'bank_name' => 'BCA',
        'account_number' => '5432167890',
        'account_holder_name' => $mitra->name,
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_file' => UploadedFile::fake()->image('selfie.jpg'),
        'sop_agreement' => '1',
    ]);

    $mismatchResponse->assertSessionHasErrors('latitude');

    // 3. Valid: selected region is Jember and coordinates are in Jember
    $validResponse = $this->actingAs($mitra)->post(route('mitra.onboarding.store'), [
        'nik' => '3509012345670001',
        'region_id' => $jember->id,
        'latitude' => -8.25,
        'longitude' => 113.65,
        'address_text' => 'Jl. Kalimantan No. 1 Jember',
        'bank_name' => 'BCA',
        'account_number' => '5432167890',
        'account_holder_name' => $mitra->name,
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_file' => UploadedFile::fake()->image('selfie.jpg'),
        'sop_agreement' => '1',
    ]);

    $validResponse->assertRedirect(route('mitra.status'));
    expect($mitra->fresh()->partnerProfile->region_id)->toBe($jember->id);
});

test('mitra can select up to 2 operating regions during onboarding and cannot pick duplicate regions', function () {
    $regionService = app(RegionService::class);
    $regionA = $regionService->createRegion(
        'Wilayah Operasional A',
        [[115.10, -8.10], [115.30, -8.10], [115.30, -8.30], [115.10, -8.30], [115.10, -8.10]]
    );
    $regionB = $regionService->createRegion(
        'Wilayah Operasional B',
        [[115.40, -8.10], [115.60, -8.10], [115.60, -8.30], [115.40, -8.30], [115.40, -8.10]]
    );

    $admin = User::factory()->create(['role' => Role::Admin]);
    $regionService->assignAdmin($admin, $regionA);
    $regionService->assignAdmin($admin, $regionB);
    $regionService->activateRegion($regionA);
    $regionService->activateRegion($regionB);

    $mitra = User::factory()->mitra()->belumLengkap()->create();

    // 1. Validation error if secondary region is the same as primary region
    $duplicateResponse = $this->actingAs($mitra)->post(route('mitra.onboarding.store'), [
        'nik' => '3509012345670002',
        'region_id' => $regionA->id,
        'secondary_region_id' => $regionA->id,
        'latitude' => -8.20,
        'longitude' => 115.20,
        'address_text' => 'Jl. Merdeka No. 2',
        'bank_name' => 'BCA',
        'account_number' => '5432167891',
        'account_holder_name' => $mitra->name,
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_file' => UploadedFile::fake()->image('selfie.jpg'),
        'sop_agreement' => '1',
    ]);

    $duplicateResponse->assertSessionHasErrors('secondary_region_id');

    // 2. Success: Primary region is regionA and secondary region is regionB
    $validResponse = $this->actingAs($mitra)->post(route('mitra.onboarding.store'), [
        'nik' => '3509012345670002',
        'region_id' => $regionA->id,
        'secondary_region_id' => $regionB->id,
        'latitude' => -8.20,
        'longitude' => 115.20,
        'address_text' => 'Jl. Merdeka No. 2',
        'bank_name' => 'BCA',
        'account_number' => '5432167891',
        'account_holder_name' => $mitra->name,
        'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_file' => UploadedFile::fake()->image('selfie.jpg'),
        'sop_agreement' => '1',
    ]);

    $validResponse->assertRedirect(route('mitra.status'));

    $profile = $mitra->fresh()->partnerProfile;
    expect($profile->region_id)->toBe($regionA->id)
        ->and($profile->secondary_region_id)->toBe($regionB->id)
        ->and($profile->getOperatingRegionIds())->toBe([$regionA->id, $regionB->id]);
});
