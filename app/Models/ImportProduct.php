<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportProduct extends Model
{
    use SoftDeletes;
	
	protected $table = 'importproducts';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

    protected $casts = [
        'attrs_value' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function import()
    {
        return $this->belongsTo(Import::class);
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
