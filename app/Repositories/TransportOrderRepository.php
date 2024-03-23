<?php

namespace App\Repositories;

use App\Models\DOrder;
use App\Models\TransportOrder;
use App\Models\Order;

class TransportOrderRepository
{
    public function updateOrCreate(Order $order, $attributes)
    {
        $toClass = $order instanceof DOrder ? '\App\Models\DTransportOrder' : '\App\Models\TransportOrder';
        $data = [
            'customer_id' => $attributes['customer_id'],
            'unit' => $attributes['unit'],
            'total_package' => array_sum(array_column($attributes['products'], 'packages')),
            'total' => $attributes['total'],
            'transport_price' => $attributes['transport_price']
        ];
        return $toClass::updateOrCreate([
            'order_id' => $order->id,
        ], $data);
    }

    public function delete(Order $order)
    {
        $transportOrder = $order->transportOrder;
        if ($transportOrder) {
            app(\App\Repositories\TransportOrderProductRepository::class)->remove($transportOrder, []);
            $transportOrder->delete();
        }
        return;
    }
}
