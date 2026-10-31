<?php

use App\Http\Controllers\OrderChatController;
use App\Http\Controllers\OrderRatingController;
use App\Http\Controllers\Pelanggan\AddressController;
use App\Http\Controllers\Pelanggan\CatalogController;
use App\Http\Controllers\Pelanggan\CustomerDashboardController;
use App\Http\Controllers\Pelanggan\OrderBookingController;
use App\Http\Controllers\Pelanggan\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:pelanggan'])->prefix('pelanggan')->name('pelanggan.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Service Catalog (CUS-03)
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/{service:slug}', [CatalogController::class, 'show'])->name('catalog.show');

    // Customer Address Management (CUS-05, CUS-23)
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::post('/addresses/{address}/primary', [AddressController::class, 'setPrimary'])->name('addresses.set-primary');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    // Customer Orders & Booking (Slice 4 & 5: CUS-04, CUS-06, CUS-07, CUS-08, SYS-08)
    Route::get('/orders', [OrderBookingController::class, 'index'])->name('orders.index');
    Route::get('/orders/check-instant', [OrderBookingController::class, 'checkInstantAvailability'])->name('orders.check-instant');
    Route::get('/orders/create', [OrderBookingController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderBookingController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderBookingController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/live-tracking', [OrderBookingController::class, 'liveTracking'])->name('orders.live-tracking');
    Route::post('/orders/{order}/pay', [OrderBookingController::class, 'payWithWallet'])->name('orders.pay');
    Route::post('/orders/{order}/cancel', [OrderBookingController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/rating', [OrderRatingController::class, 'rateByCustomer'])->name('orders.rating');
    Route::get('/orders/{order}/chat/messages', [OrderChatController::class, 'getMessages'])->name('orders.chat.messages');
    Route::post('/orders/{order}/chat/messages', [OrderChatController::class, 'sendMessage'])->name('orders.chat.send');
    Route::post('/orders/{order}/rating', [\App\Http\Controllers\OrderRatingController::class, 'rateByCustomer'])->name('orders.rating');
    Route::get('/orders/{order}/chat/messages', [\App\Http\Controllers\OrderChatController::class, 'getMessages'])->name('orders.chat.messages');
    Route::post('/orders/{order}/chat/messages', [\App\Http\Controllers\OrderChatController::class, 'sendMessage'])->name('orders.chat.send');

    // Customer Wallet & Top-up (CUS-16, SYS-05)
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/topup', [WalletController::class, 'create'])->name('wallet.topup');
    Route::post('/wallet/topup', [WalletController::class, 'store'])->name('wallet.store');
    Route::get('/wallet/invoices/{invoice}', [WalletController::class, 'invoice'])->name('wallet.invoice');
    Route::post('/wallet/invoices/{invoice}/simulate-pay', [WalletController::class, 'simulatePay'])->name('wallet.simulate-pay');
});
