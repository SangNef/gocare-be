<?php

use Illuminate\Database\Seeder;

class add_current_debt_to_orders_module extends Seeder
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
                [
                'colname' => 'current_debt',
                'label' => 'Tổng công nợ',
                'module' => $module->id,
                'field_type' => 10,
                'unique' => '0',
                'defaultvalue' => '0',
                'minlength' => 0,
                'maxlength' => 250,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
                ],
                [
                    'colname' => 'paid',
                    'label' => 'Đã thanh toán',
                    'module' => $module->id,
                    'field_type' => 13,
                    'unique' => '0',
                    'defaultvalue' => 0,
                    'minlength' => 0,
                    'maxlength' => 11,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ],
                [
                    'colname' => 'unpaid',
                    'label' => 'Chưa thanh toán',
                    'module' => $module->id,
                    'field_type' => 13,
                    'unique' => '0',
                    'defaultvalue' => 0,
                    'minlength' => 0,
                    'maxlength' => 11,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ],
            ]);
        }
    }
}
