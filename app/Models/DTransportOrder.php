<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DTransportOrder extends TransportOrder
{
    protected $table = 'd_transport_orders';

    public function order()
    {
        return $this->belongsTo(DOrder::class, 'order_id');
    }

    public function transportOrderProducts()
    {
        return $this->hasMany(DTransportOrderProduct::class, 'transport_order_id');
    }
}
