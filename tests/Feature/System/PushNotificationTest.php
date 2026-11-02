<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\Region;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->notificationService = app(NotificationService::class);

    $this->regionA = Region::factory()->create([
        'name' => 'KOTA BLITAR',
        'status' => 'aktif',
    ]);

    $this->regionB = Region::factory()->create([
        'name' => 'KABUPATEN KEDIRI',
        'status' => 'aktif',
    ]);
});

test('authenticated user can subscribe and unsubscribe to web push notifications (CUS-21, MIT-21)', function () {
    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    // 1. Get VAPID public key
    $response = $this->actingAs($customer)->getJson(route('push.public-key'));
    $response->assertOk()
        ->assertJsonStructure(['publicKey']);

    // 2. Subscribe endpoint
    $subResponse = $this->actingAs($customer)->postJson(route('push.subscribe'), [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-sub-1',
        'public_key' => 'BLvI...testPublicKey',
        'auth_token' => 'mockAuthSecretKey',
    ]);

    $subResponse->assertCreated()
        ->assertJson(['message' => 'Langganan Web Push berhasil didaftarkan.']);

    expect(PushSubscription::where('user_id', $customer->id)->count())->toBe(1);

    // 3. Unsubscribe endpoint
    $unsubResponse = $this->actingAs($customer)->postJson(route('push.unsubscribe'), [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-sub-1',
    ]);

    $unsubResponse->assertOk()
        ->assertJson(['message' => 'Langganan Web Push berhasil dihapus.']);

    expect(PushSubscription::where('user_id', $customer->id)->count())->toBe(0);
});

test('customer receives web push and in-app notifications on order lifecycle and wallet events (CUS-21)', function () {
    $customer = User::factory()->create(['role' => Role::Pelanggan]);

    // Register push subscription
    $this->notificationService->subscribe($customer, [
        'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/test-customer',
        'public_key' => 'test-key',
        'auth_token' => 'test-auth',
    ]);

    // 1. Order Status Lifecycle Notifications
    $lifecycleEvents = [
        ['NOTIF_PARTNER_ASSIGNED', 'Mitra Ditemukan!', 'Terapis Budi Santoso telah menerima pesanan Anda.'],
        ['NOTIF_PARTNER_OTW', 'Terapis Menuju Lokasi', 'Terapis sedang dalam perjalanan menuju alamat Anda.'],
        ['NOTIF_PARTNER_ARRIVED', 'Terapis Telah Tiba', 'Terapis sudah sampai di titik lokasi layanan.'],
        ['NOTIF_SERVICE_STARTED', 'Sesi Layanan Dimulai', 'Pijat relaksasi 90 menit sedang berlangsung.'],
        ['NOTIF_SERVICE_COMPLETED', 'Layanan Selesai', 'Terima kasih telah menggunakan Pijat.In. Silakan beri ulasan.'],
    ];

    foreach ($lifecycleEvents as [$eventCode, $title, $body]) {
        $this->notificationService->sendToUser(
            $customer,
            $eventCode,
            $title,
            $body,
            ['order_id' => 999]
        );
    }

    // 2. Wallet Notifications (Top-up & Refund)
    $this->notificationService->sendToUser(
        $customer,
        'NOTIF_TOPUP_SUCCESS',
        'Top-Up Dompet Berhasil',
        'Saldo sebesar Rp 150.000 telah masuk ke dompet Anda.',
        ['amount' => 150000]
    );

    $this->notificationService->sendToUser(
        $customer,
        'NOTIF_REFUND_PROCESSED',
        'Pengembalian Dana Berhasil',
        'Dana refund sebesar Rp 100.000 telah dikreditkan ke saldo dompet.',
        ['amount' => 100000]
    );

    // Verify all 7 notifications are recorded for this customer
    expect(SystemNotification::where('user_id', $customer->id)->count())->toBe(7);

    // Verify event codes
    $eventCodes = SystemNotification::where('user_id', $customer->id)->pluck('event_code')->all();
    expect($eventCodes)->toContain('NOTIF_PARTNER_ASSIGNED')
        ->toContain('NOTIF_PARTNER_OTW')
        ->toContain('NOTIF_PARTNER_ARRIVED')
        ->toContain('NOTIF_SERVICE_STARTED')
        ->toContain('NOTIF_SERVICE_COMPLETED')
        ->toContain('NOTIF_TOPUP_SUCCESS')
        ->toContain('NOTIF_REFUND_PROCESSED');
});

test('mitra receives web push notifications for dispatch, cancellations, balance, and penalties (MIT-21)', function () {
    $mitra = User::factory()->create(['role' => Role::Mitra]);

    $this->notificationService->subscribe($mitra, [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-mitra-device',
        'public_key' => 'mitra-key',
        'auth_token' => 'mitra-auth',
    ]);

    // 1. Instant Offer (High Priority)
    $this->notificationService->sendToUser(
        $mitra,
        'NOTIF_INSTANT_OFFER',
        'Tawaran Pesanan Instan Baru!',
        'Pesanan Pijat Tradisional 90m di Jl. Merdeka. Konfirmasi dalam 60 detik.',
        ['order_id' => 101, 'timeout_seconds' => 60],
        'high'
    );

    // 2. Scheduled Order Available
    $this->notificationService->sendToUser(
        $mitra,
        'NOTIF_SCHEDULED_AVAILABLE',
        'Pesanan Terjadwal Tersedia',
        'Ada pesanan terjadwal baru di bursa kerja wilayah Anda.',
        ['order_id' => 102]
    );

    // 3. Order Cancelled
    $this->notificationService->sendToUser(
        $mitra,
        'NOTIF_ORDER_CANCELLED',
        'Pesanan Dibatalkan',
        'Pelanggan membatalkan pesanan #ORD-101 karena alasan darurat.',
        ['order_id' => 101]
    );

    // 4. Balance Available (after 24h)
    $this->notificationService->sendToUser(
        $mitra,
        'NOTIF_BALANCE_AVAILABLE',
        'Saldo Tersedia Siap Ditarik',
        'Saldo tertahan Rp 80.000 dari pesanan #ORD-099 kini siap dicairkan.',
        ['amount' => 80000]
    );

    // 5. Penalty SP Issued
    $this->notificationService->sendToUser(
        $mitra,
        'NOTIF_SP_ISSUED',
        'Pemberitahuan Poin Sanksi',
        'Anda menerima penambahan 30 poin sanksi terkait laporan keterlambatan.',
        ['points' => 30]
    );

    // 6. Verification Status Updated
    $this->notificationService->sendToUser(
        $mitra,
        'NOTIF_VERIFICATION_STATUS',
        'Pendaftaran Mitra Disetujui',
        'Selamat! Berkas kemitraan Anda telah diverifikasi oleh admin wilayah.',
        ['status' => 'terverifikasi']
    );

    expect(SystemNotification::where('user_id', $mitra->id)->count())->toBe(6);

    $highPriorityNotif = SystemNotification::where('user_id', $mitra->id)
        ->where('event_code', 'NOTIF_INSTANT_OFFER')
        ->first();
    expect($highPriorityNotif->priority)->toBe('high');
});

test('notification dispatch is strictly isolated to assigned region admins and never leaks to other regions (SYS-06)', function () {
    // Admin A assigned to Region A
    $adminA = User::factory()->create(['role' => Role::Admin]);
    $adminA->assignedRegions()->attach($this->regionA->id);

    // Admin B assigned to Region B
    $adminB = User::factory()->create(['role' => Role::Admin]);
    $adminB->assignedRegions()->attach($this->regionB->id);

    // Unassigned Admin
    $unassignedAdmin = User::factory()->create(['role' => Role::Admin]);

    // Dispatch event targeted strictly at Region A
    $sent = $this->notificationService->sendToRegionAdmins(
        $this->regionA->id,
        'NOTIF_ADMIN_QUEUE',
        'Berkas Verifikasi Baru',
        'Terdapat 1 berkas mitra baru di Kota Blitar yang membutuhkan verifikasi.',
        ['type' => 'partner_verification', 'region_id' => $this->regionA->id]
    );

    // Assertions
    expect($sent)->toHaveCount(1);
    expect($sent->first()->user_id)->toBe($adminA->id);

    // Verify Admin A received it
    expect(SystemNotification::where('user_id', $adminA->id)->count())->toBe(1);

    // Verify Admin B and unassigned Admin received ZERO notifications (NO LEAKAGE)
    expect(SystemNotification::where('user_id', $adminB->id)->count())->toBe(0);
    expect(SystemNotification::where('user_id', $unassignedAdmin->id)->count())->toBe(0);
});

test('admin can view task queue summary, read notifications, and mark them as read (ADM-14)', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $admin->assignedRegions()->attach($this->regionA->id);

    // Create a pending partner in region A
    $partnerPending = User::factory()->create([
        'role' => Role::Mitra,
        'status' => UserStatus::MenungguVerifikasi,
        'region_id' => $this->regionA->id,
    ]);

    // Create a notification for admin
    $notif = $this->notificationService->sendToUser(
        $admin,
        'NOTIF_ADMIN_QUEUE',
        'Antrean Baru Masuk',
        'Mitra menunggu verifikasi.'
    );

    // 1. Admin visits notifications page
    $response = $this->actingAs($admin)->get(route('admin.notifications.index'));
    $response->assertOk()
        ->assertSee('Pusat Notifikasi dan Antrean Tugas')
        ->assertSee('Antrean Baru Masuk')
        ->assertSee('Antrean Verifikasi Mitra');

    // 2. Mark single notification as read
    $markResponse = $this->actingAs($admin)->post(route('admin.notifications.read', $notif));
    $markResponse->assertRedirect();

    $notif->refresh();
    expect($notif->read_at)->not->toBeNull();

    // 3. Test mark all as read
    $notif2 = $this->notificationService->sendToUser($admin, 'TEST_CODE', 'Notif 2', 'Body 2');
    $notif3 = $this->notificationService->sendToUser($admin, 'TEST_CODE', 'Notif 3', 'Body 3');
    expect($admin->systemNotifications()->unread()->count())->toBe(2);

    $readAllResponse = $this->actingAs($admin)->post(route('admin.notifications.read-all'));
    $readAllResponse->assertRedirect();

    expect($admin->systemNotifications()->unread()->count())->toBe(0);
});
