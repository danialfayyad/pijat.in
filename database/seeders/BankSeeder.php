<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultChannels = [
            [
                'name' => 'Bank Central Asia',
                'code' => 'BCA',
                'category' => 'bank',
                'account_number' => '880812345678',
                'account_holder_name' => 'PT Pijat In Indonesia',
                'notes' => 'Saluran utama Virtual Account & Pencairan',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Bank Mandiri',
                'code' => 'MANDIRI',
                'category' => 'bank',
                'account_number' => '890812345678',
                'account_holder_name' => 'PT Pijat In Indonesia',
                'notes' => 'Saluran Virtual Account & Pencairan Mandiri',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Bank Rakyat Indonesia',
                'code' => 'BRI',
                'category' => 'bank',
                'account_number' => '888812345678',
                'account_holder_name' => 'PT Pijat In Indonesia',
                'notes' => 'Saluran BRIVA & Pencairan BRI',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Bank Negara Indonesia',
                'code' => 'BNI',
                'category' => 'bank',
                'account_number' => '827712345678',
                'account_holder_name' => 'PT Pijat In Indonesia',
                'notes' => 'Saluran BNI VA & Pencairan BNI',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'QRIS (Quick Response Code)',
                'code' => 'QRIS',
                'category' => 'qris',
                'account_number' => null,
                'account_holder_name' => 'PT Pijat In Indonesia',
                'notes' => 'Standar kode QR Nasional (BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay)',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'GoPay',
                'code' => 'GOPAY',
                'category' => 'ewallet',
                'account_number' => null,
                'account_holder_name' => null,
                'notes' => 'Pembayaran instan via aplikasi Gojek / GoPay',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'DANA',
                'code' => 'DANA',
                'category' => 'ewallet',
                'account_number' => null,
                'account_holder_name' => null,
                'notes' => 'Pembayaran instan via aplikasi DANA Dompet Digital',
                'sort_order' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($defaultChannels as $data) {
            Bank::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
