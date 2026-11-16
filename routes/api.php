<?php

use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/xendit/topup', [XenditWebhookController::class, 'handleTopup'])->name('api.webhooks.xendit.topup');
Route::post('/webhooks/xendit/disbursement', [XenditWebhookController::class, 'handleDisbursement'])->name('api.webhooks.xendit.disbursement');
