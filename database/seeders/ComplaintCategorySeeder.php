<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Database\Seeder;

class ComplaintCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kualitas Layanan Tidak Sesuai',
                'slug' => 'layanan_tidak_sesuai',
                'penalty_points' => 20,
                'description' => 'Layanan pijat tidak sesuai dengan durasi, jenis paket, teknik pemijatan, atau spesifikasi yang dipesan.',
                'is_active' => true,
            ],
            [
                'name' => 'Keterlambatan Parah (>30 Menit)',
                'slug' => 'keterlambatan_parah',
                'penalty_points' => 15,
                'description' => 'Terapis terlambat lebih dari 30 menit dari waktu estimasi atau jadwal tanpa pemberitahuan/konfirmasi.',
                'is_active' => true,
            ],
            [
                'name' => 'Etika / Perilaku Tidak Sopan',
                'slug' => 'perilaku_tidak_sopan',
                'penalty_points' => 35,
                'description' => 'Perilaku melanggar kesopanan, berkata kasar, merokok di area klien, atau gestur tidak pantas.',
                'is_active' => true,
            ],
            [
                'name' => 'Standar Higienitas Kurang',
                'slug' => 'kebersihan_higienitas',
                'penalty_points' => 20,
                'description' => 'Perlengkapan pijat (kain alas, handuk, minyak) kotor, berbau, atau tidak higienis sesuai SOP kebersihan.',
                'is_active' => true,
            ],
            [
                'name' => 'Permintaan Tarif di Luar Aplikasi',
                'slug' => 'tarif_luar_aplikasi',
                'penalty_points' => 50,
                'description' => 'Mitra meminta uang tambahan tunai, ongkos bensin terpisah, atau tip wajib di luar sistem pembayaran resmi.',
                'is_active' => true,
            ],
            [
                'name' => 'Keluhan Lainnya',
                'slug' => 'lainnya',
                'penalty_points' => 10,
                'description' => 'Keluhan operasional umum lain yang belum tercakup pada kategori standar di atas.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $data) {
            $cat = ComplaintCategory::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Link existing complaints that match this category slug
            Complaint::where('category', $cat->slug)
                ->whereNull('complaint_category_id')
                ->update(['complaint_category_id' => $cat->id]);
        }
    }
}
