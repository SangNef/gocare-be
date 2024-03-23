<?php

namespace App\Observes;

use App\Events\OrderFinished;
use App\Models\Commission;
use App\Models\LockCommission;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\ProductSeri;
use App\Models\Transaction;
use App\Models\Voucherhistory;

class OrderObserve
{
    protected $orderStatus;

    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    public function created(Order $order)
    {
        $order->creator_id = auth()->check() ? auth()->user()->id : 1;
        if ($order->customer && $order->customer->store_id) {
            $order->store_id = $order->customer->store_id;
        }
        $order->access_key = uniqid();
        $order->save();
    }

    public function updated(Order $order)
    {
        if ($order->isDirty(['discount'])) {
            $receiver = $order->getCommissionReceiver();
            $changed = $order->discount - $order->getOriginal('discount');
            $lockCommission = LockCommission::where('customer_id', $receiver)
                ->where('order_id', $order->id)
                ->where('order_code', $order->code)
                ->first();
            if ($lockCommission) {
                $last = LockCommission::where('customer_id', $receiver)
                    ->orderBy('created_at', 'desc')
                    ->first();
                LockCommission::create([
                    'customer_id' => $receiver,
                    'amount' => -$changed,
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'note' => 'Đơn hàng #' . $order->code . ' cập nhập lại khuyến mại',
                    'balance' => $last ? $last->balance - $changed : 0,
                ]);
            }
        }

        if ($order->isDirty(['status']) && $this->orderStatus->isCancel($order->status)) {
            $orderProducts = $order->orderProducts;
            $ob = app(OrderProductObserve::class);
            foreach ($orderProducts as $orderProduct) {
                $ob->deleted($orderProduct);
            }

            $receiver = $order->getCommissionReceiver();
            $lockCommission = LockCommission::where('customer_id', $receiver)
                ->where('order_id', $order->id)
                ->where('order_code', $order->code)
                ->first();
            if ($lockCommission) {
                $last = LockCommission::where('customer_id', $receiver)
                    ->orderBy('created_at', 'desc')
                    ->first();
                LockCommission::create([
                    'customer_id' => $receiver,
                    'amount' => -($lockCommission->amount + $order->fee),
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'note' => 'Đơn hàng #' . $order->code . ' bị hoàn tiền',
                    'balance' => $last ? $last->balance - $lockCommission->amount : 0,
                ]);
            }
            $vh = Voucherhistory::where('order_id', $order->id)
                ->first();

            if ($vh) {
                $vh->customer_id = 0;
                $vh->used_at = null;
                $vh->order_id = null;
                $vh->save();
            }

            ProductSeri::where('order_id', $order->id)
                ->update([
                    'stock_status' => ProductSeri::STOCK_NOT_SOLD,
                    'status' => 0,
                    'order_id' => null,
                ]);

        }

        if ($order->isDirty(['status']) && $this->orderStatus->isSuccess($order->status) && $order->isPayingCommission()) {
            $receiver = $order->getCommissionReceiver();
            $type = 'hoa hồng';
            $commission = $order->getCommission();
            $codFee = $order->codOrder ? $order->codOrder->real_amount : 0;
            if ($order->self_cod_service) {
                $commission = -$order->subtotal;
                $codFee = 0;
                $type = 'tiền hàng';
            }
                $last = Commission::where('customer_id', $receiver)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                Commission::create([
                    'customer_id' => $receiver,
                    'order_id' => $order->id,
                    'trans_id' => 0,
                    'amount' => $commission - $codFee,
                    'balance' => ($last ? $last->balance : 0) + $commission - $codFee,
                    'note' => ucfirst($type) . ' từ đơn hàng #' . $order->code
                ]);

                $lockCommission = LockCommission::where('customer_id', $receiver)
                    ->where('order_id', $order->id)
                    ->where('order_code', $order->code)
                    ->get();
                if ($lockCommission->count() > 0) {
                    $last = LockCommission::where('customer_id', $receiver)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    LockCommission::create([
                        'customer_id' => $receiver,
                        'amount' => -$lockCommission->sum('amount'),
                        'order_id' => $order->id,
                        'order_code' => $order->code,
                        'note' => 'Chuyển '. $type .' cho đơn hàng #' . $order->code . ' sang số dư khả dụng',
                        'balance' => $last ? $last->balance - $lockCommission->sum('amount') : 0,
                    ]);
                }
            }
        if ($order->isDirty(['approve']) && $this->orderStatus->isApproved($order->approve)) {
            $order->transactions()->update([
                'status' => Transaction::STATUS_APPROVED
            ]);
        }
    }
}
