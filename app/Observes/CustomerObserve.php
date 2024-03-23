<?php
namespace App\Observes;

use App\Models\Customer;
use App\Models\CustomerBacklog;

class CustomerObserve
{
    public function creating(Customer $customer)
    {
        if (auth()->check() && auth()->user()->store_id) {
            $customer->store_id = auth()->user()->store_id;
        }
    }

    public function created(Customer $customer)
    {
        CustomerBacklog::insert([
            [
                'customer_id' => $customer->id,
                'debt_type' => CustomerBacklog::BEGINING,
                'money_in' => 0,
                'money_out' => 0,
                'has' => 0,
                'debt' => 0,
            ],
            [
                'customer_id' => $customer->id,
                'debt_type' => CustomerBacklog::IN_MONTH,
                'money_in' => 0,
                'money_out' => 0,
                'has' => 0,
                'debt' => 0,
            ]
        ]);

        $customer->code = 'DL-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT);
        $customer->save();
    }

    public function updating(Customer $customer)
    {
        if ($customer->isDirty(['debt_in_advance'])) {
            $changed = $customer->debt_in_advance - $customer->getOriginal('debt_in_advance');
            $customer->debt_total += $changed;
        }
    }
}
