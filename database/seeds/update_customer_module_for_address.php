<?php

use Illuminate\Database\Seeder;

class update_customer_module_for_address extends Seeder
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
            $data = [
                [
                    'colname' => 'province',
                    'label' => 'Tỉnh/Thành phố',
                    'module' => $module->id,
                    'field_type' => 19,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 0,
                    'maxlength' => 256,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ],
                [
                    'colname' => 'district',
                    'label' => 'Quận/Huyện',
                    'module' => $module->id,
                    'field_type' => 19,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 0,
                    'maxlength' => 256,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ],
                [
                    'colname' => 'ward',
                    'label' => 'Xã/Phường',
                    'module' => $module->id,
                    'field_type' => 19,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 0,
                    'maxlength' => 256,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ],
            ];
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert($data);
        }
    }
}
