<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Notifications\Telegram;
use App\Repositories\ProductRepository;
use Illuminate\Console\Command;
use Mail;
use DB;

class SendLowStockProductNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:low-stock';

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
        $stores = Store::where('status', "Bật")->get();
        $tele = app(Telegram::class);
        $tele->setToken('955602420:AAGmZah0xmF873uMrsrlgSifOB-xEXAeSZw');
        $rp = app(ProductRepository::class);
        foreach ($stores as $store) {
            $productIds = StoreProduct::where('store_id', $store->id)
                ->whereRaw('min > n_quantity and min > 0')
                ->get()
                ->pluck('n_quantity', 'product_id')
                ->toArray();
            $setting = $store->setting;
            if (!empty($productIds) && @$setting['tele_product_min_notification']) {
                $text = [];
                $text[] = count($productIds) . ' sản phẩm sắp hết hàng.';
//                if (count($productIds) < 5) {
                    $products = Product::whereIn('id', array_keys($productIds))
                        ->get();
                    foreach ($products as $product) {
                        $text[] = implode(' -- ', [
                            $product->sku,
                            $product->name,
                            (int) @$productIds[$product->id]
                        ]);
                    }
//                }
                $tele->sendText(implode("\n", $text), $setting['tele_product_min_notification']);
            }
        }
    }
}
