<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pelanggan'])->prefix('pelanggan')->name('pelanggan.')->group(function () {
    Route::get('/dashboard', fn () => view('pelanggan.dashboard'))->name('dashboard');
});
