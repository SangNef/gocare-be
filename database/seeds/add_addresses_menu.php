<?php

use Illuminate\Database\Seeder;

class add_addresses_menu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Dwij\Laraadmin\Models\Menu::create([
            "name" => "addresses",
            "url" => "addresses",
            "icon" => "fa-map-marker",
            "type" => 'custom',
            "parent" => 0,
            "hierarchy" => 2
        ]);
    }
}
