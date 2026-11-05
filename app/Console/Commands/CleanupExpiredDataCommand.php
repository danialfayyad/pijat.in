<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Parameter;
use App\Models\SystemNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupExpiredDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-expired-data {--days= : Override retensi hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan riwayat chat dan catatan medis pesanan lama sesuai UU PDP (SYS-07).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysOption = $this->option('days');
        $retentionDays = $daysOption !== null ? (int) $daysOption : (int) Parameter::get('chat_and_medical_retention_days', 30);

        if ($retentionDays <= 0) {
            $retentionDays = 30;
        }

        $cutoff = Carbon::now()->subDays($retentionDays);
        $this->info("Menjalankan pembersihan data kedaluwarsa dengan batas usia: {$retentionDays} hari (sebelum {$cutoff->toDateTimeString()})...");

        // 1. Delete order messages created before cutoff
        $deletedMessagesCount = OrderMessage::where('created_at', '<', $cutoff)->delete();

        // 2. Wipe / nullify sensitive medical notes for orders created before cutoff
        $clearedMedicalCount = Order::where('created_at', '<', $cutoff)
            ->whereNotNull('medical_notes')
            ->update(['medical_notes' => null]);

        // 3. Delete system notifications created before cutoff (1 month / 30 days)
        $deletedNotificationsCount = SystemNotification::where('created_at', '<', $cutoff)->delete();

        $this->info('Berhasil membersihkan:');
        $this->line("- {$deletedMessagesCount} pesan chat riwayat.");
        $this->line("- {$clearedMedicalCount} catatan medis pesanan dikosongkan.");
        $this->line("- {$deletedNotificationsCount} notifikasi sistem lama dihapus.");

        // Record audit log for compliance
        AuditLog::record([
            'user_id' => null, // Automated system cron
            'module' => 'system',
            'action' => 'data_retention_cleanup',
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => [
                'retention_days' => $retentionDays,
                'cutoff' => $cutoff->toIso8601String(),
                'deleted_messages_count' => $deletedMessagesCount,
                'cleared_medical_count' => $clearedMedicalCount,
                'deleted_notifications_count' => $deletedNotificationsCount,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'CLI/Artisan Schedule',
        ]);

        return Command::SUCCESS;
    }
}
