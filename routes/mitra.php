<?php

use App\Http\Controllers\Mitra\MitraComplaintController;
use App\Http\Controllers\Mitra\MitraDashboardController;
use App\Http\Controllers\Mitra\MitraFinanceController;
use App\Http\Controllers\Mitra\MitraOrderController;
use App\Http\Controllers\Mitra\MitraPenaltyController;
use App\Http\Controllers\Mitra\MitraProfileController;
use App\Http\Controllers\Mitra\OnboardingController;
use App\Http\Controllers\Mitra\PartnerHelpController;
use App\Http\Controllers\Mitra\PartnerRelocationController;
use App\Http\Controllers\OrderChatController;
use App\Http\Controllers\OrderRatingController;
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
    Route::get('/profile/data-diri', [MitraProfileController::class, 'editPersonal'])->name('profile.personal');
    Route::post('/profile/data-diri', [MitraProfileController::class, 'updatePersonal'])->name('profile.personal.update');
    Route::get('/profile/rekening', [MitraProfileController::class, 'editBank'])->name('profile.bank');
    Route::post('/profile/rekening', [MitraProfileController::class, 'updateBank'])->name('profile.bank.update');
    Route::get('/profile/layanan', [MitraProfileController::class, 'editServices'])->name('profile.services');
    Route::post('/profile/layanan', [MitraProfileController::class, 'updateServices'])->name('profile.services.update');
    Route::get('/profile/keamanan', [MitraProfileController::class, 'editSecurity'])->name('profile.security');
    Route::post('/profile/keamanan', [MitraProfileController::class, 'updateSecurity'])->name('profile.security.update');
    Route::get('/profile/dokumen', [MitraProfileController::class, 'documents'])->name('profile.documents');

    // Pesanan Terjadwal (FCFS) & Jadwal Saya (MIT-06, MIT-07)
    Route::get('/pesanan-terjadwal', [MitraOrderController::class, 'scheduled'])->name('orders.scheduled');
    Route::post('/pesanan-terjadwal/{order}/ambil', [MitraOrderController::class, 'take'])->name('orders.take');
    Route::get('/jadwal-saya', [MitraOrderController::class, 'mySchedule'])->name('orders.my-schedule');

    // Saldo, Keuangan & Penarikan Dana (MIT-15, MIT-16)
    Route::get('/keuangan', [MitraFinanceController::class, 'index'])->name('finance.index');
    Route::get('/keuangan/tarik', [MitraFinanceController::class, 'withdraw'])->name('finance.withdraw');
    Route::post('/keuangan/tarik', [MitraFinanceController::class, 'storeWithdrawal'])->name('finance.withdraw.store');

    // Detail Pesanan, Peta Interaktif & Update Status Layanan
    Route::get('/orders/{order}', [MitraOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [MitraOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/cancel', [MitraOrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/rating', [OrderRatingController::class, 'rateByMitra'])->name('orders.rating');
    Route::get('/orders/{order}/chat/messages', [OrderChatController::class, 'getMessages'])->name('orders.chat.messages');
    Route::post('/orders/{order}/chat/messages', [OrderChatController::class, 'sendMessage'])->name('orders.chat.send');

    // Mitra Incident & Complaints (MIT-19)
    Route::get('/orders/{order}/laporkan-insiden', [MitraComplaintController::class, 'create'])->name('orders.incident.create');
    Route::post('/orders/{order}/laporkan-insiden', [MitraComplaintController::class, 'store'])->name('orders.incident.store');

    // Sanksi & Banding SP (MIT-18)
    Route::get('/sanksi', [MitraPenaltyController::class, 'index'])->name('penalties.index');
    Route::get('/sanksi/banding', [MitraPenaltyController::class, 'createAppeal'])->name('penalties.appeal.create');
    Route::post('/sanksi/banding', [MitraPenaltyController::class, 'storeAppeal'])->name('penalties.appeal.store');

    // Relokasi / Pindah Wilayah Mitra (MIT-22)
    Route::get('/pindah-wilayah', [PartnerRelocationController::class, 'create'])->name('relocation.create');
    Route::post('/pindah-wilayah', [PartnerRelocationController::class, 'store'])->name('relocation.store');
    Route::post('/pindah-wilayah/{relocationRequest}/cancel', [PartnerRelocationController::class, 'cancel'])->name('relocation.cancel');

    // Partner Help Center & Live Chat Desk
    Route::get('/bantuan', [PartnerHelpController::class, 'index'])->name('help.index');
    Route::get('/bantuan/chat', [PartnerHelpController::class, 'chat'])->name('help.chat');
    Route::post('/bantuan/chat', [PartnerHelpController::class, 'storeTicket'])->name('help.store');
    Route::get('/bantuan/chat/{ticket}', [PartnerHelpController::class, 'showTicket'])->name('help.ticket');
    Route::post('/bantuan/chat/{ticket}/close', [PartnerHelpController::class, 'closeTicket'])->name('help.close');
    Route::post('/bantuan/chat/{ticket}/reply', [PartnerHelpController::class, 'replyTicket'])->name('help.reply');
    Route::get('/bantuan/chat/{ticket}/messages', [PartnerHelpController::class, 'fetchMessages'])->name('help.messages');
});
