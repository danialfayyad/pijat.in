<?php

use App\Services\ImageCompressionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->service = app(ImageCompressionService::class);
});

test('ImageCompressionService mengompres gambar besar dan mengubah ukurannya', function () {
    // Buat gambar buatan resolusi 2000x1500 px
    $width = 2000;
    $height = 1500;
    $image = imagecreatetruecolor($width, $height);
    $color = imagecolorallocate($image, 0, 150, 200);
    imagefilledrectangle($image, 0, 0, $width, $height, $color);

    ob_start();
    imagejpeg($image);
    $content = ob_get_clean();
    imagedestroy($image);

    $uploadedFile = UploadedFile::fake()->createWithContent('ktp_besar.jpg', $content);

    $storedPath = $this->service->compressAndStore($uploadedFile, 'partners/ktp', 1280, 80);

    expect(Storage::disk('local')->exists($storedPath))->toBeTrue();

    // Periksa dimensi file hasil kompresi di storage
    $savedContent = Storage::disk('local')->get($storedPath);
    $savedImage = imagecreatefromstring($savedContent);

    expect(imagesx($savedImage))->toBeLessThanOrEqual(1280);
    expect(imagesy($savedImage))->toBeLessThanOrEqual(1280);

    // Pastikan rasio aspek proporsional 2000:1500 -> 1280x960
    expect(imagesx($savedImage))->toBe(1280);
    expect(imagesy($savedImage))->toBe(960);

    imagedestroy($savedImage);
});

test('ImageCompressionService menyimpan dokumen PDF apa adanya tanpa kompresi gambar', function () {
    $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

    $storedPath = $this->service->compressAndStore($pdfFile, 'partners/ktp');

    expect(Storage::disk('local')->exists($storedPath))->toBeTrue();
    expect(str_ends_with($storedPath, '.pdf'))->toBeTrue();
});
