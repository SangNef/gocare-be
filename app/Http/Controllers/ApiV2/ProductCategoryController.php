<?php

namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $cates = ProductCategory::getAvailableCatesForFE(['name', 'slug', 'is_devices'])->toArray();
        if ($customer) {
            $group = $customer->group;
            $validCates = json_decode($group->product_category_ids, true);
            $validCates = ProductCategory::whereIn('id', $validCates)->pluck('slug')->toArray();
            if (!empty($validCates)) {
                $cates = array_filter($cates, function ($cate) use($validCates) {
                    return in_array($cate['slug'], $validCates);
                });
            }
        }
        return response()->json($cates);
    }
}
