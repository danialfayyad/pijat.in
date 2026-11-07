<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Parameter;
use App\Models\User;
use Database\Seeders\ParameterSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(ParameterSeeder::class);

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

test('non-superadmin cannot access branding settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('superadmin.branding.index'))
        ->assertForbidden();
});

test('superadmin can view branding settings page and see sidebar menu', function () {
    $response = $this->actingAs($this->superadmin)
        ->get(route('superadmin.branding.index'));

    $response->assertOk()
        ->assertSee('Identitas &amp; Branding Aplikasi', false)
        ->assertSee('Identitas Aplikasi')
        ->assertSee('Pijat.In');
});

test('superadmin can update app name, tagline, and meta description', function () {
    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.branding.update'), [
            'app_name' => 'PijatKuy',
            'app_tagline' => 'Solusi Pijat Panggilan Terbaik',
            'app_description' => 'Aplikasi pemesanan pijat profesional terdekat.',
            'reason' => 'Rebranding nama aplikasi per 2026',
        ]);

    $response->assertRedirect(route('superadmin.branding.index'))
        ->assertSessionHas('status');

    expect(Parameter::get('app_name'))->toBe('PijatKuy')
        ->and(Parameter::get('app_tagline'))->toBe('Solusi Pijat Panggilan Terbaik')
        ->and(Parameter::get('app_description'))->toBe('Aplikasi pemesanan pijat profesional terdekat.');

    // Verify audit log recorded
    $log = AuditLog::where('module', 'parameter')
        ->where('action', 'update')
        ->first();
    expect($log)->not->toBeNull();
});

test('superadmin can upload custom logo and favicon and see them stored on public disk', function () {
    Storage::fake('public');

    $logoFile = UploadedFile::fake()->image('custom_logo.png', 400, 100);
    $faviconFile = UploadedFile::fake()->image('custom_favicon.png', 64, 64);

    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.branding.update'), [
            'app_name' => 'Pijat.In Sehat',
            'app_logo' => $logoFile,
            'app_favicon' => $faviconFile,
            'reason' => 'Upload aset logo dan favicon baru',
        ]);

    $response->assertRedirect(route('superadmin.branding.index'))
        ->assertSessionHas('status');

    $savedLogo = Parameter::get('app_logo');
    $savedFavicon = Parameter::get('app_favicon');

    expect($savedLogo)->not->toBeEmpty()
        ->and($savedFavicon)->not->toBeEmpty();

    Storage::disk('public')->assertExists($savedLogo);
    Storage::disk('public')->assertExists($savedFavicon);

    // Assert that the page displays the uploaded logo
    $page = $this->actingAs($this->superadmin)->get(route('superadmin.branding.index'));
    $page->assertOk()
        ->assertSee(asset('storage/'.$savedLogo));
});

test('superadmin can remove custom logo and favicon to revert to default assets', function () {
    Storage::fake('public');

    // First, upload files
    $logoFile = UploadedFile::fake()->image('test_logo.png', 200, 200);
    $faviconFile = UploadedFile::fake()->image('test_favicon.png', 32, 32);

    $uploadResponse = $this->actingAs($this->superadmin)
        ->put(route('superadmin.branding.update'), [
            'app_name' => 'Pijat.In',
            'app_logo' => $logoFile,
            'app_favicon' => $faviconFile,
        ]);
    $uploadResponse->assertSessionHasNoErrors();

    $savedLogo = Parameter::get('app_logo');
    $savedFavicon = Parameter::get('app_favicon');
    expect($savedLogo)->not->toBeEmpty()
        ->and($savedFavicon)->not->toBeEmpty();

    Storage::disk('public')->assertExists($savedLogo);
    Storage::disk('public')->assertExists($savedFavicon);

    // Now remove them
    $response = $this->actingAs($this->superadmin)
        ->put(route('superadmin.branding.update'), [
            'app_name' => 'Pijat.In',
            'remove_logo' => 1,
            'remove_favicon' => 1,
            'reason' => 'Reset ke logo dan favicon bawaan sistem',
        ]);

    $response->assertRedirect(route('superadmin.branding.index'));

    expect(Parameter::get('app_logo'))->toBe('')
        ->and(Parameter::get('app_favicon'))->toBe('');

    Storage::disk('public')->assertMissing($savedLogo);
    Storage::disk('public')->assertMissing($savedFavicon);
});
