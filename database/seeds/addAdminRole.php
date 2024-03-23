<?php

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Models\Group;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSeri;
use App\Models\Transaction;
use App\Models\Transactionhistory;
use App\Services\CODPartners\GHNService;
use FontLib\Table\Type\maxp;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;

class addAdminRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = \DB::table('roles')->where('name', 'ADMIN')->first();
        if (!$role) {
            $role = \DB::table('roles')->insertGetId([
                'name' => 'ADMIN',
                'display_name' => 'ADMIN',
                'description' => '',
                'parent' => 1,
                'dept' => 1
            ]);
        } else {
            $role = $role->id;
        }

        $rolemodules = collect(\DB::table('role_module')
            ->where('role_id', 1)
            ->get())
            ->map(function ($item) use($role) {
                $exist = \DB::table('role_module')->where('module_id', $item->module_id)->where('role_id', $role)->first();
                if (!$exist) {
                    \DB::table('role_module')->insertGetId([
                        'module_id' => $item->module_id,
                        'role_id' => $role,
                        'acc_view' => $item->acc_view,
                        'acc_create' => $item->acc_create,
                        'acc_edit' => $item->acc_edit,
                        'acc_delete' => $item->acc_delete,
                    ]);
                } else {
                    \DB::table('role_module')->where('id', $exist->id)->update([
                        'acc_view' => $item->acc_view,
                        'acc_create' => $item->acc_create,
                        'acc_edit' => $item->acc_edit,
                        'acc_delete' => $item->acc_delete,
                    ]);
                }
            });
        
        $rolemoduleFields = collect(\DB::table('role_module_fields')
            ->where('role_id', 1)
            ->get())
            ->map(function ($item) use($role) {
                $exist = \DB::table('role_module_fields')->where('field_id', $item->field_id)->where('role_id',$role)->first();
                if (!$exist) {
                    \DB::table('role_module_fields')->insertGetId([
                        'role_id' => $role,
                        'field_id' => $item->field_id,
                        'access' => $item->access,
                    ]);
                } else {
                    \DB::table('role_module_fields')->where('id', $exist->id)->update([
                        'access' => $item->access,
                    ]);
                }
            });

        $rolePermission = collect(\DB::table('permission_role')
            ->where('role_id', 1)
            ->get())
            ->map(function ($item) use($role) {
                $exist = \DB::table('permission_role')->where('role_id',$role)->first();
                if (!$exist) {
                    \DB::table('permission_role')->insertGetId([
                        'role_id' => $role,
                        'permission_id' => $item->permission_id,
                    ]);
                }
            });
    }
}
