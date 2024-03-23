<?php

use Illuminate\Database\Seeder;

class AddFeeBearerColOrderTable extends Seeder
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
                'colname' => 'fee_bearer',
                'label' => 'Người chịu phí',
                'module' => $module->id,
                'field_type' => 13,
                'unique' => '0',
                'defaultvalue' => 1,
                'minlength' => 0,
                'maxlength' => 11,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
            ]);
        }
    }
}
