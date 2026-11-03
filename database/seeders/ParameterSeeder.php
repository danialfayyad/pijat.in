<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds with 11 core dynamic business parameters.
     */
    public function run(): void
    {
        $parameters = [
            // 1. Finansial & Bagi Hasil
            [
                'key' => 'platform_commission_percent',
                'value' => '20.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Persentase Komisi Platform',
                'description' => 'Persentase potongan komisi platform dari tarif layanan setiap pesanan selesai',
                'unit' => 'Persen (%)',
            ],
            [
                'key' => 'min_withdrawal_amount',
                'value' => '50000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Minimal Penarikan Dana Mitra',
                'description' => 'Batas minimal nominal penarikan saldo dompet mitra per transaksi pencairan',
                'unit' => 'IDR (Rupiah)',
            ],
            [
                'key' => 'max_withdrawal_amount',
                'value' => '5000000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Maksimal Penarikan Dana Mitra',
                'description' => 'Batas maksimal nominal penarikan saldo dompet mitra per transaksi pencairan',
                'unit' => 'IDR (Rupiah)',
            ],
            [
                'key' => 'withdrawal_fee_amount',
                'value' => '2500.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Biaya Transfer Penarikan',
                'description' => 'Biaya administrasi transfer per transaksi penarikan saldo ke rekening bank',
                'unit' => 'IDR (Rupiah)',
            ],
            [
                'key' => 'customer_wallet_min_topup',
                'value' => '20000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Minimal Top-up Dompet Pelanggan',
                'description' => 'Batas minimal transaksi pengisian ulang saldo dompet pelanggan',
                'unit' => 'IDR (Rupiah)',
            ],
            [
                'key' => 'customer_wallet_max_balance',
                'value' => '2000000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Maksimal Saldo Dompet Pelanggan',
                'description' => 'Batas maksimal akumulasi saldo yang dapat ditampung di dompet pelanggan',
                'unit' => 'IDR (Rupiah)',
            ],

            // 2. Operasional & Jangkauan
            [
                'key' => 'operational_start_time',
                'value' => '08:00',
                'type' => 'string',
                'group' => 'operational',
                'label' => 'Jam Buka Layanan',
                'description' => 'Jam mulai pemesanan dan layanan operasional waktu setempat wilayah',
                'unit' => 'Format HH:mm',
            ],
            [
                'key' => 'operational_end_time',
                'value' => '21:00',
                'type' => 'string',
                'group' => 'operational',
                'label' => 'Jam Tutup Layanan',
                'description' => 'Jam batas akhir pemesanan layanan operasional waktu setempat wilayah',
                'unit' => 'Format HH:mm',
            ],
            [
                'key' => 'dispatch_max_radius_km',
                'value' => '5',
                'type' => 'integer',
                'group' => 'operational',
                'label' => 'Radius Maksimal Penugasan',
                'description' => 'Radius garis lurus maksimal pencarian dan penawaran kandidat mitra terapis',
                'unit' => 'Kilometer',
            ],

            // 3. Kualitas & Kepatuhan
            [
                'key' => 'chat_and_medical_retention_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'compliance',
                'label' => 'Masa Retensi Chat & Riwayat Medis',
                'description' => 'Batas waktu penyimpanan sebelum data chat dan catatan medis dihapus otomatis demi kepatuhan UU PDP',
                'unit' => 'Hari',
            ],
        ];

        $validKeys = collect($parameters)->pluck('key')->all();

        // Hapus parameter non-inti yang sudah dipindahkan ke konstanta config
        Parameter::whereNotIn('key', $validKeys)->delete();

        foreach ($parameters as $param) {
            Parameter::updateOrCreate(
                ['key' => $param['key']],
                $param
            );
        }

        Parameter::clearCache();
    }
}
