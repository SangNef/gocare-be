<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSeriHistory extends Model
{
    use SoftDeletes;
	
	protected $table = 'productserihistories';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function transferOrder()
	{
		return $this->belongsTo(TransferOrder::Class);
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function creator()
	{
		return $this->belongsTo(Customer::class);
	}

	public function productSeri()
	{
		return $this->belongsTo(ProductSeri::class);
	}
}
