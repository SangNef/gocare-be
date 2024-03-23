<?php

namespace App\Observes;

use App\Models\Commission;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Transaction;
use App\Repositories\CustomerBacklogRepository;
use App\Repositories\BankBacklogRepository;
use App\Repositories\OrderTransactionRepository;
use App\Repositories\TransactionRepository;

class TransactionObserve
{
    protected $customerBacklog;
    protected $bankBacklog;
    protected $transactionRp;
    protected $orderTransactionRp;

    public function __construct(
        CustomerBacklogRepository $customerBacklog,
        BankBacklogRepository $bankBacklog,
        TransactionRepository $transactionRp,
        OrderTransactionRepository $orderTransactionRp
    ) {
        $this->customerBacklog = $customerBacklog;
        $this->bankBacklog = $bankBacklog;
        $this->transactionRp = $transactionRp;
        $this->orderTransactionRp = $orderTransactionRp;
    }

    public function created(Transaction $transaction)
    {
        if ($transaction->customer_id) {
            $this->customerBacklog->update($transaction->customer_id, $transaction->transfered_amount, $transaction->received_amount, 0, $transaction->id);
        }

        if ($transaction->bank_id) {
            $this->bankBacklog->update($transaction->bank_id, $transaction->received_amount, $transaction->transfered_amount, $transaction->fee);
            $bank = $transaction
                ->fresh()
                ->bank;

            Transaction::where('id', $transaction->id)
                ->update([
                    'bank_history' => $bank->last_balance,
                    'store_id' => $bank->store_id,
                ]);
        }

        if ($transaction->order_id) {
            $amount = $transaction->received_amount - $transaction->transfered_amount - $transaction->fee;
            $this->orderTransactionRp->updateOrderPaymentByTransaction($transaction, $amount);
        }

        $customer = Customer::find($transaction->customer_id);
        $store = Store::find($customer->store_id);
        if ($customer && $store && $store->neededToPayCommission($customer->group_id)) {
            $last = Commission::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $amount = $transaction->transfered_amount + $transaction->fee - $transaction->received_amount;
            Commission::updateOrCreate([
                'customer_id' => $customer->id,
                'order_id' => 0,
                'trans_id' => $transaction->id,
                'amount' => -$amount,
                'balance' => ($last ? $last->balance : 0) + -$amount,
                'note' => $transaction->desc
            ]);
        }
    }

    public function updated(Transaction $transaction)
    {
        if (!empty($transaction->getOriginal())) {
            if ($transaction->isDirty([
                'received_amount',
                'transfered_amount',
                'fee'
            ])) {
                $transfered = $transaction->transfered_amount - $transaction->getOriginal('transfered_amount');
                $received = $transaction->received_amount - $transaction->getOriginal('received_amount');
                $fee = $transaction->fee - $transaction->getOriginal('fee');
                $customerId = $transaction->getOriginal('customer_id');
                $bankId = $transaction->getOriginal('bank_id');

                $this->customerBacklog->update($customerId, $transfered, $received, 0, $transaction->id);

                $this->bankBacklog->update($bankId, $received, $transfered, $fee);
                $changed = $received - $transfered - $fee;
                $this->transactionRp->updateBankHistoryForNextTransactions($transaction->id, $bankId, $changed);

                if ($transaction->order_id) {
                    $this->orderTransactionRp->updateOrderPaymentByTransaction($transaction, $changed);
                }
            }

            if ($transaction->isDirty([
                'customer_id',
            ])) {
                $oldCustomer = $transaction->getOriginal('customer_id');
                if ($oldCustomer) {
                    $this->customerBacklog->update($oldCustomer, -$transaction->transfered_amount, -$transaction->received_amount, 0, $transaction->id);
                }

                $customer = $transaction->client;
                if ($customer) {
                    $this->customerBacklog->update($transaction->customer_id, $transaction->transfered_amount, $transaction->received_amount, 0, $transaction->id);
                }
            }

            if ($transaction->isDirty([
                'bank_id',
            ])) {
                $oldBank = $transaction->getOriginal('bank_id');
                $bank = $transaction->bank;

                //update for old bank and all transactions associated with
                $this->bankBacklog->update($oldBank, -$transaction->received_amount, -$transaction->transfered_amount, -$transaction->fee);
                $changed = 0 - ($transaction->received_amount - $transaction->transfered_amount - $transaction->fee);
                $closest = Transaction::where('bank_id', $oldBank)
                    ->where('id', '>', $transaction->id)
                    ->orderBy('id', 'asc')
                    ->limit(1)
                    ->first();
                if ($closest) {
                    $this->transactionRp->updateBankHistoryForNextTransactions($closest->id, $oldBank, $changed);
                }

                //update for current bank and transaction
                $closest = Transaction::where('bank_id', $transaction->bank_id)
                    ->where('id', '<', $transaction->id)
                    ->orderBy('id', 'desc')
                    ->limit(1)
                    ->first();
                $lastBalance = $closest ? $closest->bank_history : $transaction->bank->last_balance;
                Transaction::where('id', $transaction->id)
                    ->update([
                        'bank_history' => $lastBalance,
                        'store_id' => $bank->store_id,
                    ]);

                $this->bankBacklog->update($transaction->bank_id, $transaction->received_amount, $transaction->transfered_amount);
                $this->transactionRp->updateBankHistoryForNextTransactions($transaction->id, $transaction->bank_id, -$changed);
            }
        }
    }

    public function deleted(Transaction $transaction)
    {
        $this->customerBacklog->update($transaction->customer_id, -$transaction->transfered_amount, -$transaction->received_amount, 0, $transaction->id);
        $this->bankBacklog->update($transaction->bank_id, -$transaction->received_amount, -$transaction->transfered_amount, -$transaction->fee);
        $bank = $transaction
            ->bank
            ->fresh();
        $changed = 0 - ($transaction->received_amount - $transaction->transfered_amount - $transaction->fee);
        $this->transactionRp->updateBankHistoryForNextTransactions($transaction->id, $transaction->bank_id, $changed);
        if ($transaction->order_id) {
            $this->orderTransactionRp->updateOrderPaymentByTransaction($transaction, $changed);
        }
    }
}
