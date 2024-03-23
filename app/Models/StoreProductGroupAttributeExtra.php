<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreProductGroupAttributeExtra extends Model
{
	protected $table = 'store_product_attributes_value_extra';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function products()
	{
		return $this->belongsTo(Product::class);
	}

	public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
