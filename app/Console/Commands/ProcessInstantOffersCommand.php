<?php

namespace App\Console\Commands;

use App\Services\InstantOrderMatchingService;
use Illuminate\Console\Command;

class ProcessInstantOffersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:process-instant-offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire timed-out 60-second instant order offers and transition sequentially to the next nearest candidate (SYS-01).';

    /**
     * Execute the console command.
     */
    public function handle(InstantOrderMatchingService $matchingService): int
    {
        $expiredCount = $matchingService->processExpiredOffers();

        $this->info("Berhasil memproses {$expiredCount} tawaran instan kedaluwarsa.");

        return Command::SUCCESS;
    }
}
