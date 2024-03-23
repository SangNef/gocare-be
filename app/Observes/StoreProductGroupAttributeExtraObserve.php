<?php

namespace App\Observes;

use App\Models\Group;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\ProductQuantityAudit;

class StoreProductGroupAttributeExtraObserve
{
    public function created(StoreProductGroupAttributeExtra $object)
    {
        ProductQuantityAudit::create([
            'product_id' => $object->product_id,
            'store_id' => $object->store_id,
            'attrs_id' => $object->attribute_value_ids,
            'attrs_value' => $object->attribute_value_texts,
            'amount' => $object->n_quantity,
            'left' => $object->n_quantity,
            'creator_id' => auth()->check() ? auth()->user()->id : 0,
            'updated_type' => session('quantity_audit_type', 'Trực tiếp'),
            'order_id' => session('quantity_audit_order_id', '0'),
        ]);
    }

    public function updated(StoreProductGroupAttributeExtra $object)
    {
        if ($object->isDirty('n_quantity')) {
            $changed = $object->n_quantity - $object->getOriginal('n_quantity');
            ProductQuantityAudit::create([
                'product_id' => $object->product_id,
                'store_id' => $object->store_id,
                'attrs_id' => $object->attribute_value_ids,
                'attrs_value' => $object->attribute_value_texts,
                'amount' => $changed,
                'left' => $object->n_quantity,
                'creator_id' => auth()->check() ? auth()->user()->id : 0,
                'updated_type' => session('quantity_audit_type', 'Trực tiếp'),
                'order_id' => session('quantity_audit_order_id', '0'),
            ]);
        }
    }

    public function deleted(StoreProductGroupAttributeExtra $object)
    {
        ProductQuantityAudit::create([
            'product_id' => $object->product_id,
            'store_id' => $object->store_id,
            'attrs_id' => $object->attribute_value_ids,
            'attrs_value' => $object->attribute_value_texts,
            'amount' => -$object->n_quantity,
            'left' => 0,
            'creator_id' => auth()->check() ? auth()->user()->id : 0,
            'updated_type' => session('quantity_audit_type', 'Trực tiếp'),
            'order_id' => session('quantity_audit_order_id', '0'),
        ]);
    }
}
