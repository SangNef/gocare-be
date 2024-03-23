<?php

use Illuminate\Database\Seeder;

class create_statistics_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('la_menus')
            ->insert([
                "name" => "Statistics",
                "url" => "statistics",
                "icon" => "fa-cube",
                "type" => 'custom',
                "parent" => 0,
                "hierarchy" => 7,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ]);
    }
}
