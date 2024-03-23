<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use SoftDeletes;
	
	protected $table = 'imports';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

    public function products()
    {
        return $this->hasMany(ImportProduct::class, 'import_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
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
