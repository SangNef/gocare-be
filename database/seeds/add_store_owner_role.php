<?php

use Illuminate\Database\Seeder;

class add_store_owner_role extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = DB::table('roles')->insertGetId([
            'name' => 'STORE_OWNER',
            'display_name' => 'STORE_OWNER',
            'description' => 'STORE_OWNER'
        ]);
        DB::table('modules')->orderBy('id')->each(function($module) use ($role) {
            DB::table('role_module')->insert([
                'role_id' => $role,
                'module_id' => $module->id,
                'acc_view' => 1,
                'acc_create' => 1,
                'acc_edit' => 0,
                'acc_delete' => 0
            ]);
            DB::table('module_fields')->where('module', $module->id)
                ->orderBy('id')
                ->each(function($field) use ($role) {
                    DB::table('role_module_fields')->insert([
                        'role_id' => $role,
                        'field_id' => $field->id,
                        'access' => 'write'
                    ]);
                });
        });
        if ($permission = DB::table('permissions')->where('name', 'ADMIN_PANEL')->first()) {
            DB::table('permission_role')->insert([
                'permission_id' => $permission->id,
                'role_id' => $role
            ]);
        }
    }
}
