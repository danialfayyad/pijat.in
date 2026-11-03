<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationDummySeeder extends Seeder
{
    /**
     * Run the database seeds for testing the notification center UI.
     */
    public function run(): void
    {
        // Target admin & superadmin users
        $recipients = User::whereIn('role', [Role::Admin, Role::Superadmin])->get();

        if ($recipients->isEmpty()) {
            $this->command?->warn('Tidak ada user dengan role admin/superadmin untuk menerima notifikasi dummy.');

            return;
        }

        // Hapus notifikasi darurat SOS jika sebelumnya pernah terbuat
        SystemNotification::where('event_code', 'like', '%EMERGENCY%')
            ->orWhere('title', 'like', '%SOS%')
            ->orWhere('title', 'like', '%Darurat%')
            ->delete();

        $templates = [
            [
                'event_code' => 'NOTIF_ADMIN_QUEUE',
                'title' => 'Pendaftaran Mitra Baru Menunggu Verifikasi',
                'body' => 'Calon mitra Dewi Lestari telah mengunggah dokumen KTP, SKCK, dan sertifikat kompetensi. Silakan tinjau berkas pendaftaran.',
                'priority' => 'normal',
                'read' => false,
                'created_offset' => 15, // 15 minutes ago
                'data' => [
                    'partner_name' => 'Dewi Lestari',
                    'action_url' => '/admin/mitra/verifikasi',
                ],
            ],
            [
                'event_code' => 'NOTIF_ADMIN_QUEUE',
                'title' => 'Laporan Aduan Pelanggan Masuk',
                'body' => 'Pelanggan Rian melaporkan indikasi ketidaksesuaian durasi layanan pada Pesanan #ORD-202611-0071. Memerlukan investigasi admin.',
                'priority' => 'normal',
                'read' => false,
                'created_offset' => 45, // 45 minutes ago
                'data' => [
                    'complaint_id' => 14,
                    'order_code' => 'ORD-202611-0071',
                    'category' => 'Pelanggaran Etika & SOP',
                    'action_url' => '/admin/complaints',
                ],
            ],
            [
                'event_code' => 'NOTIF_ADMIN_QUEUE',
                'title' => 'Pengajuan Banding Sanksi Poin',
                'body' => 'Terapis Ahmad Dani mengajukan permohonan banding atas sanksi penalti 15 poin akibat pembatalan sepihak.',
                'priority' => 'normal',
                'read' => false,
                'created_offset' => 180, // ~3 hours ago
                'data' => [
                    'appeal_id' => 7,
                    'partner_name' => 'Ahmad Dani',
                    'penalty_points' => 15,
                    'action_url' => '/admin/appeals',
                ],
            ],
            [
                'event_code' => 'NOTIF_ADMIN_QUEUE',
                'title' => 'Pembaruan Dokumen Legal & Kebijakan',
                'body' => 'Naskah Standar Operasional Prosedur (SOP) Kemitraan Terapis telah diperbarui ke Versi 2.2 sesuai regulasi perlindungan data konsumen.',
                'priority' => 'normal',
                'read' => true,
                'created_offset' => 1440, // 1 day ago
                'data' => [
                    'document_type' => 'sop_kemitraan',
                    'version' => '2.2',
                    'action_url' => '/superadmin/legal-documents',
                ],
            ],
            [
                'event_code' => 'NOTIF_SYS_RETENTION',
                'title' => 'Pembersihan Data Kedaluwarsa Selesai',
                'body' => 'Sistem otomatis telah membersihkan 142 pesan chat lawas dan mengosongkan riwayat medis lebih dari 30 hari sesuai ketentuan UU PDP.',
                'priority' => 'normal',
                'read' => true,
                'created_offset' => 2880, // 2 days ago
                'data' => [
                    'deleted_messages' => 142,
                    'cleared_medical' => 18,
                    'retention_days' => 30,
                ],
            ],
        ];

        foreach ($recipients as $recipient) {
            foreach ($templates as $t) {
                $createdAt = Carbon::now()->subMinutes($t['created_offset']);
                $readAt = $t['read'] ? Carbon::now()->subMinutes(max(1, $t['created_offset'] - 30)) : null;

                SystemNotification::updateOrCreate(
                    [
                        'user_id' => $recipient->id,
                        'event_code' => $t['event_code'],
                        'title' => $t['title'],
                    ],
                    [
                        'body' => $t['body'],
                        'priority' => $t['priority'],
                        'data' => $t['data'],
                        'read_at' => $readAt,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
            }
        }
    }
}
