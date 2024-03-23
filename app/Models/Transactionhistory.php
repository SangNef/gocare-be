<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactionhistory extends Model
{
    use SoftDeletes, StoreOwner;
	
	protected $table = 'transactionhistories';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function transaction()
	{
		return $this->belongsTo(Transaction::class);
	}
}
