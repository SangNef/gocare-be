<?php

use Illuminate\Database\Seeder;

class create_ghtk_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('la_menus')
            ->insert([
                "name" => "GiaoHangTietKiem",
                "url" => "cod-orders/ghtk",
                "icon" => "fa-group",
                "type" => 'custom',
                "parent" => 0,
                "hierarchy" => 15
            ]);
    }
}
