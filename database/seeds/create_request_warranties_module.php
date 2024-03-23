<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\Menu;
use Dwij\Laraadmin\Models\ModuleFields;

class create_request_warranties_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::create([
            'name' => 'RequestWarranties',
            'label' => 'RequestWarranties',
            'name_db' => 'request_warranties',
            'view_col' => 'id',
            'model' => 'RequestWarranty',
            'controller' => 'RequestWarrantiesController',
            'fa_icon' => 'fa-cube',
            'is_gen' => 1
        ]);
        ModuleFields::insert([
            [
                'colname' => 'seri_number',
                'label' => 'Mã Seri',
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
                'colname' => 'name',
                'label' => 'Họ tên',
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
                'colname' => 'phone',
                'label' => 'SDT',
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
                'colname' => 'address',
                'label' => 'Địa chỉ',
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
                'colname' => 'province',
                'label' => 'Tỉnh/Thành phố',
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
                'colname' => 'district',
                'label' => 'Quận/Huyện',
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
                'colname' => 'ward',
                'label' => 'Phường/Xã',
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
                'colname' => 'product_name',
                'label' => 'Tên sản phẩm',
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
                'colname' => 'content',
                'label' => 'Nội dung',
                'module' => $module->id,
                'field_type' => 21,
                'unique' => '0',
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 1000,
                'required' => 1,
                'popup_vals' => '',
                'sort' => 0,
            ],
        ]);
        Menu::create([
            "name" => "RequestWarranties",
            "url" => "request-warranties",
            "icon" => "fa-cube",
            "type" => 'module',
            "parent" => 0,
            "hierarchy" => 0
        ]);
    }
}
