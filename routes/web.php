<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\AdminAssignmentController;
use App\Http\Controllers\SuperAdmin\RegionController;
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

Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('superadmin.regions.index'))->name('dashboard');

    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::get('/regions/presets', [RegionController::class, 'presets'])->name('regions.presets');
    Route::get('/regions/presets/{cityId}', [RegionController::class, 'cityBoundary'])->name('regions.cityBoundary');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::post('/regions/{region}/activate', [RegionController::class, 'activate'])->name('regions.activate');
    Route::post('/regions/{region}/deactivate', [RegionController::class, 'deactivate'])->name('regions.deactivate');

    Route::get('/admins', [AdminAssignmentController::class, 'index'])->name('admins.index');
    Route::post('/admins/assign', [AdminAssignmentController::class, 'assign'])->name('admins.assign');
    Route::post('/admins/revoke', [AdminAssignmentController::class, 'revoke'])->name('admins.revoke');
});

Route::middleware(['auth', 'role:admin,superadmin', 'admin.region'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
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
