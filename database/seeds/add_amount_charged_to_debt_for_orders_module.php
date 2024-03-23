<?php

use Illuminate\Database\Seeder;

class add_amount_charged_to_debt_for_orders_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Orders')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'amount_charged_to_debt',
                    'label' => 'Tiền tính vào công nợ',
                    'module' => $module->id,
                    'field_type' => 10,
                    'unique' => '0',
                    'defaultvalue' => '0',
                    'minlength' => 0,
                    'maxlength' => 250,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ]);
        }
    }
}
