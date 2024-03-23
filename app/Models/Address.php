<?php

/**
 * Model genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $table = 'addresses';

    protected $hidden = [];

    protected $guarded = [];

    protected $appends = [
        'full_address'
    ];

    protected $dates = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFullAddressAttribute()
    {
        $province = Province::find($this->province)->name;
        $district = District::find($this->district)->name;
        $ward = Ward::find($this->ward)->name;

        return implode(' - ', [
            $this->address,
            $ward,
            $district,
            $province
        ]);
    }

    public function getProvinceName()
    {
        return Province::find($this->province)->name;
    }

    public function getDistrictName()
    {
        return District::find($this->district)->name;
    }

    public function getWardName()
    {
        return Ward::find($this->ward)->name;
    }
}
