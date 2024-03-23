<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineCustomer extends Model
{
	use SoftDeletes;

	protected $table = 'onlinecustomers';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function mainAddress()
	{
		return $this->belongsTo(Address::class, 'address_id');
	}

	public function store()
	{
		return $this->belongsTo(Store::class);
	}
}
