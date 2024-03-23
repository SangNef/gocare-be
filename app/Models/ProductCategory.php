<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
	use SoftDeletes;

	protected $table = 'productcategories';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function products()
	{
		return $this->belongsToMany(Product::class, 'products_product_category');
	}

	public function setSlugAttribute($value)
	{
		$this->attributes['slug'] = str_slug($value, '-');
	}

	public static function getAvailableCatesForFE($cols = [])
	{
		$cates = static::whereNull('deleted_at');
		if (!empty($cols)) {
			$cates = $cates->select($cols);
		}
		return $cates->where('use_at_fe', 1)->get();
	}
}
