<?php

namespace Database\Factories;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_code' => 'ADU-'.now()->format('Ymd').'-'.strtoupper(fake()->bothify('??###')),
            'order_id' => Order::factory(),
            'region_id' => fn (array $attributes) => Order::find($attributes['order_id'])->region_id ?? Region::factory(),
            'complainant_id' => fn (array $attributes) => Order::find($attributes['order_id'])->customer_id ?? User::factory(),
            'complainant_role' => 'pelanggan',
            'target_id' => fn (array $attributes) => Order::find($attributes['order_id'])->mitra_id ?? User::factory(),
            'category' => fake()->randomElement(ComplaintCategory::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'evidence_photos' => null,
            'status' => ComplaintStatus::Menunggu,
            'priority' => 'normal',
            'handled_by_id' => null,
            'handled_at' => null,
            'resolution_type' => null,
            'admin_notes' => null,
            'resolved_at' => null,
        ];
    }

    public function inProcess(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Diproses,
            'handled_by_id' => $admin?->id ?? User::factory(),
            'handled_at' => now(),
        ]);
    }

    public function resolved(?User $admin = null, string $resolutionType = 'refund_penuh'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Selesai,
            'handled_by_id' => $admin?->id ?? User::factory(),
            'handled_at' => now()->subHours(2),
            'resolution_type' => $resolutionType,
            'admin_notes' => 'Investigasi selesai dan diputuskan sesuai bukti yang ada.',
            'resolved_at' => now(),
        ]);
    }

    public function rejected(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Ditolak,
            'handled_by_id' => $admin?->id ?? User::factory(),
            'handled_at' => now()->subHours(2),
            'resolution_type' => 'ditolak',
            'admin_notes' => 'Aduan tidak terbukti dan diabaikan setelah pengecekan riwayat percakapan.',
            'resolved_at' => now(),
        ]);
    }
}
