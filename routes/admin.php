<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PartnerVerificationController;
use App\Http\Controllers\Admin\ServiceCatalogController;
use Illuminate\Support\Facades\Route;

// Region-scoped operational routes
Route::middleware(['auth', 'role:admin,superadmin', 'admin.region'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/mitra/verifikasi', [PartnerVerificationController::class, 'index'])->name('mitra.index');
    Route::get('/mitra/{mitra}', [PartnerVerificationController::class, 'show'])->name('mitra.show');
    Route::post('/mitra/{mitra}/approve', [PartnerVerificationController::class, 'approve'])->name('mitra.approve');
    Route::post('/mitra/{mitra}/reject', [PartnerVerificationController::class, 'reject'])->name('mitra.reject');
});

// Global Service Catalog routes (permission: KATALOG)
Route::middleware(['auth', 'role:admin,superadmin', 'admin.menu:KATALOG'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/catalog', [ServiceCatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog', [ServiceCatalogController::class, 'store'])->name('catalog.store');
    Route::put('/catalog/{service}', [ServiceCatalogController::class, 'update'])->name('catalog.update');
    Route::post('/catalog/{service}/toggle', [ServiceCatalogController::class, 'toggleStatus'])->name('catalog.toggle');
    Route::post('/catalog/packages/{package}/toggle', [ServiceCatalogController::class, 'togglePackageStatus'])->name('catalog.packages.toggle');
    Route::delete('/catalog/{service}', [ServiceCatalogController::class, 'destroy'])->name('catalog.destroy');
});
