<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AzOrder extends Model
{
    const WITHDRAW_NOTI = 1;
    const DEPOSIT_NOTI = 2;
    const WITHDRAW_ADMIN = 3;
    const DEPOSIT_ADMIN = 4;

    protected $table = 'az_orders';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function availableTypes()
    {
        return [
            'az_withdraw_noti' => static::WITHDRAW_NOTI,
            'az_deposit_noti' => static::DEPOSIT_NOTI,
            'az_admin_withdraw' => static::WITHDRAW_ADMIN,
            'az_admin_deposit' => static::DEPOSIT_ADMIN
        ];
    }
}
