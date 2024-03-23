<?php

namespace App\Console\Commands;

use App\Models\WarrantyOrder;
use App\Models\Store;
use App\Models\OrderStatus;
use App\Notifications\Telegram;
use Illuminate\Console\Command;

class WarrrantyOrderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:warranty-order {id?}';

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
        $tele = app(Telegram::class);
        $tele->setToken('955602420:AAGmZah0xmF873uMrsrlgSifOB-xEXAeSZw');

        if ($this->argument('id')) {
            $order = WarrantyOrder::find($this->argument('id'));
            $customer = $order->customer;
            $store = $customer->store;
            $storeSetting = $store->setting;
            if ($order) {
                $group = @$storeSetting['tele_warranty_order_notification'];
                $mess = [
                    $this->generateOrderNotificationString($order)
                ];

                $tele->sendText(implode("\n", $mess), $group);
            }
        } else {
            $mess = [];
            $stores = Store::whereNull('deleted_at')
                ->get()
                ->pluck('setting', 'id')
                ->filter(function ($setting) {
                    return @$setting['tele_warranty_order_notification'] || @$setting['tele_warranty_order_notification'];
                });
            $orders = WarrantyOrder::where('status', '<>', 2)->get();
            foreach ($orders as $order) {
                $store = $order->customer->store_id;
                $key = 'tele_warranty_order_notification';
                $mess[$store][$key][] = $this->generateOrderNotificationString($order);
            }
            foreach ($mess as $storeId => $value) {
                if (@$stores[$storeId]) {
                    foreach ($value as $from => $text) {
                        $group = @$stores[$storeId][$from];
                        if ($group) {
                            while(count($text) > 0) {
                                $sending = array_splice($text, 0, min(count($text), 5));
                                $tele->sendText(implode("\n", $sending), $group);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function generateOrderNotificationString($order)
    {
        $mess = [
            $order->code . ' - ' . $order->customer->name
        ];
        foreach ($order->warrantyOrderProducts as $key => $product) {
            $mess[] = implode(' - ', [
                $key + 1,
                $product->product->name,
                $product->quantity + $product->w_quantity
            ]);
        }

        return implode("\n", $mess);
    }
}
