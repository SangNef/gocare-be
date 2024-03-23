<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreProduct extends Model
{
	public $timestamps = false;

	protected $table = 'store_products';

	protected $hidden = [];

	protected $guarded = [];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
