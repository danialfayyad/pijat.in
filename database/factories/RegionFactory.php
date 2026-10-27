<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseLng = 100.0 + (fake()->unique()->numberBetween(0, 500) * 0.05);
        $baseLat = -8.0 + (fake()->unique()->numberBetween(0, 100) * 0.05);

        $coords = [
            [$baseLng, $baseLat],
            [$baseLng + 0.05, $baseLat],
            [$baseLng + 0.05, $baseLat + 0.05],
            [$baseLng, $baseLat + 0.05],
            [$baseLng, $baseLat],
        ];

        $wkt = Region::coordinatesToWkt($coords);

        return [
            'name' => 'Wilayah '.fake()->city(),
            'boundaries' => DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')"),
            'timezone' => 'Asia/Jakarta',
            'status' => 'aktif',
        ];
    }
}
