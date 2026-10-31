<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\PartnerServicePreference;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\InstantOrderMatchingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember Kota',
        'status' => 'aktif',
    ]);

    $this->otherRegion = Region::factory()->create([
        'name' => 'Wilayah Banyuwangi',
        'status' => 'aktif',
    ]);

    $this->serviceA = Service::factory()->create(['name' => 'Pijat Tradisional', 'is_active' => true]);
    $this->serviceB = Service::factory()->create(['name' => 'Refleksi Kaki', 'is_active' => true]);

    $this->packageA = ServicePackage::create([
        'service_id' => $this->serviceA->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    // Mitra Pria di Jember
    $this->mitraPria = User::factory()->mitra()->create([
        'name' => 'Joko Terapis',
        'phone' => '081234567890',
        'gender' => 'pria',
    ]);

    $this->profileMitraPria = PartnerProfile::create([
        'user_id' => $this->mitraPria->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Kalimantan No. 5, Jember',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);

    // Customer
    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Uji',
        'gender' => 'pria',
    ]);
});

test('MIT-06: bursa pesanan terjadwal menampilkan pesanan yang cocok (wilayah, preferensi gender, layanan)', function () {
    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(1)->setTime(10, 0);

    // Order 1: Cocok (Wilayah Jember, Preferensi Pria / Bebas, Pijat Tradisional)
    $orderMatch = Order::create([
        'order_code' => 'ORD-MATCH-01',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Ahmad',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'pria',
        'recipient_phone' => '0811111111',
        'service_address_text' => 'Jl. Danau Toba No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    // Order 2: Preferensi Terapis Wanita (Tidak boleh muncul untuk mitra pria)
    $orderDiffGender = Order::create([
        'order_code' => 'ORD-GENDER-02',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Siti',
        'recipient_gender' => 'wanita',
        'preferred_gender' => 'wanita',
        'service_address_text' => 'Jl. Karimata No. 8',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    // Order 3: Beda Wilayah (Banyuwangi)
    $orderDiffRegion = Order::create([
        'order_code' => 'ORD-REGION-03',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->otherRegion->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Budi',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'pria',
        'service_address_text' => 'Jl. Banyuwangi Kota',
        'service_coordinate' => ['lat' => -8.2190, 'lng' => 114.3690],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    // Order 4: Preferensi Bebas (Harus muncul untuk mitra pria)
    $orderBebas = Order::create([
        'order_code' => 'ORD-BEBAS-04',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Ratna',
        'recipient_gender' => 'wanita',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Jawa No. 1',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->mitraPria)->get(route('mitra.orders.scheduled'));

    $response->assertOk();
    $response->assertSee('ORD-MATCH-01');
    $response->assertSee('ORD-BEBAS-04');
    $response->assertDontSee('ORD-GENDER-02');
    $response->assertDontSee('ORD-REGION-03');
});

test('MIT-06: pesanan dengan layanan yang dinonaktifkan oleh mitra tidak muncul di bursa', function () {
    // Nonaktifkan serviceB untuk mitra
    PartnerServicePreference::create([
        'partner_profile_id' => $this->profileMitraPria->id,
        'service_id' => $this->serviceB->id,
        'is_enabled' => false,
    ]);

    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(1)->setTime(14, 0);

    $orderServiceB = Order::create([
        'order_code' => 'ORD-DISABLED-SVC',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceB->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceB->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Ahmad',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Riau No. 3',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->mitraPria)->get(route('mitra.orders.scheduled'));

    $response->assertOk();
    $response->assertDontSee('ORD-DISABLED-SVC');
});

test('MIT-06: pesanan yang bentrok jadwal dengan pesanan mitra yang sudah diambil tidak muncul di bursa', function () {
    $timeA = Carbon::now('Asia/Jakarta')->addDays(1)->setTime(10, 0);

    // Mitra sudah punya order jam 10:00 - 11:00
    Order::create([
        'order_code' => 'ORD-EXISTING-10',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitraPria->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Ahmad',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Jawa',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $timeA,
        'status' => OrderStatus::Dijadwalkan,
    ]);

    // Order baru di bursa jam 10:30 (bentrok!)
    $clashingOrder = Order::create([
        'order_code' => 'ORD-CLASH-1030',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Bambang',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Mastrip',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $timeA->copy()->addMinutes(30),
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->mitraPria)->get(route('mitra.orders.scheduled'));

    $response->assertOk();
    $response->assertDontSee('ORD-CLASH-1030');
});

test('MIT-06: mitra berhasil mengambil pesanan secara FCFS dan mengunci status', function () {
    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(2)->setTime(11, 0);

    $order = Order::create([
        'order_code' => 'ORD-TAKE-FCFS',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Dimas',
        'recipient_gender' => 'pria',
        'recipient_phone' => '0822222222',
        'service_address_text' => 'Jl. Trunojoyo No. 44',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->mitraPria)->post(route('mitra.orders.take', $order));

    $response->assertRedirect(route('mitra.orders.my-schedule'));
    $response->assertSessionHas('status');

    $order->refresh();
    expect($order->mitra_id)->toBe($this->mitraPria->id);
    expect($order->status)->toBe(OrderStatus::Dijadwalkan);
    expect($order->statusHistories()->where('to_status', OrderStatus::Dijadwalkan->value)->exists())->toBeTrue();
});

test('MIT-06: pesanan yang telah diambil mitra lain gagal diambil dengan notifikasi', function () {
    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(2)->setTime(15, 0);

    $otherMitra = User::factory()->mitra()->create(['gender' => 'pria']);

    $order = Order::create([
        'order_code' => 'ORD-ALREADY-TAKEN',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'mitra_id' => $otherMitra->id, // Sudah diambil mitra lain
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Eko',
        'recipient_gender' => 'pria',
        'service_address_text' => 'Jl. Gajah Mada',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::Dijadwalkan,
    ]);

    $response = $this->actingAs($this->mitraPria)->post(route('mitra.orders.take', $order));

    $response->assertRedirect(route('mitra.orders.scheduled'));
    $response->assertSessionHas('error', 'Pesanan ini telah diambil oleh mitra lain.');
});

test('MIT-07: menu jadwal saya menampilkan pesanan dan catatan kondisi medis penerima secara terbuka', function () {
    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(1)->setTime(16, 0);

    $order = Order::create([
        'order_code' => 'ORD-SCHEDULE-MED',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitraPria->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Pak Hendra',
        'recipient_gender' => 'pria',
        'recipient_phone' => '081299887766',
        'service_address_text' => 'Perumahan Tegal Besar Permai Blok B-12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'medical_notes' => 'Pernah cedera bahu kanan 2 tahun lalu, hindari tekanan berlebih.',
        'status' => OrderStatus::Dijadwalkan,
    ]);

    $response = $this->actingAs($this->mitraPria)->get(route('mitra.orders.my-schedule'));

    $response->assertOk();
    $response->assertSee('ORD-SCHEDULE-MED');
    $response->assertSee('Pak Hendra');
    $response->assertSee('081299887766');
    $response->assertSee('Pernah cedera bahu kanan 2 tahun lalu, hindari tekanan berlebih.');
    $response->assertSee('Perumahan Tegal Besar Permai Blok B-12');
});

test('MIT-06: mitra pria gagal mengambil pesanan dengan preferensi terapis wanita', function () {
    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(2)->setTime(16, 0);

    $order = Order::create([
        'order_code' => 'ORD-FEMALE-PREF',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Ibu Maya',
        'recipient_gender' => 'wanita',
        'preferred_gender' => 'wanita',
        'service_address_text' => 'Jl. Hayam Wuruk',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    $response = $this->actingAs($this->mitraPria)->post(route('mitra.orders.take', $order));

    $response->assertRedirect(route('mitra.orders.scheduled'));
    $response->assertSessionHas('error', 'Pesanan ini memerlukan mitra terapis wanita.');

    $order->refresh();
    expect($order->mitra_id)->toBeNull();
    expect($order->status)->toBe(OrderStatus::MencariMitra);
});

test('MIT-06: pesanan terjadwal dengan jarak melebihi 10 km tidak muncul di bursa dan gagal diambil', function () {
    // Mitra berada di Yogyakarta (-7.7956, 110.3695)
    DB::table('partner_profiles')
        ->where('id', $this->profileMitraPria->id)
        ->update([
            'current_coordinate' => DB::raw('ST_SRID(POINT(110.3695, -7.7956), 4326)'),
        ]);

    $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(2)->setTime(14, 0);

    // Order berada di Jember (-8.1720, 113.7000), jarak ~370 km (> 10 km)
    $farOrder = Order::create([
        'order_code' => 'ORD-FAR-JEMBER',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Warga Jember',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Kalimantan No. 10, Jember',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => $scheduledTime,
        'status' => OrderStatus::MencariMitra,
    ]);

    // 1. Cek di Bursa Pesanan Terjadwal: Tidak boleh muncul karena jarak > 10 km
    $responseListing = $this->actingAs($this->mitraPria)->get(route('mitra.orders.scheduled'));
    $responseListing->assertOk();
    $responseListing->assertDontSee('ORD-FAR-JEMBER');

    // 2. Cek di Detail Pesanan: Muncul peringatan di luar radius 10 km
    $responseShow = $this->actingAs($this->mitraPria)->get(route('mitra.orders.show', $farOrder));
    $responseShow->assertOk();
    $responseShow->assertSee('Di Luar Radius Maksimal Layanan (10 km)');

    // 3. Coba ambil pesanan: Harus ditolak dengan notifikasi error jarak
    $responseTake = $this->actingAs($this->mitraPria)->post(route('mitra.orders.take', $farOrder));
    $responseTake->assertRedirect(route('mitra.orders.scheduled'));
    $responseTake->assertSessionHas('error');
    expect(session('error'))->toContain('melebihi batas maksimal 10 km');

    $farOrder->refresh();
    expect($farOrder->mitra_id)->toBeNull();
    expect($farOrder->status)->toBe(OrderStatus::MencariMitra);
});

test('mitra with scheduled order at 4 PM can still receive instant orders at 8 AM and pick other scheduled orders at 10 AM', function () {
    $now = Carbon::parse('2026-10-31 08:00:00');
    Carbon::setTestNow($now);

    PartnerServicePreference::create([
        'partner_profile_id' => $this->profileMitraPria->id,
        'service_id' => $this->serviceA->id,
        'is_enabled' => true,
    ]);

    // 1. Mitra has an accepted scheduled order at 4 PM (16:00, 8 hours later)
    $order4PM = Order::create([
        'order_code' => 'ORD-4PM-SESSION',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitraPria->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Warga Sore',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Sore Hari',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => Carbon::parse('2026-10-31 16:00:00'),
        'status' => OrderStatus::Dijadwalkan,
    ]);

    // 2. Can partner receive instant order at 8 AM?
    $instantOrder = Order::create([
        'order_code' => 'ORD-INSTANT-8AM',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Warga Pagi',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Pagi Hari',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::MencariMitra,
    ]);

    $matchingService = app(InstantOrderMatchingService::class);
    $eligiblePartners = $matchingService->findEligiblePartners($instantOrder);

    // Partner is eligible because 4 PM is far ahead (> 2 hours buffer)
    expect($eligiblePartners->pluck('partner_id'))->toContain($this->mitraPria->id);

    // 3. Can partner pick another scheduled order at 10 AM (10:00 - 11:00)?
    $order10AM = Order::create([
        'order_code' => 'ORD-10AM-SESSION',
        'order_type' => OrderType::Terjadwal,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Warga Siang',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Siang Hari',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'scheduled_start_time' => Carbon::parse('2026-10-31 10:00:00'),
        'status' => OrderStatus::MencariMitra,
    ]);

    $responseListing = $this->actingAs($this->mitraPria)->get(route('mitra.orders.scheduled'));
    $responseListing->assertOk();
    $responseListing->assertSee('ORD-10AM-SESSION');

    // Partner successfully takes the 10 AM order
    $responseTake = $this->actingAs($this->mitraPria)->post(route('mitra.orders.take', $order10AM));
    $responseTake->assertRedirect(route('mitra.orders.my-schedule'));

    $order10AM->refresh();
    expect($order10AM->mitra_id)->toBe($this->mitraPria->id);

    // 4. Partner has orders at 10 AM and 4 PM. Now simulate time is 2:30 PM (14:30)
    // At 14:30, partner is within 2 hours of 4 PM -> instant order matching should exclude partner!
    Carbon::setTestNow(Carbon::parse('2026-10-31 14:30:00'));

    $instantAfternoon = Order::create([
        'order_code' => 'ORD-INSTANT-1430',
        'order_type' => OrderType::Instan,
        'customer_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'service_id' => $this->serviceA->id,
        'service_package_id' => $this->packageA->id,
        'service_name' => $this->serviceA->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Warga Sore Dekat',
        'recipient_gender' => 'pria',
        'preferred_gender' => 'bebas',
        'service_address_text' => 'Jl. Sore Hari Dekat',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'status' => OrderStatus::MencariMitra,
    ]);

    $eligiblePartnersAfternoon = $matchingService->findEligiblePartners($instantAfternoon);
    expect($eligiblePartnersAfternoon->pluck('partner_id'))->not->toContain($this->mitraPria->id);

    Carbon::setTestNow(); // Reset
});
