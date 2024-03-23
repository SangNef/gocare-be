<?php

use Illuminate\Database\Seeder;

class AddPaymentMethodBillLadingTypeOrderSeriesTypeFieldToOrdersModule extends Seeder
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
                    [
                        'colname' => 'payment_method',
                        'label' => 'Thu phí',
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
                        'colname' => 'cod_partner',
                        'label' => 'Đối tác vận chuyển',
                        'module' => $module->id,
                        'field_type' => 19,
                        'unique' => '0',
                        'defaultvalue' => NULL,
                        'minlength' => 0,
                        'maxlength' => 256,
                        'required' => 0,
                        'popup_vals' => '',
                        'sort' => 0,
                    ],
                    [
                        'colname' => 'order_series_type',
                        'label' => 'Chọn seri',
                        'module' => $module->id,
                        'field_type' => 13,
                        'unique' => '0',
                        'defaultvalue' => NULL,
                        'minlength' => 0,
                        'maxlength' => 11,
                        'required' => 0,
                        'popup_vals' => '',
                        'sort' => 0,
                    ]
                ]);
        } 
    }
}
