<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreShipping extends Model
{
	protected $table = 'store_shippings';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	public $timestamps = false;

	public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
