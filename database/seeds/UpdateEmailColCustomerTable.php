<?php

use Illuminate\Database\Seeder;

class UpdateEmailColCustomerTable extends Seeder
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
            ->where('colname', 'email')
            ->update([
                'required' => 0
            ]);
        }
    }
}
