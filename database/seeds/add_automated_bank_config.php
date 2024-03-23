<?php

use Illuminate\Database\Seeder;

class add_automated_bank_config extends Seeder
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
                'key' => 'automated_bank',
                'value' => '',
                'desc' => ''
            ],
        ]);
    }
}
