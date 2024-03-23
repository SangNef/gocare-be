<?php

use Illuminate\Database\Seeder;

class add_warranty_unit_config extends Seeder
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
                'key' => 'warranty_units',
                'value' => json_encode([]),
                'desc' => 'Đơn vị bảo hành'
            ],
        ]);
    }
}
