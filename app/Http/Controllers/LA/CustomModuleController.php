<?php


namespace App\Http\Controllers\LA;


use App\Http\Controllers\Controller;
use App\Role;
use Dwij\Laraadmin\Helpers\LAHelper;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Illuminate\Http\Request;
use DB;

class CustomModuleController extends Controller
{
    public function index()
    {
        $modules = Module::all();
        $modules = $modules->filter(function ($module) {
            return Module::hasAccess($module->id, 'view');
        });

        return View('la.modules.custom.index', [
            'modules' => $modules
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ftypes = ModuleFieldTypes::getFTypes2();
        $module = Module::find($id);
        $module = Module::get($module->name);

        $tables = LAHelper::getDBTables([]);
        $modules = LAHelper::getModuleNames([]);

        if (auth()->user()->store_id) {
            $availableRoles = Role::where('name', '<>', "STORE_OWNER")->get();
            $roles = [];
            foreach ($availableRoles as $role) {

                $roles += Module::getRoleAccess($id, $role->id);
            }

            $owner = Role::where('name', "STORE_OWNER")->first();
            $owner = Module::getRoleAccess($id, $owner->id);

        }

        return view('la.modules.custom.show', [
            'no_header' => true,
            'no_padding' => "no-padding",
            'ftypes' => $ftypes,
            'tables' => $tables,
            'modules' => $modules,
            'roles' => $roles,
            'owner' => $owner[0]
        ])->with('module', $module);
    }

    public function save_role_module_permissions(Request $request, $id)
    {
        $module = Module::find($id);
        $module = Module::get($module->name);

        $roles = Role::where('name', '<>', "STORE_OWNER")->get();
        $owner = Role::where('name', "STORE_OWNER")->first();
        $owner = Module::getRoleAccess($id, $owner->id)[0];
        $now = date("Y-m-d H:i:s");

        foreach($roles as $role) {
            /* =============== role_module_fields =============== */
            foreach ($module->fields as $field) {
                $field_name = $field['colname'].'_'.$role->id;
                $field_value = $request->$field_name > $owner->fields[$field['id']]['access']
                    ? $owner->fields[$field['id']]['access']
                    : $request->$field_name;
                if($field_value == 0) {
                    $access = 'invisible';
                } else if($field_value == 1) {
                    $access = 'readonly';
                } else if($field_value == 2) {
                    $access = 'write';
                }

                $query = DB::table('role_module_fields')->where('role_id', $role->id)->where('field_id', $field['id']);
                if($query->count() == 0) {
                    DB::insert('insert into role_module_fields (role_id, field_id, access, created_at, updated_at) values (?, ?, ?, ?, ?)', [$role->id, $field['id'], $access, $now, $now]);
                } else {
                    DB::table('role_module_fields')->where('role_id', $role->id)->where('field_id', $field['id'])->update(['access' => $access]);
                }
            }

            /* =============== role_module =============== */

            $module_name = 'module_'.$role->id;
            if(isset($request->$module_name)) {
                $view = 'module_view_'.$role->id;
                $create = 'module_create_'.$role->id;
                $edit = 'module_edit_'.$role->id;
                $delete = 'module_delete_'.$role->id;
                if(isset($request->$view)) {
                    $view = 1;
                } else {
                    $view = 0;
                }
                $view = $view > $owner->view ? $owner->view : $view;
                if(isset($request->$create)) {
                    $create = 1;
                } else {
                    $create = 0;
                }
                if(isset($request->$edit)) {
                    $edit = 1;
                } else {
                    $edit = 0;
                }
                if(isset($request->$delete)) {
                    $delete = 1;
                } else {
                    $delete = 0;
                }

                $view = $view > $owner->view ? $owner->view : $view;
                $create = $create > $owner->create ? $owner->create : $create;
                $edit = $edit > $owner->edit ? $owner->edit : $edit;
                $delete = $delete > $owner->delete ? $owner->delete : $delete;

                $query = DB::table('role_module')->where('role_id', $role->id)->where('module_id', $id);
                if($query->count() == 0) {
                    DB::insert('insert into role_module (role_id, module_id, acc_view, acc_create, acc_edit, acc_delete, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?)', [$role->id, $id, $view, $create, $edit, $delete, $now, $now]);
                } else {
                    DB::table('role_module')->where('role_id', $role->id)->where('module_id', $id)->update(['acc_view' => $view, 'acc_create' => $create, 'acc_edit' => $edit, 'acc_delete' => $delete]);
                }
            }
        }
        return redirect(config('laraadmin.adminRoute') . '/custom-modules/'.$id."#access");
    }
}