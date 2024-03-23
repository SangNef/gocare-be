<?php

use Illuminate\Database\Seeder;

class SyncStoreProductQuantity extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Dwij\Laraadmin\Models\LAConfigs::create([
            'key' => 'dong_bo_so_luong_san_pham',
            'value' => '3,4'
        ]);

        $originalStore = 3;
        $toStore = 4;
        $products = \App\Models\StoreProduct::where('store_id', $originalStore)
            ->get();
        foreach ($products as $product) {
            \App\Models\StoreProduct::where('store_id', $toStore)
                ->where('product_id', $product->product_id)
                ->update([
                    'n_quantity' => $product->n_quantity,
                    'w_quantity' => $product->w_quantity,
                ]);
        }
    }
}
