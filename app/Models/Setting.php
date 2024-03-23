<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;
	
	protected $table = 'settings';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public static function getProductUnit()
	{
		$config = static::where('key', 'product_unit')->first();

		return $config ? array_filter(array_map('trim', explode(',', $config->value))) : [];
	}

	public static function getIgnoreBank()
	{
		$setting = static::where('key', 'ignore_bank')->first();

		return $setting ? explode(',', $setting->value) : [];
	}
}
