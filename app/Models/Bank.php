<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Scopes\Traits\StoreOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    const CURRENCY_VND = 1;
    const CURRENCY_NDT = 2;
    use SoftDeletes, StoreOwner;

    protected $table = 'banks';

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function backlogs()
    {
        return $this->hasMany(\App\Models\BankBacklog::class);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function getBankByAccName($ids = [], $storeId = null)
    {
        $banks = static::where('printing', 1)
            ->where(function ($query) use ($ids, $storeId) {
                if (!empty($ids)) {
                    $query->whereIn('id', $ids);
                }
                if ($storeId) {
                    $query->where('store_id', $storeId);
                }
            })
            ->get();
        $result = [];

        foreach ($banks as $bank) {
            if (!isset($result[$bank->acc_name])) {
                $result[$bank->acc_name] = [];
            }

            $result[$bank->acc_name][] = $bank->name . ': ' . $bank->acc_id;
        }

        return $result;
    }

    public static function availableCurrency()
    {
        return [
            self::CURRENCY_VND => 'VNĐ',
            self::CURRENCY_NDT => 'NDT'
        ];
    }
}
