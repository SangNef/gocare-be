<?php

use Illuminate\Database\Seeder;

class add_ghtk_default_webhook_token_configs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $token = \App\Models\AccessToken::create([
            'name' => 'ghtk_default_webhook_token',
            'status' => 1
        ]);
        \Illuminate\Support\Facades\DB::table('configs')->insert([
            'key' => 'ghtk_default_webhook_token',
            'value' => $token->api_key,
            'desc' => ""
        ]);
    }
}
