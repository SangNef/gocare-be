<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use App\Traits\SearchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audit extends Model
{
    use SoftDeletes, SearchScope;

    protected $table = 'audits';

    protected $authorized = false;

    protected $hidden = [];

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $searches = [
        'created_at'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'trans_id');
    }
}
