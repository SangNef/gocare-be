<?php

namespace App\Repositories;

use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProduct;

class WarrantyOrderProductRepository
{
    public function create(WarrantyOrder $wOrder, $products)
    {
        $wOrder = $wOrder->fresh();
        foreach ($products as $product) {
            $wop = WarrantyOrderProduct::updateOrCreate([
                'warranty_order_id' => $wOrder->id,
                'product_id' => (int) $product['product_id'],
            ], [
                'quantity' => (int) $product['quantity'],
                'note' => $product['note']
            ]);
            $results[] = $wop;
        }

        return collect($results);
    }

    public function update(WarrantyOrder $wOrder, $products)
    {
        $this->remove($wOrder, array_column($products, 'product_id'));
        return $this->create($wOrder, $products);
    }

    public function remove(WarrantyOrder $wOrder, $exlucde = [])
    {
        return $wOrder->warrantyOrderProducts()
            ->whereNotIn('product_id', $exlucde)
            ->orderBy('id', 'desc')
            ->each(function ($item) {
                $item->warrantyOrderProductSeries()->delete();
                $item->delete();
            });
    }
}
