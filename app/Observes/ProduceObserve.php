<?php
namespace App\Observes;

use App\Models\Product;
use App\Models\Produce;
use App\Models\ProductSeri;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\StoreProduct;
use App\Models\ProductGroupAttributeMedia;
use App\Repositories\ProductSeriesRepository;

class ProduceObserve
{
    public function saving(Produce $produce)
    {
        if ($produce->isDirty('status') && $produce->status == 2)
        {
            $produce->products->map(function($pproduct) {
                $stockQuantity = $pproduct->storeQuantity();
                $pproduct->stock_history = [
                    'stock_quantity' => $stockQuantity,
                    'remain' => $stockQuantity - $pproduct->quantity,
                ];
                $pproduct->save();
            });
            $storeId = $produce->store_id;
            $attrsValue = $produce->attrs_value ? implode(',', $produce->attrs_value) : '';
            $this->updateQuantity($storeId, $produce->product_id, $produce->quantity, $attrsValue);
            $products = $produce->products;
            foreach ($products  as $product)
            {
                $attrsValue = $product->attrs_value ? implode(',', $product->attrs_value) : '';
                $this->updateQuantity($storeId, $product->product_id, -$produce->quantity * $product->quantity, $attrsValue);
            }
            $producedProduct = $produce->product;
            if ($producedProduct->isUseSeries()) {
                $pSeriRp = app(ProductSeriesRepository::class);
                $groupAttr = null;
                if (!empty($produce->attrs_value)) {
                    $attrsValue = ProductGroupAttributeMedia::where('attribute_value_ids', implode(',', $produce->attrs_value))
                        ->where('product_id', $producedProduct->id)
                        ->first();
                    $groupAttr = $attrsValue ? $attrsValue->id : null;
                }
                $newSeris = $pSeriRp->createSeries($producedProduct->id, $produce->quantity, [
                    'qr_code_status' => 2,
                    'store_id' => $produce->store_id,
                    'group_attribute_id' => $groupAttr,
                ]);
                $produce->p_seris = implode(',', $newSeris);
            }
        }
    }

    protected function updateQuantity($storeId, $productId, $quantity, $attrsValue = '')
    {
        $attributeQuantity = StoreProductGroupAttributeExtra::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('attribute_value_ids', $attrsValue)
            ->first();
        if ($attributeQuantity) {
            $attributeQuantity->update([
                'n_quantity' => $attributeQuantity->n_quantity + $quantity
            ]);
        } else {
            $storep = StoreProduct::where('store_id', $storeId)
                ->where('product_id', $productId)
                ->first();

            if ($storep) {
                $storep->update([
                    'n_quantity' => $storep->n_quantity + $quantity
                ]);
            }
        }
    }
    
}
