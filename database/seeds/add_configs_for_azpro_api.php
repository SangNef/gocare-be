<?php

use Illuminate\Database\Seeder;

class add_configs_for_azpro_api extends Seeder
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
                'key' => 'az_withdraw_noti',
                'value' => '',
                'desc' => 'Thông báo rút tiền'
            ],
            [
                'key' => 'az_deposit_noti',
                'value' => '',
                'desc' => 'Thông báo nạp tiền'
            ],
            [
                'key' => 'az_admin_withdraw',
                'value' => '',
                'desc' => 'AdminPanel rút tiền trực tiếp'
            ],
            [
                'key' => 'az_admin_deposit',
                'value' => '',
                'desc' => 'AdminPanel nạp tiền trực tiếp'
            ],
        ]);
    }
}
