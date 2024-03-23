<?php

namespace App\Observes;

use App\Models\Group;
use App\Models\StoreProduct;
use App\Models\ProductQuantityAudit;

class StoreProductObserve
{
    public function created(StoreProduct $object)
    {
        ProductQuantityAudit::create([
            'product_id' => $object->product_id,
            'store_id' => $object->store_id,
            'attrs_id' => 0,
            'attrs_value' => 'Không có thuộc tính',
            'amount' => (int) $object->n_quantity,
            'left' => (int) $object->n_quantity,
            'creator_id' => auth()->check() ? auth()->user()->id : 0,
            'updated_type' => session('quantity_audit_type', 'Trực tiếp'),
            'order_id' => session('quantity_audit_order_id', '0'),
        ]);
    }

    public function updated(StoreProduct $object)
    {
        if ($object->isDirty('n_quantity')) {
            $changed = $object->n_quantity - $object->getOriginal('n_quantity');
            ProductQuantityAudit::create([
                'product_id' => $object->product_id,
                'store_id' => $object->store_id,
                'attrs_id' => 0,
                'attrs_value' => 'Không có thuộc tính',
                'amount' => $changed,
                'left' => (int) $object->n_quantity,
                'creator_id' => auth()->check() ? auth()->user()->id : 0,
                'updated_type' => session('quantity_audit_type', 'Trực tiếp'),
                'order_id' => session('quantity_audit_order_id', '0'),
            ]);
        }
    }

    public function deleted(StoreProduct $object)
    {
        ProductQuantityAudit::create([
            'product_id' => $object->product_id,
            'store_id' => $object->store_id,
            'attrs_id' => 0,
            'attrs_value' => 'Không có thuộc tính',
            'amount' => -$object->n_quantity,
            'left' => 0,
            'creator_id' => auth()->check() ? auth()->user()->id : 0,
            'updated_type' => session('quantity_audit_type', 'Trực tiếp'),
            'order_id' => session('quantity_audit_order_id', '0'),
        ]);
    }
}
