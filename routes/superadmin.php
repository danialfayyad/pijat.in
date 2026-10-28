<?php

use App\Http\Controllers\SuperAdmin\AdminAssignmentController;
use App\Http\Controllers\SuperAdmin\RegionController;
use Illuminate\Support\Facades\Route;

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

    Route::get('/catalog', fn () => redirect()->route('admin.catalog.index'))->name('catalog.index');
});
