<?php

namespace App\Support;

class QrisCode
{
    /**
     * Generate an authentic-looking, high-density QRIS SVG string.
     */
    public static function svg(string $payload, int $displaySize = 220): string
    {
        $size = 37; // Version 5 QR Code (37x37 grid)
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // 1. Finder patterns (7x7) + white separators (1px)
        $placeFinder = function (int $startRow, int $startCol) use (&$matrix, &$reserved, $size) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $row = $startRow + $r;
                    $col = $startCol + $c;
                    if ($row >= 0 && $row < $size && $col >= 0 && $col < $size) {
                        $reserved[$row][$col] = true;
                        if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                            $isOuter = ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                            $isCenter = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
                            $matrix[$row][$col] = ($isOuter || $isCenter) ? 1 : 0;
                        } else {
                            $matrix[$row][$col] = 0; // Separator
                        }
                    }
                }
            }
        };

        $placeFinder(0, 0);                 // Top-Left
        $placeFinder(0, $size - 7);         // Top-Right
        $placeFinder($size - 7, 0);         // Bottom-Left

        // 2. Alignment pattern at (28, 28) (5x5)
        $alignRow = 28;
        $alignCol = 28;
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $row = $alignRow + $r;
                $col = $alignCol + $c;
                $reserved[$row][$col] = true;
                $isOuter = (abs($r) === 2 || abs($c) === 2);
                $isCenter = ($r === 0 && $c === 0);
                $matrix[$row][$col] = ($isOuter || $isCenter) ? 1 : 0;
            }
        }

        // 3. Timing patterns (Row 6 and Col 6)
        for ($i = 8; $i < $size - 8; $i++) {
            if (! $reserved[6][$i]) {
                $reserved[6][$i] = true;
                $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
            }
            if (! $reserved[$i][6]) {
                $reserved[$i][6] = true;
                $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
            }
        }

        // 4. Reserve center area (size 7x7) for QRIS center badge
        $centerStart = 15;
        $centerEnd = 21;
        for ($r = $centerStart; $r <= $centerEnd; $r++) {
            for ($c = $centerStart; $c <= $centerEnd; $c++) {
                $reserved[$r][$c] = true;
                $matrix[$r][$c] = 0;
            }
        }

        // 5. Populate data modules deterministically using HMAC / hash stream
        $hashStream = '';
        $round = 0;
        while (strlen($hashStream) < $size * $size * 2) {
            $hashStream .= hash('sha256', $payload.'-'.$round++);
        }
        $streamIndex = 0;

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (! $reserved[$r][$c]) {
                    // Standard QR masking variation
                    $val = hexdec($hashStream[$streamIndex % strlen($hashStream)]) % 2;
                    $mask = (($r + $c) % 2 === 0);
                    $matrix[$r][$c] = $mask ? (1 - $val) : $val;
                    $streamIndex++;
                }
            }
        }

        // 6. Build SVG with paths for maximum sharpness & performance
        $moduleSize = 10;
        $totalPx = $size * $moduleSize;
        $pathD = '';

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = $c * $moduleSize;
                    $y = $r * $moduleSize;
                    $pathD .= "M{$x},{$y}h{$moduleSize}v{$moduleSize}h-{$moduleSize}z ";
                }
            }
        }

        $centerPixelX = ($size * $moduleSize) / 2;
        $centerPixelY = ($size * $moduleSize) / 2;
        $badgeRadius = 26;

        return <<<SVG
<svg id="qris-svg" class="w-full h-full max-w-[{$displaySize}px] max-h-[{$displaySize}px] mx-auto select-none" viewBox="0 0 {$totalPx} {$totalPx}" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- QR Code Background -->
    <rect width="{$totalPx}" height="{$totalPx}" fill="#FFFFFF"/>
    
    <!-- Dense Matrix & Finder Modules -->
    <path d="{$pathD}" fill="#111827"/>
    
    <!-- QRIS Center Green Badge (matching Gambar 3) -->
    <circle cx="{$centerPixelX}" cy="{$centerPixelY}" r="{$badgeRadius}" fill="#84CC16" stroke="#FFFFFF" stroke-width="6"/>
    <path d="M178 174h9a7 7 0 017 7 7 7 0 01-7 7h-4.5v9H178v-23zm4.5 9.5h4.5a2.5 2.5 0 002.5-2.5 2.5 2.5 0 00-2.5-2.5h-4.5v5z" fill="#FFFFFF"/>
</svg>
SVG;
    }
}
