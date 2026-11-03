<?php

use App\Enums\AppealStatus;
use App\Models\PartnerProfile;
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

    $this->mitra = User::factory()->mitra()->create([
        'name' => 'Mitra Terapis Bersanksi',
        'penalty_points' => 45,
        'region_id' => $this->region->id,
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
});

test('partner can view penalty status and ledger history (MIT-18)', function () {
    PenaltyLedger::create([
        'user_id' => $this->mitra->id,
        'type' => 'violation',
        'points' => 45,
        'balance_before' => 0,
        'balance_after' => 45,
        'reason' => 'Pembatalan sepihak pesanan terjadwal (SP)',
    ]);

    $response = $this->actingAs($this->mitra)->get(route('mitra.penalties.index'));

    $response->assertOk();
    $response->assertSee('45');
    $response->assertSee('Peringatan');
    $response->assertSee('Pembatalan sepihak');
});

test('partner with zero penalty points is blocked from appeal form', function () {
    $cleanMitra = User::factory()->mitra()->create([
        'penalty_points' => 0,
        'region_id' => $this->region->id,
    ]);

    $response = $this->actingAs($cleanMitra)->get(route('mitra.penalties.appeal.create'));

    $response->assertRedirect(route('mitra.penalties.index'));
    $response->assertSessionHas('info');
});

test('partner with penalty points can access appeal form (MIT-18)', function () {
    $response = $this->actingAs($this->mitra)->get(route('mitra.penalties.appeal.create'));

    $response->assertOk();
    $response->assertSee('Formulir Pengajuan Banding SP');
    $response->assertSee('45 Pts');
});

test('partner can submit appeal with statement letter upload (MIT-18)', function () {
    $statementPdf = UploadedFile::fake()->create('surat_pernyataan_mitra.pdf', 600, 'application/pdf');
    $evidenceImg = UploadedFile::fake()->image('bukti_motor_mogok.jpg', 600, 600);

    $response = $this->actingAs($this->mitra)->post(route('mitra.penalties.appeal.store'), [
        'reason' => 'Kendaraan mogok mendadak di jalan dan saya sudah mengabari admin operasional.',
        'statement_letter' => $statementPdf,
        'evidence_photos' => [$evidenceImg],
    ]);

    $response->assertRedirect(route('mitra.penalties.index'));
    $response->assertSessionHas('success');

    $appeal = PenaltyAppeal::where('user_id', $this->mitra->id)->first();
    expect($appeal)->not->toBeNull()
        ->and($appeal->status)->toBe(AppealStatus::Menunggu)
        ->and($appeal->points_to_reverse)->toBe(45)
        ->and($appeal->region_id)->toBe($this->region->id)
        ->and($appeal->statement_letter_path)->not->toBeEmpty()
        ->and($appeal->evidence_photos)->toHaveCount(1);
});

test('partner cannot submit duplicate appeal while one is pending', function () {
    PenaltyAppeal::create([
        'user_id' => $this->mitra->id,
        'region_id' => $this->region->id,
        'reason' => 'Alasan permohonan banding mitra pertama',
        'statement_letter_path' => 'appeals/statements/mitra.pdf',
        'points_to_reverse' => 45,
        'status' => AppealStatus::Menunggu,
    ]);

    $response = $this->actingAs($this->mitra)->get(route('mitra.penalties.appeal.create'));
    $response->assertRedirect(route('mitra.penalties.index'));
    $response->assertSessionHas('warning');

    $statementPdf = UploadedFile::fake()->create('surat_duplikat.pdf', 300, 'application/pdf');
    $postResponse = $this->actingAs($this->mitra)->post(route('mitra.penalties.appeal.store'), [
        'reason' => 'Pengajuan kedua yang duplikat oleh mitra.',
        'statement_letter' => $statementPdf,
    ]);

    $postResponse->assertRedirect(route('mitra.penalties.index'));
    $postResponse->assertSessionHas('warning');
    expect(PenaltyAppeal::where('user_id', $this->mitra->id)->count())->toBe(1);
});
