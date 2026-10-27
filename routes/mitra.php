<?php

use App\Http\Controllers\Mitra\MitraDashboardController;
use App\Http\Controllers\Mitra\MitraProfileController;
use App\Http\Controllers\Mitra\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/status', [OnboardingController::class, 'status'])->name('status');
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-active', [MitraDashboardController::class, 'toggleActive'])->name('toggle-active');
    Route::get('/profile', [MitraProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [MitraProfileController::class, 'update'])->name('profile.update');
});
