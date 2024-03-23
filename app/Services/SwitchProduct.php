<?php
namespace App\Services;

use App\Events\OrderFinished;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;

class SwitchProduct
{
    protected $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function switchProductForCustomer($customerId, $productId, $quantity, $type, $payment, $transactionType, $note = '')
    {
        //create export order
        $fee = $transactionType == 1 ? (int) @$payment['amount'] : 0;
        $eOrder = $this->createOrder([
            'customer_id' => $customerId,
            'type' => Order::TYPE_EXPORT,
            'fee' => $fee,
            'total' => $fee,
            'note' => $note,
            'number_of_products' => $quantity,
            'payment' => json_encode($fee > 0 ? $payment : []),
        ]);
        $nQuantity = $wQuantity = 0;
        // warranty => new
        if ($type == 1) {
            $nQuantity = $quantity;
        } else {
            $wQuantity = $quantity;
        }
        $this->createOrderProduct($eOrder->id, $productId, $nQuantity, $wQuantity);

        //create import order
        $fee = $transactionType == 1 ? 0 : (int) @$payment['amount'];
        $iOrder = $this->createOrder([
            'customer_id' => $customerId,
            'type' => Order::TYPE_IMPORT,
            'fee' => $fee,
            'total' => $fee,
            'note' => $note,
            'number_of_products' => $quantity,
            'payment' => json_encode($fee > 0 ? $payment : []),
        ]);
        $nQuantity = $wQuantity = 0;
        // warranty => new
        if ($type == 1) {
            $wQuantity = $quantity;
        } else {
            $nQuantity = $quantity;
        }
        $this->createOrderProduct($iOrder->id, $productId, $nQuantity, $wQuantity);

        $this->createTransactionAndUpdateCustomerBacklog($eOrder, $payment);
        $this->createTransactionAndUpdateCustomerBacklog($iOrder, $payment);
    }

    protected function createOrder(array $data)
    {
        return Order::create(array_merge([
            'code' => $this->generator->generateOrderCode(),
            'status' => OrderStatus::SUCCESS,
            'subtotal' => 0,
        ], $data));
    }

    protected function createOrderProduct($ordeId, $productId, $quantity, $wQuantity)
    {
        OrderProduct::create([
            'order_id' => $ordeId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'w_quantity' => $wQuantity,
            'price' => 0,
            'total' => 0
        ]);
    }

    protected function createTransactionAndUpdateCustomerBacklog(Order $order, $payment)
    {
        // Create transaction
        app(\App\Repositories\OrderTransactionRepository::class)->create($order, array($payment));
        // Update customer backlog
        $debt = $order->total - (int) @$payment['amount'];
        if ($order->isImport() && $debt > 0) {
            $debt *= -1;
        }
        app(\App\Repositories\CustomerBacklogRepository::class)->update($order->customer_id, $debt);
    }
}