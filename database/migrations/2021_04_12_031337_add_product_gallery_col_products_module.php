<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Dwij\Laraadmin\Models\ModuleFields;
use Dwij\Laraadmin\Models\Module;

class AddProductGalleryColProductsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $cols = [
        [
            'colname' => 'product_gallery',
            'label' => 'Thư viện ảnh sản phẩm',
            'field_type' => 24,
            'defaultvalue' => ''
        ],
        [
            'colname' => 'has_series',
            'label' => 'Sử dụng series',
            'field_type' => 2,
            'defaultvalue' => 0
        ]
    ];

    public function up()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Products')->first();
        if ($module) {
            foreach ($this->cols as $col) {
                ModuleFields::createField((object) [
                    'colname' => $col['colname'],
                    'label' => $col['label'],
                    'module_id' => $module->id,
                    'field_type' => $col['field_type'],
                    'unique' => '0',
                    'defaultvalue' => $col['defaultvalue'],
                    'minlength' => 0,
                    'maxlength' => 0,
                    'required' => 0,
                    'popup_vals' => '',
                    'sort' => 0,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Products')->first();
        if ($module) {
            $colnames = array_pluck($this->cols, 'colname');
            $fields = ModuleFields::where('module', $module->id)->whereIn('colname', $colnames);
            if ($fields->count() == count($colnames)) {
                // Delete from Table module_field
                Schema::table($module->name_db, function ($table) use ($colnames) {
                    $table->dropColumn($colnames);
                });
                // Delete Context
                $fields->delete();
            }
        }
    }
}
