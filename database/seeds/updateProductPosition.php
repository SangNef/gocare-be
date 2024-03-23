<?php

use Illuminate\Database\Seeder;

class updateProductPosition extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = \App\Models\ProductsProductCategory::all()->groupBy('product_category_id');
        foreach ($categories as $category) {
            foreach ($category as $key => $product) {
                $product->position = ++$key;
                $product->save();
            }
        }
    }
}
