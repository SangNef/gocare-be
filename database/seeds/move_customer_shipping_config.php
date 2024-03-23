<?php

use Illuminate\Database\Seeder;

class move_customer_shipping_config extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Customer::whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNotNull('vtp_account')
                    ->orWhereNotNull('ghtk_token')
                    ->orWhereNotNull('ghn_token');
            })
            ->get()
            ->map(function ($customer) {
                $data = [];
                if ($customer->vtp_account) {
                    $data[] = [
                        'customer_id' => $customer->id,
                        'partner' => 'vtp',
                        'connection' => json_encode($customer->vtp_account),
                        'inventory' => $customer->vtp_id,
                        'is_active' => true
                    ];
                }
                if ($customer->ghtk_token) {
                    $data[] = [
                        'customer_id' => $customer->id,
                        'partner' => 'ghtk',
                        'connection' => json_encode(['token' => $customer->ghtk_token]),
                        'inventory' => $customer->ghtk_id,
                        'is_active' => true
                    ];
                }
                if ($customer->ghn_token) {
                    $data[] = [
                        'customer_id' => $customer->id,
                        'partner' => 'ghn',
                        'connection' => json_encode(['token' => $customer->ghn_token]),
                        'inventory' => $customer->ghn_id,
                        'is_active' => true
                    ];
                }
                \App\Models\CustomerShippingSetup::insert($data);
            });
    }
}
