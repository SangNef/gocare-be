<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyOrderProduct extends Model
{
    protected $table = 'warranty_order_products';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['created_at'];

    public function warrantyOrderProductSeries()
    {
        return $this->hasMany(WarrantyOrderProductSeri::class);
    }

    public function warrantyOrder()
    {
        return $this->belongsTo(WarrantyOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
