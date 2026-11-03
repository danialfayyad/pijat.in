<?php

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
    ]);

    $this->service = Service::factory()->create([
        'name' => 'Pijat Refleksi',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 120000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Pengadu',
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Teradu',
    ]);

    Wallet::create(['user_id' => $this->customer->id, 'balance' => 200000]);
    Wallet::create(['user_id' => $this->mitra->id, 'balance' => 0]);

    $this->address = CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Mastrip No. 5',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);

    $this->category = ComplaintCategory::first() ?? ComplaintCategory::create([
        'name' => 'Kualitas Layanan Tidak Sesuai',
        'description' => 'Terapis tidak melayani sesuai SOP',
        'default_points' => 20,
        'is_active' => true,
    ]);

    $this->createOrder = function (array $overrides = []) {
        return Order::create(array_merge([
            'order_code' => 'ORD-'.strtoupper(uniqid()),
            'customer_id' => $this->customer->id,
            'mitra_id' => $this->mitra->id,
            'region_id' => $this->region->id,
            'service_id' => $this->service->id,
            'service_package_id' => $this->package->id,
            'service_name' => $this->service->name,
            'duration_minutes' => 60,
            'total_price' => 120000,
            'recipient_name' => 'Budi Penerima',
            'recipient_gender' => 'pria',
            'order_type' => OrderType::Instan,
            'status' => OrderStatus::Selesai,
            'service_address_text' => $this->address->address_text,
            'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
            'service_completed_at' => Carbon::now()->subHours(2),
        ], $overrides));
    };
});

test('customer can view complaints index page', function () {
    $order = ($this->createOrder)();

    Complaint::create([
        'order_id' => $order->id,
        'region_id' => $this->region->id,
        'complainant_id' => $this->customer->id,
        'complainant_role' => 'pelanggan',
        'target_id' => $this->mitra->id,
        'complaint_category_id' => $this->category->id,
        'title' => 'Terapis datang terlambat dan memotong durasi',
        'description' => 'Durasi pijat hanya 40 menit dari seharusnya 60 menit.',
        'status' => ComplaintStatus::Menunggu,
        'priority' => 'normal',
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.complaints.index'));

    $response->assertOk();
    $response->assertSee('Terapis datang terlambat');
    $response->assertSee('Aduan Baru');
});

test('customer can access complaint create form for completed order within 24 hours (CUS-19)', function () {
    $order = ($this->createOrder)();

    $response = $this->actingAs($this->customer)->get(route('pelanggan.complaints.create', $order));

    $response->assertOk();
    $response->assertSee('Aduan Layanan');
    $response->assertSee($order->order_code);
});

test('customer cannot access complaint create form if order is not completed', function () {
    $order = ($this->createOrder)([
        'status' => OrderStatus::SedangBerlangsung,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.complaints.create', $order));

    $response->assertRedirect(route('pelanggan.orders.show', $order));
    $response->assertSessionHas('error');
});

test('customer cannot access complaint create form if order is completed more than 24 hours ago (CUS-19)', function () {
    $order = ($this->createOrder)();
    $order->forceFill([
        'service_completed_at' => Carbon::now()->subHours(26),
    ])->save();

    $response = $this->actingAs($this->customer)->get(route('pelanggan.complaints.create', $order));

    $response->assertRedirect(route('pelanggan.orders.show', $order));
    $response->assertSessionHas('error', 'Batas waktu pengajuan aduan (maksimal 24 jam sejak layanan selesai) telah berakhir.');
});

test('customer can submit valid complaint with photo evidence (CUS-19)', function () {
    $order = ($this->createOrder)();

    $photo = UploadedFile::fake()->image('bukti.jpg', 600, 600);

    $response = $this->actingAs($this->customer)->post(route('pelanggan.complaints.store', $order), [
        'complaint_category_id' => $this->category->id,
        'title' => 'Layanan Tidak Sesuai SOP',
        'description' => 'Terapis bersikap kurang ramah dan durasi dipotong 20 menit.',
        'evidence_photos' => [$photo],
    ]);

    $complaint = Complaint::where('order_id', $order->id)->first();
    expect($complaint)->not->toBeNull()
        ->and($complaint->complainant_id)->toBe($this->customer->id)
        ->and($complaint->complainant_role)->toBe('pelanggan')
        ->and($complaint->target_id)->toBe($this->mitra->id)
        ->and($complaint->status)->toBe(ComplaintStatus::Menunggu)
        ->and($complaint->priority)->toBe('normal')
        ->and($complaint->evidence_photos)->toHaveCount(1);

    $response->assertRedirect(route('pelanggan.complaints.show', $complaint));
});

test('customer cannot submit duplicate complaint on same order', function () {
    $order = ($this->createOrder)();

    Complaint::create([
        'order_id' => $order->id,
        'region_id' => $this->region->id,
        'complainant_id' => $this->customer->id,
        'complainant_role' => 'pelanggan',
        'target_id' => $this->mitra->id,
        'complaint_category_id' => $this->category->id,
        'title' => 'Keluhan pertama',
        'description' => 'Deskripsi keluhan pertama',
        'status' => ComplaintStatus::Menunggu,
        'priority' => 'normal',
    ]);

    $response = $this->actingAs($this->customer)->post(route('pelanggan.complaints.store', $order), [
        'complaint_category_id' => $this->category->id,
        'title' => 'Keluhan kedua',
        'description' => 'Mencoba membuat keluhan kedua pada order yang sama.',
    ]);

    $response->assertRedirect(route('pelanggan.orders.show', $order));
    $response->assertSessionHas('error');
    expect(Complaint::where('order_id', $order->id)->count())->toBe(1);
});
