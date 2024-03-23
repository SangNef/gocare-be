<?php

namespace App\Console\Commands;

use App\Models\DOrder;
use App\Models\Order;
use App\Models\Store;
use App\Models\OrderStatus;
use App\Notifications\Telegram;
use Illuminate\Console\Command;

class OrderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:order {id?} {--fe} {--notdraft}';

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
            /** @var DOrder $order */
            $order = $this->option('notdraft') ? Order::find($this->argument('id')) : DOrder::find($this->argument('id'));
            $customer = $order->customer;
            $store = $customer->store;
            $storeSetting = $store->setting;
            if ($order) {
                $group = $order->isFromFE() || $order->cross_store ? @$storeSetting['tele_fe_order_notification'] : @$storeSetting['tele_be_order_notification'];
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
                    return @$setting['tele_fe_order_notification'] || @$setting['tele_fe_order_notification'];
                });
            $orders = DOrder::where('order_from', $this->option('fe') ? 2 : 1)
                ->get();
            /** @var DOrder $order */
            foreach ($orders as $order) {
                $store = $order->customer->store_id;
                $key = $order->isFromFE() ? 'tele_fe_order_notification' : 'tele_be_order_notification';
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
        if (app(OrderStatus::class)->isCancel($order->status))
        {
            return $this->generateCancelOrderNotificationString($order);
        }
        $mess = [
            $order->code . ' - ' . $order->customer->name
        ];
        foreach ($order->orderProducts as $key => $product) {
            $mess[] = implode(' - ', [
                $key + 1,
                $product->product->name,
                $product->attr_texts,
                $product->quantity + $product->w_quantity
            ]);
        }

        return implode("\n", $mess);
    }

    protected function generateCancelOrderNotificationString($order)
    {
        $mess = [
            'Đơn hàng bị HUỶ #'. $order->code . ' - ' . $order->customer->name
        ];
        
        return implode("\n", $mess);
    }
}
