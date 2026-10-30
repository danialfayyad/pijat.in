<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    /**
     * Compress image to a maximum dimension and quality, or store directly if not an image.
     *
     * @param  int  $maxDimension  Maximum width or height in pixels (default: 1280)
     * @param  int  $quality  JPEG compression quality 1-100 (default: 80)
     * @return string Stored file path
     */
    public function compressAndStore(
        UploadedFile|string|null $file,
        string $directory,
        int $maxDimension = 1280,
        int $quality = 80,
        string $disk = 'local'
    ): string {
        if (! ($file instanceof UploadedFile)) {
            return (string) $file;
        }

        $mime = $file->getMimeType();

        // If it's a PDF or non-image format, store as-is
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])) {
            return $file->store($directory, $disk);
        }

        // If GD extension is not available for any reason, fallback to default store
        if (! extension_loaded('gd')) {
            return $file->store($directory, $disk);
        }

        $realPath = $file->getRealPath();
        $sourceData = file_get_contents($realPath);
        if ($sourceData === false) {
            return $file->store($directory, $disk);
        }

        $sourceImage = @imagecreatefromstring($sourceData);
        if ($sourceImage === false) {
            return $file->store($directory, $disk);
        }

        // Fix image orientation based on EXIF data if present
        $sourceImage = $this->correctExifOrientation($sourceImage, $realPath);

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate scaled dimensions
        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Fill background with white (helps clean transparency from PNGs converted to JPEG)
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);

        imagecopyresampled(
            $canvas,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        ob_start();
        imagejpeg($canvas, null, $quality);
        $compressedData = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($canvas);

        if ($compressedData === false || empty($compressedData)) {
            return $file->store($directory, $disk);
        }

        $fileName = Str::random(40).'.jpg';
        $fullPath = trim($directory, '/').'/'.$fileName;

        Storage::disk($disk)->put($fullPath, $compressedData);

        return $fullPath;
    }

    /**
     * Correct image orientation based on EXIF tag.
     */
    protected function correctExifOrientation($image, string $path)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        if (! $exif || ! isset($exif['Orientation'])) {
            return $image;
        }

        return match ((int) $exif['Orientation']) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
