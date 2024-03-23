<?php

use Illuminate\Database\Seeder;

class add_group_column_to_produces_module extends Seeder
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
                    'colname' => 'group_id',
                    'label' => 'Nhóm khách hàng',
                    'module' => $module->id,
                    'field_type' => 7,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 0,
                    'maxlength' => 0,
                    'required' => 0,
                    'popup_vals' => '@groups',
                    'sort' => 0,
                ]);
        }
    }
}
