<?php

use Illuminate\Database\Seeder;

class add_ghtk_configs_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('configs')->insert([
            'key' => 'ghtk_configs',
            'value' => "",
            'desc' => ""
        ]);
    }
}
