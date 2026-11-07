<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Banner::firstOrCreate(
            ['title' => 'Voucher Relaksasi Pertama'],
            [
                'type' => 'template',
                'badge_text' => 'Promo Spesial',
                'subtitle' => 'Potongan Rp 25.000 untuk Anda',
                'description' => 'Nikmati perawatan tubuh berkualitas di rumah dengan harga lebih hemat.',
                'promo_code' => 'PIJATHEMAT',
                'action_url' => '/pelanggan/catalog',
                'action_text' => 'Gunakan Promo',
                'background_color' => 'emerald',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        Banner::firstOrCreate(
            ['title' => 'Layanan Pijat Refleksi Segar Bugar'],
            [
                'type' => 'template',
                'badge_text' => 'Rekomendasi',
                'subtitle' => 'Terapis Berpengalaman & Higienis',
                'description' => 'Hilangkan pegal dan lelah setelah beraktivitas dengan terapis terpercaya.',
                'promo_code' => null,
                'action_url' => '/pelanggan/catalog',
                'action_text' => 'Lihat Katalog',
                'background_color' => 'teal',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
