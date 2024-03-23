<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucherhistory extends Model
{
    use SoftDeletes;
	
	protected $table = 'voucherhistories';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
