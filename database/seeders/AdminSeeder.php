<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\AdminRegionAssignment;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Superadmin (Akses Penuh Seluruh Wilayah & Pengaturan Global)
        User::updateOrCreate(
            ['email' => 'superadmin@pijatin.test'],
            [
                'name' => 'Superadmin Pijat.in',
                'role' => Role::Superadmin,
                'status' => UserStatus::Terverifikasi,
                'phone' => '081100000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // 2. Daftar Admin Regional dan Wilayah Penugasannya
        $regionalAdmins = [
            [
                'name' => 'Admin Regional Jember',
                'email' => 'admin@pijatin.test',
                'phone' => '081200000001',
                'region_keyword' => 'Jember',
            ],
            [
                'name' => 'Admin Regional Yogyakarta',
                'email' => 'admin.jogja@pijatin.test',
                'phone' => '081200000002',
                'region_keyword' => 'Yogyakarta',
            ],
            [
                'name' => 'Admin Regional Surabaya',
                'email' => 'admin.surabaya@pijatin.test',
                'phone' => '081200000003',
                'region_keyword' => 'Surabaya',
            ],
            [
                'name' => 'Admin Regional Blitar',
                'email' => 'admin.blitar@pijatin.test',
                'phone' => '081200000004',
                'region_keyword' => 'Blitar',
            ],
            [
                'name' => 'Admin Regional Jakarta Selatan',
                'email' => 'admin.jaksel@pijatin.test',
                'phone' => '081200000005',
                'region_keyword' => 'Jakarta Selatan',
            ],
        ];

        foreach ($regionalAdmins as $adminData) {
            $admin = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'role' => Role::Admin,
                    'status' => UserStatus::Terverifikasi,
                    'phone' => $adminData['phone'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ]
            );

            // Cari wilayah dan tautkan penugasan admin regional
            $region = Region::where('name', 'like', '%'.$adminData['region_keyword'].'%')->first();

            if ($region) {
                AdminRegionAssignment::updateOrCreate(
                    [
                        'user_id' => $admin->id,
                        'region_id' => $region->id,
                    ],
                    [
                        'can_handle_emergency' => true,
                    ]
                );

                // Pastikan wilayah operasional berstatus aktif
                $region->update(['status' => 'aktif']);
            }
        }
    }
}
