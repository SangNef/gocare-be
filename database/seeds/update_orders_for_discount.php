<?php

use Illuminate\Database\Seeder;

class update_orders_for_discount extends Seeder
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
                    'colname' => 'discount',
                    'label' => 'Giảm giá',
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
