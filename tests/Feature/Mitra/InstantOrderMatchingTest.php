<?php

use App\Enums\OrderStatus;
use App\Enums\UserStatus;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderDispatch;
use App\Models\PartnerProfile;
use App\Models\PartnerServicePreference;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // 1. Region
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember Kota',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    // 2. Service & Package
    $this->service = Service::factory()->create([
        'name' => 'Pijat Tradisional Relaksasi',
        'is_active' => true,
    ]);

    $this->package60 = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    // 3. Customer (Pria)
    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Pria',
        'gender' => 'pria',
        'status' => UserStatus::Terverifikasi,
    ]);
    $this->customer->getOrCreateWallet()->update(['balance' => 200000]);

    // Customer Address at -8.1720, 113.7000
    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kalimantan No. 37, Sumbersari, Jember',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);
});

/**
 * Helper to create a verified mitra in the same region.
 */
function createMitraInLocation(Region $region, Service $service, string $gender, float $lat, float $lng, bool $isActive = true, UserStatus $status = UserStatus::Terverifikasi): User
{
    $mitra = User::factory()->mitra()->create([
        'name' => 'Terapis '.ucfirst($gender).' '.uniqid(),
        'gender' => $gender,
        'status' => $status,
    ]);

    $pointWkt = sprintf('POINT(%F %F)', $lng, $lat);

    $profile = PartnerProfile::create([
        'user_id' => $mitra->id,
        'region_id' => $region->id,
        'address_text' => 'Alamat Terapis',
        'coordinate' => ['lat' => $lat, 'lng' => $lng],
        'current_coordinate' => ['lat' => $lat, 'lng' => $lng],
        'ktp_path' => 'documents/ktp/dummy.jpg',
        'selfie_path' => 'documents/selfie/dummy.jpg',
        'is_active' => $isActive,
        'last_active_at' => now(),
    ]);

    PartnerServicePreference::create([
        'partner_profile_id' => $profile->id,
        'service_id' => $service->id,
        'is_enabled' => true,
    ]);

    return $mitra;
}

test('MIT-04: hanya mitra terverifikasi yang dapat mengubah status online dan wajib izin GPS', function () {
    // Mitra belum verifikasi (MenungguVerifikasi)
    $unverifiedMitra = User::factory()->mitra()->create([
        'status' => UserStatus::MenungguVerifikasi,
    ]);
    PartnerProfile::create([
        'user_id' => $unverifiedMitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Alamat Belum Lengkap',
        'coordinate' => ['lat' => -8.1725, 'lng' => 113.7005],
        'ktp_path' => 'documents/ktp/dummy.jpg',
        'selfie_path' => 'documents/selfie/dummy.jpg',
        'is_active' => false,
    ]);

    $res = $this->actingAs($unverifiedMitra)->postJson(route('mitra.toggle-active'), [
        'is_active' => 1,
        'latitude' => -8.1725,
        'longitude' => 113.7005,
    ]);
    $res->assertStatus(422);

    // Mitra terverifikasi sukses online
    $verifiedMitra = createMitraInLocation($this->region, $this->service, 'pria', -8.1730, 113.7010, false);

    $resSuccess = $this->actingAs($verifiedMitra)->postJson(route('mitra.toggle-active'), [
        'is_active' => 1,
        'latitude' => -8.1730,
        'longitude' => 113.7010,
    ]);
    $resSuccess->assertOk();
    $resSuccess->assertJson(['is_active' => true]);

    $profile = $verifiedMitra->partnerProfile->fresh();
    expect($profile->is_active)->toBeTrue();
    expect($profile->last_active_at)->not->toBeNull();
});

test('MIT-04: lokasi koordinat mitra dapat diperbarui via ping lokasi setiap 5 menit', function () {
    $mitra = createMitraInLocation($this->region, $this->service, 'pria', -8.1730, 113.7010, true);

    $res = $this->actingAs($mitra)->postJson(route('mitra.location.ping'), [
        'latitude' => -8.1750,
        'longitude' => 113.7020,
    ]);
    $res->assertOk();
    $res->assertJson(['status' => 'success']);
});

test('CUS-08: pengecekan ketersediaan mitra instan via AJAX (tersedia vs tidak tersedia)', function () {
    // 1. Kondisi 0 mitra online
    $resUnavailable = $this->actingAs($this->customer)->getJson(route('pelanggan.orders.check-instant', [
        'address_id' => $this->address->id,
        'gender' => 'pria',
        'package_id' => $this->package60->id,
    ]));
    $resUnavailable->assertOk();
    $resUnavailable->assertJson(['available' => false]);

    // 2. Tambah mitra pria online dalam radius 1.5 km
    createMitraInLocation($this->region, $this->service, 'pria', -8.1740, 113.7020, true);

    $resAvailable = $this->actingAs($this->customer)->getJson(route('pelanggan.orders.check-instant', [
        'address_id' => $this->address->id,
        'gender' => 'pria',
        'package_id' => $this->package60->id,
    ]));
    $resAvailable->assertOk();
    $resAvailable->assertJson(['available' => true]);
});

test('CUS-08: pembuatan pesanan instan ditolak jika 0 mitra online dan disarankan ke terjadwal', function () {
    // 0 mitra online
    $res = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'instan',
        'risk_agreement' => '1',
    ]);

    $res->assertSessionHasErrors(['order_type']);
});

test('SYS-01 & MIT-05: penawaran sekuensial 60 detik ke mitra terdekat dan terima pesanan', function () {
    // Mitra 1 terdekat (jarak ~300m)
    $mitra1 = createMitraInLocation($this->region, $this->service, 'pria', -8.1730, 113.7010, true);
    // Mitra 2 lebih jauh (jarak ~2.5km)
    $mitra2 = createMitraInLocation($this->region, $this->service, 'pria', -8.1900, 113.7150, true);

    // Customer memesan instan
    $res = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'instan',
        'risk_agreement' => '1',
    ]);
    $res->assertRedirect();

    $order = Order::latest()->first();
    expect($order->status)->toBe(OrderStatus::MencariMitra);

    // Tawaran pertama wajib masuk ke Mitra 1 (terdekat)
    $dispatch1 = OrderDispatch::where('order_id', $order->id)->first();
    expect($dispatch1)->not->toBeNull();
    expect($dispatch1->partner_id)->toBe($mitra1->id);
    expect($dispatch1->status)->toBe(OrderDispatch::STATUS_OFFERED);

    // Mitra 1 memeriksa pending offer (MIT-05: tidak ada alamat lengkap)
    $resOffer = $this->actingAs($mitra1)->getJson(route('mitra.orders.pending-offer'));
    $resOffer->assertOk();
    $resOffer->assertJson([
        'has_offer' => true,
        'dispatch_id' => $dispatch1->id,
        'service_name' => $order->service_name,
        'duration_minutes' => 60,
    ]);
    $resOffer->assertJsonMissing(['address_text' => $this->address->address_text]);

    // Mitra 1 menerima pesanan -> status pesanan menjadi Dijadwalkan
    $resAccept = $this->actingAs($mitra1)->postJson(route('mitra.orders.offers.accept', $dispatch1));
    $resAccept->assertOk();
    $resAccept->assertJson(['success' => true]);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Dijadwalkan);
    expect($order->mitra_id)->toBe($mitra1->id);
    expect($dispatch1->fresh()->status)->toBe(OrderDispatch::STATUS_ACCEPTED);
});

test('SYS-01 & MIT-05: mitra menolak atau timeout 60 detik berlanjut ke mitra terdekat berikutnya', function () {
    // Mitra 1 (dekat ~300m)
    $mitra1 = createMitraInLocation($this->region, $this->service, 'pria', -8.1730, 113.7010, true);
    // Mitra 2 (agak jauh ~2.5km)
    $mitra2 = createMitraInLocation($this->region, $this->service, 'pria', -8.1900, 113.7150, true);

    // Buat pesanan instan
    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'instan',
        'risk_agreement' => '1',
    ]);

    $order = Order::latest()->first();
    $dispatch1 = OrderDispatch::where('order_id', $order->id)->where('partner_id', $mitra1->id)->first();
    expect($dispatch1)->not->toBeNull();

    // Skenario 1: Mitra 1 menolak tawaran
    $resReject = $this->actingAs($mitra1)->postJson(route('mitra.orders.offers.reject', $dispatch1), [
        'reason' => 'Sedang ada urusan mendadak.',
    ]);
    $resReject->assertOk();

    // Tawaran otomatis berpindah ke Mitra 2
    $dispatch2 = OrderDispatch::where('order_id', $order->id)->where('partner_id', $mitra2->id)->first();
    expect($dispatch2)->not->toBeNull();
    expect($dispatch2->status)->toBe(OrderDispatch::STATUS_OFFERED);

    // Skenario 2: Simulasi 60 detik timeout pada Mitra 2 via command console
    $dispatch2->update(['expires_at' => Carbon::now()->subSeconds(5)]);

    $this->artisan('orders:process-instant-offers')->assertSuccessful();

    expect($dispatch2->fresh()->status)->toBe(OrderDispatch::STATUS_EXPIRED);
});

test('CUS-08 & SYS-01: pesanan instan dengan preferensi gender terapis (Opsi A) mencocokkan mitra sesuai preferensi', function () {
    // Mitra Pria lebih dekat (~300m)
    $mitraPria = createMitraInLocation($this->region, $this->service, 'pria', -8.1730, 113.7010, true);
    // Mitra Wanita lebih jauh (~1.5km)
    $mitraWanita = createMitraInLocation($this->region, $this->service, 'wanita', -8.1820, 113.7050, true);

    // Skenario 1: Pelanggan meminta khusus Terapis Wanita
    $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'preferred_gender' => 'wanita',
        'order_type' => 'instan',
        'risk_agreement' => '1',
    ]);

    $order1 = Order::latest()->first();
    expect($order1->preferred_gender)->toBe('wanita');

    // Tawaran harus langsung ke Mitra Wanita (meskipun Mitra Pria lebih dekat)
    $dispatch1 = OrderDispatch::where('order_id', $order1->id)->first();
    expect($dispatch1)->not->toBeNull();
    expect($dispatch1->partner_id)->toBe($mitraWanita->id);

    // Skenario 2: Pelanggan memilih 'bebas' (siapa saja)
    $this->customer->getOrCreateWallet()->update(['balance' => 200000]);
    $res2 = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'preferred_gender' => 'bebas',
        'order_type' => 'instan',
        'risk_agreement' => '1',
    ]);
    $res2->assertSessionHasNoErrors();
    $res2->assertRedirect();

    $order2 = Order::latest('id')->first();
    expect($order2->id)->not->toBe($order1->id);
    expect($order2->preferred_gender)->toBe('bebas');

    // Tawaran harus masuk ke mitra terdekat (Mitra Pria)
    $dispatch2 = OrderDispatch::where('order_id', $order2->id)->first();
    expect($dispatch2)->not->toBeNull();
    expect($dispatch2->partner_id)->toBe($mitraPria->id);
});
