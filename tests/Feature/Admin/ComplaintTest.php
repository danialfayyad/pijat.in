<?php

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\Role;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\User;
use App\Services\RegionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->regionService = app(RegionService::class);

    $coordsA = [
        [110.0, -7.0],
        [110.4, -7.0],
        [110.4, -6.6],
        [110.0, -6.6],
        [110.0, -7.0],
    ];

    $coordsB = [
        [111.0, -7.0],
        [111.4, -7.0],
        [111.4, -6.6],
        [111.0, -6.6],
        [111.0, -7.0],
    ];

    $this->regionA = $this->regionService->createRegion('Wilayah Solo', $coordsA);
    $this->regionB = $this->regionService->createRegion('Wilayah Madiun', $coordsB);

    $this->adminA = User::factory()->create([
        'name' => 'Admin Solo',
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
    $this->regionService->assignAdmin($this->adminA, $this->regionA, true);
    $this->regionService->activateRegion($this->regionA);

    $this->adminB = User::factory()->create([
        'name' => 'Admin Madiun',
        'role' => Role::Admin,
        'email_verified_at' => now(),
    ]);
    $this->regionService->assignAdmin($this->adminB, $this->regionB, true);
    $this->regionService->activateRegion($this->regionB);

    $this->superadmin = User::factory()->create([
        'name' => 'Superadmin Utama',
        'role' => Role::Superadmin,
        'email_verified_at' => now(),
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Solo',
        'phone' => '08123456789',
    ]);

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Terapis',
        'phone' => '08987654321',
    ]);

    $this->orderA = Order::factory()->create([
        'region_id' => $this->regionA->id,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'order_code' => 'ORD-SOLO-201',
    ]);

    $this->orderB = Order::factory()->create([
        'region_id' => $this->regionB->id,
        'customer_id' => $this->customer->id,
        'mitra_id' => $this->mitra->id,
        'order_code' => 'ORD-MADIUN-202',
    ]);
});

test('guest or non-admin cannot access complaint center', function () {
    $this->get(route('admin.complaints.index'))->assertRedirect(route('login'));

    $this->actingAs($this->customer)
        ->get(route('admin.complaints.index'))
        ->assertForbidden();
});

test('admin without assigned regions receives 403 on complaint center', function () {
    $unassignedAdmin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($unassignedAdmin)
        ->get(route('admin.complaints.index'))
        ->assertForbidden();
});

test('admin only sees complaints within their assigned regions (ADM-08)', function () {
    $complaintA = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'complainant_id' => $this->customer->id,
        'target_id' => $this->mitra->id,
        'title' => 'Keluhan keterlambatan terapis di Solo',
    ]);

    $complaintB = Complaint::factory()->create([
        'region_id' => $this->regionB->id,
        'order_id' => $this->orderB->id,
        'complainant_id' => $this->customer->id,
        'target_id' => $this->mitra->id,
        'title' => 'Keluhan pelayanan pijat di Madiun',
    ]);

    $response = $this->actingAs($this->adminA)->get(route('admin.complaints.index'));

    $response->assertOk()
        ->assertSee($complaintA->complaint_code)
        ->assertDontSee($complaintB->complaint_code);
});

test('superadmin can view complaints across all regions (SA-07)', function () {
    $complaintA = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'title' => 'Aduan Wilayah Solo',
    ]);

    $complaintB = Complaint::factory()->create([
        'region_id' => $this->regionB->id,
        'order_id' => $this->orderB->id,
        'title' => 'Aduan Wilayah Madiun',
    ]);

    $response = $this->actingAs($this->superadmin)->get(route('admin.complaints.index'));

    $response->assertOk()
        ->assertSee($complaintA->complaint_code)
        ->assertSee($complaintB->complaint_code);
});

test('admin can view complaint detail for assigned region but 403 for unassigned region', function () {
    $complaintA = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
    ]);

    $complaintB = Complaint::factory()->create([
        'region_id' => $this->regionB->id,
        'order_id' => $this->orderB->id,
    ]);

    $this->actingAs($this->adminA)
        ->get(route('admin.complaints.show', $complaintA))
        ->assertOk();

    $this->actingAs($this->adminA)
        ->get(route('admin.complaints.show', $complaintB))
        ->assertForbidden();
});

test('admin can transition complaint from menunggu to diproses', function () {
    $complaint = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.complaints.process', $complaint));

    $response->assertRedirect(route('admin.complaints.show', $complaint))
        ->assertSessionHas('status');

    $complaint->refresh();
    expect($complaint->status)->toBe(ComplaintStatus::Diproses)
        ->and($complaint->handled_by_id)->toBe($this->adminA->id)
        ->and($complaint->handled_at)->not->toBeNull();
});

test('admin can resolve complaint with refund_penuh decision (ADM-08)', function () {
    $complaint = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Diproses,
        'handled_by_id' => $this->adminA->id,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.complaints.resolve', $complaint), [
            'resolution_type' => 'refund_penuh',
            'admin_notes' => 'Terapis terbukti tidak hadir di lokasi sesuai jam pesanan. Refund penuh diberikan ke customer.',
        ]);

    $response->assertRedirect(route('admin.complaints.show', $complaint))
        ->assertSessionHas('status');

    $complaint->refresh();
    expect($complaint->status)->toBe(ComplaintStatus::Selesai)
        ->and($complaint->resolution_type)->toBe('refund_penuh')
        ->and($complaint->resolved_at)->not->toBeNull();
});

test('admin can resolve complaint with terbukti_bersalah decision and apply penalty points (ADM-08)', function () {
    $category = App\Models\ComplaintCategory::factory()->create([
        'penalty_points' => 35,
    ]);

    $complaint = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'target_id' => $this->mitra->id,
        'complaint_category_id' => $category->id,
        'status' => ComplaintStatus::Diproses,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.complaints.resolve', $complaint), [
            'resolution_type' => 'terbukti_bersalah',
            'admin_notes' => 'Terapis terbukti melanggar etika profesional saat melayani pelanggan di kamar.',
        ]);

    $response->assertRedirect(route('admin.complaints.show', $complaint))
        ->assertSessionHas('status');

    $complaint->refresh();
    $this->mitra->refresh();

    expect($complaint->status)->toBe(ComplaintStatus::Selesai)
        ->and($complaint->resolution_type)->toBe('terbukti_bersalah')
        ->and($complaint->penalty_points_applied)->toBe(35)
        ->and($this->mitra->penalty_points)->toBe(35);

    $this->assertDatabaseHas('penalty_ledgers', [
        'user_id' => $this->mitra->id,
        'complaint_id' => $complaint->id,
        'points' => 35,
        'type' => 'violation',
    ]);
});

test('admin can resolve complaint with rekomendasi_sp decision (ADM-08)', function () {
    $complaint = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Diproses,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.complaints.resolve', $complaint), [
            'resolution_type' => 'rekomendasi_sp',
            'admin_notes' => 'Terapis terbukti melanggar etika profesional saat melayani pelanggan di kamar.',
        ]);

    $response->assertRedirect(route('admin.complaints.show', $complaint));

    $complaint->refresh();
    expect($complaint->status)->toBe(ComplaintStatus::Selesai)
        ->and($complaint->resolution_type)->toBe('rekomendasi_sp');
});

test('resolving complaint fails validation if admin_notes is shorter than 10 characters', function () {
    $complaint = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Diproses,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.complaints.resolve', $complaint), [
            'resolution_type' => 'refund_penuh',
            'admin_notes' => 'Pendek',
        ]);

    $response->assertSessionHasErrors(['admin_notes']);

    $complaint->refresh();
    expect($complaint->status)->toBe(ComplaintStatus::Diproses);
});

test('admin can reject an unfounded complaint with optional false report flag (ADM-08)', function () {
    $complaint = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Diproses,
    ]);

    $response = $this->actingAs($this->adminA)
        ->post(route('admin.complaints.reject', $complaint), [
            'admin_notes' => 'Setelah diverifikasi dengan chat dan log GPS, terapis telah bekerja sesuai durasi penuh.',
            'is_false_complaint' => 1,
        ]);

    $response->assertRedirect(route('admin.complaints.show', $complaint))
        ->assertSessionHas('status');

    $complaint->refresh();
    expect($complaint->status)->toBe(ComplaintStatus::Ditolak)
        ->and($complaint->resolution_type)->toBe('ditolak')
        ->and($complaint->admin_notes)->toContain('Laporan terindikasi aduan palsu')
        ->and($complaint->resolved_at)->not->toBeNull();
});

test('admin cannot resolve or reject a complaint from unassigned region', function () {
    $complaintB = Complaint::factory()->create([
        'region_id' => $this->regionB->id,
        'order_id' => $this->orderB->id,
        'status' => ComplaintStatus::Diproses,
    ]);

    $this->actingAs($this->adminA)
        ->post(route('admin.complaints.resolve', $complaintB), [
            'resolution_type' => 'refund_penuh',
            'admin_notes' => 'Mencoba menyelesaikan aduan wilayah lain tanpa izin.',
        ])
        ->assertForbidden();

    $this->actingAs($this->adminA)
        ->post(route('admin.complaints.reject', $complaintB), [
            'admin_notes' => 'Mencoba menolak aduan wilayah lain tanpa izin.',
        ])
        ->assertForbidden();

    $complaintB->refresh();
    expect($complaintB->status)->toBe(ComplaintStatus::Diproses);
});

test('filtering by status tab and category works accurately', function () {
    $c1 = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Menunggu,
        'category' => ComplaintCategory::KeterlambatanParah,
        'title' => 'Aduan Terlambat 1',
    ]);

    $c2 = Complaint::factory()->create([
        'region_id' => $this->regionA->id,
        'order_id' => $this->orderA->id,
        'status' => ComplaintStatus::Selesai,
        'category' => ComplaintCategory::LayananTidakSesuai,
        'title' => 'Aduan Layanan Kurang Pas',
    ]);

    // Test filter status=menunggu
    $response = $this->actingAs($this->adminA)
        ->get(route('admin.complaints.index', ['status' => 'menunggu']));

    $response->assertOk()
        ->assertSee($c1->complaint_code)
        ->assertDontSee($c2->complaint_code);

    // Test filter category=layanan_tidak_sesuai
    $responseCat = $this->actingAs($this->adminA)
        ->get(route('admin.complaints.index', ['category' => 'layanan_tidak_sesuai']));

    $responseCat->assertOk()
        ->assertSee($c2->complaint_code)
        ->assertDontSee($c1->complaint_code);
});
