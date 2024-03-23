<?php

use Illuminate\Database\Seeder;

class create_ctv_dien_tu_group extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Group::updateOrCreate([
            'name' => 'ctv_dien_tu'
        ], [
            'name' => 'ctv_dien_tu',
            'display_name' => 'Cộng tác viên Điện tử'
        ]);
    }
}
