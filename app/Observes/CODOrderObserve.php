<?php

namespace App\Observes;

use App\Models\CODOrder;
use App\Models\Order;
use App\Models\LockCommission;
use App\Repositories\CustomerBacklogRepository;
use App\Repositories\OrderRepository;;

class CODOrderObserve
{
    protected $customerBacklogRp;

    public function __construct(CustomerBacklogRepository $customerBacklogRp)
    {
        $this->customerBacklogRp = $customerBacklogRp;
    }

    public function updated(CODOrder $codOrder)
    {
        $order = $codOrder->order;
        if ($order && $order instanceof Order) {
            if ($codOrder->isDirty(['status'])) {
                $status = null;
                if ($codOrder->vtpIsSuccessfulDelivery() || $codOrder->ghtkIsSuccessfulDelivery()) {
                    $status = 2;
                }
                if ($codOrder->vtpIsSuccessfulReturn() || $codOrder->ghtkIsSuccessfulReturn()) {
                    $status = 3;
                }
                if ($status) {
                    $order->status = $status;
                    $this->customerBacklogRp->processForUpdateOrder($order);
                }
            }
            if ($order->isFromFE() && $codOrder->isDirty(['fee_amount'])) {
                $changed = $codOrder->fee_amount - $codOrder->getOriginal('fee_amount');

                $this->customerBacklogRp->update($order->customer_id, $changed, 0, $order->id);

                $order->updateDebtForCurrentAndNextOrders($changed);
                $order->amount_charged_to_debt += $changed;
                $order->fee += $changed;
                $order->save();
                app(OrderRepository::class)->updateAmount($order);
                $receiver = $order->getCommissionReceiver();
                $lockCommission = LockCommission::where('customer_id', $receiver)
                    ->where('order_id', $order->id)
                    ->where('order_code', $order->code)
                    ->first();
                if ($lockCommission) {
                    $last = LockCommission::where('customer_id', $receiver)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    LockCommission::create([
                        'customer_id' => $receiver,
                        'amount' => -$changed,
                        'order_id' => $order->id,
                        'order_code' => $order->code,
                        'note' => 'Đơn hàng #' . $order->code . ' cập nhập lại phí vận chuyển',
                        'balance' => $last ? $last->balance - $changed : 0,
                    ]);
                }
            }

            if ($order->isFromFE() && $codOrder->isDirty(['cod_amount'])) {
                $changed = $codOrder->cod_amount - $codOrder->getOriginal('cod_amount');

                $this->customerBacklogRp->update($order->customer_id, $changed, 0, $order->id);

                $order->updateDebtForCurrentAndNextOrders($changed);
                $order->amount_charged_to_debt += $changed;
                $order->discount -= $changed;
                $order->save();
                app(OrderRepository::class)->updateAmount($order);
            }
        }
    }
}
