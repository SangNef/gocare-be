<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DOrderProduct extends OrderProduct
{
    protected $table = 'd_orderproducts';

    protected $casts = [
        'dimension' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(DOrder::class, 'order_id');
    }
}
