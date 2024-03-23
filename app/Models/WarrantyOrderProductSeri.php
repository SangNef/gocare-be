<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyOrderProductSeri extends Model
{
	const STATUS_RECEIVED = 1;
	const STATUS_PROCESSING = 2;
	const STATUS_PROCESSED = 3;
	const STATUS_ADVANCE_WARRANTY = 4;
	const STATUS_RETURNED = 5;

	protected $table = 'warranty_order_product_series';

	protected $hidden = [];

	protected $guarded = [];

	protected $dates = ['created_at', 'return_at'];

	public function codOrder()
	{
		return $this->belongsTo(CODOrder::class, 'cod_order_id', 'id');
	}

	public function warrantyOrderProduct()
	{
		return $this->belongsTo(WarrantyOrderProduct::class);
	}

	public function productSeri()
	{
		return $this->belongsTo(ProductSeri::class);
	}

	public function customer()
	{
		return $this->warrantyOrderProduct->warrantyOrder->customer;
	}

	public static function getAvailableStatus()
	{
		return [
			static::STATUS_RECEIVED => trans('status.received'),
			static::STATUS_PROCESSING => trans('status.processing'),
			static::STATUS_PROCESSED => trans('status.processed'),
			static::STATUS_ADVANCE_WARRANTY => trans('status.advance_warranty'),
			static::STATUS_RETURNED => trans('status.returned')
		];
	}


	public static function getAvailableErrorTypes()
	{
		return [
			1 => trans('warranty_order.error_type_1'),
			2 => trans('warranty_order.error_type_2'),
			3 => trans('warranty_order.error_type_3'),
		];
	}

	public function isProcessed()
	{
		return $this->status === static::STATUS_PROCESSED;
	}
}
