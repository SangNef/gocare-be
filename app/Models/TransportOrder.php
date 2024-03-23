<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportOrder extends Model
{
    protected $table = 'transport_orders';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transportOrderProducts()
    {
        return $this->hasMany(TransportOrderProduct::class);
    }
}
