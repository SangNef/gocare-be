<?php

namespace App\Repositories;

use App\Models\AttributeValue;
use App\Models\Config;
use App\Models\Product;
use App\Models\ProductCombo;
use App\Models\ProductGroupAttributeMedia;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\StoreProductGroupAttributeExtra;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductComboRepository
{
    public function syncGroups(ProductCombo $combo, $groups = [])
    {
        if (!empty($groups)) {
            foreach ($groups as $group) {
                DB::table('product_combo_groups')
                    ->updateOrInsert([
                        'combo_id' => $combo->id,
                        'group_id' => $group['group_id']
                    ], [
                        'discount' => $group['discount']
                    ]);
            }
        }
    }
}
