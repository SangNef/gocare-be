<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
	use SoftDeletes;

	protected $table = 'configs';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public static function getContactConfigs()
	{
		$keys = ['name', 'address', 'sales_phone', 'cs_phone', 'ts_phone'];
		return static::whereIn('key', $keys)->get();
	}

	public static function getAzConfigs()
	{
		return [
			'az_withdraw_noti',
			'az_deposit_noti',
			'az_admin_withdraw',
			'az_admin_deposit'
		];
	}

	public function getAzConfigProduct($key)
	{
		$product = $this->whereIn('key', self::getAzConfigs())
			->where('key', $key)
			->join('products', 'configs.value', '=', 'products.id')
			->select('products.name', 'products.id')
			->first();
		return $product;
	}

	public static function getHomeSection1()
	{
		$config = static::where('key', 'home_section_1')->first();
		return $config
			? json_decode($config->value)
			: [];
	}
	public static function getHomeSection2()
	{
		$config = static::where('key', 'home_section_2')->first();
		return $config
			? json_decode($config->value)
			: [];
	}
	public static function getHomeSection3()
	{
		$config = static::where('key', 'home_section_3')->first();
		return $config
			? json_decode($config->value)
			: [];
	}
	public static function getHomeSection4()
	{
		$config = static::where('key', 'home_section_4')->first();
		return $config
			? json_decode($config->value)
			: [];
	}
	public static function getHomeSection5()
	{
		$config = static::where('key', 'home_section_5')->first();
		return $config
			? json_decode($config->value)
			: [];
	}
	public static function getVTPConfigs()
	{
		$config = static::where('key', 'vtp_configs')->first();
		return $config
			? json_decode($config->value)
			: [];
	}

	public static function getGHNConfigs()
	{
		$config = static::where('key', 'ghn_configs')->first();
		return $config
			? json_decode($config->value)
			: [];
	}

	public static function getGHTKConfigs()
	{
		$config = static::where('key', 'ghtk_configs')->first();
		return $config
			? json_decode($config->value)
			: [];
	}

	public static function getGHNToken()
	{
		$config = static::getGHNConfigs();
		return !empty($config) && isset($config->token)
			? $config->token
			: null;
	}

	public static function getGHTKToken()
	{
		$config = static::getGHTKConfigs();
		return !empty($config) && isset($config->token)
			? $config->token
			: null;
	}

	public static function getFESliderConfig()
	{
		$config = static::where('key', 'fe_slider')->first();
		return $config ? json_decode($config->value) : [];
	}

	public static function getAZProSliderConfig()
	{
		$config = static::where('key', 'azpro_slider')->first();
		return $config ? json_decode($config->value) : [];
	}

	public static function getWarrantyUnitsConfig()
	{
		$config = static::where('key', 'warranty_units')->first();
		return $config ? json_decode($config->value) : [];
	}

	public static function getFEDiscountGroupsConfig()
	{
		$config = static::where('key', 'fe_discount_groups')->first();
		return $config ? json_decode($config->value) : [];
	}

	public static function getGHTKDefaultAccessToken()
	{
		$config = static::where('key', 'ghtk_default_webhook_token')->first();
		return $config ? $config->value : null;
	}

	public static function getHomepageExcludeCategories()
	{
		$config = static::where('key', 'fe_homepage_exclude_categories')->first();

		return $config ? explode(',', $config->value) : [];
	}
}
