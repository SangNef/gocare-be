<?php

use Illuminate\Database\Seeder;

class CODPartnerConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('configs')->insert([
            [
                'key' => 'ghn_configs',
                'value' => json_encode([
                    'token' => ''
                ]),
                'desc' => ''
            ],
            [
                'key' => 'vtp_configs',
                'value' => json_encode([
                    'username' => '',
                    'password' => ''
                ]),
                'desc' => ''
            ]
        ]); 
    }
}
