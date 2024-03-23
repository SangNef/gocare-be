<?php

use Illuminate\Database\Seeder;

class add_created_at_column_to_produces_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Produces')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
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
                ]);
        }
    }
}
