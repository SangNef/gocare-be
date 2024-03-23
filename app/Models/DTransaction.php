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

class DTransaction extends Transaction
{
    protected $table = 'd_transactions';

    public function order()
    {
        return $this->belongsTo(\App\Models\DOrder::class);
    }
}
