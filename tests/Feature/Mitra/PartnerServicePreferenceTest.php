<?php

use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Budi Santoso',
        'phone' => '081234567891',
        'gender' => 'pria',
    ]);

    $this->profile = PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Jawa No. 1, Jember',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);

    $this->service1 = Service::factory()->create(['name' => 'Pijat Tradisional', 'is_active' => true]);
    $this->service2 = Service::factory()->create(['name' => 'Refleksi Kaki', 'is_active' => true]);
    $this->service3 = Service::factory()->create(['name' => 'Aromaterapi Spa', 'is_active' => true]);
});

test('MIT-08: secara default seluruh jenis layanan aktif untuk mitra', function () {
    expect($this->profile->isServiceEnabled($this->service1->id))->toBeTrue();
    expect($this->profile->isServiceEnabled($this->service2->id))->toBeTrue();
    expect($this->profile->isServiceEnabled($this->service3->id))->toBeTrue();

    $enabledIds = $this->profile->getEnabledServiceIds();
    expect($enabledIds)->toContain($this->service1->id, $this->service2->id, $this->service3->id);
});

test('MIT-08: mitra dapat membuka halaman profil dan melihat opsi preferensi layanan', function () {
    $response = $this->actingAs($this->mitra)->get(route('mitra.profile'));

    $response->assertOk();
    $response->assertSee('Preferensi Jenis Layanan');
    $response->assertSee('Pijat Tradisional');
    $response->assertSee('Refleksi Kaki');
    $response->assertSee('Aromaterapi Spa');
});

test('MIT-08: mitra dapat menonaktifkan salah satu layanan dan menyimpannya', function () {
    $response = $this->actingAs($this->mitra)->post(route('mitra.profile.update'), [
        'name' => $this->mitra->name,
        'phone' => $this->mitra->phone,
        'services' => [$this->service1->id, $this->service2->id], // nonaktifkan service3
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Profil berhasil diperbarui.');

    expect($this->profile->fresh()->isServiceEnabled($this->service1->id))->toBeTrue();
    expect($this->profile->fresh()->isServiceEnabled($this->service2->id))->toBeTrue();
    expect($this->profile->fresh()->isServiceEnabled($this->service3->id))->toBeFalse();

    $enabledIds = $this->profile->fresh()->getEnabledServiceIds();
    expect($enabledIds)->toContain($this->service1->id, $this->service2->id);
    expect($enabledIds)->not->toContain($this->service3->id);
});

test('MIT-08: validasi menolak jika seluruh layanan dinonaktifkan (minimal 1 harus aktif)', function () {
    $response = $this->actingAs($this->mitra)->post(route('mitra.profile.update'), [
        'name' => $this->mitra->name,
        'phone' => $this->mitra->phone,
        'services' => [], // kosong
    ]);

    $response->assertSessionHasErrors('services');
});
