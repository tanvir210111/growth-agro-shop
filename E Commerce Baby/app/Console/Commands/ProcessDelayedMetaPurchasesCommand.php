<?php

namespace App\Console\Commands;

use App\Services\MetaPurchaseControlService;
use Illuminate\Console\Command;

class ProcessDelayedMetaPurchasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meta:process-delayed-purchases {--limit=50 : Maximum number of delayed purchases to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and dispatch due delayed Meta CAPI Purchase events';

    /**
     * Execute the console command.
     */
    public function handle(MetaPurchaseControlService $service): int
    {
        $limit = (int) $this->option('limit');
        $this->info("Checking for due delayed Meta Purchase events (limit: {$limit})...");

        $result = $service->processDueDelayedPurchases($limit);

        $this->info(sprintf(
            'Processed: %d, Succeeded: %d, Failed: %d',
            $result['processed'],
            $result['succeeded'],
            $result['failed']
        ));

        return Command::SUCCESS;
    }
}
