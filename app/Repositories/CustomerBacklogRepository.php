<?php

namespace App\Repositories;

use App\Models\Audit;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\StoreObserve;
use Illuminate\Support\Facades\DB;

class CustomerBacklogRepository
{
    protected $orderStatus;

    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    public function update($customerId, $in, $out = 0, $orderId = null, $transId = null)
    {
        if ($customerId) {
            $isObserver = StoreObserve::where('customer_id', $customerId)->exists();
            $changed = $in - $out;
            if ($orderId && $isObserver) {
                $changed = -$changed;
            }

            DB::table('customer_backlogs')
                ->where('customer_id', $customerId)
                ->update([
                    'money_in' => DB::raw('money_in + ' . $in),
                    'money_out' => DB::raw('money_out + ' . $out),
                    'debt' => DB::raw('debt + ' . $changed),
                ]);

            DB::table('customers')
                ->where('id', $customerId)
                ->update([
                    'debt_total' => DB::raw('debt_total + ' . $changed),
                ]);

            $current = DB::table('customers')
                ->where('id', $customerId)
                ->first();
            if ($orderId && $current && $isObserver) {
                Audit::create([
                    'customer_id' => $customerId,
                    'order_id' => $orderId,
                    'amount' => $changed,
                    'balance' => $current->debt_total
                ]);
            } else {
                Audit::create([
                    'customer_id' => $customerId,
                    'order_id' => (int) $orderId,
                    'trans_id' => (int) $transId,
                    'amount' => $changed,
                    'balance' => $current->debt_total
                ]);
            }
        }
    }

    public function processForCreateOrder(Order $order)
    {
        $order = $order->fresh();
        $debt = 0;
        if (!$order->isCODOrder() || $order->isCODOrderChargeDebt()) {
            $debt = $order->isFromAdmin()
                ? $order->total
                : $order->getCTVPriceForOrderFromFE();
        }

        if ($order->isImport() && $debt != 0) {
            $debt *= -1;
        }
        $order->amount_charged_to_debt = $debt;
        $order->save();
        return $this->update($order->customer_id, $debt, 0, $order->id);
    }

    public function processForUpdateOrder(Order $order)
    {
        $changed = 0;
        $status = $order->status;
        if (!$order->isCODOrder() || $order->isCODOrderChargeDebt()) {
            $changed = $order->fresh()->total - $order->getOriginal('total');
            if ($this->orderStatus->isCancel($status)) {
                $changed = $order->total;
                if ($order->isFromFE()) {
                    $shippingFee = $order->codOrder ? $order->codOrder->fee_amount : 0;
                    // if order is cancel => ctv pays double the fee.
                    $changed = $order->getCTVPriceForOrderFromFE() - $shippingFee;
                }
                $changed *= -1;
            }
            if ($order->isFromFE() && $this->orderStatus->isSuccess($status)) {
                // if order is success 
                // => minus debt when create order and commission.
                $changed = $order->getOrderFeProductsPrice();
                $changed *= -1;
            }
            if ($order->isImport() && $changed != 0) {
                $changed *= -1;
            }
        }
        $order->updateDebtForCurrentAndNextOrders($changed);
        $order->amount_charged_to_debt += $changed;
        $order->save();

        return $this->update($order->customer_id, $changed, 0, $order->id);
    }

    public function processForDeleteOrder(Order $order)
    {
        $debt = 0;
        if (!$this->orderStatus->isCancel($order->status) && (!$order->isCODOrder() || $order->isCODOrderChargeDebt())) {
            $debt = $order->total;
            if ($order->isFromFE()) {
                $debt = $order->getCTVPriceForOrderFromFE();
                if ($this->orderStatus->isSuccess($order->status)) {
                    $debt = $order->getOrderFeProductsPrice() - $order->getCTVPriceForOrderFromFE();
                    $debt *= -1;
                }
            }
            if ($order->isImport() && $debt != 0) {
                $debt *= -1;
            }
        }
        $debt *= -1;
        $order->updateDebtForCurrentAndNextOrders($debt);
        return $this->update($order->customer_id, $debt, 0, $order->id);
    }
}
