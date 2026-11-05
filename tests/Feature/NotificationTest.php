<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->notificationService = app(NotificationService::class);
    $this->pelanggan = User::factory()->create(['role' => Role::Pelanggan]);
    $this->mitra = User::factory()->create(['role' => Role::Mitra]);
});

test('pelanggan and mitra can view notification list as HTML and JSON', function () {
    // Create notifications for pelanggan
    SystemNotification::create([
        'user_id' => $this->pelanggan->id,
        'event_code' => 'ORDER_STATUS_CHANGED',
        'title' => 'Terapis Sedang Menuju Lokasi',
        'body' => 'Mitra Budi telah berangkat ke alamat Anda.',
        'priority' => 'normal',
    ]);

    // HTML request
    $response = $this->actingAs($this->pelanggan)
        ->get(route('notifications.index'));

    $response->assertOk()
        ->assertSee('Terapis Sedang Menuju Lokasi')
        ->assertSee('Mitra Budi telah berangkat ke alamat Anda.');

    // JSON request (used by drawer / modal)
    $jsonResponse = $this->actingAs($this->pelanggan)
        ->getJson(route('notifications.index'));

    $jsonResponse->assertOk()
        ->assertJsonStructure([
            'notifications' => [
                '*' => ['id', 'title', 'body', 'read', 'created_at_human'],
            ],
            'unread_count',
        ])
        ->assertJsonPath('unread_count', 1);
});

test('user can mark individual notification as read via POST', function () {
    $notif = SystemNotification::create([
        'user_id' => $this->mitra->id,
        'event_code' => 'WITHDRAWAL_SUCCESS',
        'title' => 'Penarikan Saldo Berhasil',
        'body' => 'Dana Rp 150.000 telah ditransfer ke rekening Anda.',
        'priority' => 'normal',
        'read_at' => null,
    ]);

    $response = $this->actingAs($this->mitra)
        ->postJson(route('notifications.read', $notif));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('unread_count', 0);

    $notif->refresh();
    expect($notif->read_at)->not->toBeNull();
});

test('user cannot mark another users notification as read', function () {
    $notif = SystemNotification::create([
        'user_id' => $this->mitra->id,
        'event_code' => 'TEST_EVENT',
        'title' => 'Rahasia Mitra',
        'body' => 'Pesan khusus mitra',
    ]);

    // Pelanggan attempts to read Mitra's notification
    $response = $this->actingAs($this->pelanggan)
        ->postJson(route('notifications.read', $notif));

    $response->assertForbidden();
    expect($notif->fresh()->read_at)->toBeNull();
});

test('user can mark all notifications as read at once', function () {
    SystemNotification::create([
        'user_id' => $this->pelanggan->id,
        'event_code' => 'NOTIF_1',
        'title' => 'Notif 1',
        'body' => 'Body 1',
    ]);
    SystemNotification::create([
        'user_id' => $this->pelanggan->id,
        'event_code' => 'NOTIF_2',
        'title' => 'Notif 2',
        'body' => 'Body 2',
    ]);

    expect($this->pelanggan->systemNotifications()->unread()->count())->toBe(2);

    $response = $this->actingAs($this->pelanggan)
        ->postJson(route('notifications.read-all'));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('unread_count', 0);

    expect($this->pelanggan->systemNotifications()->unread()->count())->toBe(0);
});

test('notifications older than 1 month (30 days) are automatically pruned by cleanup scheduler', function () {
    // 1. Old notification (created 35 days ago)
    $oldNotif = SystemNotification::create([
        'user_id' => $this->pelanggan->id,
        'event_code' => 'OLD_NOTIF',
        'title' => 'Notifikasi 35 Hari Lalu',
        'body' => 'Sudah melewati batas retensi 1 bulan',
        'created_at' => Carbon::now()->subDays(35),
    ]);
    DB::table('system_notifications')->where('id', $oldNotif->id)->update([
        'created_at' => Carbon::now()->subDays(35),
    ]);

    // 2. Fresh notification (created 5 days ago)
    $freshNotif = SystemNotification::create([
        'user_id' => $this->pelanggan->id,
        'event_code' => 'FRESH_NOTIF',
        'title' => 'Notifikasi 5 Hari Lalu',
        'body' => 'Masih dalam masa retensi aktif',
        'created_at' => Carbon::now()->subDays(5),
    ]);
    DB::table('system_notifications')->where('id', $freshNotif->id)->update([
        'created_at' => Carbon::now()->subDays(5),
    ]);

    // Run the artisan cleanup command
    $exitCode = Artisan::call('app:cleanup-expired-data');
    expect($exitCode)->toBe(0);

    // Old notification must be pruned
    expect(SystemNotification::find($oldNotif->id))->toBeNull();

    // Fresh notification must be preserved
    expect(SystemNotification::find($freshNotif->id))->not->toBeNull();

    // Check audit log
    $log = AuditLog::where('action', 'data_retention_cleanup')->latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->new_values['deleted_notifications_count'])->toBe(1);
});

test('NotificationService pruneOldNotifications helper deletes records older than given days', function () {
    $notif = SystemNotification::create([
        'user_id' => $this->pelanggan->id,
        'event_code' => 'TEST_EXPIRE',
        'title' => 'Uji Usia Notifikasi',
        'body' => 'Dibuat 40 hari lalu',
    ]);
    DB::table('system_notifications')->where('id', $notif->id)->update([
        'created_at' => Carbon::now()->subDays(40),
    ]);

    $deletedCount = $this->notificationService->pruneOldNotifications(30);
    expect($deletedCount)->toBe(1)
        ->and(SystemNotification::find($notif->id))->toBeNull();
});
