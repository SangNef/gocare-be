<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Menu;

class add_electric_order_from_admin_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Menu::create([
            "name" => "orderfromadmin",
            "url" => "orders?from=1",
            "icon" => "fa-cube",
            "type" => 'custom',
            "parent" => 0,
            "hierarchy" => 8
        ]);
    }
}
