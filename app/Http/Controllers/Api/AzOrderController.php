<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseOrderController;
use App\Models\AzOrder;
use App\Models\Config;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Bank;
use App\Repositories\BankRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderTransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomerStatistic;
use App\Repositories\CustomerBacklogRepository;
use Carbon\Carbon;

class AzOrderController extends BaseOrderController
{
    protected $config;
    protected $customerRp;
    protected $bankRp;
    protected $customerBacklogRp;

    public function __construct(
        Order $order,
        Config $config,
        CustomerRepository $customerRp,
        OrderProductRepository $orderProductRp,
        OrderRepository $orderRp,
        OrderTransactionRepository $OrderTransaction,
        BankRepository $bankRp,
        CustomerBacklogRepository $customerBacklogRp
    )
    {
        parent::__construct($order, $orderProductRp, $orderRp, $OrderTransaction);
        $this->config = $config;
        $this->customerRp = $customerRp;
        $this->bankRp = $bankRp;
        $this->customerBacklogRp = $customerBacklogRp;
    }

    public function adminHandle(Request $request)
    {
        $rules = [
            'type' => 'required|in:' . implode(',', array_keys($this->order->availableType())),
            'customer' => 'required',
            'customer.username' => 'required',
            'customer.email' => 'required',
            'customer.phone' => 'required',
            'amount' => 'required|integer|min:0',
            'service' => 'required|in:' . implode(',', array_keys(AzOrder::availableTypes())),
            'o_amount' => 'sometimes|integer',
            'total_amount' => 'sometimes|integer|min:0',
            'payment.amount' => 'sometimes|integer|min:0',
            'payment.acc_id' => 'required_with:payment.amount|integer|min:0',
            'payment.acc_name' => 'required_with:payment.amount',
            'payment.name' => 'required_with:payment.amount',
            'payment.branch' => 'required_with:payment.amount',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
        DB::beginTransaction();
        try {
            $customer = $this->filterCustomer($request->customer);
            $data = $this->prepareData($request, $customer->id);
            $order = $this->order->create($data);
            $configProduct = $this->config->getAzConfigProduct($request->service);
            $product = $this->prepareProduct($configProduct, $request->amount);
            $payment = [];
            if ($request->service == 'az_admin_deposit' && is_array($request->payment)) {
                $bank = Bank::whereNull('deleted_at')->where('acc_id', $request->payment['acc_id'])->first() ?: $this->bankRp->create(collect($request->payment)->only(['name', 'branch', 'acc_name', 'acc_id'])->toArray());
                $payment[] = [
                    'desc' => @$request->payment['note'],
                    'bank_id' => $bank->id,
                    'amount' => $request->payment['amount'],
                    'fee' => (int) @$request->payment['fee'],
                    'code' => 'AZ' . app(\App\Services\Generator::class)->generateOrderCode()
                ]; 
            }
            $this->orderProductRp->createForOrder($product, $order);
            $this->orderRp->updateAmount($order);

            if ($request->service == 'az_admin_deposit') {
                $order->update(['status' => 2]);
            }
            if (!empty($payment)) {
                $this->OrderTransaction->create($order, $payment);
            } 

            $this->customerBacklogRp->processForCreateOrder($order);
            $order->current_debt = $order->customer->debt_total;
            $order->save();
            $order->azOrder()->updateOrCreate([
                'type' => AzOrder::availableTypes()[$request->service]
            ]);

            DB::commit();
            if ($request->service == 'az_admin_deposit' && $request->o_amount) {
                $customer = $customer->fresh();
                CustomerStatistic::create([
                    'customer_id' => $customer->id,
                    'amount' => $request->total_amount,
                    'o_amount' => $request->o_amount,
                    'debt' => $customer->debt_total,
                    'percent' => round($customer->debt_total/$request->o_amount, 2)*100,
                    'created_at' => Carbon::now(),
                ]);
                app(\App\Notifications\Telegram::class)->sendText(implode(' - ', [
                    $customer->username,
                    'Tổng nạp:' . number_format($request->total_amount),
                    'Sản lượng:' . number_format($request->o_amount),
                    'Tổng nợ:' . number_format($customer->debt_total),
                    round($customer->debt_total/$request->o_amount, 2)*100 . '%',
                ]));
            }
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
        }

        return response()->json('Ok');
    }

    public function notiHandle(Request $request)
    {
        $rules = [
            'type' => 'required|in:' . implode(',', array_keys($this->order->availableType())),
            'customer' => 'required',
            'customer.username' => 'required',
            'customer.email' => 'required',
            'customer.phone' => 'required',
            'amount' => 'required|integer|min:0',
            'request_id' =>'required|alpha_dash|max:64',
            'service' => 'required|in:' . implode(',', array_keys(AzOrder::availableTypes()))
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
        DB::beginTransaction();
        try {
            $customer = $this->filterCustomer($request->customer);
            $data = $this->prepareData($request, $customer->id);
            $order = $this->order->create($data);
            $configProduct = $this->config->getAzConfigProduct($request->service);
            $product = $this->prepareProduct($configProduct, $request->amount);
            $payment = [];
            if (isset($request->bank)) {
                $bank = Bank::whereNull('deleted_at')->where('acc_id', $request->bank['acc_id'])->first() ?: $this->bankRp->create($request->bank);
                $payment[] = [
                    'bank_id' => $bank->id,
                    'amount' => $request->amount,
                    'fee' => $request->bank['fee'],
                    'code' => 'AZ' . app(\App\Services\Generator::class)->generateOrderCode()
                ]; 
            }
            $this->orderProductRp->createForOrder($product, $order);
            if ($request->status) {
                $order->status = $request->status;
                $order->save();
            }
            $this->orderRp->updateAmount($order);

            $this->customerBacklogRp->processForCreateOrder($order);
            $order->current_debt = $order->customer->debt_total;
            $order->save();
            if (!empty($payment)) {
                $this->OrderTransaction->create($order, $payment);
            }
            $order->azOrder()->updateOrCreate([
                'request_id' => $request->request_id,
                'type' => AzOrder::availableTypes()[$request->service]
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
        }
    }

    public function updateByRequetId(Request $request)
    {
        $azOrder = AzOrder::where('request_id', $request->request_id)->first();
        if ($azOrder 
            && ($order = $azOrder->order)
            && $order->status = 1
        ) {
            $order->status = $request->status;
            $order->save();
            return response()->json('OK');
        }

        return response()->json('Request ID not found');
    }

    private function filterCustomer($attributes)
    {
        $customer = Customer::where(function ($q) use ($attributes) {
            $q->where('username', $attributes['username'])
            ->orWhere('phone', $attributes['phone']);
        })->first();
        
        return $customer ?: $this->customerRp->create(array_merge($attributes, [
            'store_id' => 3
        ]));
    }
}
