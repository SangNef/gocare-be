<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\Traits\StoreOwner;

use App\Traits\SearchScope;

class WarrantyOrder extends Model
{
    use SoftDeletes, SearchScope, StoreOwner;

    const TYPE_IMPORT = 1;
    const STATUS_RECEIVED = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_SUCCESS = 3;

    protected $authorized = false;

    protected $table = 'warrantyorders';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $searches = [
        'created_at'
    ];

    public function codOrder()
    {
        return $this->morphOne(CODOrder::class, 'order');
    }

    public function warrantyOrderProducts()
    {
        return $this->hasMany(WarrantyOrderProduct::class);
    }

    public function warrantyOrderProductSeries()
    {
        return $this->hasManyThrough(WarrantyOrderProductSeri::class, WarrantyOrderProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warranty_order_products');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getTypeHTMLFormatted()
    {
        return '<span class="label label-warning">' . trans('order.type_1') . '</span>';
    }

    public function getCurrencyHTMLFormatted()
    {
        return '<span class="label label-warning">VND</span>';
    }

    public function canCreateBillLadingAllProduct()
    {
        foreach ($this->warrantyOrderProductSeries as $wops) {
            if (!$wops->isProcessed()) {
                return false;
            }
        }
        return true;
    }
}
