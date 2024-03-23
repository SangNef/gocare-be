<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\GroupCateDiscount;
use App\Models\GroupProductDiscount;
use App\Models\Import;
use App\Models\ImportProduct;
use App\Models\Order;
use App\Models\DOrder;
use App\Models\Store;
use App\Models\OrderProduct;
use App\Services\Discount;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function updateAmount(Order $order)
    {
        $order = $order->fresh();
        $subTotal = $order->orderProducts()->sum('total');
        $order->subtotal = $subTotal;
        $subTotal *= ((100 - $order->discount_percent) / 100);
        $grandTotal = $subTotal + $order->fee - $order->discount;
        if ($order->fee_bearer == Order::BEARER_FEE_SELLER) {
            $grandTotal  = $subTotal - $order->fee - $order->discount;
        }
        if ($order->voucher) {
            $grandTotal -= $order->voucher->realAmount($subTotal, $order->orderProducts);
        }
        $discountByCate = $this->getDiscountByCate($order);
        $order->discount_by_cate = $discountByCate;
        $grandTotal -= $order->discount_by_cate;
        $order->total = $grandTotal;
        $order->unpaid = $grandTotal;
        $order->number_of_products = $order->orderProducts()->sum(\DB::raw('quantity + w_quantity'));
        $order->cod_price_statement = $order->isCODOrder() ? $grandTotal : 0;
        $order->save();
        return $order;
    }

    public function updateCODPriceStatement(Order $order, $amount = null)
    {
        $order = $order->fresh();
        if ($order->isCODOrder()) {
            if ($order->isFromAdmin()) {
                $amount = $order->total;
            }
            if ($order->isFromFE() && is_null($amount)) {
                $amount = $order->products->reduce(function ($total, $product) {
                    return $total + intval($product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true));
                }, 0);
            }
            $order->cod_price_statement = $amount;
            $order->save();
        }
        return $order;
    }

    public function syncCopier(Order $order)
    {
        $customerBacklogRp = app(CustomerBacklogRepository::class);
        $orderDispatcher = Order::getEventDispatcher();
        $orderProductDispatcher = OrderProduct::getEventDispatcher();
        Order::unsetEventDispatcher();
        OrderProduct::unsetEventDispatcher();

        $oldCopier = Order::where('copier_id', $order->id)->first();
        $code = $oldCopier ? $oldCopier->code : app(\App\Services\Generator::class)->generateOrderCode();
        $id = $oldCopier ? $oldCopier->id : null;
        if ($oldCopier) {
            OrderProduct::where('order_id', $oldCopier->id)->forceDelete();
            $oldCopier->forceDelete();
            $customerBacklogRp->processForDeleteOrder($oldCopier);
        }
        $copier = $order->replicate();
        $copier->id = $id;
        $copier->copier_id = $order->id;
        $copier->code = $code;
        $copier->type = $order->type == 2 ? 1 : 2;
        $store = Store::find($copier->store_id);
        $customer = Customer::where('cloner_id', $store ? $store->owner->id : 0)
            ->where('store_id', $order->customer->ownedStore->id)
            ->first();
        $copier->customer_id = $customer ? $customer->id : 0;
        $copier->store_id = $order->customer->ownedStore->id;
        $copier->save();

        $orderProducts = $order->orderProducts;
        foreach ($orderProducts as $orderProduct) {
            $newOP = $orderProduct->replicate();
            $newOP->order_id = $copier->id;
            $newOP->save();
        }
        $this->updateAmount($copier);
        Order::setEventDispatcher($orderDispatcher);
        OrderProduct::setEventDispatcher($orderProductDispatcher);
        $copier = $copier->fresh();
        $customerBacklogRp->processForCreateOrder($copier);
        $copier = $copier->fresh();
        $copier->current_debt = $copier->customer->debt_total;
        $copier->save();

        return $copier;
    }

    public function cloneOrderForObserver($order)
    {
        $observers = $order->store->observes;
        $customerBacklogRp = app(CustomerBacklogRepository::class);
        foreach ($observers as $observer) {
            $orderDispatcher = Order::getEventDispatcher();
            $orderProductDispatcher = OrderProduct::getEventDispatcher();
            Order::unsetEventDispatcher();
            OrderProduct::unsetEventDispatcher();

            $copier = $order->replicate();
            $copier->id = null;
            $copier->code = $order->code . '_hh_' . $observer->customer_id;
            $copier->type = $order->type;
            $copier->customer_id = $observer->customer_id;
            $copier->store_id = $order->store_id;
            $copier->copier_id = $order->id;
            $copier->save();

            $orderProducts = $order->orderProducts;
            $discountSv = app(Discount::class);
            foreach ($orderProducts as $orderProduct) {
                $newOP = $orderProduct->replicate();
                $newOP->order_id = $copier->id;
                $discountPrice = $discountSv->getDiscountForCustomer($observer->customer_id, $orderProduct->product_id, true);
                if (!$discountPrice) {
                    throw new \Exception('Sản phẩm ' . $orderProduct->product->name . ' chưa được cài đặt giá khách hàng hoặc nhóm khách hàng theo dõi công nợ');
                }
                $newOP->price = $orderProduct->price - $discountPrice;
                $newOP->total = $newOP->price * ($orderProduct->quantity ?: $orderProduct->w_quantity);
                $newOP->save();
            }
            $this->updateAmount($copier);
            Order::setEventDispatcher($orderDispatcher);
            OrderProduct::setEventDispatcher($orderProductDispatcher);
            $copier = $copier->fresh();
            $customerBacklogRp->processForCreateOrder($copier);
            $copier = $copier->fresh();
            $copier->current_debt = $copier->customer->debt_total;
            $copier->save();
        }
    }

    public function createImportOrderForOnlineOrder($from, $to, $onlineOrder, $feOrderId)
    {
        $store = Store::find($from);
        $customer = $store->owner;
        $order = Order::create([
            'code' => app(\App\Services\Generator::class)->generateOrderCode(),
            'customer_id' => $customer->id,
            'type' => Order::TYPE_IMPORT,
            'fee' => 0,
            'subtotal' => 0,
            'total' => 0,
            'status' => 'Đang xử lý',
            'note' => 'Nhập hàng cho Cộng tác viên đặt hàng #' . $onlineOrder->code . '. Hoa hồng được cộng trực tiếp vào giá nhập lại',
            'number_of_products' => 0,
            'payment' => '',
            'creator_id' => 0,
            'discount' => 0,
            'sub_type' => 1,
            'fee_bearer' => 1,
            'discount_percent' => 0,
            'currency_type' => 1,
            'approve' => 0,
            'approver_id' => 0,
            'payment_method' => 1,
            'cod_partner' => 0,
            'order_series_type' => 0,
            'shipping_status' => 0,
            'current_debt' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'order_from' => 1,
            'address_id' => 0,
            'store_id' => $to,
            'copier_id' => 0,
            'cod_compare_status' => 0,
            'amount_charged_to_debt' => 0,
            'self_cod_service' => 0,
            'cross_store' => $feOrderId
        ]);
        foreach ($onlineOrder->orderProducts as $op) {
            $product = $op->product;
            $category = $product->categories->first();
            $commission = $category->commission;
            $retailPrice = $product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true);
            $price = $retailPrice * (1 - $commission / 100);
            $op = OrderProduct::updateOrCreate([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'attr_ids' => $op->attr_ids,
            ], [
                'quantity' => $op->quantity,
                'w_quantity' => 0,
                'price' => $price,
                'total' => $price * $op->quantity,
                'note' => 'Hoa hồng từ đơn hàng CTV ' . $commission . '%. Cộng trực tiếp vào giá nhập lại',
                'attr_texts' => $op->texts,
                'retail_price' => $retailPrice,
                'discount_percent' => 0
            ]);
        }
        $this->updateAmount($order);
        $order = $order->fresh();
        $customerBacklogRp = app(CustomerBacklogRepository::class);
        $customerBacklogRp->processForCreateOrder($order);
        $order->current_debt = $order->customer->debt_total;
        $order->save();
        $copier = $this->syncCopier($order);
        $copier->note = str_replace('Nhập hàng', 'Xuất hàng', $copier->note);
        $copier->save();
        $this->notify($copier);
    }

    public function notify(Order $order)
    {
        $cmd = 'php ' . base_path() . '/artisan notification:order ' . $order->id;
        if (!$order instanceof DOrder) {
            $cmd .= ' --notdraft';
        }
        shell_exec($cmd . ' > /dev/null 2>&1 &');
    }

    public function createImportOrder(ImportProduct $importProduct, $quantity)
    {
        $import = $importProduct->import;
        $product = $importProduct->product;
        $store = $import->store;
        $customer = $import->customer;
        $order = Order::create([
            'code' => app(\App\Services\Generator::class)->generateOrderCode(),
            'customer_id' => $customer->id,
            'type' => Order::TYPE_IMPORT,
            'fee' => 0,
            'subtotal' => 0,
            'total' => 0,
            'status' => 'Đang xử lý',
            'note' => 'Nhập theo đơn đặt hàng #' . $import->code,
            'number_of_products' => 1,
            'payment' => '',
            'creator_id' => 0,
            'discount' => 0,
            'sub_type' => 1,
            'fee_bearer' => 1,
            'discount_percent' => 0,
            'currency_type' => 1,
            'approve' => 0,
            'approver_id' => 0,
            'payment_method' => 1,
            'cod_partner' => 0,
            'order_series_type' => 0,
            'shipping_status' => 0,
            'current_debt' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'order_from' => 1,
            'address_id' => 0,
            'store_id' => $store->id,
            'copier_id' => 0,
            'cod_compare_status' => 0,
            'amount_charged_to_debt' => 0,
            'self_cod_service' => 0,
        ]);
        $attrIds = $importProduct->attrs_value ? implode(',', $importProduct->attrs_value) : '';
        $retailPrice = $product->getLastestPriceForCustomer($customer->id);
        OrderProduct::updateOrCreate([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'attr_ids' => $attrIds,
        ], [
            'quantity' => $quantity,
            'w_quantity' => 0,
            'price' => $retailPrice,
            'total' => $retailPrice * $quantity,
            'note' => 'Nhập hàng theo đơn đặt hàng',
            'attr_texts' => '',
            'retail_price' => $retailPrice,
            'discount_percent' => 0
        ]);
        $this->updateAmount($order);
        $order = $order->fresh();
        $customerBacklogRp = app(CustomerBacklogRepository::class);
        $customerBacklogRp->processForCreateOrder($order);
        $order->current_debt = $order->customer->debt_total;
        $order->save();

        return $order;
    }

    public function getDiscountByCate(Order $order)
    {
        $orderProducts = $order->orderProducts;
        $productIds = $orderProducts->pluck('product_id');
        $categories = DB::table('products_product_category')
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->pluck('product_category_id', 'product_id');
        $data = [];
        foreach ($orderProducts as $orderProduct) {
            $pCateId = $categories[$orderProduct->product_id];
            if (!isset($data[$pCateId])) {
                $data[$pCateId] = [
                    'quantity' => 0,
                    'amount' => 0,
                    'discount' => 0,
                ];
            }
            $data[$pCateId]['quantity'] += $orderProduct->quantity;
            $data[$pCateId]['amount'] += $orderProduct->total;
        }
        $group = $order->customer->group_id;

        $discountByCate = GroupCateDiscount::where('group_id', $group)
            ->where("type", 1)
            ->orderBy('quantity', 'asc')
            ->get();
        foreach ($discountByCate as $dc)
        {
            $cate = @$data[$dc['cate_id']];
            if (@$cate && $dc['quantity'] <= $cate['quantity'])
            {
                $cate['discount'] = $cate['amount'] * $dc['discount_1'] / 100 + $dc['discount'];
                $data[$dc['cate_id']] = $cate;
            }
        }

        return (int) collect($data)->sum('discount');
    }
}
