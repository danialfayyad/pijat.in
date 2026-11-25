<?php

namespace App\Services;

use App\Models\Parameter;

class PwaService
{
    /**
     * Generate dynamic PWA Web App Manifest array based on Superadmin branding parameters.
     *
     * @return array<string, mixed>
     */
    public static function getManifest(): array
    {
        $appName = Parameter::get('app_name', config('app.name', 'Pijat.In'));
        $appTagline = Parameter::get('app_tagline', 'Layanan Pijat & Relaksasi Profesional');
        $appDescription = Parameter::get('app_description', 'Platform pemesanan terapis pijat dan relaksasi profesional terpercaya langsung ke lokasi Anda.');
        $appLogo = Parameter::get('app_logo', '');
        $appFavicon = Parameter::get('app_favicon', '');

        $icons = [];

        if (! empty($appLogo)) {
            $logoUrl = '/storage/'.$appLogo;
            $extension = strtolower(pathinfo($appLogo, PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };

            // 1. Standard transparent icon for splash screen and general devices (PURPOSE: ANY)
            // Chrome/Android Splash screen renders transparent icons cleanly over background_color WITHOUT turning black
            $icons[] = [
                'src' => $logoUrl,
                'sizes' => '192x192',
                'type' => $mimeType,
                'purpose' => 'any',
            ];
            $icons[] = [
                'src' => $logoUrl,
                'sizes' => '512x512',
                'type' => $mimeType,
                'purpose' => 'any',
            ];

            // 2. Adaptive Maskable Icon with solid white background for Android home screen
            $maskablePath = self::ensureMaskableIcon($appLogo);
            if ($maskablePath) {
                $icons[] = [
                    'src' => '/storage/'.$maskablePath,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ];
            }
        } else {
            $icons[] = [
                'src' => '/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ];
            $icons[] = [
                'src' => '/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ];
        }

        if (! empty($appFavicon)) {
            $faviconUrl = '/storage/'.$appFavicon;
            $icons[] = [
                'src' => $faviconUrl,
                'sizes' => '64x64 32x32 24x24 16x16',
                'type' => 'image/x-icon',
            ];
        } else {
            $icons[] = [
                'src' => '/favicon.ico',
                'sizes' => '64x64 32x32 24x24 16x16',
                'type' => 'image/x-icon',
            ];
        }

        return [
            'name' => trim($appName.($appTagline ? ' - '.$appTagline : '')),
            'short_name' => $appName,
            'description' => $appDescription,
            'start_url' => '/dashboard',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#ffffff',
            'theme_color' => '#059669',
            'icons' => $icons,
        ];
    }

    /**
     * Ensure a dedicated 512x512 maskable icon with solid white background exists for adaptive icons.
     */
    public static function ensureMaskableIcon(string $relativeLogoPath): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $fullPath = storage_path('app/public/'.$relativeLogoPath);
        if (! file_exists($fullPath)) {
            return null;
        }

        $filename = 'maskable_'.md5($relativeLogoPath).'.png';
        $relativeMaskable = 'branding/'.$filename;
        $outputFullPath = storage_path('app/public/'.$relativeMaskable);

        if (file_exists($outputFullPath)) {
            return $relativeMaskable;
        }

        try {
            $extension = strtolower(pathinfo($relativeLogoPath, PATHINFO_EXTENSION));
            $src = match ($extension) {
                'png' => @imagecreatefrompng($fullPath),
                'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
                'webp' => @imagecreatefromwebp($fullPath),
                default => null,
            };

            if (! $src) {
                return null;
            }

            $srcW = imagesx($src);
            $srcH = imagesy($src);

            $targetSize = 512;
            $canvas = imagecreatetruecolor($targetSize, $targetSize);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $targetSize, $targetSize, $white);

            // Safe zone: scale logo to 75% of canvas (384px) to avoid clipping on Android squircles
            $safeSize = (int) ($targetSize * 0.75);
            $scale = min($safeSize / $srcW, $safeSize / $srcH);
            $newW = (int) ($srcW * $scale);
            $newH = (int) ($srcH * $scale);
            $dstX = (int) (($targetSize - $newW) / 2);
            $dstY = (int) (($targetSize - $newH) / 2);

            imagealphablending($canvas, true);
            imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);

            @imagepng($canvas, $outputFullPath, 9);
            @imagedestroy($canvas);
            @imagedestroy($src);

            return $relativeMaskable;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Synchronize the public/manifest.json physical file with current branding parameters.
     */
    public static function syncManifestFile(): void
    {
        try {
            $manifest = self::getManifest();
            $path = public_path('manifest.json');
            @file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            // Silently fallback if filesystem is read-only in some environments
        }
    }
}
