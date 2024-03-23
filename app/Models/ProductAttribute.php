<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
	protected $table = 'product_attributes';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function products()
	{
		return $this->belongsTo(Product::class);
	}

	public function attr()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function getValues()
    {
        return AttributeValue::whereIn('id', explode(',', $this->attribute_value_id))->get();
    }
}
