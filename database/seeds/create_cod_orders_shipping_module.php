<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\Menu;
use Dwij\Laraadmin\Models\ModuleFields;

class create_cod_orders_shipping_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::create([
            'name' => 'CODOrdersShipping',
            'label' => 'CODOrdersShipping',
            'name_db' => 'cod_orders_shipping',
            'view_col' => 'id',
            'model' => 'CODOrdersShipping',
            'controller' => 'CODOrdersShippingController',
            'fa_icon' => 'fa-cube',
            'is_gen' => 1
        ]);
        ModuleFields::insert([
            [
                'colname' => 'partner',
                'label' => 'Đối tác vận chuyển',
                'module' => $module->id,
                'field_type' => 19,
                'unique' => '0',
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 256,
                'required' => 1,
                'popup_vals' => '',
                'sort' => 0,
            ],
            [
                'colname' => 'type',
                'label' => 'Loại',
                'module' => $module->id,
                'field_type' => 13,
                'unique' => '0',
                'defaultvalue' => 1,
                'minlength' => 0,
                'maxlength' => 0,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
            ],
            [
                'colname' => 'status',
                'label' => 'Trạng thái',
                'module' => $module->id,
                'field_type' => 13,
                'unique' => '0',
                'defaultvalue' => 1,
                'minlength' => 0,
                'maxlength' => 0,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
            ],
            [
                'colname' => 'handle_type',
                'label' => 'Thao tác',
                'module' => $module->id,
                'field_type' => 13,
                'unique' => '0',
                'defaultvalue' => 1,
                'minlength' => 0,
                'maxlength' => 0,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
            ]
        ]);
        $parent = Menu::create([
            "name" => "CODOrdersShipping",
            "url" => "#",
            "icon" => "fa-cube",
            "type" => 'module',
            "parent" => 0,
            "hierarchy" => 0
        ]);
        Menu::create([
            "name" => "Export",
            "url" => "cod-orders-shipping?type=1",
            "icon" => "fa-cube",
            "type" => 'module',
            "parent" => $parent->id,
            "hierarchy" => 1
        ]);
        Menu::create([
            "name" => "Refund",
            "url" => "cod-orders-shipping?type=2",
            "icon" => "fa-cube",
            "type" => 'module',
            "parent" => $parent->id,
            "hierarchy" => 2
        ]);
    }
}
