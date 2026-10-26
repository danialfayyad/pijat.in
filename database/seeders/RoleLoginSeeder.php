<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleLoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        /** @var list<array{name: string, email: string, role: Role}> $demoUsers */
        $demoUsers = [
            ['name' => 'Superadmin Pijat.in', 'email' => 'superadmin@pijatin.test', 'role' => Role::Superadmin],
            ['name' => 'Admin Pijat.in', 'email' => 'admin@pijatin.test', 'role' => Role::Admin],
            ['name' => 'Pelanggan Pijat.in', 'email' => 'pelanggan@pijatin.test', 'role' => Role::Pelanggan],
            ['name' => 'Mitra Pijat.in', 'email' => 'mitra@pijatin.test', 'role' => Role::Mitra],
        ];

        foreach ($demoUsers as $demoUser) {
            User::updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'role' => $demoUser['role'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
