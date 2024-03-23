<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SearchScope;

class Transaction extends Model
{
    use SoftDeletes, SearchScope, StoreOwner;

    protected $table = 'transactions';

    protected $authorized = false;

    const RECEIVED_TYPE = 1;
    const TRANSFERED_TYPE = 2;
    const STATUS_NEW = 1;
    const STATUS_APPROVED = 2;

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $searches = [
        'created_at',
    ];

    public static function getAvailableTypes()
    {
        return [
            self::RECEIVED_TYPE => 'Nhận',
            self::TRANSFERED_TYPE => 'Chuyển',
        ];
    }

    public function bank()
    {
        return $this->belongsTo(\App\Models\Bank::class);
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    public function isReceived()
    {
        return $this->type == static::RECEIVED_TYPE;
    }

    public function isTransfered()
    {
        return $this->type == static::TRANSFERED_TYPE;
    }

    public function isApprovable()
    {
        return $this->status == static::STATUS_NEW;
    }
}
