<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerStatistic extends Model
{
	protected $table = 'customer_activity_statistics';
	
	protected $hidden = [
        
    ];

    public $timestamps = false;

	protected $guarded = [];

	protected $dates = ['created_at'];
}
