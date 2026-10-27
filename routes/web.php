<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PartnerVerificationController;
use App\Http\Controllers\Mitra\MitraDashboardController;
use App\Http\Controllers\Mitra\MitraProfileController;
use App\Http\Controllers\Mitra\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\AdminAssignmentController;
use App\Http\Controllers\SuperAdmin\RegionController;
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
    Route::get('/mitra/verifikasi', [PartnerVerificationController::class, 'index'])->name('mitra.index');
    Route::get('/mitra/{mitra}', [PartnerVerificationController::class, 'show'])->name('mitra.show');
    Route::post('/mitra/{mitra}/approve', [PartnerVerificationController::class, 'approve'])->name('mitra.approve');
    Route::post('/mitra/{mitra}/reject', [PartnerVerificationController::class, 'reject'])->name('mitra.reject');
});

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/status', [OnboardingController::class, 'status'])->name('status');
    Route::get('/dashboard', [MitraDashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-active', [MitraDashboardController::class, 'toggleActive'])->name('toggle-active');
    Route::get('/profile', [MitraProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [MitraProfileController::class, 'update'])->name('profile.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/pelanggan/dashboard', fn () => view('role-dashboard', ['roleLabel' => 'Pelanggan']))
        ->middleware('role:pelanggan')->name('pelanggan.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
