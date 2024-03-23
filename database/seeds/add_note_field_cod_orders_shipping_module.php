<?php

use Illuminate\Database\Seeder;

class add_note_field_cod_orders_shipping_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'CODOrdersShipping')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'note',
                    'label' => 'Ghi chú',
                    'module' => $module->id,
                    'field_type' => 21,
                    'unique' => '0',
                    'defaultvalue' => '',
                    'minlength' => 0,
                    'maxlength' => 0,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ]);
        }
    }
}
