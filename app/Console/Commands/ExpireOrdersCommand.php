<?php

namespace App\Console\Commands;

use App\Services\OrderWorkflowService;
use Illuminate\Console\Command;

class ExpireOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan pesanan yang belum dibayar melewati batas waktu 15 menit dan kembalikan stok.';

    /**
     * Execute the console command.
     */
    public function handle(OrderWorkflowService $workflowService): int
    {
        $count = $workflowService->expireUnpaidOrders();
        $this->info("Berhasil memeriksa dan membatalkan {$count} pesanan kedaluwarsa.");

        return self::SUCCESS;
    }
}
