<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MasterWilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            [
                'code' => '3509',
                'name' => 'Kabupaten Jember',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [113.60, -8.15],
                    [113.80, -8.15],
                    [113.80, -8.30],
                    [113.60, -8.30],
                    [113.60, -8.15],
                ],
            ],
            [
                'code' => '3471',
                'name' => 'Kota Yogyakarta',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [110.35, -7.78],
                    [110.42, -7.78],
                    [110.42, -7.84],
                    [110.35, -7.84],
                    [110.35, -7.78],
                ],
            ],
            [
                'code' => '3578',
                'name' => 'Kota Surabaya',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [112.65, -7.20],
                    [112.82, -7.20],
                    [112.82, -7.35],
                    [112.65, -7.35],
                    [112.65, -7.20],
                ],
            ],
            [
                'code' => '3572',
                'name' => 'Kota Blitar',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [112.14, -8.08],
                    [112.20, -8.08],
                    [112.20, -8.13],
                    [112.14, -8.13],
                    [112.14, -8.08],
                ],
            ],
            [
                'code' => '3174',
                'name' => 'Kota Jakarta Selatan',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [106.77, -6.22],
                    [106.87, -6.22],
                    [106.87, -6.34],
                    [106.77, -6.34],
                    [106.77, -6.22],
                ],
            ],
            [
                'code' => '3510',
                'name' => 'Kabupaten Banyuwangi',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [114.25, -8.15],
                    [114.45, -8.15],
                    [114.45, -8.35],
                    [114.25, -8.35],
                    [114.25, -8.15],
                ],
            ],
            [
                'code' => '3573',
                'name' => 'Kota Malang',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [112.58, -7.94],
                    [112.68, -7.94],
                    [112.68, -8.04],
                    [112.58, -8.04],
                    [112.58, -7.94],
                ],
            ],
            [
                'code' => '3273',
                'name' => 'Kota Bandung',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [107.56, -6.88],
                    [107.68, -6.88],
                    [107.68, -6.98],
                    [107.56, -6.98],
                    [107.56, -6.88],
                ],
            ],
            [
                'code' => '3374',
                'name' => 'Kota Semarang',
                'timezone' => 'Asia/Jakarta',
                'fallback_coords' => [
                    [110.34, -6.95],
                    [110.48, -6.95],
                    [110.48, -7.08],
                    [110.34, -7.08],
                    [110.34, -6.95],
                ],
            ],
            [
                'code' => '5171',
                'name' => 'Kota Denpasar',
                'timezone' => 'Asia/Makassar',
                'fallback_coords' => [
                    [115.18, -8.62],
                    [115.28, -8.62],
                    [115.28, -8.72],
                    [115.18, -8.72],
                    [115.18, -8.62],
                ],
            ],
        ];

        foreach ($regions as $data) {
            $polygonWkt = $this->resolvePolygonWkt($data['code'], $data['fallback_coords']);

            $existing = Region::where('name', $data['name'])->first();

            if ($existing) {
                DB::statement(
                    "UPDATE regions 
                     SET boundaries = ST_GeomFromText(?, 4326, 'axis-order=long-lat'),
                         timezone = ?,
                         status = 'aktif',
                         updated_at = NOW()
                     WHERE id = ?",
                    [$polygonWkt, $data['timezone'], $existing->id]
                );
            } else {
                DB::statement(
                    "INSERT INTO regions (name, boundaries, timezone, status, created_at, updated_at)
                     VALUES (?, ST_GeomFromText(?, 4326, 'axis-order=long-lat'), ?, 'aktif', NOW(), NOW())",
                    [$data['name'], $polygonWkt, $data['timezone']]
                );
            }
        }
    }

    /**
     * Resolve boundary WKT from official GeoJSON file or fallback coordinate ring.
     *
     * @param  array<int, array{0: float, 1: float}>  $fallbackCoords
     */
    private function resolvePolygonWkt(string $cityCode, array $fallbackCoords): string
    {
        $underscored = strlen($cityCode) === 4 ? substr($cityCode, 0, 2).'_'.substr($cityCode, 2, 2) : $cityCode;
        $candidates = [
            storage_path("app/geojson/boundaries/{$cityCode}.json"),
            storage_path("app/geojson/boundaries/{$underscored}.json"),
        ];

        foreach ($candidates as $filePath) {
            if (File::exists($filePath)) {
                $raw = json_decode(File::get($filePath), true);
                if (isset($raw['geometry']) && is_array($raw['geometry'])) {
                    return Region::geoJsonToWkt($raw['geometry']);
                }
            }
        }

        return Region::coordinatesToWkt($fallbackCoords);
    }
}
