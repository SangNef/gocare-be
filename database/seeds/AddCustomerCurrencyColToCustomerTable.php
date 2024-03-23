<?php

use Illuminate\Database\Seeder;

class AddCustomerCurrencyColToCustomerTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Customers')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
            ->insert([
                'colname' => 'customer_currency',
                'label' => 'Loại tiền tệ',
                'module' => $module->id,
                'field_type' => 13,
                'unique' => '0',
                'defaultvalue' => '0',
                'minlength' => 0,
                'maxlength' => 11,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
            ]);
        }
    }
}
