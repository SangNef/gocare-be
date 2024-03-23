<?php

namespace App\Repositories;

use App\Models\ProductsProductCategory;
use App\Models\Product;
use DB;

class ProductsProductCategoryRepository
{
    public function create($productId, $categoryIds = [])
    {
        $results = collect();
        foreach ($categoryIds as $cateId) {
            $latestPosition = ProductsProductCategory::query()
                ->where('product_category_id', $cateId)
                ->max('position');
            $record = ProductsProductCategory::firstOrNew([
                'product_id' => $productId,
                'product_category_id' => $cateId,
            ]);
            if (!$record->exists) {
                $record->position = $latestPosition + 1;
                $record->save();
            }
            
            $results->push($record);
        }
        return $results;
    }

    public function update($productId, $categoryIds = [])
    {
        $this->delete($productId, $categoryIds);
        $this->create($productId, $categoryIds);
    }

    public function delete($productId, $categoryIds = [])
    {
        $records = ProductsProductCategory::where('product_id', $productId)
            ->whereNotIn('product_category_id', $categoryIds)
            ->get();
        foreach ($records as $record) {
            // Update position for lower priority records
            ProductsProductCategory::query()
                ->where('product_category_id', $record->product_category_id)
                ->where('id', '>', $record->id)
                ->update([
                    'position' => DB::raw('position - 1')
                ]);
            $record->delete();
        }
    }
}
