<?php

use Illuminate\Database\Seeder;

class UpdateCustomerBacklogs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $azCustomer = \App\Models\Customer::where('username', 'khoazpro')->first();
        $dtclCustomer = \App\Models\Customer::where('username', 'khodtcl')->first();
        $azCustomerClone = $azCustomer->replicate();
        $azCustomerClone->id = null;
        $azCustomerClone->name = 'DTCL_' . $azCustomer->name;
        $azCustomerClone->email = 'DTCL_' . $azCustomer->email;
        $azCustomerClone->phone = 'DTCL_' . $azCustomer->phone;
        $azCustomerClone->username = 'DTCL_' . $azCustomer->username;
        $azCustomerClone->store_id = $dtclCustomer->ownedStore->id;
        $azCustomerClone->cloner_id = $azCustomer->id;
        $azCustomerClone->debt_total = -$dtclCustomer->debt_total;
        $azCustomerClone->save();

    }
}
