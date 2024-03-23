<?php

use Illuminate\Database\Seeder;

class add_store_id_for_employee extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Employees')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'store_id',
                    'label' => 'Store',
                    'module' => $module->id,
                    'field_type' => 7,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 0,
                    'maxlength' => 0,
                    'required' => 0,
                    'popup_vals' => '@stores',
                    'sort' => 0,
                ]);
        }
    }
}
