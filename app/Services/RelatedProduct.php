<?php
namespace App\Services;

use App\Models\Group;
use App\Models\GroupProductDiscount;
use App\Models\Product;
use App\Models\ProductRelated;
use App\Models\Upload as UploadModel;

class RelatedProduct
{
    public function makeGroupProduct(Product $product, array $relatedProducts, $useChildProduct = false)
    {
        foreach ($relatedProducts as $productId => $quantity) {
            ProductRelated::create([
                'product_id' => $product->id,
                'r_id' => $productId,
                'quantity' => $quantity
            ]);
        }
        if ($useChildProduct) {
            foreach ($relatedProducts as $pID => $quantity) {
                $pro = Product::find($pID);
                $pro->n_quantity -= $product->n_quantity * $quantity;
                $pro->save();
            }
        }

    }
}