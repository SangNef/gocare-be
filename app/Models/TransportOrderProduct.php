<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportOrderProduct extends Model
{
    protected $table = 'transport_order_products';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = [];

    protected $appends = [
        'product_name'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductNameAttribute()
    {
        return $this->product->name;
    }

    public function getCubicMeterAttribute()
    {
        return ($this->width * $this->length * $this->height);
    }
}
