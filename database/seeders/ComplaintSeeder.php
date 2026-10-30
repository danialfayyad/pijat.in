<?php

namespace Database\Seeders;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@pijatin.test')->first() ?? User::where('role', 'admin')->first();
        $customer = User::where('email', 'pelanggan@pijatin.test')->first() ?? User::where('role', 'pelanggan')->first();
        $mitra = User::where('email', 'mitra@pijatin.test')->first() ?? User::where('role', 'mitra')->first();

        $blitar = Region::where('name', 'like', '%BLITAR%')->first() ?? Region::first();
        $jember = Region::where('name', 'like', '%JEMBER%')->first() ?? Region::skip(1)->first() ?? Region::first();

        $ordersBlitar = Order::where('region_id', $blitar->id)->get();
        $ordersJember = Order::where('region_id', $jember->id)->get();

        $orderB1 = $ordersBlitar->first();
        $orderB2 = $ordersBlitar->skip(1)->first() ?? $orderB1;
        $orderB3 = $ordersBlitar->skip(2)->first() ?? $orderB1;
        $orderB4 = $ordersBlitar->skip(3)->first() ?? $orderB2;

        $orderJ1 = $ordersJember->first() ?? $orderB1;
        $orderJ2 = $ordersJember->skip(1)->first() ?? $orderJ1;

        $complaints = [
            // === WILAYAH BLITAR ===
            [
                'complaint_code' => 'ADU-BLT-001',
                'order_id' => $orderB1?->id,
                'region_id' => $blitar->id,
                'complainant_id' => $orderB1?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderB1?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::KeterlambatanParah,
                'title' => 'Terapis terlambat 45 menit tanpa kabar atau respon chat',
                'description' => 'Saya memesan layanan untuk jam 14:00, namun terapis baru tiba di lokasi pukul 14:45 WIB tanpa ada pemberitahuan atau pesan chat sebelumnya. Jadwal kegiatan saya setelahnya jadi terganggu.',
                'status' => ComplaintStatus::Menunggu,
                'priority' => 'tinggi',
                'handled_by_id' => null,
                'handled_at' => null,
                'resolution_type' => null,
                'admin_notes' => null,
                'created_at' => Carbon::now()->subMinutes(35),
            ],
            [
                'complaint_code' => 'ADU-BLT-002',
                'order_id' => $orderB2?->id,
                'region_id' => $blitar->id,
                'complainant_id' => $orderB2?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderB2?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::TarifLuarAplikasi,
                'title' => 'Mitra meminta biaya bensin tunai tambahan sebesar Rp 25.000',
                'description' => 'Saat selesai pemijatan, terapis meminta uang tambahan sebesar Rp 25.000 dengan alasan jarak rumah agak jauh ke dalam gang, padahal tarif di aplikasi sudah termasuk semua biaya.',
                'status' => ComplaintStatus::Diproses,
                'priority' => 'normal',
                'handled_by_id' => $admin?->id,
                'handled_at' => Carbon::now()->subHours(1),
                'resolution_type' => null,
                'admin_notes' => 'Admin telah menghubungi pihak terapis untuk meminta klarifikasi bukti pembayaran tunai.',
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'complaint_code' => 'ADU-BLT-003',
                'order_id' => $orderB3?->id,
                'region_id' => $blitar->id,
                'complainant_id' => $orderB3?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderB3?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::LayananTidakSesuai,
                'title' => 'Layanan baru berjalan 30 menit namun terapis pamit pulang mendadak',
                'description' => 'Paket durasi yang dipesan adalah 90 menit. Namun terapis hanya memijat selama 35 menit lalu terburu-buru pamit dengan alasan ada urusan keluarga mendadak.',
                'status' => ComplaintStatus::Selesai,
                'priority' => 'tinggi',
                'handled_by_id' => $admin?->id,
                'handled_at' => Carbon::now()->subHours(8),
                'resolution_type' => 'refund_penuh',
                'admin_notes' => 'Setelah memverifikasi rekaman linimasa dan klarifikasi ke pihak mitra, terapis mengakui meninggalkan lokasi lebih awal. Diputuskan refund penuh ke saldo pelanggan dan komisi mitra dibatalkan.',
                'resolved_at' => Carbon::now()->subHours(6),
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'complaint_code' => 'ADU-BLT-004',
                'order_id' => $orderB4?->id,
                'region_id' => $blitar->id,
                'complainant_id' => $orderB4?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderB4?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::Lainnya,
                'title' => 'Pelanggan mengaku terapis tidak pernah hadir di lokasi layanan',
                'description' => 'Pelanggan meminta pengembalian dana penuh dengan alasan terapis tidak pernah hadir di lokasi layanan.',
                'status' => ComplaintStatus::Ditolak,
                'priority' => 'normal',
                'handled_by_id' => $admin?->id,
                'handled_at' => Carbon::now()->subHours(24),
                'resolution_type' => 'ditolak',
                'admin_notes' => "Dilakukan pengecekan log koordinat GPS mitra yang tercatat berada tepat di titik lokasi selama 90 menit, serta bukti chat foto sambutan pelanggan di depan rumah. Aduan tidak terbukti valid.\n[CATATAN ADMIN]: Laporan terindikasi aduan palsu / mengada-ada.",
                'resolved_at' => Carbon::now()->subHours(20),
                'created_at' => Carbon::now()->subHours(30),
            ],
            [
                'complaint_code' => 'ADU-BLT-005',
                'order_id' => $orderB1?->id,
                'region_id' => $blitar->id,
                'complainant_id' => $orderB1?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderB1?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::PerilakuTidakSopan,
                'title' => 'Mitra menggunakan kata-kata kurang pantas saat berbicara',
                'description' => 'Terapis berbicara dengan nada tinggi dan melontarkan komentar bernada sarkasme yang membuat pelanggan merasa sangat tidak nyaman selama durasi pemijatan.',
                'status' => ComplaintStatus::Menunggu,
                'priority' => 'tinggi',
                'handled_by_id' => null,
                'handled_at' => null,
                'resolution_type' => null,
                'admin_notes' => null,
                'created_at' => Carbon::now()->subMinutes(15),
            ],

            // === WILAYAH JEMBER ===
            [
                'complaint_code' => 'ADU-JMB-001',
                'order_id' => $orderJ1?->id,
                'region_id' => $jember->id,
                'complainant_id' => $orderJ1?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderJ1?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::Lainnya,
                'title' => 'Mitra membatalkan pesanan secara sepihak saat sudah dekat lokasi',
                'description' => "Status pesanan sudah menunjukkan 'Menuju Lokasi' selama 20 menit, namun tiba-tiba dibatalkan sepihak oleh mitra tanpa alasan yang jelas.",
                'status' => ComplaintStatus::Menunggu,
                'priority' => 'normal',
                'handled_by_id' => null,
                'handled_at' => null,
                'resolution_type' => null,
                'admin_notes' => null,
                'created_at' => Carbon::now()->subMinutes(50),
            ],
            [
                'complaint_code' => 'ADU-JMB-002',
                'order_id' => $orderJ2?->id,
                'region_id' => $jember->id,
                'complainant_id' => $orderJ2?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderJ2?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::TarifLuarAplikasi,
                'title' => 'Terapis meminta tip wajib secara memaksa',
                'description' => 'Setelah sesi pijat selesai, terapis bersikeras meminta uang tip sebesar Rp 50.000 dan tidak mau beranjak sebelum diberikan.',
                'status' => ComplaintStatus::Diproses,
                'priority' => 'tinggi',
                'handled_by_id' => $admin?->id,
                'handled_at' => Carbon::now()->subHours(2),
                'resolution_type' => null,
                'admin_notes' => 'Investigasi sedang berlangsung, menunggu bukti tangkapan layar chat dari pelapor.',
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'complaint_code' => 'ADU-JMB-003',
                'order_id' => $orderJ1?->id,
                'region_id' => $jember->id,
                'complainant_id' => $orderJ1?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderJ1?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::KebersihanHigienitas,
                'title' => 'Aroma kain alas tidak segar dan kurang higienis',
                'description' => 'Pelanggan mengeluhkan alas matras yang dibawa oleh mitra terapis tercium kurang bersih dan tampak belum diganti dari pemakaian sebelumnya.',
                'status' => ComplaintStatus::Selesai,
                'priority' => 'normal',
                'handled_by_id' => $admin?->id,
                'handled_at' => Carbon::now()->subHours(10),
                'resolution_type' => 'damai_tanpa_tindakan',
                'admin_notes' => 'Terapis telah menyampaikan permohonan maaf dan admin memberikan teguran lisan terkait standar kebersihan perlengkapan. Pelanggan sepakat berdamai.',
                'resolved_at' => Carbon::now()->subHours(9),
                'created_at' => Carbon::now()->subHours(14),
            ],
            [
                'complaint_code' => 'ADU-JMB-004',
                'order_id' => $orderJ2?->id,
                'region_id' => $jember->id,
                'complainant_id' => $orderJ2?->customer_id ?? $customer->id,
                'complainant_role' => 'pelanggan',
                'target_id' => $orderJ2?->mitra_id ?? $mitra->id,
                'category' => ComplaintCategory::PerilakuTidakSopan,
                'title' => 'Perilaku tidak sopan berulang dari mitra terapis',
                'description' => 'Terapis merokok di dalam teras rumah pelanggan tanpa izin saat sedang mempersiapkan perlengkapan pijat.',
                'status' => ComplaintStatus::Selesai,
                'priority' => 'tinggi',
                'handled_by_id' => $admin?->id,
                'handled_at' => Carbon::now()->subHours(16),
                'resolution_type' => 'rekomendasi_sp',
                'admin_notes' => 'Pelanggaran SOP etika kebersihan dan kenyamanan pelanggan. Rekomendasi penerbitan Surat Peringatan (SP 1) untuk terapis bersangkutan telah disetujui.',
                'resolved_at' => Carbon::now()->subHours(15),
                'created_at' => Carbon::now()->subHours(18),
            ],
        ];

        foreach ($complaints as $complaintData) {
            $catSlug = $complaintData['category'] instanceof ComplaintCategory
                ? $complaintData['category']->value
                : (string) $complaintData['category'];

            $catModel = \App\Models\ComplaintCategory::where('slug', $catSlug)->first();
            $complaintData['complaint_category_id'] = $catModel?->id;
            $complaintData['category'] = $catSlug;

            Complaint::updateOrCreate(
                ['complaint_code' => $complaintData['complaint_code']],
                $complaintData
            );
        }
    }
}
