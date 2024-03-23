<?php

use Illuminate\Database\Seeder;

class add_configs_for_home_section_5 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \Illuminate\Support\Facades\DB::table('configs')->insert([
            'key' => 'home_section_5',
            'value' => '{"link":[""]}',
            'desc' => 'Home section 5'
        ]);
    }
}
