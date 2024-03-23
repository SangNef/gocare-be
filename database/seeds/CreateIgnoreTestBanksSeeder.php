<?php

use Illuminate\Database\Seeder;

class CreateIgnoreTestBanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Setting::create([
            'key' => 'ignore_bank',
            'value' => ''
        ]);
    }
}
