<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferOrder extends Model
{
    use SoftDeletes;
	
	protected $table = 'transferorders';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function creator()
	{
		return $this->belongsTo(Customer::class);
	}

	public function seris()
	{
		return $this->hasMany(ProductSeriHistory::class);
	}
}
