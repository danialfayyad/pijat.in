<?php

use App\Enums\AppealStatus;
use App\Models\CustomerAddress;
use App\Models\PenaltyAppeal;
use App\Models\PenaltyLedger;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->region = Region::where('status', 'aktif')->first() ?? Region::factory()->create([
        'name' => 'Wilayah Jember',
        'status' => 'aktif',
    ]);

    $this->customer = User::factory()->customer()->create([
        'name' => 'Pelanggan Pelanggar',
        'penalty_points' => 30,
        'region_id' => $this->region->id,
    ]);

    CustomerAddress::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'label' => 'Rumah',
        'address_text' => 'Jl. Mastrip No. 10',
        'latitude' => -8.1720,
        'longitude' => 113.7000,
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'is_primary' => true,
    ]);
});

test('customer can view penalty status and ledger history (CUS-20)', function () {
    PenaltyLedger::create([
        'user_id' => $this->customer->id,
        'type' => 'violation',
        'points' => 30,
        'balance_before' => 0,
        'balance_after' => 30,
        'reason' => 'Customer no-show pada pesanan ORD-TEST',
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.penalties.index'));

    $response->assertOk();
    $response->assertSee('30');
    $response->assertSee('Peringatan');
    $response->assertSee('Customer no-show');
});

test('customer with zero penalty points is blocked from appeal form', function () {
    $cleanCustomer = User::factory()->customer()->create([
        'penalty_points' => 0,
        'region_id' => $this->region->id,
    ]);

    $response = $this->actingAs($cleanCustomer)->get(route('pelanggan.penalties.appeal.create'));

    $response->assertRedirect(route('pelanggan.penalties.index'));
    $response->assertSessionHas('info');
});

test('customer with penalty points can access appeal form (CUS-20)', function () {
    $response = $this->actingAs($this->customer)->get(route('pelanggan.penalties.appeal.create'));

    $response->assertOk();
    $response->assertSee('Formulir Surat Pernyataan Banding');
    $response->assertSee('30 Pts');
});

test('customer can submit appeal with statement letter upload (CUS-20)', function () {
    $statementPdf = UploadedFile::fake()->create('surat_pernyataan.pdf', 500, 'application/pdf');
    $evidenceImg = UploadedFile::fake()->image('bukti_alasan.jpg', 600, 600);

    $response = $this->actingAs($this->customer)->post(route('pelanggan.penalties.appeal.store'), [
        'reason' => 'Terjadi kendala darurat keluarga mendadak sehingga tidak berada di tempat.',
        'statement_letter' => $statementPdf,
        'evidence_photos' => [$evidenceImg],
    ]);

    $response->assertRedirect(route('pelanggan.penalties.index'));
    $response->assertSessionHas('success');

    $appeal = PenaltyAppeal::where('user_id', $this->customer->id)->first();
    expect($appeal)->not->toBeNull()
        ->and($appeal->status)->toBe(AppealStatus::Menunggu)
        ->and($appeal->points_to_reverse)->toBe(30)
        ->and($appeal->region_id)->toBe($this->region->id)
        ->and($appeal->statement_letter_path)->not->toBeEmpty()
        ->and($appeal->evidence_photos)->toHaveCount(1);
});

test('customer cannot submit duplicate appeal while one is pending', function () {
    PenaltyAppeal::create([
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'reason' => 'Alasan permohonan banding pertama',
        'statement_letter_path' => 'appeals/statements/test.pdf',
        'points_to_reverse' => 30,
        'status' => AppealStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.penalties.appeal.create'));
    $response->assertRedirect(route('pelanggan.penalties.index'));
    $response->assertSessionHas('warning');

    $statementPdf = UploadedFile::fake()->create('surat_pernyataan_dua.pdf', 300, 'application/pdf');
    $postResponse = $this->actingAs($this->customer)->post(route('pelanggan.penalties.appeal.store'), [
        'reason' => 'Alasan permohonan banding kedua yang duplikat.',
        'statement_letter' => $statementPdf,
    ]);

    $postResponse->assertRedirect(route('pelanggan.penalties.index'));
    $postResponse->assertSessionHas('warning');
    expect(PenaltyAppeal::where('user_id', $this->customer->id)->count())->toBe(1);
});
