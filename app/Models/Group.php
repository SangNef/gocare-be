<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
	use SoftDeletes, StoreOwner;

	protected $table = 'groups';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public static function getWarrantyUnitsGroup()
	{
		$configs = Config::getWarrantyUnitsConfig();
		return static::whereIn('id', $configs)
			->whereNull('deleted_at')
			->pluck('display_name', 'id');
	}

	public static function getFEDiscountGroups()
	{
		$configs = Config::getFEDiscountGroupsConfig();
		return static::whereIn('id', $configs)
			->whereNull('deleted_at')
			->pluck('display_name', 'id');
	}

	public static function getFECustomerGroup()
	{
		return static::where('name', 'khách_hàng_Điện_tử')
			->whereNull('deleted_at')
			->first();
	}

	public static function getElectronicGroup()
	{
		return static::whereNull('deleted_at')
			->where('display_name', 'LIKE', '%Điện tử%')
			->pluck('id');
	}

	public function isAgentGroup()
    {
        return $this->name == 'dai_ly';
    }

	public function isGeneralAgentGroup()
	{
		return $this->name == 'tong_dai_ly';
	}
}
