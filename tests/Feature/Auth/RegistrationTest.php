<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'nik' => '1234567890123456',
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
