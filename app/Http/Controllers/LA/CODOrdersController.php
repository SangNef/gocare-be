<?php

namespace App\Http\Controllers\LA;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\CODPartners\GHNService;
use App\Services\CODPartners\GHN5Service;
use App\Services\CODPartners\VTPService;

use App\Models\CODOrder;
use App\Models\Order;
use App\Models\OrderStatus;

use App\Datatable\Datatables;
use App\Events\WarrantyOrderSaved;
use App\Models\DOrder;
use App\Models\StoreShipping;
use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProductSeri;
use App\Repositories\BankBacklogRepository;
use Carbon\Carbon;
use App\Repositories\CODOrderRepository;
use App\Repositories\CustomerBacklogRepository;
use App\Repositories\OrderRepository;
use App\Services\CODPartners\GHTKService;
use App\Services\CODPartners\StoreShippingService;
use App\Services\CODPartners\VNPostService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Validator;

class CODOrdersController extends Controller
{
    protected $ghnSv;
    protected $ghn5Sv;
    protected $vtpSv;
    protected $ghtkSv;
    protected $vnpostSv;
    protected $orderStatus;
    protected $codOrderRp;
    protected $bankBacklogRp;
    protected $codOrder;
    protected $customerBacklogRp;
    protected $orderRp;
    protected $listingCols = [
        'id' => 'ID',
        'order_id' => 'Đơn hàng',
        'customer_id' => 'Khách hàng',
        'store_id' => 'Mã kho',
        'order_code' => 'Mã vận đơn',
        'partner' => 'Đối tác',
        'quantity' => 'Số kiện hàng',
        'cod_amount' => 'Tiền COD',
        'fee_amount' => 'Tiền cước',
        'real_amount' => 'Tiền cước thực tế',
        'package_price' => 'Tiền hàng',
        'compare_status' => 'Trạng thái đối soát',
        'charge_method' => 'Hình thức thu tiền',
        'created_at' => 'Ngày tạo'
    ];
    protected $showAction = true;

    public function __construct(
        GHNService $ghnSv,
        GHN5Service $ghn5Sv,
        VTPService $vtpSv,
        GHTKService $ghtkSv,
        VNPostService $vnpostSv,
        OrderStatus $orderStatus,
        CODOrderRepository $codOrderRp,
        BankBacklogRepository $bankBacklogRp,
        CODOrder $codOrder,
        CustomerBacklogRepository $customerBacklogRp,
        OrderRepository $orderRp
    ) {
        $this->ghnSv = $ghnSv;
        $this->ghn5Sv = $ghn5Sv;
        $this->vtpSv = $vtpSv;
        $this->ghtkSv = $ghtkSv;
        $this->vnpostSv = $vnpostSv;
        $this->orderStatus = $orderStatus;
        $this->codOrderRp = $codOrderRp;
        $this->bankBacklogRp = $bankBacklogRp;
        $this->codOrder = $codOrder;
        $this->customerBacklogRp = $customerBacklogRp;
        $this->orderRp = $orderRp;
    }

    public function getAddress($partner, Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required_if:type,ghn',
            'id' => 'sometimes',
            'type' => 'required',
            'oClass' => 'required_if:type,ghn'
        ]);

        $shippingSv = StoreShippingService::getProvider($partner);
        if ($partner === CODOrder::PARTNER_GHN) {
            $oClass = $request->oClass;
            $order = $oClass::find($request->order_id);
            $loadConnectionByStore = $order instanceof Order || $order instanceof DOrder ? $order->isFromAdmin() : true;
            $customer = $order->customer;
            $shippingSv->loadConnection($customer, $loadConnectionByStore);
        }
        $results = $shippingSv->getAddress($request->type, $request->id);

        return $results;
    }

    public function getPrice($partner, Request $request)
    {
        try {
            $oClass = $request->oClass;
            $order = $oClass::find($request->order_id);
            $loadConnectionByStore = $order instanceof Order || $order instanceof DOrder ? $order->isFromAdmin() : true;
            $customer = $order->customer;
            switch ($partner) {
                case CODOrder::PARTNER_GHN:
                    $results = $this->ghnSv->loadConnection($customer, $loadConnectionByStore)->getServicePrice($request->inventory, $request->except('inventory'));
                    break;
                case CODOrder::PARTNER_GHN_5:
                    $results = $this->ghn5Sv->loadConnection($customer, $loadConnectionByStore)->getServicePrice($request->inventory, $request->except('inventory'));
                    break;
                case CODOrder::PARTNER_VTP:
                    $results = $this->vtpSv->loadConnection($customer, $loadConnectionByStore)->requestServicePrice($request);
                    break;
                case CODOrder::PARTNER_GHTK:
                    $data = $request->all();
                    $data += [
                        'value' => $request->insurance_value,
                    ];
                    unset($data['insurance_value']);
                    $results = $this->ghtkSv->loadConnection($customer, $loadConnectionByStore)->getServicePrice($data);
                    break;
                case CODOrder::PARTNER_VNPOST:
                    $applyDiscount = $order instanceof Order || $order instanceof DOrder ? $order->isFromAdmin() : false;
                    $results = $this->vnpostSv->loadConnection($customer, $loadConnectionByStore)->getPriceForAllServices($request->all(), $applyDiscount);
                    break;
                default:
                    $results = [];
            }
            return response()->json($results);
        } catch (\Exception $exception) {
            $message = $exception instanceof ClientException
                ? $exception->getResponse()->getBody()->getContents()
                : $exception->getMessage();
            \Log::error($exception);
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 400);
        }
    }

    public function ghnGetServices(Request $request)
    {
        $oClass = $request->oClass;
        $order = $oClass::find($request->order_id);
        $loadConnectionByStore = $order instanceof Order || $order instanceof DOrder ? $order->isFromAdmin() : true;
        $results = $this->ghnSv->loadConnection($order->customer, $loadConnectionByStore)->getServices($request->all());
        return response()->json($results);
    }

    public function createBillLading($id, $partner, Request $request)
    {
        if ($request->get('d') == 1) {
            return $this->updateCodOrderForDOrder($id, $partner, $request);
        }
        try {
            $order = Order::findOrFail($id);
            $order->cod_partner = $partner;
            $order->save();
            $data = $request->all();

            DB::beginTransaction();
            if (isset($data['DELIVERY_DATE'])) {
                $data['DELIVERY_DATE'] = strpos($data['DELIVERY_DATE'], '-') !== false 
                    ? Carbon::createFromFormat('Y-m-d H:i:s', $data['DELIVERY_DATE'])->format('d/m/Y H:i:s')
                    : Carbon::createFromFormat('d/m/Y H:i:s', $data['DELIVERY_DATE'])->format('d/m/Y H:i:s');
            }
            $this->codOrderRp->createBillLading($partner, $order, $data);
            if ($order->codOrder()->exists() && $order->isCODOrderChargeDebt()) {
                $this->customerBacklogRp->processForCreateOrder($order);
                $debt = $order->isImport() ? -$order->total : $order->total;
                $order->update([
                    'current_debt' => \DB::raw('current_debt + ' . $debt)
                ]);
            }

            DB::commit();
            return redirect()->back();
        } catch (\Exception $exception) {
            DB::rollback();
            $message = $exception instanceof ClientException
                ? $exception->getResponse()->getBody()->getContents()
                : $exception->getMessage();
            \Log::error($exception->getMessage());
            return redirect()->back()->withErrors($message);
        }
    }

    public function updateCodOrderForDOrder($id, $partner, Request $request)
    {
        try {
            $order = DOrder::findOrFail($id);

            $ops = $order->orderProducts;
            foreach ($ops as $key => $op) {
                $data = $request->items[$key];
                $op->update([
                    'dimension' => collect($data)->only([
                        'weight',
                        'length',
                        'height',
                        'width'
                    ])
                ]);
            }
            DB::commit();
            return redirect()->back();
        } catch (\Exception $exception) {
            DB::rollback();
            $message = $exception instanceof ClientException
                ? $exception->getResponse()->getBody()->getContents()
                : $exception->getMessage();
            \Log::error($exception->getMessage());
            return redirect()->back()->withErrors($message);
        }
    }

    public function createBillLadingForWarrantyOrder($id, $partner, Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:all,some',
            'wops_ids' => 'required_if:type,some'
        ]);

        try {
            $order = WarrantyOrder::findOrFail($id);
            DB::beginTransaction();

            $data = $request->all();
            $this->codOrderRp->createBillLadingForWarrantyOrder($partner, $order, $data);

            DB::commit();
            event(new WarrantyOrderSaved($order));

            return $request->ajax()
                ? response()->json([
                    'order_id' => $id,
                    'status' => "OK"
                ])
                : redirect()->back();
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::error($exception->getMessage());
            \Log::error($exception->getTraceAsString());
            $message = $exception instanceof ClientException
                ? $exception->getResponse()->getBody()->getContents()
                : $exception->getMessage();
            return $request->ajax()
                ? response()->json([
                    'order_id' => $id,
                    'error' => [$message]
                ], 422)
                : redirect()->back()->withErrors($message);
        }
    }

    public function index($type)
    {
        return view('la.cod_orders.index', [
            'type' => $type,
            'listing_cols' => $this->listingCols,
            'show_actions' => $this->showAction
        ]);
    }

    public function edit($type, $id)
    {
        $codOrder = $this->codOrder->where('partner', $type)->findOrFail($id);
        return view('la.cod_orders.edit', ['codOrder' => $codOrder, 'type' => $type]);
    }

    public function update($type, $id, Request $request)
    {
        $codOrder = $this->codOrder->where('partner', $type)->findOrFail($id);
        $order = $codOrder->order;
        switch ($type) {
            case CODOrder::PARTNER_VTP:
                $shipping = $this->vtpSv;
                break;
            case CODOrder::PARTNER_GHN:
                $shipping = $this->ghnSv;
                break;
            case CODOrder::PARTNER_GHN_5:
                $shipping = $this->ghn5Sv;
                break;
            case CODOrder::PARTNER_GHTK:
                $shipping = $this->ghtkSv;
                break;
            default:
                $shipping = null;
                break;
        }
        try {
            DB::beginTransaction();
            $feeAmount = $shipping->loadConnection($codOrder->customer, true)->applyDiscount($request->real_amount);
            if ($order && $order instanceof Order && $order->isFromAdmin() && $order->fee_bearer == Order::BEARER_FEE_BUYER) {
                $feeAmount = 0;
            }
            $codOrder->update([
                'real_amount' => $request->real_amount,
                'cod_amount' => $request->cod_amount,
                'fee_amount' => $feeAmount
            ]);

            DB::commit();
            return redirect()->route('co.index', ['type' => $type]);
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::error($exception->getMessage());
            return redirect()->back()->withErrors($exception->getMessage());
        }
    }

    public function dtajax($type, Request $request)
    {
        $columns = array_keys($this->listingCols);
        $values = $this->codOrder
            ->where('partner', $type)
            ->select($columns)
            ->search($request->all())
            ->orderBy('id', 'desc');
        $dataTable = Datatables::of($values);

        $dataTable->filterColumn('order_id', function ($query, $keyword) {
            if (!empty($keyword)) {
                $query->whereExists(function ($q) use ($keyword) {
                    $q->select(DB::raw(1))
                        ->from('orders')
                        ->whereRaw('cod_orders.order_id = orders.id')
                        ->where('orders.code', $keyword);
                });
            }
        });

        $out = $dataTable->make();
        $data = $out->getData();
        $total = [
            'total_fee' => number_format($values->sum('fee_amount')),
            'total_cod' => number_format($values->sum('cod_amount')),
            'insurance_value' => number_format($values->sum('package_price'))
        ];

        for ($i = 0; $i < count($data->data); $i++) {
            $id = $data->data[$i][0];
            $codOrder = $this->codOrder->find($id);
            $order = $codOrder->order;
            for ($j = 0; $j < count($columns); $j++) {
                $col = $columns[$j];
                if ($col === 'id') {
                    $data->data[$i][0] = '<input type="checkbox" class="row" value="' . $id . '"/>' . $id;
                } else if ($col == "created_at") {
                    $data->data[$i][$j] = Carbon::parse($data->data[$i][$j])->format('d/m/Y H:i');
                } else if (in_array($col, ['fee_amount', 'cod_amount', 'package_price', 'real_amount'])) {
                    $amount = $col == 'fee_amount'
                        && $order
                        && $order instanceof Order
                        && $order->isBuyerBearsTheFee()
                        && $order->isFromAdmin()
                        ? 0
                        : $data->data[$i][$j];
                    $data->data[$i][$j] = number_format($amount) . ' đ';
                } else if ($col == 'order_id') {
                    if (!$order && $codOrder->order_type === 'WarrantyOrderProductSeri') {
                        $warrantyOrder = $codOrder->warrantyOrderProductSeriActualOrder();
                        $orderCode = $warrantyOrder->code;
                        $orderId = $warrantyOrder->id;
                    } else {
                        $orderCode = $order->code;
                        $orderId = $order->id;
                    }
                    $path = in_array($codOrder->order_type, ['WarrantyOrder', 'WarrantyOrderProductSeri']) ? "/warrantyorders/{$orderId}" : "/orders/{$orderId}";
                    $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . $path . '/edit') . '">' . $orderCode . '</a>';
                } else if ($col == 'compare_status') {
                    $data->data[$i][$j] = $codOrder->getCompareStatusLabelHTML();
                } else if ($col == 'partner') {
                    $data->data[$i][$j] = $codOrder->getPartnerLabelHTML();
                } else if ($col == 'charge_method') {
                    $data->data[$i][$j] = $codOrder->getChargeMethodLabelHTML();
                } else if ($col == 'customer_id') {
                    $data->data[$i][$j] =  @$codOrder->customer->name ?? '';
                }
            }
            if ($this->showAction) {
                $output = '';
                $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/cod-orders/' . $type . '/' . $id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                $data->data[$i][] = (string)$output;
            }
        }
        $data->total = $total;
        $out->setData($data);
        return $out;
    }

    private function prepareBills($bills)
    {
        return array_filter(array_map('trim', explode(',', preg_replace('/\s+/', '', $bills))));
    }

    private function codOrderQueryForCompare($type, $bills)
    {
        if (!is_array($bills)) {
            $bills = $this->prepareBills($bills);
        }
        return function ($query) use ($type, $bills) {
            $query->where('partner', $type)
                ->where(function ($query) use ($bills, $type) {
                    if (in_array($type, [CODOrder::PARTNER_GHN, CODOrder::PARTNER_VTP, CODOrder::PARTNER_VNPOST])) {
                        $query->whereIn('order_code', $bills);
                    } else {
                        foreach ($bills as $key => $bill) {
                            $code = explode('.', $bill);
                            $clause = $key == 0 ? 'where' : 'orWhere';
                            $query->{$clause}('order_code', 'LIKE', '%' . end($code) . '%');
                        }
                    }
                })
                ->excludeCancel();
        };
    }

    public function checkBills($type, Request $request)
    {
        $errors = [];
        $bills = $this->prepareBills($request->bills);
        if (in_array($type, ['ghn', 'ghn_5', 'vtp'])) {
            $rules = [
                'bills.*' => 'exists:cod_orders,order_code,partner,' . $type
            ];
            $messages = [];
            if (!empty($bills)) {
                foreach ($bills as $key => $value) {
                    $messages['bills.' . $key . '.exists'] = 'Mã vận đơn ' . $value . ' không tồn tại trong hệ thống';
                }
            }
            $validator = Validator::make(['bills' => $bills], $rules, $messages);
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
            }
        } else {
            foreach ($bills as $key => $bill) {
                $input = explode('.', $bill);
                $code = end($input);
                $exists = CODOrder::where('partner', $type)
                    ->where('order_code', 'LIKE', '%' . $code . '%')
                    ->exists();
                if (!$exists) {
                    $errors[$key] = 'Mã vận đơn ' . $bill . ' không tồn tại trong hệ thống';
                }
            }
        }

        return !empty($errors)
            ? response()->json($errors, 422)
            : response()->json();
    }

    public function getMoney($type, Request $request)
    {
        $codTotal = (int) $this->codOrder->getTotalAmountOfColumnByOrderCode($type, $this->prepareBills($request->cod_bills), 'cod_amount');
        $feeTotal = (int) $this->codOrder->getTotalAmountOfColumnByOrderCode($type, $this->prepareBills($request->fee_bills), 'real_amount');
        if (isset($request->discount_percent) && $request->discount_percent) {
            $feeTotal *= ((100 - $request->discount_percent) / 100);
            $feeTotal = round($feeTotal);
        }
        return response()->json([
            'cod_amount' => number_format($codTotal) . ' đ',
            'real_amount' => number_format($feeTotal) . ' đ',
            'total_amount' => number_format($codTotal - $feeTotal) . ' đ'
        ]);
    }

    public function updateBankBalance($type, Request $request)
    {
        $this->validate($request, [
            'bank_id' => 'required|exists:banks,id'
        ]);
        $codBills = $this->prepareBills($request->cod_bill_ids);
        $feeBills = $this->prepareBills($request->fee_bill_ids);

        try {
            \DB::beginTransaction();

            // Cập nhật trạng thái 'Đã duyệt' cho những đơn hàng đối soát COD
            Order::whereHas('codOrder', $this->codOrderQueryForCompare($type, $codBills))->each(function ($order) {
                $order->approve = 1;
                $order->status = 2;
                $order->shipping_status = 'Thành công';
                $this->customerBacklogRp->processForUpdateOrder($order);
            });

            // Cập nhật trạng thái đối soát cước thành 'Đã đối soát'
            $this->codOrder->where($this->codOrderQueryForCompare($type, $feeBills))
                ->update([
                    'compare_status' => 1
                ]);

            $this->bankBacklogRp->update($request->bank_id, $request->amount);

            \DB::commit();
            return redirect()->back();
        } catch (\Exception $exception) {
            \DB::rollback();
            \Log::error($exception->getMessage());
        }
        return redirect()->back()->withErrors(trans('messages.cannot_save'));
    }

    public function updateProviderStatus($partner, $orderCode, Request $request)
    {
        try {
            $codOrder = CODOrder::where('partner', $partner)
                ->where('order_code', $orderCode)
                ->firstOrFail();
            $order = $codOrder->order;
            if ($order && $order instanceof Order) {
                if ($request->status === 'delete_on_system') {
                    $amount = $order->total;
                    if ($order->isFromFE()) {
                        $amount = $order->status != 3 ? $order->getCTVPriceForOrderFromFE() : $codOrder->fee_amount;
                    }
                    if ($codOrder->isChargeToCustomerDebt()) {
                        $this->customerBacklogRp->update($order->customer_id, -$amount, 0, $order->id);
                        $order->current_debt -= $amount;
                        $order->amount_charged_to_debt -= $amount;
                        $order->save();
                    }
                    $codOrder->delete();
                    return redirect()->back();
                }

                switch ($partner) {
                    case CODOrder::PARTNER_GHN:
                        $this->ghnSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode, $request->status, $codOrder->store_id);
                        $cancelStatus = $request->status == 'cancel' ? 'cancel' : null;
                        break;
                    case CODOrder::PARTNER_GHN_5:
                        $this->ghn5Sv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode, $request->status, $codOrder->store_id);
                        $cancelStatus = $request->status == 'cancel' ? 'cancel' : null;
                        break;
                    case CODOrder::PARTNER_GHTK:
                        // GHTK order can only be canceled
                        $this->ghtkSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode);
                        $cancelStatus = '-1';
                        break;
                    case CODOrder::PARTNER_VTP:
                        // VTP's order cancellation status is 4
                        $this->vtpSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode, $request->status);
                        $cancelStatus = $request->status == 4 ? '201' : null;
                        break;
                    case CODOrder::PARTNER_VNPOST:
                        // VNPost order can only be canceled
                        $vnPostOrderId = @$codOrder['additional_data']['id'];
                        if (!$vnPostOrderId) {
                            return redirect()->back()->withErrors(['id' => 'VNPost order id không tồn tại']);
                        }
                        $this->vnpostSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($vnPostOrderId);
                        $cancelStatus = '60';
                    default:
                        $cancelStatus = null;
                        break;
                }
                if ($cancelStatus) {
                    $codOrder->status = $cancelStatus;
                    $codOrder->save();
                }
            }

            return redirect()->back();
        } catch (\Exception $exception) {
            \Log::error($exception->getTraceAsString());
            return redirect()->back()->withErrors($exception->getMessage());
        }
    }

    public function getPartnerInventory($partner, $orderId)
    {
        $order = Order::find($orderId);
        $inventories = $this->codOrderRp->getPartnerStores($partner, $order);
        return response()->json($inventories->toArray());
    }

    public function fakeBill($orderId, Request $request)
    {
        $rules = [
            'partner' => 'required|in:other,' . implode(',', array_keys($this->codOrderRp->getAvailableProvider())),
            'order_code' => 'required|unique:cod_orders,order_code',
            // 'store_id' => 'required_if|partner',
            'cod_amount' => 'required|integer|min:0',
            'fee_amount' => 'required|integer|min:0',
            'package_price' => 'required|integer|min:0'
        ];
        if ($request->partner != 'other') {
            $rules['store_id'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            if ($request->get('d') == 1) {
                $order = DOrder::findOrFail($orderId);
                $request->request->add([
                    'order_type' => 'DOrder'
                ]);
                $request->request->remove('d');
            } else {
                $order = Order::findOrFail($orderId);
            }
            if ($order->codOrder()->exists()) {
                throw new \Exception(trans('messages.cod_order_exists'));
            }
            $data = array_merge($request->all(), [
                'order_id' => $order->id,
                'quantity' => 1,
                'real_amount' => 0,
            ]);
            $order->codOrder()->create($data);
            return redirect()->back();
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::error($exception->getMessage());
            \Log::error($exception->getTraceAsString());
            return redirect()->back()->withErrors($exception->getMessage());
        }
    }
}
