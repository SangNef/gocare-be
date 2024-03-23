<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupCateDiscount extends Model
{
    use SoftDeletes;
    CONST TYPE_WITH_PRODUCT = 1;
    CONST TYPE_WITHOUT_PRODUCT = 2;
	
	protected $table = 'groupcatediscounts';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function cate()
    {
        return $this->belongsTo(ProductCategory::class, 'cate_id');
    }
}
