<?php
/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    CONST TYPE_ONE_CODE = 1;
    CONST TYPE_MULTI_CODE = 2;
	
	protected $table = 'vouchers';
	
	protected $hidden = [
        
    ];

	protected $guarded = [];

	protected $dates = ['deleted_at'];

	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	public function owner()
	{
		return $this->belongsTo(Customer::class);
	}

	public function realAmount($orderAmount, $orderProducts)
	{
		if ($orderAmount > $this->min_order_amount) {
		    $productIds = json_decode($this->product_ids, true);
		    if (count($productIds) > 0) {
                $orderProducts = $orderProducts->filter(function ($orderProduct) use ($productIds) {
                    return in_array($orderProduct->product_id, $productIds);
                });
            }

            $orderAmount = $orderProducts->sum('total');
			if ($this->percent > 0) {
				$amount = $orderAmount * $this->percent / 100;
				return $this->max > 0 ? min($amount, $this->max) : $amount;
			} else {
				return $this->amount;
			}
		}

		return 0;
	}

	public function getProducts()
    {
        return Product::whereIn('id', json_decode($this->product_ids, true))
            ->get();
    }

    public function histories()
    {
        return $this->hasMany(Voucherhistory::class);
    }

    public function isUsableMultile()
    {
        return $this->type == 2;
    }
}
