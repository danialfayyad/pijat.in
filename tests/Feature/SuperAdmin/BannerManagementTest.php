<?php

use App\Enums\Role;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('non-superadmin cannot access banner management page', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $response = $this->actingAs($admin)->get(route('superadmin.banners.index'));

    $response->assertForbidden();
});

test('superadmin can view banner management listing and see existing banners', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    $banner = Banner::factory()->create([
        'title' => 'Diskon Promo Gajian',
        'subtitle' => 'Pijat relaksasi hemat hingga 30%',
        'type' => 'template',
        'is_active' => true,
    ]);

    $response = $this->actingAs($superadmin)->get(route('superadmin.banners.index'));

    $response->assertOk();
    $response->assertSee('Manajemen Banner Promosi Pelanggan');
    $response->assertSee('Diskon Promo Gajian');
    $response->assertSee('Pijat relaksasi hemat hingga 30%');
});

test('superadmin can create a template banner without image', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    $payload = [
        'title' => 'Promo Spesial Awal Tahun',
        'type' => 'template',
        'badge_text' => 'Tahun Baru',
        'subtitle' => 'Voucher Potongan Rp 30.000',
        'description' => 'Khusus pemesanan pijat seluruh Indonesia.',
        'promo_code' => 'TAHUNBARU',
        'action_url' => '/pelanggan/catalog',
        'action_text' => 'Klaim Sekarang',
        'background_color' => 'teal',
        'sort_order' => 1,
        'is_active' => '1',
    ];

    $response = $this->actingAs($superadmin)->post(route('superadmin.banners.store'), $payload);

    $response->assertRedirect(route('superadmin.banners.index'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('banners', [
        'title' => 'Promo Spesial Awal Tahun',
        'promo_code' => 'TAHUNBARU',
        'background_color' => 'teal',
        'is_active' => true,
    ]);
});

test('superadmin can create an image banner with auto compression', function () {
    Storage::fake('public');

    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $fakeImage = UploadedFile::fake()->image('banner_hero.jpg', 1600, 800)->size(3000); // 3MB

    $payload = [
        'title' => 'Poster Festival Spa',
        'type' => 'image',
        'image' => $fakeImage,
        'action_url' => '/pelanggan/catalog',
        'action_text' => 'Lihat Paket',
        'sort_order' => 2,
        'is_active' => '1',
    ];

    $response = $this->actingAs($superadmin)->post(route('superadmin.banners.store'), $payload);

    $response->assertRedirect(route('superadmin.banners.index'));

    $banner = Banner::where('title', 'Poster Festival Spa')->first();
    expect($banner)->not->toBeNull()
        ->and($banner->image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($banner->image_path);
});

test('superadmin can toggle banner active status', function () {
    $superadmin = User::factory()->create(['role' => Role::Superadmin]);

    $banner = Banner::factory()->create([
        'title' => 'Banner Uji Toggle',
        'is_active' => true,
    ]);

    $response = $this->actingAs($superadmin)->post(route('superadmin.banners.toggle', $banner));

    $response->assertRedirect();
    expect($banner->fresh()->is_active)->toBeFalse();

    $response = $this->actingAs($superadmin)->post(route('superadmin.banners.toggle', $banner));
    expect($banner->fresh()->is_active)->toBeTrue();
});

test('superadmin can delete banner and its stored image file', function () {
    Storage::fake('public');

    $superadmin = User::factory()->create(['role' => Role::Superadmin]);
    $storedPath = UploadedFile::fake()->image('delete_me.jpg')->store('banners', 'public');

    $banner = Banner::factory()->create([
        'title' => 'Banner Akan Dihapus',
        'image_path' => $storedPath,
    ]);

    Storage::disk('public')->assertExists($storedPath);

    $response = $this->actingAs($superadmin)->delete(route('superadmin.banners.destroy', $banner));

    $response->assertRedirect(route('superadmin.banners.index'));
    $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    Storage::disk('public')->assertMissing($storedPath);
});

test('customer dashboard displays active banners', function () {
    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    Banner::factory()->create([
        'title' => 'Banner Aktif Di Beranda',
        'subtitle' => 'Promo relaksasi akhir pekan',
        'is_active' => true,
    ]);

    Banner::factory()->create([
        'title' => 'Banner Rahasia Nonaktif',
        'is_active' => false,
    ]);

    $response = $this->actingAs($customer)->get(route('pelanggan.dashboard'));

    $response->assertOk();
    $response->assertSee('Banner Aktif Di Beranda');
    $response->assertSee('Promo relaksasi akhir pekan');
    $response->assertDontSee('Banner Rahasia Nonaktif');
});

test('customer dashboard allows banner without action text to be directly clickable without button', function () {
    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    Banner::factory()->create([
        'title' => 'Banner Tanpa Tombol',
        'subtitle' => 'Pencet di mana saja untuk ke katalog',
        'type' => 'template',
        'action_url' => '/pelanggan/catalog',
        'action_text' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($customer)->get(route('pelanggan.dashboard'));

    $response->assertOk();
    $response->assertSee('href="/pelanggan/catalog"', false);
    $response->assertDontSee('Gunakan Promo');
});
