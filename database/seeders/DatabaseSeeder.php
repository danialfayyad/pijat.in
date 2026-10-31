<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'role' => Role::Pelanggan, 'password' => 'password'],
        );

        $this->call([
            MasterWilayahSeeder::class,
            AdminSeeder::class,
            RoleLoginSeeder::class,
            ServiceCatalogSeeder::class,
            OrderSeeder::class,
            ComplaintCategorySeeder::class,
            ComplaintSeeder::class,
            PenaltySeeder::class,
        ]);
    }
}
