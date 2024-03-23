<?php

namespace App\Listeners;

use App\Events\OrderSaved;
use App\Models\StoreProduct;
use Dwij\Laraadmin\Models\LAConfigs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncStoreProductQuantity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderSaved  $event
     * @return void
     */
    public function handle(OrderSaved $event)
    {
        $order = $event->order();
        $products = $order->products()->pluck('product_id');
        $stores = LAConfigs::where('key', 'dong_bo_so_luong_san_pham')->first();
        if ($stores) {
            $stores = explode(',', $stores->value);
            if (in_array($order->store_id, $stores)) {
                $orthers = array_filter($stores, function ($id) use ($order) {
                    return $order->store_id != $id;
                });
                foreach ($products as $productId) {
                    $left = StoreProduct::where('product_id', $productId)
                        ->where('store_id', $order->store_id)
                        ->first();
                    if ($left) {
                        StoreProduct::whereIn('store_id', $orthers)
                            ->where('product_id', $productId)
                            ->update([
                                'n_quantity' => $left->n_quantity,
                                'w_quantity' => $left->w_quantity,
                            ]);
                    }
                }
            }
        }
    }
}
