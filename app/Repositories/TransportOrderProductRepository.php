<?php

namespace App\Repositories;

use App\Models\TransportOrder;
use App\Models\DTransportOrder;

class TransportOrderProductRepository
{
    public function create(TransportOrder $transportOrder, $products)
    {
        $toClass = $transportOrder instanceof DTransportOrder ? '\App\Models\DTransportOrderProduct' : '\App\Models\TransportOrderProduct';
        $results = collect();
        foreach ($products as $product) {
            $top = $toClass::updateOrCreate([
                'transport_order_id' => $transportOrder->id,
                'product_id' => $product['product_id']
            ], [
                'quantity' => $product['quantity'],
                'packages' => $product['packages'],
                'weight' => $product['weight'],
                'width' => $product['width'],
                'height' => $product['height'],
                'length' => $product['length'],
                'price' => $product['price'],
                'total' => $product['total']
            ]);
            $results->merge($top);
        }
        return $results;
    }

    public function update(TransportOrder $transportOrder, $products)
    {
        $this->remove($transportOrder, array_column($products, 'product_id'));
        return $this->create($transportOrder, $products);
    }

    public function remove(TransportOrder $transportOrder, $exclude)
    {
        return $transportOrder->transportOrderProducts()
            ->whereNotIn('product_id', $exclude)
            ->delete();
    }
}
