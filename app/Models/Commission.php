<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Traits\SearchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use SoftDeletes, SearchScope;
	
	protected $table = 'commissions';
	
	protected $hidden = [
        
    ];

	public $authorized = false;

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	protected $searches = [
	    'created_at'
    ];

    protected $dateFiltes = [
        'created_at'
    ];
}
