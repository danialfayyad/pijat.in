<?php

namespace Database\Seeders;

use App\Enums\AppealStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\PenaltyAppeal;
use App\Models\PenaltyLedger;
use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PenaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@pijatin.test')->first() ?? User::where('role', 'admin')->first();
        $superadmin = User::where('email', 'superadmin@pijatin.test')->first() ?? User::where('role', 'superadmin')->first();
        $pelangganDemo = User::where('email', 'pelanggan@pijatin.test')->first() ?? User::where('role', 'pelanggan')->first();

        $blitar = Region::where('name', 'like', '%BLITAR%')->first() ?? Region::first();
        $jember = Region::where('name', 'like', '%JEMBER%')->first() ?? Region::skip(1)->first() ?? Region::first();

        $orderBlitar = Order::where('region_id', $blitar->id)->first();
        $orderJember = Order::where('region_id', $jember->id)->first() ?? $orderBlitar;

        $complaintBlitar1 = Complaint::where('region_id', $blitar->id)->first();
        $complaintBlitar2 = Complaint::where('region_id', $blitar->id)->skip(1)->first() ?? $complaintBlitar1;
        $complaintJember1 = Complaint::where('region_id', $jember->id)->first() ?? $complaintBlitar1;

        // =========================================================================
        // 1. MITRA BUDI SANTOSO (Status: Peringatan, Poin: 38)
        // Alur: 2x Pelanggaran (+20, +20) diselingi 2x Penebusan Bintang 5 (-1, -1)
        // =========================================================================
        $budi = User::updateOrCreate(
            ['email' => 'budi.terapis@pijatin.test'],
            [
                'name' => 'Budi Santoso',
                'nik' => '3505011204850001',
                'gender' => 'laki-laki',
                'phone' => '081234567891',
                'role' => Role::Mitra,
                'status' => UserStatus::Terverifikasi,
                'region_id' => $blitar->id,
                'penalty_points' => 38,
                'suspended_until' => null,
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subMonths(3),
                'phone_verified_at' => Carbon::now()->subMonths(3),
            ]
        );

        $budi->partnerProfile()->updateOrCreate(
            ['user_id' => $budi->id],
            [
                'region_id' => $blitar->id,
                'address_text' => 'Jl. Merdeka No. 45, Kepanjenkidul, Kota Blitar',
                'coordinate' => ['lat' => -8.0983, 'lng' => 112.1682],
                'ktp_path' => 'ktp/dummy_budi.jpg',
                'selfie_path' => 'selfie/dummy_budi.jpg',
                'skck_path' => 'skck/dummy_budi.jpg',
                'is_active' => true,
                'verified_at' => Carbon::now()->subMonths(3),
                'verified_by' => $admin?->id,
            ]
        );

        PenaltyLedger::where('user_id', $budi->id)->delete();

        PenaltyLedger::create([
            'user_id' => $budi->id,
            'complaint_id' => $complaintBlitar1?->id,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 20,
            'balance_before' => 0,
            'balance_after' => 20,
            'reason' => 'Pelanggaran: Terapis terlambat lebih dari 35 menit pada pesanan tanpa konfirmasi (ADU-BLT-001).',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        PenaltyLedger::create([
            'user_id' => $budi->id,
            'complaint_id' => $complaintBlitar2?->id,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 20,
            'balance_before' => 20,
            'balance_after' => 40,
            'reason' => 'Pelanggaran: Pembatalan sepihak saat jam pemesanan telah terjadwal.',
            'created_at' => Carbon::now()->subDays(3),
            'updated_at' => Carbon::now()->subDays(3),
        ]);

        PenaltyLedger::create([
            'user_id' => $budi->id,
            'complaint_id' => null,
            'order_id' => $orderBlitar?->id,
            'actor_id' => null,
            'type' => 'merit_redemption',
            'points' => -1,
            'balance_before' => 40,
            'balance_after' => 39,
            'reason' => 'Penebusan sanksi: Layanan bintang 5 selesai pada pesanan ORD-BLT-004.',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        PenaltyLedger::create([
            'user_id' => $budi->id,
            'complaint_id' => null,
            'order_id' => $orderBlitar?->id,
            'actor_id' => null,
            'type' => 'merit_redemption',
            'points' => -1,
            'balance_before' => 39,
            'balance_after' => 38,
            'reason' => 'Penebusan sanksi: Layanan bintang 5 selesai pada pesanan ORD-BLT-005.',
            'created_at' => Carbon::now()->subHours(18),
            'updated_at' => Carbon::now()->subHours(18),
        ]);

        // =========================================================================
        // 2. MITRA SITI AMINAH (Status: Ditangguhkan 3 Hari, Poin: 75, Banding: Menunggu)
        // Alur: 2x Pelanggaran (+40, +35) -> Suspend 3 Hari -> Mengajukan Banding BND-20261029-0001
        // =========================================================================
        $siti = User::updateOrCreate(
            ['email' => 'siti.terapis@pijatin.test'],
            [
                'name' => 'Siti Aminah',
                'nik' => '3505015508890002',
                'gender' => 'perempuan',
                'phone' => '081234567892',
                'role' => Role::Mitra,
                'status' => UserStatus::Ditangguhkan,
                'region_id' => $blitar->id,
                'penalty_points' => 75,
                'suspended_until' => Carbon::now()->addDays(3),
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subMonths(2),
                'phone_verified_at' => Carbon::now()->subMonths(2),
            ]
        );

        $siti->partnerProfile()->updateOrCreate(
            ['user_id' => $siti->id],
            [
                'region_id' => $blitar->id,
                'address_text' => 'Jl. Veteran No. 12, Sananwetan, Kota Blitar',
                'coordinate' => ['lat' => -8.1021, 'lng' => 112.1755],
                'ktp_path' => 'ktp/dummy_siti.jpg',
                'selfie_path' => 'selfie/dummy_siti.jpg',
                'skck_path' => 'skck/dummy_siti.jpg',
                'is_active' => true,
                'verified_at' => Carbon::now()->subMonths(2),
                'verified_by' => $admin?->id,
            ]
        );

        PenaltyLedger::where('user_id', $siti->id)->delete();

        PenaltyLedger::create([
            'user_id' => $siti->id,
            'complaint_id' => $complaintBlitar1?->id,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 40,
            'balance_before' => 0,
            'balance_after' => 40,
            'reason' => 'Pelanggaran: Tidak hadir di lokasi tanpa konfirmasi (No-show) pada sesi layanan pelanggan.',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        PenaltyLedger::create([
            'user_id' => $siti->id,
            'complaint_id' => $complaintBlitar2?->id,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 35,
            'balance_before' => 40,
            'balance_after' => 75,
            'reason' => 'Pelanggaran: Ketidaksesuaian durasi pijat dan penghentian sesi sepihak sebelum waktu selesai.',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        PenaltyAppeal::where('user_id', $siti->id)->delete();

        PenaltyAppeal::create([
            'appeal_code' => 'BND-20261029-0001',
            'user_id' => $siti->id,
            'region_id' => $blitar->id,
            'complaint_id' => $complaintBlitar2?->id,
            'statement_letter_path' => 'appeals/surat_pernyataan_siti_aminah.jpg',
            'evidence_photos' => [
                'appeals/evidence/nota_bengkel_siti.png',
                'appeals/evidence/kondisi_ban_siti.png',
            ],
            'reason' => 'Saya memohon peninjauan kembali sanksi pembatasan akun. Pada saat kejadian, kendaraan saya mengalami kebocoran ban dan baterai ponsel habis sehingga tidak dapat memberikan kabar secara langsung. Saya melampirkan foto surat pernyataan fisik bermaterai, nota bengkel resmi, serta foto kondisi ban bocor di lokasi kejadian.',
            'status' => AppealStatus::Menunggu,
            'points_to_reverse' => 35,
            'reviewed_by_id' => null,
            'reviewed_at' => null,
            'admin_notes' => null,
            'created_at' => Carbon::now()->subHours(8),
            'updated_at' => Carbon::now()->subHours(8),
        ]);

        // =========================================================================
        // 3. MITRA HENDRA WIJAYA (Status: Tersuspend 7 Hari, Poin: 75, Banding: Disetujui)
        // Alur: 2x Pelanggaran (+60, +50 = 110) -> Banding Disetujui (-35) -> Poin 75
        // =========================================================================
        $hendra = User::updateOrCreate(
            ['email' => 'hendra.terapis@pijatin.test'],
            [
                'name' => 'Hendra Wijaya',
                'nik' => '3509012306870003',
                'gender' => 'laki-laki',
                'phone' => '081234567893',
                'role' => Role::Mitra,
                'status' => UserStatus::Ditangguhkan,
                'region_id' => $jember->id,
                'penalty_points' => 75,
                'suspended_until' => Carbon::now()->addDays(2),
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subMonths(4),
                'phone_verified_at' => Carbon::now()->subMonths(4),
            ]
        );

        $hendra->partnerProfile()->updateOrCreate(
            ['user_id' => $hendra->id],
            [
                'region_id' => $jember->id,
                'address_text' => 'Jl. Gajah Mada No. 88, Kaliwates, Kabupaten Jember',
                'coordinate' => ['lat' => -8.1724, 'lng' => 113.6995],
                'ktp_path' => 'ktp/dummy_hendra.jpg',
                'selfie_path' => 'selfie/dummy_hendra.jpg',
                'skck_path' => 'skck/dummy_hendra.jpg',
                'is_active' => true,
                'verified_at' => Carbon::now()->subMonths(4),
                'verified_by' => $admin?->id,
            ]
        );

        PenaltyLedger::where('user_id', $hendra->id)->delete();

        PenaltyLedger::create([
            'user_id' => $hendra->id,
            'complaint_id' => $complaintJember1?->id,
            'order_id' => $orderJember?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 60,
            'balance_before' => 0,
            'balance_after' => 60,
            'reason' => 'Pelanggaran: Permintaan tambahan tarif secara tunai di luar rincian transaksi aplikasi.',
            'created_at' => Carbon::now()->subDays(8),
            'updated_at' => Carbon::now()->subDays(8),
        ]);

        PenaltyLedger::create([
            'user_id' => $hendra->id,
            'complaint_id' => null,
            'order_id' => $orderJember?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 50,
            'balance_before' => 60,
            'balance_after' => 110,
            'reason' => 'Pelanggaran: Standar higienitas perlengkapan tidak terpenuhi dan keluhan kebersihan berulang.',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        PenaltyLedger::create([
            'user_id' => $hendra->id,
            'complaint_id' => $complaintJember1?->id,
            'order_id' => $orderJember?->id,
            'actor_id' => $admin?->id,
            'type' => 'appeal_reversal',
            'points' => -35,
            'balance_before' => 110,
            'balance_after' => 75,
            'reason' => 'Pemulihan Poin Sanksi: Banding BND-20261028-0002 disetujui sebagian oleh Admin Wilayah.',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        PenaltyAppeal::where('user_id', $hendra->id)->delete();

        PenaltyAppeal::create([
            'appeal_code' => 'BND-20261028-0002',
            'user_id' => $hendra->id,
            'region_id' => $jember->id,
            'complaint_id' => $complaintJember1?->id,
            'statement_letter_path' => 'appeals/surat_pernyataan_hendra_wijaya.pdf',
            'evidence_photos' => [
                'appeals/evidence/chat_kesepakatan_hendra.png',
                'appeals/evidence/bukti_transfer_opsional.png',
            ],
            'reason' => 'Klarifikasi terkait laporan tarif tambahan. Pelanggan meminta penambahan durasi pijat refleksi 30 menit di luar aplikasi secara langsung. Saya melampirkan tangkapan layar percakapan dan nota pembelian minyak aroma.',
            'status' => AppealStatus::Disetujui,
            'points_to_reverse' => 35,
            'reviewed_by_id' => $admin?->id,
            'reviewed_at' => Carbon::now()->subDays(1),
            'admin_notes' => 'Banding disetujui sebagian setelah verifikasi silang bersama pelanggan. Terapis tetap diberikan pembinaan kepatuhan terhadap aturan pembayaran digital. Poin sanksi dipulihkan 35 poin.',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        // =========================================================================
        // 4. MITRA JOKO PRASETYO (Status: Diblokir Permanen, Poin: 160, Banding: Ditolak)
        // Alur: 2x Pelanggaran Berat (+60, +100 = 160) -> Banned -> Banding Ditolak
        // =========================================================================
        $joko = User::updateOrCreate(
            ['email' => 'joko.terapis@pijatin.test'],
            [
                'name' => 'Joko Prasetyo',
                'nik' => '3505021703830004',
                'gender' => 'laki-laki',
                'phone' => '081234567894',
                'role' => Role::Mitra,
                'status' => UserStatus::Diblokir,
                'region_id' => $blitar->id,
                'penalty_points' => 160,
                'suspended_until' => null,
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subMonths(6),
                'phone_verified_at' => Carbon::now()->subMonths(6),
            ]
        );

        $joko->partnerProfile()->updateOrCreate(
            ['user_id' => $joko->id],
            [
                'region_id' => $blitar->id,
                'address_text' => 'Jl. Semeru No. 101, Sukorejo, Kota Blitar',
                'coordinate' => ['lat' => -8.1095, 'lng' => 112.1550],
                'ktp_path' => 'ktp/dummy_joko.jpg',
                'selfie_path' => 'selfie/dummy_joko.jpg',
                'skck_path' => 'skck/dummy_joko.jpg',
                'is_active' => false,
                'verified_at' => Carbon::now()->subMonths(6),
                'verified_by' => $admin?->id,
            ]
        );

        PenaltyLedger::where('user_id', $joko->id)->delete();

        PenaltyLedger::create([
            'user_id' => $joko->id,
            'complaint_id' => $complaintBlitar1?->id,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 60,
            'balance_before' => 0,
            'balance_after' => 60,
            'reason' => 'Pelanggaran: Perkataan kasar dan pelecehan verbal terhadap pelanggan di lokasi layanan.',
            'created_at' => Carbon::now()->subDays(12),
            'updated_at' => Carbon::now()->subDays(12),
        ]);

        PenaltyLedger::create([
            'user_id' => $joko->id,
            'complaint_id' => $complaintBlitar2?->id,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 100,
            'balance_before' => 60,
            'balance_after' => 160,
            'reason' => 'Pelanggaran Berat: Intimidasi, ancaman keselamatan, dan percobaan pemerasan pelanggan.',
            'created_at' => Carbon::now()->subDays(6),
            'updated_at' => Carbon::now()->subDays(6),
        ]);

        PenaltyAppeal::where('user_id', $joko->id)->delete();

        PenaltyAppeal::create([
            'appeal_code' => 'BND-20261025-0003',
            'user_id' => $joko->id,
            'region_id' => $blitar->id,
            'complaint_id' => $complaintBlitar2?->id,
            'statement_letter_path' => 'appeals/surat_pernyataan_joko_prasetyo.pdf',
            'reason' => 'Saya memohon kebijakan manajemen untuk membuka pemblokiran akun saya dan berjanji tidak akan mengulangi kesalahan tersebut. Surat permohonan maaf tertulis telah saya lampirkan.',
            'status' => AppealStatus::Ditolak,
            'points_to_reverse' => 100,
            'reviewed_by_id' => $admin?->id,
            'reviewed_at' => Carbon::now()->subDays(3),
            'admin_notes' => 'Permohonan banding ditolak secara permanen. Tindakan intimidasi dan pelecehan terhadap pelanggan merupakan pelanggaran kode etik fatal (Zero Tolerance Policy) sesuai syarat dan ketentuan kemitraan.',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(3),
        ]);

        // =========================================================================
        // 5. PELANGGAN RIAN PRATAMA (Status: Terverifikasi, Poin: 45, Peringatan)
        // Alur: 1x Pelanggaran Pelanggan (+35) + 1x Penyesuaian Manual Admin (+10)
        // =========================================================================
        $rian = User::updateOrCreate(
            ['email' => 'rian.pelanggan@pijatin.test'],
            [
                'name' => 'Rian Pratama',
                'nik' => '3505011905950005',
                'gender' => 'laki-laki',
                'phone' => '081234567895',
                'role' => Role::Pelanggan,
                'status' => UserStatus::Terverifikasi,
                'region_id' => $blitar->id,
                'penalty_points' => 45,
                'suspended_until' => null,
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subMonths(1),
                'phone_verified_at' => Carbon::now()->subMonths(1),
            ]
        );

        PenaltyLedger::where('user_id', $rian->id)->delete();

        PenaltyLedger::create([
            'user_id' => $rian->id,
            'complaint_id' => null,
            'order_id' => $orderBlitar?->id,
            'actor_id' => $admin?->id,
            'type' => 'violation',
            'points' => 35,
            'balance_before' => 0,
            'balance_after' => 35,
            'reason' => 'Laporan aduan terapis: Pelanggan membatalkan pesanan secara sepihak setelah terapis tiba di lokasi.',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(4),
        ]);

        PenaltyLedger::create([
            'user_id' => $rian->id,
            'complaint_id' => null,
            'order_id' => null,
            'actor_id' => $superadmin?->id,
            'type' => 'admin_adjustment',
            'points' => 10,
            'balance_before' => 35,
            'balance_after' => 45,
            'reason' => 'Penyesuaian manual oleh Superadmin: Penalti tambahan akibat riwayat perilaku tidak kooperatif dalam penanganan aduan.',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);
    }
}
