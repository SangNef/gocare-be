<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftOrder extends Model
{
    protected $table = 'draft_orders';

    protected $hidden = [];

    protected $guarded = [];

    public function orderProducts()
    {
        return $this->hasMany(DraftOrderProduct::class);
    }

    public function getOrderProductData()
    {
        return $this->orderProducts()
            ->join('products', 'draft_order_products.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->select(['products.name as product_name', 'products.sku', 'draft_order_products.product_id', 'draft_order_products.product_seri'])
            ->get();
    }

    public function deleteDraftOrder()
    {
        return $this->orderProducts()->delete() && $this->delete();
    }
}
