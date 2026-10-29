<?php

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Ensure an active region exists
    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember Kota',
        'status' => 'aktif',
        'timezone' => 'Asia/Jakarta',
    ]);

    // Create service & packages
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

    $this->package90 = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 90,
        'price' => 140000,
        'is_active' => true,
    ]);

    // Customer
    $this->customer = User::factory()->customer()->create([
        'name' => 'Ahmad Pelanggan',
        'email' => 'ahmad@example.com',
        'phone' => '081234567890',
        'gender' => 'pria',
    ]);

    // Customer address
    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Kaliurang No. 10, Sumbersari',
        'benchmark' => 'Depan Masjid',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);
});

test('tamu tidak dapat mengakses halaman pemesanan', function () {
    $response = $this->get(route('pelanggan.orders.create'));
    $response->assertRedirect(route('login'));
});

test('pelanggan tanpa alamat diarahkan ke halaman tambah alamat', function () {
    $newUser = User::factory()->customer()->create(['gender' => 'pria']);

    $response = $this->actingAs($newUser)->get(route('pelanggan.orders.create'));
    $response->assertRedirect(route('pelanggan.addresses.create'));
});

test('pelanggan dengan alamat dapat membuka halaman pemesanan', function () {
    $response = $this->actingAs($this->customer)->get(route('pelanggan.orders.create', ['package_id' => $this->package60->id]));
    $response->assertOk();
    $response->assertSee('Pijat Tradisional Relaksasi');
    $response->assertSee('Untuk Diri Sendiri');
    $response->assertSee('Untuk Orang Lain');
    $response->assertSee('08:00 - 21:00');
});

test('CUS-04: pemesanan untuk diri sendiri otomatis mengunci data & gender pelanggan', function () {
    $validDate = Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d');

    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $validDate,
        'scheduled_time' => '10:00',
        'risk_agreement' => '1',
    ]);

    $response->assertRedirect();

    $order = Order::latest()->first();
    expect($order)->not->toBeNull();
    expect($order->recipient_type)->toBe('myself');
    expect($order->recipient_name)->toBe($this->customer->name);
    expect($order->recipient_phone)->toBe($this->customer->phone);
    expect($order->recipient_gender)->toBe('pria');
    expect($order->order_type)->toBe(OrderType::Terjadwal);
});

test('CUS-04: pemesanan untuk orang lain wajib mengisi nama, telepon, dan gender penerima', function () {
    $validDate = Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d');

    // Submit tanpa data penerima orang lain -> gagal validasi
    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'other',
        'recipient_name' => '',
        'recipient_phone' => '',
        'recipient_gender' => '',
        'order_type' => 'terjadwal',
        'scheduled_date' => $validDate,
        'scheduled_time' => '10:00',
        'risk_agreement' => '1',
    ]);

    $response->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'recipient_gender']);

    // Submit dengan data penerima orang lain (wanita)
    $responseValid = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'other',
        'recipient_name' => 'Siti Nurhaliza',
        'recipient_phone' => '089876543210',
        'recipient_gender' => 'wanita',
        'order_type' => 'terjadwal',
        'scheduled_date' => $validDate,
        'scheduled_time' => '14:00',
        'risk_agreement' => '1',
    ]);

    $responseValid->assertRedirect();

    $order = Order::latest()->first();
    expect($order->recipient_type)->toBe('other');
    expect($order->recipient_name)->toBe('Siti Nurhaliza');
    expect($order->recipient_phone)->toBe('089876543210');
    expect($order->recipient_gender)->toBe('wanita');
});

test('CUS-06: pemesanan terjadwal menolak waktu kurang dari 3 jam dari sekarang', function () {
    // 1 jam dari sekarang
    $tooSoon = Carbon::now('Asia/Jakarta')->addHour();

    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $tooSoon->format('Y-m-d'),
        'scheduled_time' => $tooSoon->format('H:i'),
        'risk_agreement' => '1',
    ]);

    $response->assertSessionHasErrors(['scheduled_time']);
});

test('CUS-06: pemesanan terjadwal menolak waktu lebih dari 7 hari ke depan', function () {
    $tooFar = Carbon::now('Asia/Jakarta')->addDays(8);

    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $tooFar->format('Y-m-d'),
        'scheduled_time' => '10:00',
        'risk_agreement' => '1',
    ]);

    $response->assertSessionHasErrors(['scheduled_time']);
});

test('CUS-06: pemesanan terjadwal menolak waktu di luar jam operasional (08:00 - 21:00)', function () {
    $tomorrow = Carbon::now('Asia/Jakarta')->addDay()->format('Y-m-d');

    // Terlalu pagi (07:00)
    $responseMorning = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $tomorrow,
        'scheduled_time' => '07:00',
        'risk_agreement' => '1',
    ]);
    $responseMorning->assertSessionHasErrors(['scheduled_time']);

    // Mulai 20:30 dengan durasi 90 menit -> selesai 22:00 (melebihi 21:00)
    $responseOvertime = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package90->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $tomorrow,
        'scheduled_time' => '20:30',
        'risk_agreement' => '1',
    ]);
    $responseOvertime->assertSessionHasErrors(['scheduled_time']);
});

test('CUS-07 & SYS-08: wajib menyetujui ketentuan risiko dan menyimpan versi persetujuan', function () {
    $validDate = Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d');

    // Tanpa centang risk_agreement -> gagal
    $responseNoRisk = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $validDate,
        'scheduled_time' => '10:00',
    ]);
    $responseNoRisk->assertSessionHasErrors(['risk_agreement']);

    // Dengan centang risk_agreement -> berhasil dan tersimpan versi v1.0
    $responseWithRisk = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $validDate,
        'scheduled_time' => '10:00',
        'risk_agreement' => '1',
    ]);
    $responseWithRisk->assertRedirect();

    $order = Order::latest()->first();
    expect($order->risk_agreement_version)->toBe('v1.0');
    expect($order->consent_timestamp)->not->toBeNull();
});

test('CUS-07: catatan medis terenkripsi di database dan didekripsi saat diakses model', function () {
    $validDate = Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d');
    $secretNotes = 'Pasien memiliki riwayat cedera tulang ekor dan alergi minyak kayu putih.';

    $response = $this->actingAs($this->customer)->post(route('pelanggan.orders.store'), [
        'package_id' => $this->package60->id,
        'customer_address_id' => $this->address->id,
        'recipient_type' => 'myself',
        'order_type' => 'terjadwal',
        'scheduled_date' => $validDate,
        'scheduled_time' => '10:00',
        'risk_agreement' => '1',
        'medical_notes' => $secretNotes,
    ]);

    $response->assertRedirect();

    // 1. Raw DB check: Kolom medical_notes di database TIDAK tersimpan sebagai plain text
    $rawOrder = DB::table('orders')->latest('id')->first();
    expect($rawOrder->medical_notes)->not->toBe($secretNotes);
    expect($rawOrder->medical_notes)->not->toBeNull();

    // 2. Eloquent model check: Didekripsi secara transparan
    $order = Order::latest()->first();
    expect($order->medical_notes)->toBe($secretNotes);

    // 3. Privacy check (CUS-07): Catatan medis TIDAK tampil pada penawaran awal (menunggu_pembayaran/mencari_mitra)
    expect($order->getMedicalNotesForPartner())->toBeNull();

    // Catatan medis hanya tampil saat mitra sudah menerima/dijadwalkan
    $order->status = OrderStatus::Dijadwalkan;
    expect($order->getMedicalNotesForPartner())->toBe($secretNotes);
});
