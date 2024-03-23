<?php

use Illuminate\Database\Seeder;

class UpdateGeneratedModule extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->whereIn('name_db', ['groups', 'customers'])->update(['is_gen' => 1]);
    }
}
