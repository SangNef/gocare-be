<?php

namespace App\Observes;

use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\StoreProduct;
use App\Repositories\StoreRepository;

class OrderProductObserve
{
    public function created(OrderProduct $op)
    {
        session([
            'quantity_audit_type' => 'Đơn hàng',
            'quantity_audit_order_id' => $op->order->id
        ]);
        $storeRp = new StoreRepository();
        if ($op->order->customer->ownedStore) {
            if ($op->order->isExport()) {
                $storeRp->moveProduct($op->product_id, $op->order->customer->ownedStore->id, $op->quantity, $op->w_quantity, $op->order->customer->store_id, $op->attr_ids);
            } else {
                $storeRp->moveProduct($op->product_id, $op->order->customer->store_id, $op->quantity, $op->w_quantity, $op->order->customer->ownedStore->id, $op->attr_ids);
            }
        } else {
            $storeId = $op->order->customer->store_id ?: 0;
            if ($op->order->isExport()) {
                $storeRp->updateQuantity($storeId, $op->product_id, -$op->quantity, -$op->w_quantity, $op->attr_ids);
            } else {
                $storeRp->updateQuantity($storeId, $op->product_id, $op->quantity, $op->w_quantity, $op->attr_ids);
            }
        }
    }

    public function updated(OrderProduct $op)
    {
        session([
            'quantity_audit_type' => 'Đơn hàng',
            'quantity_audit_order_id' => $op->order->id
        ]);
        if ($op->isDirty(['quantity', 'w_quantity'])) {
            $storeRp = new StoreRepository();
            $nQuantity = $op->isDirty(['quantity']) ? $op->quantity - $op->getOriginal('quantity') : 0;
            $wQuantity = $op->isDirty(['w_quantity']) ? $op->w_quantity - $op->getOriginal('w_quantity') : 0;
            if ($op->order->customer->ownedStore) {
                if ($op->order->isExport()) {
                    $storeRp->moveProduct($op->product_id, $op->order->customer->ownedStore->id, $nQuantity, $wQuantity, $op->order->customer->store_id, $op->attr_ids);
                } else {
                    $storeRp->moveProduct($op->product_id, $op->order->customer->store_id, $nQuantity, $wQuantity, $op->order->customer->ownedStore->id, $op->attr_ids);
                }
            } else {
                $storeId = $op->order->customer->store_id ?: 0;
                if ($op->order->isExport()) {
                    $storeRp->updateQuantity($storeId, $op->product_id, -$nQuantity, -$wQuantity, $op->attr_ids);
                } else {
                    $storeRp->updateQuantity($storeId, $op->product_id, $nQuantity, $wQuantity, $op->attr_ids);
                }
            }
        }
    }

    public function saving(OrderProduct $orderProduct)
    {
        $discountedPrice = $orderProduct->product->getLastestPriceForCustomer($orderProduct->order->customer_id);
        $discountedPrice *= ((100 - $orderProduct->discount_percent) / 100);
        $orderProduct->discounted_price = $discountedPrice;
    }

    public function deleted(OrderProduct $op)
    {
        session([
            'quantity_audit_type' => 'Đơn hàng',
            'quantity_audit_order_id' => $op->order->id
        ]);
        $storeRp = new StoreRepository();
        if ($op->order->customer->ownedStore) {
            if ($op->order->isExport()) {
                $storeRp->moveProduct($op->product_id, $op->order->customer->store_id, $op->quantity, $op->w_quantity, $op->order->customer->ownedStore->id, $op->attr_ids);
            } else {
                $storeRp->moveProduct($op->product_id, $op->order->customer->ownedStore->id, $op->quantity, $op->w_quantity, $op->order->customer->store_id, $op->attr_ids);
            }
        } else {
            $storeId = $op->order->customer->store_id ?: 0;
            if ($op->order->isExport()) {
                $storeRp->updateQuantity($storeId, $op->product_id, $op->quantity, $op->w_quantity, $op->attr_ids);
            } else {
                $storeRp->updateQuantity($storeId, $op->product_id, -$op->quantity, -$op->w_quantity, $op->attr_ids);
            }
        }
    }
}

