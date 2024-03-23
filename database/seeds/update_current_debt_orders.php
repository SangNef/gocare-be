<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use Carbon\Carbon;

class update_current_debt_orders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', '2021-05-19 00:00:00');
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $groupOrders = Order::whereHas('customer', function ($q) {
                // Exclude test orders
                $q->whereIn('group_id', Group::getElectronicGroup())
                    ->where('id', '<>', 1)
                    ->where('name', 'not like', '%test%');
            })
                ->whereNull('deleted_at')
                ->whereBetween('created_at', [$startDate, $now])
                ->get()
                ->groupBy('customer_id');
            foreach ($groupOrders as $customerId => $orders) {
                $orders = $orders->sortByDesc('id')->values();
                $currentDebt = Customer::find($customerId)->debt_total; // Current debt of customer
                foreach ($orders as $key => $order) {
                    $index = $key;
                    $from = Carbon::parse($order->created_at)->format('Y-m-d H:i:s');
                    if ($key == 0) {
                        // latest order debt = current debt (included paid amount)
                        $to = Carbon::now()->format('Y-m-d H:i:s');
                        $currentDebt -= $this->getTransactionTotalBetween($customerId, [$from, $to]);
                        $order->current_debt = $currentDebt;
                        $order->save();
                        continue;
                    }
                    $higherOrder = $orders[--$index];
                    $to = Carbon::parse($higherOrder->created_at)->format('Y-m-d H:i:s');
                    $currentDebt -= $this->getTransactionTotalBetween($customerId, [$from, $to]);
                    $debt = 0;
                    if (!$higherOrder->isCODOrder() || $higherOrder->isCODOrderChargeDebt()) {
                        $isFromFE = $higherOrder->isFromFE();
                        $orderTotal = $higherOrder->total;
                        $debt = $isFromFE ? $higherOrder->getCTVPriceForOrderFromFE() : $orderTotal;

                        if ($higherOrder->status == 2) {
                            // if order success
                            // => order fe: selling price for customer - selling price for ctv (include shipping fee)
                            // => order admin: order total
                            $debt = $isFromFE
                                ? ($higherOrder->getOrderFeProductsPrice() - $higherOrder->getCTVPriceForOrderFromFE()) * -1
                                : $orderTotal;
                        }
                        if ($higherOrder->status == 3) {
                            // if order cancel
                            // => order fe: ctv bear the shipping fee
                            // => order admin: order total
                            $debt = $isFromFE
                                ? ($higherOrder->codOrder ? $higherOrder->codOrder->fee_amount : 0)
                                : $orderTotal;
                        }
                        if ($higherOrder->isImport() && $debt != 0) {
                            $debt *= -1;
                        }
                    }
                    $currentDebt -= $debt - $higherOrder->paid;
                    $order->current_debt = $currentDebt;
                    $order->save();
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            $this->command->info($exception->getMessage());
        }
    }

    private function getTransactionTotalBetween($customerId, $between = [])
    {
        return Transaction::whereNull('deleted_at')
            ->where('customer_id', $customerId)
            ->whereDoesntHave('order')
            ->whereBetween('created_at', $between)
            ->selectRaw('(CASE WHEN type = 1 THEN received_amount ELSE -transfered_amount END) as amount')
            ->get()
            ->sum('amount');
    }
}
