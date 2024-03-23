<?php

use Illuminate\Database\Seeder;

class add_product_category_ids_to_groups_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Groups')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
                ->insert([
                    'colname' => 'product_category_ids',
                    'label' => 'Danh mục sản phẩm',
                    'module' => $module->id,
                    'field_type' => 15,
                    'unique' => '0',
                    'defaultvalue' => '[""]',
                    'minlength' => 0,
                    'maxlength' => 0,
                    'required' => 0,
                    'popup_vals' => '@productcategories',
                    'sort' => 0,
                ]);
        }
    }
}
