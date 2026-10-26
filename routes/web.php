<?php

use App\Enums\Role;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    return redirect()->route(match ($role) {
        Role::Superadmin => 'superadmin.dashboard',
        Role::Admin => 'admin.dashboard',
        Role::Pelanggan => 'pelanggan.dashboard',
        Role::Mitra => 'mitra.dashboard',
    });
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/superadmin/dashboard', fn () => view('role-dashboard', ['roleLabel' => 'Super Admin']))
        ->middleware('role:superadmin')->name('superadmin.dashboard');
    Route::get('/admin/dashboard', fn () => view('role-dashboard', ['roleLabel' => 'Admin']))
        ->middleware('role:admin,superadmin')->name('admin.dashboard');
    Route::get('/pelanggan/dashboard', fn () => view('role-dashboard', ['roleLabel' => 'Pelanggan']))
        ->middleware('role:pelanggan')->name('pelanggan.dashboard');
    Route::get('/mitra/dashboard', fn () => view('role-dashboard', ['roleLabel' => 'Mitra']))
        ->middleware('role:mitra')->name('mitra.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
