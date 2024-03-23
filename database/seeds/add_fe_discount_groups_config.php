<?php

use Illuminate\Database\Seeder;

class add_fe_discount_groups_config extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('configs')->insert([
            'key' => 'fe_discount_groups',
            'value' => json_encode([]),
            'desc' => 'FE - Hiển thị ô giảm giá'
        ]);
    }
}
