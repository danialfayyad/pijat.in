<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Region;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $duration = fake()->randomElement([60, 90, 120]);
        $price = match ($duration) {
            60 => 100000,
            90 => 140000,
            120 => 180000,
        };

        return [
            'order_code' => 'ORD-'.strtoupper(fake()->unique()->bothify('##??####')),
            'order_type' => fake()->randomElement([OrderType::Instan, OrderType::Terjadwal]),
            'customer_id' => User::factory()->customer(),
            'mitra_id' => null,
            'region_id' => Region::factory(),
            'service_id' => Service::factory(),
            'service_package_id' => null,
            'service_name' => fake()->randomElement(['Pijat Tradisional Jawa', 'Refleksi Kaki & Relaksasi', 'Pijat Aromaterapi']),
            'duration_minutes' => $duration,
            'total_price' => $price,
            'commission_percent' => 10.00,
            'recipient_name' => fake()->name(),
            'recipient_gender' => fake()->randomElement(['pria', 'wanita']),
            'recipient_phone' => fake()->phoneNumber(),
            'service_address_text' => fake()->address(),
            'service_coordinate' => [
                'lat' => fake()->latitude(-7.8, -6.9),
                'lng' => fake()->longitude(107.5, 112.8),
            ],
            'scheduled_start_time' => Carbon::now()->addHours(fake()->numberBetween(3, 24)),
            'status' => OrderStatus::MencariMitra,
            'cancellation_reason' => null,
            'reschedule_count' => 0,
        ];
    }

    /**
     * State for scheduled order.
     */
    public function scheduled(?Carbon $startTime = null): static
    {
        return $this->state(fn () => [
            'order_type' => OrderType::Terjadwal,
            'scheduled_start_time' => $startTime ?? Carbon::now()->addHours(4),
        ]);
    }

    /**
     * State for instant order.
     */
    public function instant(): static
    {
        return $this->state(fn () => [
            'order_type' => OrderType::Instan,
            'scheduled_start_time' => null,
        ]);
    }

    /**
     * State for stuck order (ADM-04).
     */
    public function stuck(): static
    {
        return $this->state(fn () => [
            'order_type' => OrderType::Instan,
            'status' => OrderStatus::MencariMitra,
            'created_at' => Carbon::now()->subMinutes(45),
            'updated_at' => Carbon::now()->subMinutes(45),
        ]);
    }

    /**
     * State for assigned order.
     */
    public function assigned(User $mitra): static
    {
        return $this->state(fn () => [
            'mitra_id' => $mitra->id,
            'status' => OrderStatus::Dijadwalkan,
        ]);
    }

    /**
     * State for completed order.
     */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Selesai,
        ]);
    }

    /**
     * State for cancelled order.
     */
    public function cancelled(string $reason = 'Pembatalan oleh admin'): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Dibatalkan,
            'cancellation_reason' => $reason,
        ]);
    }
}
