<?php

use App\Enums\Role;
use App\Http\Controllers\ProfileController;
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
});

// Modular Role-Based Routes
require __DIR__.'/auth.php';
require __DIR__.'/superadmin.php';
require __DIR__.'/admin.php';
require __DIR__.'/mitra.php';
require __DIR__.'/pelanggan.php';
