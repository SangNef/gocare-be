<?php

namespace App\Scopes\ApiV2;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class ProductScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $cates = ProductCategory::getAvailableCatesForFE(['id'])
            ->toArray();
        $cates = !empty($cates) ? array_column($cates, 'id') : [0];

        $builder->whereRaw($model->getTable() . '.id in (select product_id from products_product_category where product_category_id in (' . implode(',', $cates) . '))')
            ->where($model->getTable() . '.status', 1);
    }
}
