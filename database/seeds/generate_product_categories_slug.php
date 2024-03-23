<?php

use Illuminate\Database\Seeder;

class generate_product_categories_slug extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\ProductCategory::all()
            ->map(function ($category) {
                $category->update([
                    'slug' => str_slug($category->name, '-')
                ]);
            });
    }
}
