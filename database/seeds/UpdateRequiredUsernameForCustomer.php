<?php

use Illuminate\Database\Seeder;

class UpdateRequiredUsernameForCustomer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = \Illuminate\Support\Facades\DB::table('modules')->where('name', 'Customers')->first();
        if ($module) {
            \Illuminate\Support\Facades\DB::table('module_fields')
            ->where('module', $module->id)
            ->where('colname', 'username')
            ->update([
                'required' => 1
            ]);
        }
    }
}
