<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Api\BaseOrderController;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderRepository;
use App\Repositories\CustomerBacklogRepository;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderTransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeviceOrderController extends BaseOrderController
{
    protected $customerBacklogRp;

    public function __construct(
        Order $order,
        OrderProductRepository $orderProductRp,
        OrderRepository $orderRp,
        OrderTransactionRepository $OrderTransaction,
        CustomerBacklogRepository $customerBacklogRp
    ) {
        parent::__construct($order, $orderProductRp, $orderRp, $OrderTransaction);
        $this->customerBacklogRp = $customerBacklogRp;
    }

    public function getSuppliersAndProducts()
    {
        $customers = Customer::whereHas('group', function($q) {
                $q->where('name', 'nha_cung_cap');
            })
            ->whereNull('deleted_at')
            ->select(DB::raw('id, CONCAT(username," - ",email) as text'))
            ->pluck('text', 'id')
            ->toArray();
        $products = Product::whereNull('deleted_at')
            ->where('status', 1)
            ->select(DB::raw('id, CONCAT(name, " - ", sku) as text'))
            ->pluck('text', 'id')
            ->toArray();
        return response()->json([
            'customers' => $customers,
            'products' => $products
        ]);
    }

    public function simTradingOrder(Request $request)
    {
        $rules = [
            'type' => 'required|in:' . implode(',', array_keys($this->order->availableType())),
            'customer_id' => 'required|exists:customers,id,deleted_at,NULL',
            'product_id' => 'required|exists:customers,id,deleted_at,NULL',
            'amount' => 'required|integer|min:0',
            'discount_percent' => 'required|integer|min:0'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($request->customer_id);
            $requestProduct = Product::findOrFail($request->product_id);
            $data = $this->prepareData($request, $customer->id);
            $order = $this->order->create($data);
            $product = $this->prepareProduct($requestProduct, $request->amount);

            $this->orderProductRp->createForOrder($product, $order);
            $this->orderRp->updateAmount($order);
            $this->customerBacklogRp->processForCreateOrder($order);
            $order->current_debt = $order->customer->debt_total;
            $order->save();
            
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception->getMessage());
        }
        return response()->json('Ok');
    }
}
