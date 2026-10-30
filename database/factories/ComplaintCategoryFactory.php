<?php

namespace Database\Factories;

use App\Models\ComplaintCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ComplaintCategory>
 */
class ComplaintCategoryFactory extends Factory
{
    protected $model = ComplaintCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name, '_'),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Inactive state.
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
