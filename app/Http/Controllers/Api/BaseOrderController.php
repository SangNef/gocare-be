<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AzOrder;
use App\Models\Order;
use App\Models\Product;
use App\Services\Generator;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderTransactionRepository;
use Carbon\Carbon;

class BaseOrderController extends Controller
{
    protected $order;
    protected $orderProductRp;
    protected $orderRp;
    protected $OrderTransaction;

    public function __construct(
        Order $order,
        OrderProductRepository $orderProductRp,
        OrderRepository $orderRp,
        OrderTransactionRepository $OrderTransaction
    ) {
        $this->order = $order;
        $this->orderProductRp = $orderProductRp;
        $this->orderRp = $orderRp;
        $this->OrderTransaction = $OrderTransaction;
    }

    protected function prepareData($attributes, $customerId)
    {
        return [
            'store_id' => 3,
            'code' => app(Generator::class)->generateOrderCode(),
            'total' => $attributes->amount,
            'subtotal' => $attributes->amount,
            'note' => $attributes->desc ?: '',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'customer_id' => $customerId,
            'type' => $attributes->type,
            'sub_type' => Product::NEW_PRODUCT,
            'discount_percent' => $attributes->discount_percent ?: 0,
            'fee' => (int) $attributes->fee,
            'fee_bearer' => AzOrder::availableTypes()[$attributes->service] == AzOrder::WITHDRAW_NOTI ? Order::BEARER_FEE_SELLER : Order::BEARER_FEE_BUYER
        ];
    }


    protected function prepareProduct($product, $amount)
    {
        $result = [];
        if ($product) {
            $result[$product->id] = [
                'product_id' => $product->id,
                'price' => $amount,
                'n_quantity' => 1,
                'note' => ''
            ];
        }
        return $result;
    }
}
