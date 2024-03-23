<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produce extends Model
{
    use SoftDeletes;
	
	protected $table = 'produces';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	protected $casts = [
		'attrs_value' => 'array'
	];

	public function products()
	{
		return $this->hasMany(ProduceProduct::class, 'produce_id', 'id');
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function group()
	{
		return $this->belongsTo(Group::class);
	}

	public function store()
	{
		return $this->belongsTo(Store::class);
	}

	public function isSuccess()
	{
		return $this->status == 2;
	}

	public function attrsName()
	{
		$name = '';
		if ($this->attrs_value) {
			$productId = $this->product_id;
			$attrsValue = implode(',', $this->attrs_value);
			$name = $attrGroup = StoreProductGroupAttributeExtra::where('product_id', $productId)
				->where('attribute_value_ids', $attrsValue)
				->first();
			
			return $name ? $name->attribute_value_texts : '';
		}
		
		return $name;
	}
}
