<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerShippingSetup extends Model
{
    protected $table = 'customer_shipping_setups';

    protected $hidden = [];

    protected $guarded = [];

    protected $casts = [
        'connection' => 'array',
        'available_services' => 'array'
    ];

    public $timestamps = false;

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
