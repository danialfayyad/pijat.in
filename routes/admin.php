<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PartnerVerificationController;
use App\Http\Controllers\Admin\PenaltyAppealController;
use App\Http\Controllers\Admin\PenaltyMonitoringController;
use App\Http\Controllers\Admin\ServiceCatalogController;
use App\Http\Controllers\Admin\SupportChatController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

// Region-scoped operational routes
Route::middleware(['auth', 'role:admin,superadmin', 'admin.region'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/mitra/verifikasi', [PartnerVerificationController::class, 'index'])->name('mitra.index');
    Route::get('/mitra/{mitra}', [PartnerVerificationController::class, 'show'])->name('mitra.show');
    Route::post('/mitra/{mitra}/approve', [PartnerVerificationController::class, 'approve'])->name('mitra.approve');
    Route::post('/mitra/{mitra}/reject', [PartnerVerificationController::class, 'reject'])->name('mitra.reject');
    Route::post('/mitra/relokasi/{relocationRequest}/approve', [PartnerVerificationController::class, 'approveRelocation'])->name('mitra.relocation.approve');
    Route::post('/mitra/relokasi/{relocationRequest}/reject', [PartnerVerificationController::class, 'rejectRelocation'])->name('mitra.relocation.reject');

    // Operational Order Monitoring & Intervention (ADM-04)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Complaint Center / Laporan Aduan (ADM-08)
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/process', [ComplaintController::class, 'process'])->name('complaints.process');
    Route::post('/complaints/{complaint}/resolve', [ComplaintController::class, 'resolve'])->name('complaints.resolve');
    Route::post('/complaints/{complaint}/reject', [ComplaintController::class, 'reject'])->name('complaints.reject');

    // Demerit Points & Penalty Monitoring (ADM-06)
    Route::get('/penalties', [PenaltyMonitoringController::class, 'index'])->name('penalties.index');
    Route::get('/penalties/{user}', [PenaltyMonitoringController::class, 'show'])->name('penalties.show');
    Route::post('/penalties/{user}/adjust', [PenaltyMonitoringController::class, 'adjust'])->name('penalties.adjust');

    // Penalty Appeals Review (ADM-07)
    Route::get('/appeals', [PenaltyAppealController::class, 'index'])->name('appeals.index');
    Route::get('/appeals/{appeal}', [PenaltyAppealController::class, 'show'])->name('appeals.show');
    Route::post('/appeals/{appeal}/approve', [PenaltyAppealController::class, 'approve'])->name('appeals.approve');
    Route::post('/appeals/{appeal}/reject', [PenaltyAppealController::class, 'reject'])->name('appeals.reject');

    // User & Partner Management (ADM-05)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/partners/{user}', [UserManagementController::class, 'showPartner'])->name('users.partners.show');
    Route::get('/users/customers/{user}', [UserManagementController::class, 'showCustomer'])->name('users.customers.show');
    Route::post('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.update-status');
    Route::post('/users/deletion-requests/{deletionRequest}/approve', [UserManagementController::class, 'approveDeletionRequest'])->name('users.deletion-requests.approve');
    Route::post('/users/deletion-requests/{deletionRequest}/reject', [UserManagementController::class, 'rejectDeletionRequest'])->name('users.deletion-requests.reject');

    // Regional Finance & Withdrawal Management (ADM-11)
    Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/withdrawals/{withdrawal}/approve', [AdminFinanceController::class, 'approveWithdrawal'])->name('finance.withdrawals.approve');
    Route::post('/finance/withdrawals/{withdrawal}/reject', [AdminFinanceController::class, 'rejectWithdrawal'])->name('finance.withdrawals.reject');
    Route::get('/finance/export/csv', [AdminFinanceController::class, 'exportCsv'])->name('finance.export.csv');
    Route::get('/finance/export/print', [AdminFinanceController::class, 'exportPrint'])->name('finance.export.print');

    // Admin Profile & Password (ADM-14)
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // Notifications & Task Queue (ADM-14, SYS-06)
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/check-unread', [AdminNotificationController::class, 'checkUnread'])->name('notifications.check-unread');
    Route::get('/notifications/{notification}/go', [AdminNotificationController::class, 'navigate'])->name('notifications.go');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Live Chat Support Desk (Admin Wilayah)
    Route::get('/support-chats', [SupportChatController::class, 'index'])->name('support-chats.index');
    Route::get('/support-chats/{ticket}', [SupportChatController::class, 'show'])->name('support-chats.show');
    Route::post('/support-chats/{ticket}/reply', [SupportChatController::class, 'reply'])->name('support-chats.reply');
    Route::post('/support-chats/{ticket}/close', [SupportChatController::class, 'close'])->name('support-chats.close');
    Route::post('/support-chats/{ticket}/reopen', [SupportChatController::class, 'reopen'])->name('support-chats.reopen');
    Route::get('/support-chats/{ticket}/messages', [SupportChatController::class, 'fetchMessages'])->name('support-chats.messages');
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
