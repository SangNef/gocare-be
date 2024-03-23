<?php

use Illuminate\Database\Seeder;
use Dwij\Laraadmin\Models\Menu;

class add_activated_warranties_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Menu::create([
            "name" => "activatedwarranties",
            "url" => "activated-warranties",
            "icon" => "fa-cube",
            "type" => 'custom',
            "parent" => 0,
            "hierarchy" => 2
        ]);
    }
}
