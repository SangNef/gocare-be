<?php
namespace App\Observes;

use App\Models\Product;
use App\Models\ProductSeri;

class ProductObserve
{
    public function saving(Product $product)
    {
        $product->quantity = (int) ((int) $product->n_quantity + (int) $product->w_quantity);
    }
}
