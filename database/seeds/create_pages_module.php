<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\Menu;
use Dwij\Laraadmin\Models\ModuleFields;

class create_pages_module extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::create([
            'name' => 'Pages',
            'label' => 'Pages',
            'name_db' => 'pages',
            'view_col' => 'title',
            'model' => 'Page',
            'controller' => 'PagesController',
            'fa_icon' => 'fa-cube',
            'is_gen' => 1
        ]);
        ModuleFields::insert([
            [
                'colname' => 'title',
                'label' => 'Tiêu đề',
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
                'colname' => 'slug',
                'label' => 'Slug',
                'module' => $module->id,
                'field_type' => 19,
                'unique' => '0',
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 256,
                'required' => 0,
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
                'maxlength' => 0,
                'required' => 0,
                'popup_vals' => '',
                'sort' => 0,
            ],
        ]);
        Menu::create([
            "name" => "Pages",
            "url" => "pages",
            "icon" => "fa-page",
            "type" => 'module',
            "parent" => 0,
            "hierarchy" => 0
        ]);
    }
}
