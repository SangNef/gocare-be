<?php

namespace App\Console\Commands;

use App\Models\DOrder;
use App\Models\Order;
use App\Models\Store;
use App\Models\OrderStatus;
use App\Notifications\Telegram;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cancel {id?}';

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
        $orders = Order::where('payment_method', 3)
            ->where('paid', 0)
            ->where('status', 1)
            ->where('payment', 'online')
            ->where('created_at', '<=', Carbon::now()->subMinutes(15));
        if ($this->argument('id')) {
            $orders->where('id', $this->argument('id'));
        }
        $orders = $orders->get();

        foreach ($orders as $order) {
            $order->status = '4';
            $order->save();
        }

    }
}
