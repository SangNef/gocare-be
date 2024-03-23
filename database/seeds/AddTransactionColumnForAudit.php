<?php

use Illuminate\Database\Seeder;

class AddTransactionColumnForAudit extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Audits')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'trans_id',
                    'label' => 'Giao dịch',
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
