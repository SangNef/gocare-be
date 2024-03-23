<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProduceProduct extends Model
{
    use SoftDeletes;
	
	protected $table = 'produceproducts';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	protected $casts = [
		'attrs_value' => 'array',
		'stock_history' => 'array'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function produce()
	{
		return $this->belongsTo(Produce::class);
	}

	public function storeQuantity()
	{
		$storeId = $this->produce->store_id;
		
		if ($this->attrs_value) {
			$attrsValue = implode(',', $this->attrs_value);
			$storeQuantity = StoreProductGroupAttributeExtra::where('store_id', $storeId)
				->where('product_id', $this->product_id)
				->where('attribute_value_ids', $attrsValue)
				->sum('n_quantity');
		} else {
			$storeQuantity = StoreProduct::where('store_id', $storeId)
				->where('product_id', $this->product_id)
				->sum('n_quantity');
		}

		return (int) $storeQuantity;
	}

	public function isAbleToProduce()
	{
		return $this->storeQuantity() >= $this->quantity * $this->produce->quantity;
	}

	public function attrs()
	{
		$attrsValue = @$this->attrs_value;
		$product = $this->product;

		$attrs = $product->attrs->map(function ($productAttribute, $index) use($attrsValue) {
			$selected = @$attrsValue[$index];
			return [
				'id' => $productAttribute->attribute_id,
				'text' => $productAttribute->attr->name,
				'values' => $productAttribute->getValues()->map(function ($value) use($selected) {
					return [
						'id' => $value->id,
						'value' => $value->value,
						'selected' => $selected == $value->id
					];
				}),
			];
		});

		return $attrs;
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
