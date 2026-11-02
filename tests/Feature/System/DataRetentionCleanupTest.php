<?php

use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Parameter;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\ParameterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ParameterSeeder::class);

    $this->region = Region::factory()->create([
        'name' => 'KOTA BLITAR',
        'status' => 'aktif',
    ]);
});

test('scheduler deletes chat messages and wipes medical notes older than 30 days (SYS-07)', function () {
    // Set parameter
    Parameter::set('chat_and_medical_retention_days', 30, 'Initial test setup');

    $customer = User::factory()->create();
    $mitra = User::factory()->create();

    // 1. Expired Order (created 35 days ago)
    $expiredOrder = Order::factory()->create([
        'customer_id' => $customer->id,
        'mitra_id' => $mitra->id,
        'region_id' => $this->region->id,
        'status' => OrderStatus::Selesai,
        'medical_notes' => 'Pasien memiliki riwayat darah tinggi kronis.',
        'created_at' => Carbon::now()->subDays(35),
    ]);

    // Force DB update for created_at if factory/model touched it
    DB::table('orders')->where('id', $expiredOrder->id)->update([
        'created_at' => Carbon::now()->subDays(35),
    ]);

    // Expired Message (created 35 days ago)
    $expiredMessage = OrderMessage::create([
        'order_id' => $expiredOrder->id,
        'sender_id' => $customer->id,
        'message' => 'Halo terapis, saya di lantai 2 ya.',
        'created_at' => Carbon::now()->subDays(35),
    ]);
    DB::table('order_messages')->where('id', $expiredMessage->id)->update([
        'created_at' => Carbon::now()->subDays(35),
    ]);

    // 2. Fresh Order (created 5 days ago)
    $freshOrder = Order::factory()->create([
        'customer_id' => $customer->id,
        'mitra_id' => $mitra->id,
        'region_id' => $this->region->id,
        'status' => OrderStatus::Selesai,
        'medical_notes' => 'Sering pegal di bagian pinggang kanan.',
        'created_at' => Carbon::now()->subDays(5),
    ]);
    DB::table('orders')->where('id', $freshOrder->id)->update([
        'created_at' => Carbon::now()->subDays(5),
    ]);

    // Fresh Message (created 5 days ago)
    $freshMessage = OrderMessage::create([
        'order_id' => $freshOrder->id,
        'sender_id' => $customer->id,
        'message' => 'Halo, sudah sampai di gerbang depan.',
        'created_at' => Carbon::now()->subDays(5),
    ]);
    DB::table('order_messages')->where('id', $freshMessage->id)->update([
        'created_at' => Carbon::now()->subDays(5),
    ]);

    // Run the Artisan command
    $exitCode = Artisan::call('app:cleanup-expired-data');
    expect($exitCode)->toBe(0);

    // Verify Expired Data is cleared / wiped
    expect(OrderMessage::find($expiredMessage->id))->toBeNull();
    $expiredOrder->refresh();
    expect($expiredOrder->medical_notes)->toBeNull();
    // Order itself still exists with all accounting/operational data
    expect($expiredOrder->exists)->toBeTrue();

    // Verify Fresh Data is preserved untouched
    expect(OrderMessage::find($freshMessage->id))->not->toBeNull();
    $freshOrder->refresh();
    expect($freshOrder->medical_notes)->toBe('Sering pegal di bagian pinggang kanan.');

    // Verify Audit Log was recorded
    $log = AuditLog::where('action', 'data_retention_cleanup')->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->new_values['deleted_messages_count'])->toBe(1);
    expect($log->new_values['cleared_medical_count'])->toBe(1);
});

test('cleanup command respects custom retention parameter', function () {
    // Set parameter to 10 days
    Parameter::set('chat_and_medical_retention_days', 10, 'Custom shorter retention test');

    $customer = User::factory()->create();
    $mitra = User::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'mitra_id' => $mitra->id,
        'region_id' => $this->region->id,
        'status' => OrderStatus::Selesai,
        'medical_notes' => 'Catatan 12 hari lalu.',
    ]);
    DB::table('orders')->where('id', $order->id)->update([
        'created_at' => Carbon::now()->subDays(12),
    ]);

    $message = OrderMessage::create([
        'order_id' => $order->id,
        'sender_id' => $customer->id,
        'message' => 'Pesan 12 hari lalu',
    ]);
    DB::table('order_messages')->where('id', $message->id)->update([
        'created_at' => Carbon::now()->subDays(12),
    ]);

    Artisan::call('app:cleanup-expired-data');

    expect(OrderMessage::find($message->id))->toBeNull();
    $order->refresh();
    expect($order->medical_notes)->toBeNull();
});
