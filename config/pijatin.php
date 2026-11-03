<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fixed System Parameters & Technical Constants
    |--------------------------------------------------------------------------
    |
    | Parameter teknis dan konstanta operasional baku yang tidak perlu diubah
    | secara dinamis oleh Superadmin melalui antarmuka pengguna.
    |
    */

    // Algoritma Penugasan & Waktu Respons
    'instant_offer_timeout_seconds' => 60,
    'instant_search_timeout_minutes' => 30,
    'scheduled_search_timeout_hours' => 24,
    'scheduled_search_deadline_before_hours' => 2,
    'scheduled_min_advance_hours' => 3,
    'scheduled_max_advance_days' => 7,
    'partner_order_interval_buffer_minutes' => 60,

    // Batasan Pesanan & Reschedule
    'customer_max_active_orders' => 3,
    'order_max_reschedule_count' => 2,
    'reschedule_min_advance_hours' => 3,

    // Lokasi & Algoritma Geofence
    'partner_online_location_interval_minutes' => 5,
    'partner_location_stale_timeout_minutes' => 10,
    'partner_otw_location_interval_seconds' => 30,
    'arrival_geofence_radius_meters' => 50,
    'arrival_gps_accuracy_tolerance_meters' => 150,

    // Waktu Tunggu & SLA
    'payment_expiry_timeout_minutes' => 15,
    'customer_noshow_wait_minutes' => 15,
    'partner_late_arrival_threshold_minutes' => 30,
    'complaint_window_hours' => 24,
    'partner_verification_sla_hours' => 24,

    // Finansial Baku
    'max_withdrawal_amount' => 5000000.00,
    'late_cancel_penalty_percent' => 50.00,
    'customer_wallet_daily_topup_limit' => 5000000.00,
    'partner_earning_hold_period_hours' => 24,

    // Sanksi & Penegakan Regulasi
    'sp_expiry_months' => 6,
    'customer_sp2_suspension_days' => 3,
    'partner_appeal_retry_cooldown_days' => 7,
    'sp1_sp3_appeal_deadline_days' => 3,
    'low_rating_min_orders' => 10,
];
