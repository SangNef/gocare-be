<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\Menu;
use Dwij\Laraadmin\Models\ModuleFields;

class UpdateTransactionHistoryModule extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::where('name_db', 'transactionhistories')->first();
        if ($module) {
            $module->update(['is_gen' => 1]);
            Menu::create([
                "name" => $module->name,
                "url" => $module->name_db,
                "icon" => $module->fa_icon,
                "type" => 'module',
                "parent" => 0
            ]);
        }
        $module = Module::where('name_db', 'orders')->first();
        if ($module) {
            $transantionCol = ModuleFields::where('module', $module->id)->where('colname', 'transantion_id')->first();
            if ($transantionCol) {
                $transantionCol->delete();
            }
        }
    }
}
