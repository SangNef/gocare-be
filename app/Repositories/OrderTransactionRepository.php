<?php

namespace App\Repositories;

use App\Models\DOrder;
use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;

class OrderTransactionRepository
{
    public function create(Order $order, $payments = [])
    {
        $tClass = $order instanceof DOrder ? '\App\Models\DTransaction' : '\App\Models\Transaction';
        $payments = array_filter($payments, function ($paid) {
            return @$paid['bank_id'];
        });

        $result = collect();
        $type = $order->isExport() ? Transaction::RECEIVED_TYPE : Transaction::TRANSFERED_TYPE;
        foreach ($payments as $key => $paid) {
            $paymentType = isset($paid['payment_type']) ? $paid['payment_type'] : $type;
            $data = [
                'user_id' => $order->creator_id,
                'desc' => '',
                'trans_id' => @$paid['code'],
                'order_id' => $order->id,
                'bank_id' => (int)@$paid['bank_id'],
                'type' => $paymentType,
                'received_amount' => $paymentType == Transaction::RECEIVED_TYPE ? $paid['amount'] : 0,
                'transfered_amount' => $paymentType == Transaction::TRANSFERED_TYPE ? $paid['amount'] : 0,
                'fee' => (int)$paid['fee'],
                'note' => $order->note,
                'status' => $order->approve == 1 ? 2 : 1,
                'customer_id' => $order->customer_id,
                'created_at' => @$paid['paid_date']
                    ? Carbon::createFromFormat('Y/m/d', $paid['paid_date'])->format('Y-m-d H:i:s')
                    : Carbon::now()->format('Y-m-d H:i:s'),
                'store_id' => $order->store_id,
            ];
            $transaction = $tClass::where('id', @$paid['transaction_id'])
                ->where('order_id', $order->id)
                ->first();
            if (isset($paid['transaction_id']) && $paid['transaction_id'] && $transaction) {
                $transaction->update($data);
            } else {
                $transaction = $tClass::create($data);
            }
            $result->push($transaction);
        }

        return $result;
    }

    public function update(Order $order, $payments = [])
    {
        $order = $order->fresh();
        $this->removeExistTransaction($order, array_unique(array_column($payments, 'transaction_id')));
        $this->create($order, $payments);
    }

    public function removeExistTransaction(Order $order, $exclude)
    {
        $old = $order->transactions
            ->pluck('id')
            ->toArray();
        $ids = array_diff($old, $exclude);
        if (!empty($ids)) {
            $transactions = Transaction::whereIn('id', $ids)->get();

            //trigger deleted event
            foreach ($transactions as $transaction) {
                $transaction->delete();
            }
        }
    }

    public function updateOrderPaymentByTransaction(Transaction $transaction, $amount)
    {
        $order = $transaction->order;
        if ($order) {
            $currentDebtChange = $amount;
            if ($order->isImport()) {
                $amount *= -1;
                if ($transaction->isTransfered()) {
                    $currentDebtChange = $amount * -1;
                }
            }

            $order->update([
                'paid' => \DB::raw('paid + ' . $amount),
                'unpaid' => \DB::raw('total - paid'),
                'amount_charged_to_debt' => \DB::raw('amount_charged_to_debt - ' . $amount)
            ]);
            $order->updateDebtForCurrentAndNextOrders(-$currentDebtChange);
        }
    }
}
