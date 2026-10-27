<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pelanggan'])->prefix('pelanggan')->name('pelanggan.')->group(function () {
    Route::get('/dashboard', fn () => view('role-dashboard', ['roleLabel' => 'Pelanggan']))->name('dashboard');
});
