<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

test('redirects to login with error if google credentials not configured', function () {
    Config::set('services.google.client_id', null);
    Config::set('services.google.client_secret', null);

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('redirects to google oauth provider when credentials configured', function () {
    Config::set('services.google.client_id', 'dummy-client-id');
    Config::set('services.google.client_secret', 'dummy-client-secret');

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('accounts.google.com');
});

test('creates a new customer user and logs in via google callback', function () {
    Config::set('services.google.client_id', 'dummy-client-id');
    Config::set('services.google.client_secret', 'dummy-client-secret');

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-id-cust-1');
    $googleUser->shouldReceive('getName')->andReturn('Customer Google');
    $googleUser->shouldReceive('getEmail')->andReturn('cust.google@example.com');
    $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->withSession(['oauth_intended_role' => Role::Pelanggan->value])
        ->get(route('auth.google.callback'));

    $this->assertAuthenticated();

    $user = User::where('email', 'cust.google@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->google_id)->toBe('google-id-cust-1');
    expect($user->role)->toBe(Role::Pelanggan);
    expect($user->status)->toBe(UserStatus::Terverifikasi);

    $response->assertRedirect(route('dashboard'));
});

test('creates a new mitra user with belum_lengkap status via google callback', function () {
    Config::set('services.google.client_id', 'dummy-client-id');
    Config::set('services.google.client_secret', 'dummy-client-secret');

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-id-mitra-1');
    $googleUser->shouldReceive('getName')->andReturn('Mitra Google');
    $googleUser->shouldReceive('getEmail')->andReturn('mitra.google@example.com');
    $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar-mitra.jpg');

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->withSession(['oauth_intended_role' => Role::Mitra->value])
        ->get(route('auth.google.callback'));

    $this->assertAuthenticated();

    $user = User::where('email', 'mitra.google@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->google_id)->toBe('google-id-mitra-1');
    expect($user->role)->toBe(Role::Mitra);
    expect($user->status)->toBe(UserStatus::BelumLengkap);

    $response->assertRedirect(route('dashboard'));
});

test('links existing user by email when logging in with google', function () {
    Config::set('services.google.client_id', 'dummy-client-id');
    Config::set('services.google.client_secret', 'dummy-client-secret');

    $existingUser = User::factory()->create([
        'email' => 'existing.user@example.com',
        'google_id' => null,
    ]);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-id-linked');
    $googleUser->shouldReceive('getName')->andReturn($existingUser->name);
    $googleUser->shouldReceive('getEmail')->andReturn('existing.user@example.com');
    $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.jpg');

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $this->assertAuthenticatedAs($existingUser);

    $existingUser->refresh();
    expect($existingUser->google_id)->toBe('google-id-linked');
    expect($existingUser->avatar)->toBe('https://example.com/new-avatar.jpg');

    $response->assertRedirect(route('dashboard'));
});

test('prevents blocked user from logging in via google', function () {
    Config::set('services.google.client_id', 'dummy-client-id');
    Config::set('services.google.client_secret', 'dummy-client-secret');

    $blockedUser = User::factory()->create([
        'email' => 'blocked.user@example.com',
        'status' => UserStatus::Diblokir,
        'google_id' => 'google-id-blocked',
    ]);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-id-blocked');
    $googleUser->shouldReceive('getName')->andReturn($blockedUser->name);
    $googleUser->shouldReceive('getEmail')->andReturn('blocked.user@example.com');
    $googleUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('prevents suspended user from logging in via google', function () {
    Config::set('services.google.client_id', 'dummy-client-id');
    Config::set('services.google.client_secret', 'dummy-client-secret');

    $suspendedUser = User::factory()->create([
        'email' => 'suspended.user@example.com',
        'status' => UserStatus::Ditangguhkan,
        'suspended_until' => now()->addDays(2),
        'google_id' => 'google-id-suspended',
    ]);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-id-suspended');
    $googleUser->shouldReceive('getName')->andReturn($suspendedUser->name);
    $googleUser->shouldReceive('getEmail')->andReturn('suspended.user@example.com');
    $googleUser->shouldReceive('getAvatar')->andReturn(null);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($googleUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});
