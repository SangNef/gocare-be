<?php

namespace App\Console\Commands;

use App\Models\DOrder;
use App\Models\Order;
use App\Models\Store;
use App\Models\OrderStatus;
use App\Models\Voucher;
use App\Notifications\Telegram;
use App\Observes\VoucherObserve;
use Illuminate\Console\Command;

class ExportVoucherHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucher-histories:exports {voucherId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $voucher = Voucher::find($this->argument('voucherId'));
        if ($voucher) {
            $observer = app(VoucherObserve::class);
            $observer->syncHistoriesQuantity($voucher);
        }
    }
}
