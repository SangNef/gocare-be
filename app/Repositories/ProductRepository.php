<?php

namespace App\Repositories;

use App\Models\AttributeValue;
use App\Models\Config;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroupAttributeMedia;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\StoreProductGroupAttributeExtra;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function getProducts(array $filter, array $orderBy, $page = 1, $perpage = 12)
    {
        $query = Product::whereNull('deleted_at');
        $this->applyFilter($query, $filter);
        $this->applyOrderBy($query, $orderBy);
        $result = $query->limit($perpage + 1)
            ->offset(($page - 1) * $perpage)
            ->get();

        $hasMore = $result->count() > $perpage;

        return [
            'items' => $result->splice(0, $perpage),
            'hasMore' => $hasMore
        ];
    }

    protected function applyFilter($query, array $filter = [])
    {
        if (@$filter['sku']) {
            $query->where('sku', 'like', '%' . $filter['sku'] . '%');
        }

        if (@$filter['name']) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        if (@$filter['serial']) {
            $query->whereHas('series', function ($q) use ($filter) {
                $q->where('seri_number', 'like', '%' . $filter['serial'] . '%');
            });
        }

        if (@$filter['category']) {
            $query->whereHas('categories', function ($q) use ($filter) {
                $q->where('slug', $filter['category']);
            });
            $category = ProductCategory::where('slug', $filter['category'])->first();
            if ($category && $category->products_order) {
                $query->orderByRaw("FIELD(id, $category->products_order) DESC");
            }
        }

        if (isset($filter['is_devices'])) {
            $query->whereHas('categories', function ($q) use ($filter) {
                $q->where('is_devices', (int)$filter['is_devices']);
            });
        }

        if (@$filter['location'] == 'home-page') {
            $exclude = Config::getHomepageExcludeCategories();
            if (!empty($exclude)) {
                $query->whereDoesntHave('categories', function ($q) use ($exclude) {
                    $q->whereIn('productcategories.id', $exclude);
                });
            }
            $include = ProductCategory::where('use_at_fe', 1)->pluck('id');
            if (!empty($include)) {
                $query->whereHas('categories', function ($q) use ($include) {
                    $q->whereIn('productcategories.id', $include);
                });
            }
        }

        if (@$filter['q']) {
            $query->where(function ($q) use ($filter) {
                $q->where('sku', 'like', '%' . $filter['q'] . '%')
                    ->orWhere('name', 'like', '%' . $filter['q'] . '%')
                    ->orWhereHas('series', function ($q) use ($filter) {
                        $q->where('seri_number', $filter['q']);
                    });
            });
        }
    }

    protected function applyOrderBy($query, array $orderBy = [])
    {
        if (@$orderBy['top_selling']) {
            $query->orderBy('sold', 'desc');
        }

        if (@$orderBy['top_rate']) {
            $query->orderBy('rate', 'desc');
        }
        return $query->inRandomOrder();
    }

    public function getProductsByStore($storeId, $filters)
    {
        $products = Product::whereExists(function ($q) use ($storeId, $filters) {
            $q->select(\DB::raw(1))
                ->from('store_products')
                ->whereRaw('store_products.product_id = products.id')
                ->where('store_id', $storeId);
            if (@$filters['product_search']) {
                switch ($filters['product_search']) {
                    case 'minimum':
                        $q->whereRaw('min > n_quantity and min > 0');
                        break;
                    case 'out_of_stock':
                        $q->where('n_quantity', '<=', 0);
                        break;
                    case 'in_stock':
                        $q->where('n_quantity', '>', 0);
                        break;
                }
            }
        })->where('status', 1);
        if (@$filters['sku']) {
            $products->where(function ($q) use ($filters) {
                $q->where('sku', 'like', '%' . $filters['sku'] . '%');
                $q->orWhere('name', 'like', '%' . $filters['sku'] . '%');
            });
        }

        if (@$filters['pc_ids']) {
            $products->join('products_product_category', 'products.id', '=', 'products_product_category.product_id')
                ->select(\DB::raw('products.*, products_product_category.product_category_id, products_product_category.position'))
                ->whereIn('products_product_category.product_category_id', $filters['pc_ids']);
        }

        return $products;
    }

    public function getProductsOutOfStock()
    {
        $products = Product::whereExists(function ($q) {
            $q->select(\DB::raw(1))
                ->from('store_products')
                ->whereRaw('store_products.product_id = products.id')->where('n_quantity', '<=', 0);
        })->where('status', 1)->get();
        return $products;
    }

    public function getProductQuantityByProducts(\Illuminate\Support\Collection $productCollection, $storeId)
    {
        $productIds = $productCollection
            ->pluck('id')
            ->toArray();
        $quantities = StoreProduct::whereIn('product_id', $productIds)
            ->where('store_id', $storeId)
            ->get()
            ->map(function ($item) {
                $item->quantity = $item->n_quantity + $item->w_quantity;
                return $item;
            })
            ->keyBy('product_id')
            ->toArray();
        $productCollection->map(function ($product) use ($quantities) {
            if (isset($quantities[$product->id])) {
                $product->n_quantity = $quantities[$product->id]['n_quantity'];
                $product->w_quantity = $quantities[$product->id]['w_quantity'];
                $product->quantity = $product->n_quantity + $product->w_quantity;
                $product->min = $quantities[$product->id]['min'];
            } else {
                $product->n_quantity = 0;
                $product->w_quantity = 0;
                $product->quantity = 0;
                $product->min = 0;
            }

            return $product;
        });

        return $productCollection;
    }

    public function createProductGroupAttribute(Product $product)
    {
        $attrs = $product->attrs
            ->map(function ($attr) {
                return $attr->getValues()->pluck('id');
            })
            ->toArray();
        $combinations = $this->combinations($attrs);
        $combinations = array_map(function ($combination) use ($product) {
            if (!is_array($combination)) $combination = [$combination];
            $ids = implode(',', $combination);
            $texts = AttributeValue::whereIn('id', $combination)
                ->get()
                ->implode('value', ',');
            $group = ProductGroupAttributeMedia::where('product_id', $product->id)
                ->where('attribute_value_ids', $ids)
                ->first();
            if ($group) {
                $group->update([
                    'attribute_value_texts' => $texts,
                ]);
            } else {
                ProductGroupAttributeMedia::create([
                    'product_id' => $product->id,
                    'attribute_value_ids' => $ids,
                    'attribute_value_texts' => $texts,
                    'media_ids' => '',
                ]);
            }
            $stores = StoreProduct::where('product_id', $product->id)->get();
            foreach ($stores as $store) {
                $group = StoreProductGroupAttributeExtra::where('product_id', $product->id)
                    ->where('store_id', $store->store_id)
                    ->where('attribute_value_ids', $ids)
                    ->first();
                if ($group) {
                    $group->update([
                        'attribute_value_texts' => $texts,
                    ]);
                } else {
                    StoreProductGroupAttributeExtra::create([
                        'product_id' => $product->id,
                        'store_id' => $store->store_id,
                        'attribute_value_ids' => $ids,
                        'attribute_value_texts' => $texts,
                        'n_quantity' => 0,
                        'w_quantity' => 0
                    ]);
                }
            }
            return implode(',', $combination);
        }, $combinations);

        ProductGroupAttributeMedia::where('product_id', $product->id)
            ->whereNotIn('attribute_value_ids', $combinations)
            ->delete();
        StoreProductGroupAttributeExtra::where('product_id', $product->id)
            ->whereNotIn('attribute_value_ids', $combinations)
            ->delete();
    }

    public function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->combinations($arrays, $i + 1);

        $result = array();

        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        $result = array_unique($result, SORT_REGULAR);

        return $result;
    }
}
