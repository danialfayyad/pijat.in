<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register without nik', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'gender' => 'laki-laki',
        'phone' => '081234567890',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('pelanggan.dashboard', absolute: false));
});

test('registration screen does not require nik ktp for customer', function () {
    $response = $this->get('/register');

    $response->assertOk();
    $response->assertDontSee('NIK KTP');
});
