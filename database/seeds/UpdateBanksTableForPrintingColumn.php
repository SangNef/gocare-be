<?php

use Illuminate\Database\Seeder;

class UpdateBanksTableForPrintingColumn extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Banks')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'printing',
                    'label' => 'Hiển thị in ĐH',
                    'module' => $module->id,
                    'field_type' => 2,
                    'unique' => '0',
                    'defaultvalue' => '0',
                    'minlength' => 0,
                    'maxlength' => 0,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ]);
        } 
    }
}
