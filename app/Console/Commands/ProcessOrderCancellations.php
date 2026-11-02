<?php

namespace App\Console\Commands;

use App\Services\OrderCancellationService;
use Illuminate\Console\Command;

class ProcessOrderCancellations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:process-cancellations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic cancellations for stuck orders and expired payments (SYS-02)';

    /**
     * Execute the console command.
     */
    public function handle(OrderCancellationService $cancellationService): int
    {
        $this->info('Processing automatic cancellations...');

        $result = $cancellationService->autoCancelStuckOrders();

        $this->info("Cancelled stuck instant orders: {$result['cancelled_instant']}");
        $this->info("Cancelled stuck scheduled orders: {$result['cancelled_scheduled']}");
        $this->info("Expired unpaid orders: {$result['expired_unpaid']}");
        $this->info("Expired top-up invoices: {$result['expired_invoices']}");

        return Command::SUCCESS;
    }
}
