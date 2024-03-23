<?php

use Illuminate\Database\Seeder;

class add_from_col_to_request_warranties_module extends Seeder
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
                    'colname' => 'from',
                    'label' => 'Nơi tạo',
                    'module' => $module->id,
                    'field_type' => 13,
                    'unique' => '0',
                    'defaultvalue' => 2,
                    'minlength' => 0,
                    'maxlength' => 11,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ]);
        }
    }
}
