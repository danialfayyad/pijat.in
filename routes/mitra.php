<?php

use App\Http\Controllers\Mitra\MitraDashboardController;
use App\Http\Controllers\Mitra\MitraOrderController;
use App\Http\Controllers\Mitra\MitraProfileController;
use App\Http\Controllers\Mitra\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/status', [OnboardingController::class, 'status'])->name('status');
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-active', [MitraDashboardController::class, 'toggleActive'])->name('toggle-active');
    Route::post('/location/ping', [MitraDashboardController::class, 'pingLocation'])->name('location.ping');
    Route::get('/orders/pending-offer', [MitraDashboardController::class, 'pendingOffer'])->name('orders.pending-offer');
    Route::post('/orders/offers/{dispatch}/accept', [MitraDashboardController::class, 'acceptOffer'])->name('orders.offers.accept');
    Route::post('/orders/offers/{dispatch}/reject', [MitraDashboardController::class, 'rejectOffer'])->name('orders.offers.reject');
    Route::get('/profile', [MitraProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [MitraProfileController::class, 'update'])->name('profile.update');

    // Pesanan Terjadwal (FCFS) & Jadwal Saya (MIT-06, MIT-07)
    Route::get('/pesanan-terjadwal', [MitraOrderController::class, 'scheduled'])->name('orders.scheduled');
    Route::post('/pesanan-terjadwal/{order}/ambil', [MitraOrderController::class, 'take'])->name('orders.take');
    Route::get('/jadwal-saya', [MitraOrderController::class, 'mySchedule'])->name('orders.my-schedule');

    // Detail Pesanan, Peta Interaktif & Update Status Layanan
    Route::get('/orders/{order}', [MitraOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [MitraOrderController::class, 'updateStatus'])->name('orders.update-status');
});
