<?php

namespace App\Console\Commands;

use App\Services\SupportChatService;
use Illuminate\Console\Command;

class ProcessSupportChatInactivityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support:check-inactivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatisasi pengingat 1 menit dan penutupan sopan sesi live chat yang tidak aktif (5 menit).';

    /**
     * Execute the console command.
     */
    public function handle(SupportChatService $service): int
    {
        $result = $service->processInactivityTimeouts();

        if ($result['warned'] > 0 || $result['closed'] > 0) {
            $this->info("Inactivity check: {$result['warned']} tiket diperingatkan, {$result['closed']} tiket diselesaikan.");
        }

        return Command::SUCCESS;
    }
}
