<?php


namespace App\Repositories;


use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\StoreProductGroupAttributeExtra;
use Illuminate\Support\Facades\DB;

class StoreRepository
{
    public function moveProduct($productId, $to, $nQuantity = 0, $wQuantity = 0, $from = 0, $attrs = '')
    {
       if ($this->isInStock($productId, $from, $nQuantity, $wQuantity)) {
           $this->updateQuantity($to, $productId, $nQuantity, $wQuantity, $attrs);
           $this->updateQuantity($from, $productId, -$nQuantity, -$wQuantity, $attrs);

           return true;
       }

       return false;
    }

    public function updateQuantity($storeId, $productId, $nQuantity, $wQuantity, $attrs = '')
    {
        if ($storeId) {
            $receiver = StoreProduct::firstOrCreate([
                'store_id' => $storeId,
                'product_id' => $productId
            ]);
        } else {
            $receiver = Product::find($productId);
        }

        $receiver->n_quantity += $nQuantity;
        $receiver->w_quantity += $wQuantity;
        $receiver->save();

        if ($attrs && $storeId) {
            $group = StoreProductGroupAttributeExtra::firstOrCreate([
                'store_id' => $storeId,
                'product_id' => $productId,
                'attribute_value_ids' => $attrs
            ]);

            $group->n_quantity += $nQuantity;
            $group->w_quantity += $wQuantity;
            $group->save();
        }
    }

    public function isInStock($productId, $storeId, $nQuantity = 0, $wQuantity = 0)
    {
        if ($storeId) {
            return StoreProduct::where('product_id', $productId)
                ->where('n_quantity', '>=', $nQuantity)
                ->where('w_quantity', '>=', $wQuantity)
                ->where('store_id', $storeId)
                ->count() > 0;
        }

        return Product::where('id', $productId)
            ->where('n_quantity', '>=', $nQuantity)
            ->where('w_quantity', '>=', $wQuantity)
            ->count() > 0;
    }
}