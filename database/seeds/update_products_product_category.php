<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class update_products_product_category extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            Product::query()
                ->whereNull('deleted_at')
                ->get()
                ->each(function ($product) {
                    app(\App\Repositories\ProductsProductCategoryRepository::class)->update($product->id, json_decode($product->category_ids));
                });
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            $this->command->info($exception->getMessage());
        }
    }
}
