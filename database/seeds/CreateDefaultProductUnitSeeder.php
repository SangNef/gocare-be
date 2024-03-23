<?php

use Illuminate\Database\Seeder;

class CreateDefaultProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Setting::create([
            'key' => 'product_unit',
            'value' => 'bộ, chiếc, cái, thùng'
        ]);
    }
}
