<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Menu;

class add_order_from_fe_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Menu::create([
            "name" => "orderfromfe",
            "url" => "orders?from=2",
            "icon" => "fa-cube",
            "type" => 'custom',
            "parent" => 0,
            "hierarchy" => 8
        ]);
    }
}
