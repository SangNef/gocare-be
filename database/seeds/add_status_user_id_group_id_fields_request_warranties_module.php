<?php

use Illuminate\Database\Seeder;

class add_status_user_id_group_id_fields_request_warranties_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'RequestWarranties')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    [
                        'colname' => 'status',
                        'label' => 'Trạng thái',
                        'module' => $module->id,
                        'field_type' => 13,
                        'unique' => '0',
                        'defaultvalue' => 1,
                        'minlength' => 0,
                        'maxlength' => 11,
                        'required' => 0,
                        'popup_vals' => '',
                        'sort' => 0,
                    ],
                    [
                        'colname' => 'user_id',
                        'label' => 'Người xử lý',
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
                        'colname' => 'group_id',
                        'label' => 'Đơn vị xử lý',
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
                        'colname' => 'created_at',
                        'label' => 'Ngày tạo',
                        'module' => $module->id,
                        'field_type' => 19,
                        'unique' => '0',
                        'defaultvalue' => '',
                        'minlength' => 0,
                        'maxlength' => 250,
                        'required' => 0,
                        'popup_vals' => '',
                        'sort' => 0,
                    ]
                ]);
        }
    }
}
