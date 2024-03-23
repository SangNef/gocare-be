<?php

use Illuminate\Database\Seeder;

class add_name_field_accesstokens_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Accesstokens')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'name',
                    'label' => 'Tên',
                    'module' => $module->id,
                    'field_type' => 16,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 5,
                    'maxlength' => 250,
                    'required' => 1,
                    'popup_vals' => '',
                    'sort' => 0,
                ]);
        }
    }
}
