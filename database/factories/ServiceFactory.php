<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Pijat Tradisional Jawa',
            'Pijat Refleksi Kaki',
            'Pijat Aromaterapi Lavender',
            'Totok Wajah & Relaksasi',
            'Pijat Kebugaran Olahraga',
            'Lulur Tubuh & Body Scrub',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
