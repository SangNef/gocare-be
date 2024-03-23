<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Traits\SearchScope;
use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DOrder extends Order
{
    protected $table = 'd_orders';

    public function orderProducts()
    {
        return $this->hasMany(DOrderProduct::class, 'order_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(DTransaction::class, 'order_id', 'id');
    }

    public function transportOrder()
    {
        return $this->hasOne(DTransportOrder::class, 'order_id', 'id');
    }

    public function codOrder()
    {
        return $this->morphOne(CODOrder::class, 'order');
    }

    public function productSeries()
    {
        return $this->hasMany(ProductSeri::class, 'order_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'd_orderproducts', 'order_id', 'product_id')->wherePivot('deleted_at',  NULL);
    }

    public function isReadyCreateOrder()
    {
        return $this->payment_method != static::PAYMENT_METHOD_PAY_ONLINE || $this->paid > 0;
    }
}
