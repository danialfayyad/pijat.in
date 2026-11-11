<?php

use App\Http\Controllers\SuperAdmin\AdminAssignmentController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\BankController;
use App\Http\Controllers\Superadmin\BannerController;
use App\Http\Controllers\SuperAdmin\BrandingSettingController;
use App\Http\Controllers\SuperAdmin\ComplaintCategoryController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\FaqController;
use App\Http\Controllers\SuperAdmin\FinanceReportController;
use App\Http\Controllers\SuperAdmin\LegalDocumentController;
use App\Http\Controllers\SuperAdmin\ParameterController;
use App\Http\Controllers\SuperAdmin\RegionController;
use App\Http\Controllers\SuperAdmin\VoucherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::get('/regions/presets', [RegionController::class, 'presets'])->name('regions.presets');
    Route::get('/regions/presets/{cityId}', [RegionController::class, 'cityBoundary'])->name('regions.cityBoundary');
    Route::post('/regions', [RegionController::class, 'store'])->name('regions.store');
    Route::post('/regions/{region}/activate', [RegionController::class, 'activate'])->name('regions.activate');
    Route::post('/regions/{region}/deactivate', [RegionController::class, 'deactivate'])->name('regions.deactivate');

    Route::get('/admins', [AdminAssignmentController::class, 'index'])->name('admins.index');
    Route::post('/admins', [AdminAssignmentController::class, 'store'])->name('admins.store');
    Route::post('/admins/assign', [AdminAssignmentController::class, 'assign'])->name('admins.assign');
    Route::post('/admins/revoke', [AdminAssignmentController::class, 'revoke'])->name('admins.revoke');
    Route::delete('/admins/{user}', [AdminAssignmentController::class, 'destroy'])->name('admins.destroy');

    Route::get('/complaint-categories', [ComplaintCategoryController::class, 'index'])->name('complaint-categories.index');
    Route::post('/complaint-categories', [ComplaintCategoryController::class, 'store'])->name('complaint-categories.store');
    Route::put('/complaint-categories/{complaintCategory}', [ComplaintCategoryController::class, 'update'])->name('complaint-categories.update');
    Route::post('/complaint-categories/{complaintCategory}/toggle', [ComplaintCategoryController::class, 'toggle'])->name('complaint-categories.toggle');
    Route::delete('/complaint-categories/{complaintCategory}', [ComplaintCategoryController::class, 'destroy'])->name('complaint-categories.destroy');

    // System Parameters (SA-01)
    Route::get('/parameters', [ParameterController::class, 'index'])->name('parameters.index');
    Route::post('/parameters', [ParameterController::class, 'update'])->name('parameters.update');

    // App Branding & Identity Settings
    Route::get('/branding', [BrandingSettingController::class, 'index'])->name('branding.index');
    Route::put('/branding', [BrandingSettingController::class, 'update'])->name('branding.update');

    // Customer Promo & Announcement Banners Management
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::post('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

    // Master Rekening & Saluran Bank Pencairan
    Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
    Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
    Route::put('/banks/{bank}', [BankController::class, 'update'])->name('banks.update');
    Route::post('/banks/{bank}/toggle', [BankController::class, 'toggle'])->name('banks.toggle');
    Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');

    // Master Voucher & Kupon Diskon (Bisnis & Finansial)
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::post('/vouchers/{voucher}/toggle', [VoucherController::class, 'toggle'])->name('vouchers.toggle');
    Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // Centralized Audit Trail Logs (SA-03)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Legal Documents Management (SA-05)
    Route::get('/legal-documents', [LegalDocumentController::class, 'index'])->name('legal-documents.index');
    Route::get('/legal-documents/{document}/edit', [LegalDocumentController::class, 'edit'])->name('legal-documents.edit');
    Route::put('/legal-documents/{document}', [LegalDocumentController::class, 'update'])->name('legal-documents.update');

    // FAQ Management (Super Admin)
    Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');
    Route::post('/faqs', [FaqController::class, 'store'])->name('faqs.store');
    Route::put('/faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
    Route::post('/faqs/{faq}/toggle', [FaqController::class, 'toggle'])->name('faqs.toggle');
    Route::delete('/faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

    // Financial Monitoring & Cross-Region Reports (SA-04, SA-08)
    Route::get('/finance', [FinanceReportController::class, 'index'])->name('finance.index');
    Route::get('/finance/cross-region', [FinanceReportController::class, 'crossRegion'])->name('finance.cross-region');
    Route::get('/finance/export/csv', [FinanceReportController::class, 'exportCsv'])->name('finance.export.csv');
    Route::get('/finance/export/print', [FinanceReportController::class, 'exportPrint'])->name('finance.export.print');

    Route::get('/catalog', fn () => redirect()->route('admin.catalog.index'))->name('catalog.index');
});
