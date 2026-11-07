<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data dummy lama
        Faq::truncate();

        $admin = User::whereIn('role', ['superadmin', 'admin'])->first();
        $adminId = $admin?->id;

        $faqs = [
            // Pelanggan
            [
                'target_audience' => 'pelanggan',
                'category' => 'Pemesanan',
                'question' => 'Bagaimana cara memesan layanan pijat ke rumah?',
                'answer' => 'Buka menu Pesan Instan di beranda, pilih jenis layanan dan durasi yang Anda inginkan, tentukan alamat tujuan, lalu klik Pesan Sekarang. Sistem akan mencarikan mitra terapis terdekat yang sedang aktif.',
                'order_index' => 1,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'pelanggan',
                'category' => 'Preferensi Layanan',
                'question' => 'Apakah saya bisa memilih gender terapis (pria / wanita)?',
                'answer' => 'Bisa. Pada saat mengisi form pemesanan, Anda dapat memilih preferensi gender terapis demi kenyamanan privasi Anda selama layanan berlangsung.',
                'order_index' => 2,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'pelanggan',
                'category' => 'Pembayaran',
                'question' => 'Metode pembayaran apa saja yang dapat digunakan?',
                'answer' => 'Pijat.In mendukung pembayaran melalui Saldo Dompet Pijat.In, Transfer Bank (Virtual Account), dan QRIS otomatis tanpa perlu konfirmasi manual.',
                'order_index' => 3,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'pelanggan',
                'category' => 'Pembatalan & Refund',
                'question' => 'Bagaimana ketentuan pembatalan pesanan dan pengembalian dana?',
                'answer' => 'Pembatalan sebelum terapis bergerak menuju lokasi tidak dikenakan biaya dan dana dikembalikan penuh ke saldo dompet. Jika pembatalan dilakukan setelah terapis dalam perjalanan, berlaku biaya kompensasi jalan.',
                'order_index' => 4,
                'is_active' => true,
                'created_by' => $adminId,
            ],

            // Mitra (Terapis)
            [
                'target_audience' => 'mitra',
                'category' => 'Penghasilan Mitra',
                'question' => 'Berapa potongan komisi platform untuk setiap pesanan?',
                'answer' => 'Komisi platform adalah 20% dari tarif pesanan selesai. Sisa 80% langsung masuk ke saldo dompet mitra secara seketika setelah pesanan diselesaikan.',
                'order_index' => 5,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'mitra',
                'category' => 'Pencairan Dana',
                'question' => 'Kapan saldo dompet mitra bisa dicairkan ke rekening bank?',
                'answer' => 'Pencairan saldo dapat diajukan kapan saja melalui menu Dompet Mitra. Pengajuan diproses oleh tim admin keuangan ke rekening bank Anda pada hari kerja.',
                'order_index' => 6,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'mitra',
                'category' => 'SOP Layanan',
                'question' => 'Apa yang harus dilakukan jika pelanggan tidak berada di lokasi?',
                'answer' => 'Hubungi pelanggan lewat chat/telepon aplikasi dan tunggu minimal 15 menit di titik jemput. Jika tetap tidak ada respons, laporkan pembatalan lewat aplikasi dengan memilih alasan Pelanggan Tidak Berada di Lokasi.',
                'order_index' => 7,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'mitra',
                'category' => 'Poin Sanksi',
                'question' => 'Bagaimana sistem akumulasi poin sanksi mitra bekerja?',
                'answer' => 'Poin sanksi diberikan otomatis jika terapis membatalkan pesanan sepihak atau terlambat tanpa kabar. Jika poin mencapai batas akumulasi tertentu, akun akan disuspend sementara sesuai ketentuan kode etik.',
                'order_index' => 8,
                'is_active' => true,
                'created_by' => $adminId,
            ],

            // Umum / Semua
            [
                'target_audience' => 'semua',
                'category' => 'Operasional',
                'question' => 'Apakah layanan Pijat.In melayani panggilan 24 jam?',
                'answer' => 'Jam operasional bergantung pada ketersediaan mitra terapis di wilayah Anda. Mayoritas mitra terapis aktif menerima order antara pukul 07.00 hingga 23.00 WIB.',
                'order_index' => 9,
                'is_active' => true,
                'created_by' => $adminId,
            ],
            [
                'target_audience' => 'semua',
                'category' => 'Keamanan & Privasi',
                'question' => 'Apakah terapis Pijat.In telah terverifikasi dan tersertifikasi?',
                'answer' => 'Setiap mitra terapis wajib melewati verifikasi identitas (KTP, SKCK/Surat Keterangan), uji kompetensi pijat, serta menyetujui pakta integritas dan SOP keselamatan sebelum dapat menerima pesanan.',
                'order_index' => 10,
                'is_active' => true,
                'created_by' => $adminId,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
