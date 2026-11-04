<?php

namespace App\Console\Commands;

use App\Services\OrderSettlementService;
use Illuminate\Console\Command;

class ReleaseHeldBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-held-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release partner held balances to available balances 24 hours post-completion if no active complaints (SYS-03)';

    /**
     * Execute the console command.
     */
    public function handle(OrderSettlementService $settlementService): int
    {
        $this->info('Memeriksa saldo tertahan mitra yang berumur >= 24 jam...');

        $count = $settlementService->releaseEligibleHeldBalances();

        $this->info("Berhasil melepaskan {$count} pendapatan pesanan ke saldo siap tarik mitra.");

        return Command::SUCCESS;
    }
}
