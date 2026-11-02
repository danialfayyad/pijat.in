<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parameters = [
            // Jam Operasional
            [
                'key' => 'operational_start_time',
                'value' => '08:00',
                'type' => 'string',
                'group' => 'operational_hours',
                'label' => 'Jam Buka Layanan',
                'description' => 'Jam mulai layanan operasional waktu setempat wilayah',
                'unit' => 'Format HH:mm',
            ],
            [
                'key' => 'operational_end_time',
                'value' => '21:00',
                'type' => 'string',
                'group' => 'operational_hours',
                'label' => 'Jam Tutup Layanan',
                'description' => 'Jam tutup pemesanan layanan waktu setempat wilayah',
                'unit' => 'Format HH:mm',
            ],

            // Penugasan
            [
                'key' => 'dispatch_max_radius_km',
                'value' => '5',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Radius Maksimal Penugasan',
                'description' => 'Radius garis lurus maksimal pencarian kandidat mitra terapis',
                'unit' => 'Kilometer',
            ],
            [
                'key' => 'instant_offer_timeout_seconds',
                'value' => '60',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Batas Waktu Respon Tawaran Instan',
                'description' => 'Batas waktu hitung mundur bagi mitra untuk menerima tawaran instan sebelum dialihkan',
                'unit' => 'Detik',
            ],
            [
                'key' => 'instant_search_timeout_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Batas Waktu Total Pencarian Instan',
                'description' => 'Batas waktu maksimal sistem mencari mitra sebelum pesanan dinyatakan gagal/stuck',
                'unit' => 'Menit',
            ],
            [
                'key' => 'scheduled_search_timeout_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Batas Waktu Bursa Terjadwal',
                'description' => 'Batas waktu pesanan terjadwal tayang di bursa sejak dibuat customer',
                'unit' => 'Jam',
            ],
            [
                'key' => 'scheduled_search_deadline_before_hours',
                'value' => '2',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Batas Tenggat Terjadwal Sebelum Sesi',
                'description' => 'Batas waktu minimal sebelum jadwal mulai layanan; jika belum ada mitra dianggap macet',
                'unit' => 'Jam',
            ],
            [
                'key' => 'scheduled_min_advance_hours',
                'value' => '3',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Minimal Waktu Booking di Muka',
                'description' => 'Jarak waktu minimal pemesanan terjadwal ke depan',
                'unit' => 'Jam',
            ],
            [
                'key' => 'scheduled_max_advance_days',
                'value' => '7',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Maksimal Hari Pemesanan Terjadwal',
                'description' => 'Batas terjauh tanggal pemesanan terjadwal ke depan',
                'unit' => 'Hari',
            ],
            [
                'key' => 'partner_order_interval_buffer_minutes',
                'value' => '60',
                'type' => 'integer',
                'group' => 'dispatch',
                'label' => 'Jeda Buffer Antar Pesanan Mitra',
                'description' => 'Jeda istirahat dan perjalanan aman antar pesanan mitra terapis',
                'unit' => 'Menit',
            ],

            // Pesanan
            [
                'key' => 'customer_max_active_orders',
                'value' => '3',
                'type' => 'integer',
                'group' => 'order',
                'label' => 'Maksimal Pesanan Aktif Pelanggan',
                'description' => 'Batas jumlah pesanan berjalan/dijadwalkan sekaligus per akun pelanggan',
                'unit' => 'Pesanan',
            ],
            [
                'key' => 'order_max_reschedule_count',
                'value' => '2',
                'type' => 'integer',
                'group' => 'order',
                'label' => 'Batas Maksimal Penjadwalan Ulang',
                'description' => 'Batas maksimal reschedule yang diizinkan per satu nomor pesanan terjadwal',
                'unit' => 'Kali',
            ],
            [
                'key' => 'reschedule_min_advance_hours',
                'value' => '3',
                'type' => 'integer',
                'group' => 'order',
                'label' => 'Tenggat Waktu Pengajuan Reschedule',
                'description' => 'Batas minimal pengajuan reschedule sebelum jam jadwal layanan',
                'unit' => 'Jam',
            ],

            // Lokasi
            [
                'key' => 'partner_online_location_interval_minutes',
                'value' => '5',
                'type' => 'integer',
                'group' => 'location',
                'label' => 'Interval Update GPS Mitra Siaga',
                'description' => 'Interval pembaruan titik GPS berkala saat mitra berstatus Online',
                'unit' => 'Menit',
            ],
            [
                'key' => 'partner_location_stale_timeout_minutes',
                'value' => '10',
                'type' => 'integer',
                'group' => 'location',
                'label' => 'Batas Kedaluwarsa Koordinat Siaga',
                'description' => 'Jika GPS mitra tidak update melewati batas ini, status diexclude dari matching',
                'unit' => 'Menit',
            ],
            [
                'key' => 'partner_otw_location_interval_seconds',
                'value' => '30',
                'type' => 'integer',
                'group' => 'location',
                'label' => 'Interval Update GPS saat OTW',
                'description' => 'Frekuensi kirim live tracking saat mitra terapis menuju lokasi pelanggan',
                'unit' => 'Detik',
            ],
            [
                'key' => 'arrival_geofence_radius_meters',
                'value' => '50',
                'type' => 'integer',
                'group' => 'location',
                'label' => 'Radius Geofence Tiba di Lokasi',
                'description' => 'Radius toleransi jarak maksimal untuk mengaktifkan tombol Saya Sudah Tiba',
                'unit' => 'Meter',
            ],
            [
                'key' => 'arrival_gps_accuracy_tolerance_meters',
                'value' => '150',
                'type' => 'integer',
                'group' => 'location',
                'label' => 'Toleransi Akurasi Deviasi GPS',
                'description' => 'Batas toleransi deviasi pembacaan akurasi GPS perangkat seluler',
                'unit' => 'Meter',
            ],

            // Waktu Tunggu
            [
                'key' => 'payment_expiry_timeout_minutes',
                'value' => '15',
                'type' => 'integer',
                'group' => 'timeout',
                'label' => 'Batas Kedaluwarsa Pembayaran Top-up',
                'description' => 'Batas waktu tagihan payment gateway / Virtual Account sebelum dibatalkan otomatis',
                'unit' => 'Menit',
            ],
            [
                'key' => 'customer_noshow_wait_minutes',
                'value' => '15',
                'type' => 'integer',
                'group' => 'timeout',
                'label' => 'Waktu Tunggu Pelanggan Tidak Ditempat',
                'description' => 'Waktu tunggu terapis di lokasi sebelum diperbolehkan melapor pelanggan No-Show',
                'unit' => 'Menit',
            ],
            [
                'key' => 'partner_late_arrival_threshold_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'timeout',
                'label' => 'Ambang Keterlambatan Terapis',
                'description' => 'Batas keterlambatan mitra dari estimasi sebelum pelanggan berhak membatalkan bebas sanksi',
                'unit' => 'Menit',
            ],
            [
                'key' => 'complaint_window_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'timeout',
                'label' => 'Jendela Waktu Pengajuan Aduan',
                'description' => 'Batas waktu pelanggan dapat mengajukan tiket keluhan sejak pesanan selesai',
                'unit' => 'Jam',
            ],
            [
                'key' => 'partner_verification_sla_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'timeout',
                'label' => 'Target SLA Verifikasi Dokumen Mitra',
                'description' => 'Target maksimal admin wilayah meninjau dan memutuskan pendaftaran mitra baru',
                'unit' => 'Jam',
            ],

            // Finansial
            [
                'key' => 'platform_commission_percent',
                'value' => '20.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Persentase Komisi Platform',
                'description' => 'Persentase bagi hasil platform dari total tarif layanan pesanan',
                'unit' => '%',
            ],
            [
                'key' => 'late_cancel_penalty_percent',
                'value' => '50.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Persentase Denda Pembatalan Mendadak',
                'description' => 'Potongan biaya kompensasi untuk mitra jika pelanggan batal < 3 jam sebelum jadwal',
                'unit' => '%',
            ],
            [
                'key' => 'customer_wallet_min_topup',
                'value' => '20000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Minimal Top-up Saldo Dompet',
                'description' => 'Batas nominal minimal per transaksi isi ulang dompet pelanggan',
                'unit' => 'IDR',
            ],
            [
                'key' => 'customer_wallet_max_balance',
                'value' => '2000000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Batas Maksimal Saldo Dompet',
                'description' => 'Batas plafon saldo mengendap yang dapat ditampung pada akun pelanggan',
                'unit' => 'IDR',
            ],
            [
                'key' => 'customer_wallet_daily_topup_limit',
                'value' => '5000000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Batas Akumulasi Top-up Harian',
                'description' => 'Batas akumulasi transaksi top-up pelanggan dalam kurun waktu 24 jam',
                'unit' => 'IDR',
            ],
            [
                'key' => 'min_withdrawal_amount',
                'value' => '50000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Minimal Penarikan Dana (Payout)',
                'description' => 'Batas nominal saldo minimal yang dapat diajukan penarikan oleh mitra',
                'unit' => 'IDR',
            ],
            [
                'key' => 'withdrawal_fee_amount',
                'value' => '2500.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Biaya Admin Penarikan Dana',
                'description' => 'Biaya transfer antar bank per transaksi penarikan saldo',
                'unit' => 'IDR',
            ],
            [
                'key' => 'withdrawal_manual_review_threshold',
                'value' => '1000000.00',
                'type' => 'decimal',
                'group' => 'financial',
                'label' => 'Ambang Penarikan Wajib Tinjauan Admin',
                'description' => 'Nominal pencairan dana di atas batas ini memerlukan persetujuan manual admin wilayah',
                'unit' => 'IDR',
            ],
            [
                'key' => 'partner_earning_hold_period_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'financial',
                'label' => 'Masa Tahan Pendapatan Mitra',
                'description' => 'Masa pengendapan saldo hasil pesanan sebelum statusnya berubah menjadi Saldo Tersedia',
                'unit' => 'Jam',
            ],

            // Sanksi & Privasi
            [
                'key' => 'partner_appeal_retry_cooldown_days',
                'value' => '7',
                'type' => 'integer',
                'group' => 'penalty',
                'label' => 'Jeda Pengajuan Ulang Banding',
                'description' => 'Jeda waktu sebelum mitra dapat mengajukan permohonan banding baru jika banding ditolak',
                'unit' => 'Hari',
            ],
            [
                'key' => 'chat_and_medical_retention_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'privacy',
                'label' => 'Masa Retensi Chat & Catatan Medis',
                'description' => 'Jangka waktu penyimpanan riwayat percakapan dan catatan medis sebelum dihapus otomatis (UU PDP)',
                'unit' => 'Hari',
            ],
        ];

        foreach ($parameters as $param) {
            Parameter::updateOrCreate(
                ['key' => $param['key']],
                $param
            );
        }
    }
}
