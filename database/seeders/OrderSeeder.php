<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Region;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'pelanggan@pijatin.test')->first() ?? User::factory()->customer()->create();
        $mitra = User::where('email', 'mitra@pijatin.test')->first() ?? User::factory()->mitra()->create();
        $admin = User::where('email', 'admin@pijatin.test')->first() ?? User::factory()->admin()->create();

        $blitar = Region::where('name', 'like', '%BLITAR%')->first() ?? Region::first();
        $jember = Region::where('name', 'like', '%JEMBER%')->first() ?? Region::latest()->first();
        $service = Service::first() ?? Service::factory()->create();

        // 1. Pesanan Macet (Instant order looking for partner > 30 minutes)
        if ($blitar) {
            $stuckOrder = Order::create([
                'order_code' => 'ORD-20261028-MCT01',
                'order_type' => OrderType::Instan,
                'customer_id' => $customer->id,
                'mitra_id' => null,
                'region_id' => $blitar->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'duration_minutes' => 90,
                'total_price' => 140000,
                'commission_percent' => 10.00,
                'recipient_name' => 'Bambang Supriyadi',
                'recipient_gender' => 'pria',
                'recipient_phone' => '081234567890',
                'service_address_text' => 'Jl. Merdeka No. 45, Kepanjenkidul, Kota Blitar',
                'service_coordinate' => ['lat' => -8.0983, 'lng' => 112.1681],
                'scheduled_start_time' => null,
                'status' => OrderStatus::MencariMitra,
                'reschedule_count' => 0,
                'created_at' => Carbon::now()->subMinutes(45),
                'updated_at' => Carbon::now()->subMinutes(45),
            ]);

            OrderStatusHistory::create([
                'order_id' => $stuckOrder->id,
                'from_status' => OrderStatus::MenungguPembayaran,
                'to_status' => OrderStatus::MencariMitra,
                'actor_id' => $customer->id,
                'actor_role' => 'pelanggan',
                'note' => 'Pembayaran via QRIS terkonfirmasi. Sistem mulai menyiarkan pencarian mitra.',
                'created_at' => Carbon::now()->subMinutes(45),
            ]);
        }

        // 2. Pesanan Aktif (Sedang Berlangsung)
        if ($blitar) {
            $activeOrder = Order::create([
                'order_code' => 'ORD-20261028-AKT02',
                'order_type' => OrderType::Instan,
                'customer_id' => $customer->id,
                'mitra_id' => $mitra->id,
                'region_id' => $blitar->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'duration_minutes' => 60,
                'total_price' => 100000,
                'commission_percent' => 10.00,
                'recipient_name' => 'Dewi Lestari',
                'recipient_gender' => 'wanita',
                'recipient_phone' => '081398765432',
                'service_address_text' => 'Perum Griya Indah Blok C-12, Sananwetan, Kota Blitar',
                'service_coordinate' => ['lat' => -8.1025, 'lng' => 112.1755],
                'scheduled_start_time' => null,
                'status' => OrderStatus::SedangBerlangsung,
                'reschedule_count' => 0,
                'created_at' => Carbon::now()->subMinutes(50),
                'updated_at' => Carbon::now()->subMinutes(10),
            ]);

            OrderStatusHistory::create([
                'order_id' => $activeOrder->id,
                'from_status' => OrderStatus::MenungguPembayaran,
                'to_status' => OrderStatus::MencariMitra,
                'actor_id' => $customer->id,
                'actor_role' => 'pelanggan',
                'note' => 'Pembayaran pesanan berhasil.',
                'created_at' => Carbon::now()->subMinutes(50),
            ]);

            OrderStatusHistory::create([
                'order_id' => $activeOrder->id,
                'from_status' => OrderStatus::MencariMitra,
                'to_status' => OrderStatus::MenujuLokasi,
                'actor_id' => $mitra->id,
                'actor_role' => 'mitra',
                'note' => 'Mitra menerima pesanan dan bergerak menuju lokasi customer.',
                'created_at' => Carbon::now()->subMinutes(40),
            ]);

            OrderStatusHistory::create([
                'order_id' => $activeOrder->id,
                'from_status' => OrderStatus::MenujuLokasi,
                'to_status' => OrderStatus::Tiba,
                'actor_id' => $mitra->id,
                'actor_role' => 'mitra',
                'note' => 'Mitra tiba di lokasi penerima layanan (Radius validasi < 50m).',
                'created_at' => Carbon::now()->subMinutes(15),
            ]);

            OrderStatusHistory::create([
                'order_id' => $activeOrder->id,
                'from_status' => OrderStatus::Tiba,
                'to_status' => OrderStatus::SedangBerlangsung,
                'actor_id' => $mitra->id,
                'actor_role' => 'mitra',
                'note' => 'Sesi pijat telah dimulai.',
                'created_at' => Carbon::now()->subMinutes(10),
            ]);
        }

        // 3. Pesanan Terjadwal di Jember
        if ($jember) {
            $scheduledOrder = Order::create([
                'order_code' => 'ORD-20261028-SCH03',
                'order_type' => OrderType::Terjadwal,
                'customer_id' => $customer->id,
                'mitra_id' => $mitra->id,
                'region_id' => $jember->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'duration_minutes' => 120,
                'total_price' => 180000,
                'commission_percent' => 10.00,
                'recipient_name' => 'Ahmad Fauzi',
                'recipient_gender' => 'pria',
                'recipient_phone' => '082155667788',
                'service_address_text' => 'Jl. Kalimantan No. 37, Sumbersari, Kabupaten Jember',
                'service_coordinate' => ['lat' => -8.1724, 'lng' => 113.7178],
                'scheduled_start_time' => Carbon::now()->addDay()->setHour(14)->setMinute(0),
                'status' => OrderStatus::Dijadwalkan,
                'reschedule_count' => 0,
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(1),
            ]);

            OrderStatusHistory::create([
                'order_id' => $scheduledOrder->id,
                'from_status' => OrderStatus::MenungguPembayaran,
                'to_status' => OrderStatus::Dijadwalkan,
                'actor_id' => $customer->id,
                'actor_role' => 'pelanggan',
                'note' => 'Pesanan terjadwal berhasil dibuat dan terkonfirmasi.',
                'created_at' => Carbon::now()->subHours(2),
            ]);
        }

        // 4. Pesanan Selesai di Blitar
        if ($blitar) {
            $doneOrder = Order::create([
                'order_code' => 'ORD-20261027-SLS04',
                'order_type' => OrderType::Instan,
                'customer_id' => $customer->id,
                'mitra_id' => $mitra->id,
                'region_id' => $blitar->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'duration_minutes' => 60,
                'total_price' => 100000,
                'commission_percent' => 10.00,
                'recipient_name' => 'Rini Astuti',
                'recipient_gender' => 'wanita',
                'recipient_phone' => '085711223344',
                'service_address_text' => 'Jl. Veteran No. 88, Sukorejo, Kota Blitar',
                'service_coordinate' => ['lat' => -8.1050, 'lng' => 112.1620],
                'scheduled_start_time' => null,
                'status' => OrderStatus::Selesai,
                'reschedule_count' => 0,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1)->addHours(2),
            ]);

            OrderStatusHistory::create([
                'order_id' => $doneOrder->id,
                'from_status' => OrderStatus::SedangBerlangsung,
                'to_status' => OrderStatus::Selesai,
                'actor_id' => $mitra->id,
                'actor_role' => 'mitra',
                'note' => 'Layanan pijat selesai dengan baik.',
                'created_at' => Carbon::now()->subDays(1)->addHours(2),
            ]);
        }

        // 5. Pesanan Dibatalkan oleh Admin (Intervensi Macet)
        if ($jember) {
            $cancelledOrder = Order::create([
                'order_code' => 'ORD-20261027-BTL05',
                'order_type' => OrderType::Instan,
                'customer_id' => $customer->id,
                'mitra_id' => null,
                'region_id' => $jember->id,
                'service_id' => $service->id,
                'service_name' => $service->name,
                'duration_minutes' => 90,
                'total_price' => 140000,
                'commission_percent' => 10.00,
                'recipient_name' => 'Hendro Utomo',
                'recipient_gender' => 'pria',
                'recipient_phone' => '087812349900',
                'service_address_text' => 'Jl. Gajah Mada No. 101, Kaliwates, Kabupaten Jember',
                'service_coordinate' => ['lat' => -8.1832, 'lng' => 113.6890],
                'scheduled_start_time' => null,
                'status' => OrderStatus::Dibatalkan,
                'cancellation_reason' => 'Tidak ada mitra yang menerima penawaran dalam batas toleransi SLA 30 menit.',
                'cancelled_by_id' => $admin->id,
                'cancelled_by_role' => 'admin',
                'reschedule_count' => 0,
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(5),
            ]);

            OrderStatusHistory::create([
                'order_id' => $cancelledOrder->id,
                'from_status' => OrderStatus::MencariMitra,
                'to_status' => OrderStatus::Dibatalkan,
                'actor_id' => $admin->id,
                'actor_role' => 'admin',
                'note' => 'Pembatalan intervensi oleh Admin: Tidak ada mitra yang menerima penawaran dalam batas toleransi SLA 30 menit.',
                'created_at' => Carbon::now()->subHours(5),
            ]);
        }
    }
}
