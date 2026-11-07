<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => 'template',
            'image_path' => null,
            'badge_text' => 'Promo Spesial',
            'subtitle' => 'Diskon Menarik',
            'description' => fake()->sentence(8),
            'promo_code' => 'HEMAT'.fake()->numberBetween(10, 99),
            'action_url' => '/pelanggan/catalog',
            'action_text' => 'Pesan Sekarang',
            'background_color' => fake()->randomElement(['emerald', 'teal', 'slate', 'amber', 'indigo', 'rose']),
            'sort_order' => 0,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * State untuk banner tipe gambar penuh.
     */
    public function imageType(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'image_path' => 'banners/dummy.jpg',
            'badge_text' => null,
            'subtitle' => null,
            'description' => null,
            'promo_code' => null,
        ]);
    }
}
