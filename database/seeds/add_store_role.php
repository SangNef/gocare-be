<?php

use Illuminate\Database\Seeder;

class add_store_role extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = DB::table('roles')->insertGetId([
            'name' => 'STORE',
            'display_name' => 'Nhân viên kho',
            'description' => 'Nhân viên kho'
        ]);
        if ($permission = DB::table('permissions')->where('name', 'ADMIN_PANEL')->first()) {
            DB::table('permission_role')->insert([
               'permission_id' => $permission->id,
                'role_id' => $role
            ]);
        }
        $module = DB::table('modules')->insertGetId([
            'name' => 'dorders',
            'label' => 'DraftOrders',
            'name_db' => 'dorders',
            'view_col' => 'code',
            'model' => 'DOrder',
            'controller' => 'OrdersController',
            'fa_icon' => 'fa-cube',
            'is_gen' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('role_module')->insert([
            'role_id' => $role,
            'module_id' => $module,
            'acc_view' => 1,
            'acc_create' => 1,
            'acc_edit' => 0,
            'acc_delete' => 0
        ]);
    }
}
