<?php

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
    ]);

    $this->service = Service::factory()->create([
        'name' => 'Pijat Tradisional',
        'is_active' => true,
    ]);

    $this->package = ServicePackage::create([
        'service_id' => $this->service->id,
        'duration_minutes' => 60,
        'price' => 100000,
        'is_active' => true,
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Uji',
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Pelapor',
    ]);

    $this->otherMitra = User::factory()->mitra()->create([
        'name' => 'Mitra Lain',
    ]);

    CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Mastrip No. 12',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);

    PartnerProfile::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Jawa No. 10',
        'coordinate' => ['lat' => -8.1725, 'lng' => 113.7005],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'is_active' => true,
        'verified_at' => now(),
    ]);

    $this->category = ComplaintCategory::first() ?? ComplaintCategory::create([
        'name' => 'Etika / Perilaku Tidak Sopan',
        'description' => 'Pelanggan berperilaku tidak sopan atau kasar',
        'default_points' => 15,
        'is_active' => true,
    ]);

    $this->order = Order::create([
        'order_code' => 'ORD-'.strtoupper(uniqid()),
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'service_id' => $this->service->id,
        'service_package_id' => $this->package->id,
        'service_name' => $this->service->name,
        'duration_minutes' => 60,
        'total_price' => 100000,
        'recipient_name' => 'Penerima Uji',
        'recipient_gender' => 'pria',
        'order_type' => OrderType::Instan,
        'status' => OrderStatus::SedangBerlangsung,
        'service_address_text' => 'Jl. Mastrip No. 12',
        'service_coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
    ]);
});

test('partner assigned to order can access incident report form (MIT-19)', function () {
    $response = $this->actingAs($this->mitra)->get(route('mitra.orders.incident.create', $this->order));

    $response->assertOk();
    $response->assertSee('Lapor Kendala');
    $response->assertSee($this->order->order_code);
    $response->assertSee('Tandai sebagai Keadaan Darurat');
});

test('unassigned partner cannot access incident report form', function () {
    $response = $this->actingAs($this->otherMitra)->get(route('mitra.orders.incident.create', $this->order));

    $response->assertForbidden();
});

test('partner can submit standard incident report (priority tinggi) (MIT-19)', function () {
    $photo = UploadedFile::fake()->image('kendala.jpg', 600, 600);

    $response = $this->actingAs($this->mitra)->post(route('mitra.orders.incident.store', $this->order), [
        'complaint_category_id' => $this->category->id,
        'title' => 'Pelanggan Minta Layanan Tambahan di Luar SOP',
        'description' => 'Pelanggan memaksa meminta layanan di luar katalog dan menolak SOP aplikasi.',
        'is_emergency' => '0',
        'evidence_photos' => [$photo],
    ]);

    $response->assertRedirect(route('mitra.orders.show', $this->order));
    $response->assertSessionHas('status');

    $complaint = Complaint::where('order_id', $this->order->id)->where('complainant_role', 'mitra')->first();
    expect($complaint)->not->toBeNull()
        ->and($complaint->complainant_id)->toBe($this->mitra->id)
        ->and($complaint->target_id)->toBe($this->customer->id)
        ->and($complaint->priority)->toBe('tinggi')
        ->and($complaint->status)->toBe(ComplaintStatus::Menunggu)
        ->and($complaint->evidence_photos)->toHaveCount(1);
});

test('partner can submit emergency incident report (priority darurat) (MIT-19)', function () {
    $response = $this->actingAs($this->mitra)->post(route('mitra.orders.incident.store', $this->order), [
        'title' => 'Pelecehan / Ancaman Fisik di Tempat Layanan',
        'description' => 'Pelanggan mengunci pintu dan bertindak agresif secara fisik.',
        'is_emergency' => '1',
    ]);

    $response->assertRedirect(route('mitra.orders.show', $this->order));
    $response->assertSessionHas('status');

    $complaint = Complaint::where('order_id', $this->order->id)->where('complainant_role', 'mitra')->first();
    expect($complaint)->not->toBeNull()
        ->and($complaint->priority)->toBe('darurat')
        ->and($complaint->status)->toBe(ComplaintStatus::Menunggu);
});
