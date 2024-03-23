<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Traits\SearchScope;
use App\Services\Discount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRelated extends Model
{
    protected $table = 'product_related';
    protected $hidden = [];
    protected $guarded = [];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function child()
    {
        return $this->belongsTo(Product::class, 'r_id');
    }
}
