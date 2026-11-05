<?php

use App\Models\SystemNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sequential 60-second instant order timeout processor (SYS-01)
Schedule::command('orders:process-instant-offers')->everyMinute();

// Automatic cancellations for stuck orders and expired payments (SYS-02)
Schedule::command('orders:process-cancellations')->everyMinute();

// Release partner held balances 24 hours post-completion if no active complaints (SYS-03)
Schedule::command('app:release-held-balances')->hourly();

// Daily cleanup of expired chat messages, medical notes, & old notifications (SYS-07)
Schedule::command('app:cleanup-expired-data')->daily();

// Prune models implementing Prunable older than retention threshold (e.g., SystemNotification > 30 days)
Schedule::command('model:prune', ['--model' => [SystemNotification::class]])->daily();
