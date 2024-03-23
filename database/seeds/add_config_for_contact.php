<?php

use Illuminate\Database\Seeder;

class add_config_for_contact extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('configs')->insert([
            [
                'key' => 'name',
                'value' => '',
                'desc' => ''
            ],
            [
                'key' => 'address',
                'value' => '',
                'desc' => ''
            ],
            [
                'key' => 'sales_phone',
                'value' => '',
                'desc' => ''
            ],
            [
               'key' => 'cs_phone',
                'value' => '',
                'desc' => ''
            ],
            [
                'key' => 'ts_phone',
                'value' => '',
                'desc' => ''
            ]
        ]);
    }
}
