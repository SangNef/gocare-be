<?php

use Illuminate\Database\Seeder;

class cod_orders_customer_id extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\CODOrder::each(function ($codOrder) {
            $customer = $codOrder->customer();
            if ($customer) {
                $codOrder->customer_id = $customer->id;
                $codOrder->save();
            }
        });
    }
}
