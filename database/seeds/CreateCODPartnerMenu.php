<?php

use Illuminate\Database\Seeder;

class CreateCODPartnerMenu extends Seeder
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
                [
                    "name" => "GHN",
                    "url" => "cod-orders/ghn",
                    "icon" => "fa-group",
                    "type" => 'custom',
                    "parent" => 0,
                    "hierarchy" => 1
                ],
                [
                    "name" => "ViettelPost",
                    "url" => "cod-orders/vtp",
                    "icon" => "fa-group",
                    "type" => 'custom',
                    "parent" => 0,
                    "hierarchy" => 2
                ]
            ]);
    }
}
