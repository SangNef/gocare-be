<?php

namespace App\Repositories;

use App\Models\AttributeValue;
use App\Models\DOrder;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductCombo;
use Illuminate\Support\Facades\DB;
use App\Models\DOrderProduct;

class OrderProductRepository
{
    public function createForOrder($products, Order $order)
    {
        $order = $order->fresh();
        $result = collect();
        //        $products = $this->prepareCombo($products, $order->customer->group_id);
        foreach ($products as $product) {
            $productId = $product['product_id'];
            $total = 0;
            if ($order->sub_type == Product::NEW_PRODUCT && isset($product['n_quantity'])) {
                $total = $product['n_quantity'] * $product['price'];
            }
            if ($order->sub_type == Product::WARRANTY_PRODUCT && isset($product['w_quantity'])) {
                $total = $product['w_quantity'] * $product['price'];
            }
            $discountPercent = @$product['discount_percent'] ?: 0;
            $total *= ((100 - $discountPercent) / 100);
            $attrIds = @$product['attr_ids'] ? implode(',', $product['attr_ids']) : '';
            $texts = '';
            if ($attrIds) {
                $texts = AttributeValue::whereIn('id', $product['attr_ids'])
                    ->get()
                    ->implode('value', ',');
            }
            $p = Product::find($productId);

            $opClass = $order instanceof DOrder ? '\App\Models\DOrderProduct' : '\App\Models\OrderProduct';
            $op = $opClass::updateOrCreate([
                'order_id' => $order->id,
                'product_id' => $productId,
                'attr_ids' => $attrIds,
                'combo_id' => @$product['combo_id'],
            ], [
                'quantity' => $order->sub_type == Product::NEW_PRODUCT && isset($product['n_quantity']) ? $product['n_quantity'] : 0,
                'w_quantity' => $order->sub_type == Product::WARRANTY_PRODUCT && isset($product['w_quantity']) ? $product['w_quantity'] : 0,
                'price' => $product['price'],
                'total' => $total,
                'note' => $product['note'],
                'attr_texts' => $texts,
                'combo_id' => @$product['combo_id'],
                'retail_price' => $p->getPriceForCustomerGroup('khách_hàng_Điện_tử', true),
                'discount_percent' => $discountPercent,
                'dimension' => [
                    'weight' => @$product['weight'] ?? $p->weight,
                    'length' => @$product['length'] ?? $p->length,
                    'width' => @$product['width'] ?? $p->width,
                    'height' => @$product['height'] ?? $p->height
                ]
            ]);
            $result->merge($op);
        }

        return $result;
    }

    protected function prepareCombo($products, $groupId)
    {
        $quantities = [];
        foreach ($products as $product) {
            $quantities[$product['product_id']] += $product['n_quantity'];
        }
        $results = [];
        foreach ($products as $key => $product) {
            $productId = $product['product_id'];
            $combos = DB::table('productcombos')
                ->select(DB::raw('productcombos.id, productcombos.related, product_combo_groups.discount'))
                ->join('product_combo_groups', 'productcombos.id', '=', 'product_combo_groups.combo_id')
                ->where('product_id', $productId)
                ->where('group_id', $groupId)
                ->where('product_combo_groups.discount', '>', 0)
                ->orderBy('discount', 'desc')
                ->get();
            $comboId = null;
            if (count($combos) > 0) {
                foreach ($combos as $combo) {
                    $relatedPros = json_decode($combo->related, true);
                    $valid = true;
                    foreach ($relatedPros as $pro) {
                        if (@$quantities[$pro[0]] < $pro[1]) {
                            $valid = false;
                            break;
                        }
                    }

                    if ($valid) {
                        $products[$key]['note'] .= 'Giảm ' . number_format($combo->discount) . 'đ khi mua cùng '
                            . Product::whereIn('id', array_map(function ($p) {
                                return $p[0];
                            }, $relatedPros))
                            ->get()
                            ->implode('name', ', ');
                        foreach ($relatedPros as $pro) {
                            $quantities[$pro[0]] -= $pro[1];
                        }
                        $products[$key]['combo_id'] = $combo->id;
                        break;
                    }
                }
            }
        }

        return $products;
    }

    public function updateForOrder($products, Order $order)
    {
        $this->removeExistProducts($order, array_keys($products));

        return $this->createForOrder($products, $order);
    }

    public function removeExistProducts(Order $order, $exclude, $disableEvent = false)
    {
        $order->orderProducts()
            ->whereNull('deleted_at')
            ->whereNotIn('product_id', $exclude)
            ->orderBy('id', 'desc')
            ->each(function ($item) use ($disableEvent) {
                if ($disableEvent) {
                    $event = OrderProduct::getEventDispatcher();
                    OrderProduct::unsetEventDispatcher();
                    $item->delete();
                    OrderProduct::setEventDispatcher($event);
                } else {
                    $item->delete();
                }
            });
    }
}
