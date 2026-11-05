<?php

use App\Enums\Role;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $role = $user->role;

    if ($role === Role::Mitra) {
        if ($user->isBelumLengkap()) {
            return redirect()->route('mitra.onboarding');
        }
        if ($user->isMenungguVerifikasi() || $user->isDitolak()) {
            return redirect()->route('mitra.status');
        }

        return redirect()->route('mitra.dashboard');
    }

    return redirect()->route(match ($role) {
        Role::Superadmin => 'superadmin.dashboard',
        Role::Admin => 'admin.dashboard',
        Role::Pelanggan => 'pelanggan.dashboard',
    });
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/informasi-pribadi', [ProfileController::class, 'editPersonal'])->name('profile.personal');
    Route::get('/profile/keamanan', [ProfileController::class, 'editSecurity'])->name('profile.security');
    Route::get('/profile/hapus-akun', [ProfileController::class, 'editDeleteAccount'])->name('profile.delete');

    // Web Push Notification Subscriptions (CUS-21, MIT-21, SYS-06)
    Route::get('/push/public-key', [PushSubscriptionController::class, 'publicKey'])->name('push.public-key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

    // In-App User Notifications (Pelanggan & Mitra)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Modular Role-Based Routes
require __DIR__.'/auth.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/admin.php';
require __DIR__.'/mitra.php';
require __DIR__.'/pelanggan.php';
