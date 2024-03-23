<?php

namespace App\Observes;

use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;

class AddressObserve
{
    public function saved(Address $address)
    {
        if ($address->default) {
            Address::where('customer_id', $address->customer_id)
                ->where('id', '<>', $address->id)
                ->update([
                    'default' => 0
                ]);
        }
    }
}
