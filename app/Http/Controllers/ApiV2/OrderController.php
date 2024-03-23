<?php

namespace App\Http\Controllers\ApiV2;

use App\Enums\PaymentsEnum;
use App\Events\OrderSaved;
use App\Exceptions\CODException;
use App\Exceptions\StoreProductException;
use App\Http\Requests\InitPaymentRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Group;
use App\Models\LockCommission;
use App\Models\ProductSeri;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\Voucherhistory;
use App\Repositories\ProductSeriesRepository;
use App\Services\Payments\ViettelMoneyService;
use App\Services\Payments\VnpayService;
use App\Services\ViettelActivationService;
use App\Traits\Order\OrderTrait;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CODOrder;
use App\Models\Order;
use App\Models\DOrder;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\Bank;
use App\Models\StoreProduct;
use App\Models\CODOrdersShipping;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Repositories\CODOrderRepository;
use App\Repositories\CustomerBacklogRepository;
use App\Repositories\OrderProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StoreRepository;
use App\Repositories\DOrderRepository;
use App\Repositories\OrderTransactionRepository;
use App\Services\Upload;
use App\Models\PaymentHistory;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB as DBFacade;

class OrderController extends Controller
{
    use OrderTrait;

    protected $order;
    protected $codOrder;
    protected $orderProductRp;
    protected $orderRp;
    protected $customerBacklogRp;
    protected $codOrderRp;
    protected $storeRp;

    protected $productSeriesRp;

    public function __construct(
        Order $order,
        CODOrder $codOrder,
        OrderProductRepository $orderProductRp,
        ProductSeriesRepository $productSeriesRp,
        OrderRepository $orderRp,
        CustomerBacklogRepository $customerBacklogRp,
        CODOrderRepository $codOrderRp,
        StoreRepository $storeRp
    ) {
        $this->order = $order;
        $this->codOrder = $codOrder;
        $this->orderProductRp = $orderProductRp;
        $this->orderRp = $orderRp;
        $this->customerBacklogRp = $customerBacklogRp;
        $this->codOrderRp = $codOrderRp;
        $this->storeRp = $storeRp;
        $this->productSeriesRp = $productSeriesRp;
    }

    public function store(Request $request)
    {
        $rules = [
            'payment_method' => 'required|in:cod,bank,online,installment',
            'cod_partner' => 'required|in:ghn,ghn_5,vtp,ghtk,vnpost,self_serve,other',
            'service_id' => 'required_if:cod_partner,ghn,ghn_5,vtp,vnpost,ghtk',
            'name' => 'required',
            'phone' => 'required|regex:/^0/|digits_between:10,10',
            'address' => 'required',
            'province' => 'required|exists:provinces,id',
            'district' => 'required|exists:districts,id,province_id,' . $request->province,
            'ward' => 'required|exists:wards,id,district_id,' . $request->district,
            'products' => 'required',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.price' => 'required|min:0',
            'products.*.price_for_ctv' => 'required|min:0',
            'products.*.id' => 'required',
            'cod_price_statement' => 'required_if:cod_partner,ghn,ghn_5,vtp,ghtk,vnpost',
            'voucher_id' => 'sometimes|exists:vouchers,id',
            'voucher_history_id' => 'sometimes|exists:voucherhistories,id,customer_id,0,voucher_id,' . $request->voucher_id
        ];
        if (!Auth::guard('customer')->check()) {
            $rules['email'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
        } else {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $request->merge(['debt_total' => $request->debt_in_advance]);
            $customer = Customer::where('email', $request->email)->first();
            if (!$customer) {
                $customer = Customer::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'province' => $request->province,
                    'district' => $request->district,
                    'ward' => $request->ward,
                    'store_id' => Store::first()->id,
                    'vtp_id' => '',
                    'can_create_sub' => 0,
                    'group_id' => Group::where('name', 'khach_le')->first()->id,
                    'note' => '',
                    'debt_total' => 0,
                    'debt_in_advance' => 0,
                    'username' => $request->email,
                    'parent_id' => User::first()->id,
                    'email' => $request->email,
                    'password' => '',
                ]);
            }
            Address::create([
                'customer_id' => $customer->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'district' => $request->district,
                'ward' => $request->ward,
                'default' => 1,
            ]);
            \Illuminate\Support\Facades\DB::commit();
        }
        if ($request->ref) {
            $referer = Customer::where('code', $request->ref)->first();
            if ($referer && $customer && !$customer->group->isAgentGroup() && !$customer->group->isGeneralAgentGroup()) {
                if (!$customer->customer_parent_id) {
                    $customer->customer_parent_id = $referer->id;
                    $customer->save();
                }
            }
        }
        $error = [];
        foreach ($request->products as $product) {
            if (!empty(@$product['attr_ids'])) {
                $ave = StoreProductGroupAttributeExtra::where('attribute_value_ids', implode(',', $product['attr_ids']))
                    ->where('store_id', $customer->store_id)
                    ->where('product_id', $product['id'])
                    ->first();
                if ($ave && $ave->n_quantity < @$product['quantity']) {
                    $error[] = 'Sản phẩm ' . $product['name'] . ' (' . implode(',', $product['attrs']) . ')' . ' Số lượng trong kho không đủ. Vui lòng liên hệ theo HOTLINE';
                }
            }
        }

        if (!empty($error)) {
            return response()->json([
                'status' => 'error',
                'message' => implode("\n", $error),
            ], 400);
        }
        $store = $customer->store;
        $codeGeneratorSv = app(\App\Services\Generator::class);
        if ($request->from && $request->from != $customer->store_id) {
            $crossStore = [
                'from_store_id' => $request->from,
                'cod_partner_store_id' => $request->partner_store,
            ];
        }
        if ($request->payment_method == 'online' || $request->payment_method == 'installment') {
            $paymentMethod = Order::PAYMENT_METHOD_PAY_ONLINE;
        } else if ($request->cod_partner == "self_serve") {
            $paymentMethod = Order::PAYMENT_METHOD_PAY_LATER;
        } else {
            $paymentMethod = Order::PAYMENT_METHOD_COD;
        }

        $data = [
            'payment_method' => $paymentMethod,
            'code' => $codeGeneratorSv->generateOrderCode(),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'customer_id' => $customer->id,
            'type' => Order::TYPE_EXPORT,
            'sub_type' => Product::NEW_PRODUCT,
            'fee_bearer' => 1,
            'cod_partner' => $request->cod_partner != "self_serve" ? $request->cod_partner : null,
            'discount_percent' => 0,
            'discount' => $request->discount,
            'order_from' => Order::ORDER_FROM_FE,
            'note' => $request->note ?? "",
            'store_id' => $customer->store_id,
            //            'cod_service_id' => $request->service_id,
            //            'cod_charge_fee_customer' => $request->countFeeCustomer,
            //            'cross_store' => isset($crossStore) ? json_encode($crossStore) : '',
            //            'cod_tag' => $request->cod_tag ? 1 : 0,
            'voucher_id' => $request->voucher_id,
            'order_series_type' => 1,
        ];

        $subTotal = 0;
        $products = array_map(function ($product) use ($store, $subTotal) {
            $result['name'] = $product['name'];
            $result['product_id'] = $product['id'];
            $result['n_quantity'] = $product['quantity'];
            $result['price'] = $product['price_for_ctv'] ?: $product['price'];
            $result['note'] = "";
            $result['weight'] = @$product['weight'];
            $result['length'] = @$product['length'];
            $result['width'] = @$product['width'];
            $result['height'] = @$product['height'];
            $result['combo_id'] = @$product['combos'] ? $product['combos'][0]['id'] : null;
            $result['has_series'] = Product::find($product['id'])->has_series;
            // Check product is not in stock
            $stockStatus = $this->storeRp->isInStock($product['id'], $store->id, $product['quantity']);
            $result['out_of_stock'] = !$stockStatus;
            if (!$stockStatus) {
                // Find store available for this product order by ASC
                $storeAvailable = StoreProduct::where('store_id', '!=', $store->id)
                    ->where('product_id', $product['id'])
                    ->where('n_quantity', '>=', $product['quantity'])
                    ->first();
                $result['store_available'] = $storeAvailable ? $storeAvailable->store_id : null;
            }
            if (@$product['attr_ids']) {
                $result['attr_ids'] = $product['attr_ids'];
            }
            $subTotal += $result['n_quantity'] * $result['price'];

            return $result;
        }, $request->products);

        if ($request->voucher_id) {
            $voucher = Voucher::find($request->voucher_id);
            $customerGroup = $customer->group_id;
            $voucherGroup = json_decode($voucher->group_ids);
            if (
                !$voucher
                || $subTotal < $voucher->order_min_amount
                || (!empty($voucherGroup) && !in_array($customerGroup, $voucherGroup))
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mã khuyến mại không hợp lệ',
                ], 400);
            }
        }
        try {
            DB::beginTransaction();
            // Get products is out of stock in store
            // if not empty => create import order from other store
            $outOfStockProducts = collect($products)->where('out_of_stock', true);
            if (!$outOfStockProducts->isEmpty()) {
                // Check if has store not available => throw exception
                $storeNotAvailable = $outOfStockProducts->where('store_available', null);
                if ($storeNotAvailable->count() > 0) {
                    $messages = $storeNotAvailable->map(function ($product) {
                        return 'Sản phẩm ' . $product['name'] . ' số lượng trong kho không đủ';
                    })->toArray();
                    throw new StoreProductException(implode("\n", $messages));
                }
                $storeProducts = $outOfStockProducts->groupBy('store_available');
                $discountSv = app(\App\Services\Discount::class);
                foreach ($storeProducts as $storeId => $listProducts) {
                    $moveStore = Store::find($storeId);
                    $owner = $moveStore->owner;
                    $productsToImport = $listProducts->keyBy('product_id')->map(function ($item, $id) use ($discountSv, $owner) {
                        // get price for owner group
                        $priceForOwnerGroup = $discountSv->getDiscountForGroup($owner->group_id, $id);
                        if (isset($priceForOwnerGroup[$id])) {
                            $item['price'] = $priceForOwnerGroup[$id]['price'];
                        }
                        return $item;
                    });
                    // Create import order products
                    $moveOrder = $this->order->create([
                        'code' => $codeGeneratorSv->generateOrderCode(),
                        'customer_id' => $owner->id,
                        'type' => !$owner->store_id ? Order::TYPE_EXPORT : Order::TYPE_IMPORT,
                        'sub_type' => Product::NEW_PRODUCT,
                        'cod_partner' => null,
                        'order_from' => Order::ORDER_FROM_ADMIN,
                        'payment_method' => Order::PAYMENT_METHOD_PAY_LATER,
                        'discount_percent' => 0,
                        'note' => ""
                    ]);
                    $this->orderProductRp->createForOrder($productsToImport, $moveOrder);
                    $this->orderRp->updateAmount($moveOrder);
                    $this->customerBacklogRp->processForCreateOrder($moveOrder);
                    $moveOrder->current_debt = $moveOrder->customer->fresh()->debt_total;
                    $moveOrder->save();
                    // Sync order
                    $this->orderRp->syncCopier($moveOrder);
                }
            }
            // Create order for customer
            $order = $this->order->create($data);
            $this->orderProductRp->createForOrder($products, $order);
            $this->productSeriesRp->processForNewOrder($order, $products);
            $this->orderRp->updateAmount($order);
            $this->orderRp->updateCODPriceStatement($order, @$request->cod_price_statement);
            $order->payment = $request->payment_method;
            $order->current_debt = $order->customer->fresh()->debt_total;
            if (!$request->id) {
                $address = Address::where('customer_id', $customer->id)
                    ->where('default', 1)
                    ->first();

                $request->id = $address ? $address->id : 0;
            }
            $order->address_id = $request->id;
            $order->save();
            DB::commit();
            $codFee = 0;
            if (!$order->cod_charge_fee_customer && $order->isCODOrder()) {
                $this->codOrderRp->loadCodServiceByStore($customer);
                try {
                    switch ($order->cod_partner) {
                        case "ghn":
                            if ($customer->ghn_token) {
                                $selfCodService = true;
                            } else {
                                $options = $this->codOrderRp->GHNShippingOptionsForFE($request);
                            }
                            break;
                        case "ghn_5":
                            if ($customer->ghn_token) {
                                $selfCodService = true;
                            } else {
                                $options = $this->codOrderRp->GHN5ShippingOptionsForFE($request);
                            }
                            break;
                        case "vtp":
                            $options = $this->codOrderRp->VTPShippingOptionsForFE($request);
                            break;
                        case "ghtk":
                            $options = $this->codOrderRp->GHTKShippingOptionsForFE($request);
                            break;
                        case "vnpost":
                            $options = $this->codOrderRp->VNPOSTShippingOptionsForFE($request);
                            break;
                        default:
                            $options = [];
                    }
                    if (isset($options[$order->cod_service_id])) {
                        $codFee = (int)@$options[$order->cod_service_id]['price'];
                    }
                } catch (\Exception $exception) {
                    \Log::error($exception->getMessage());
                    \Log::error($exception->getTraceAsString());
                }
            }
            if ($order->voucher_id) {
                $owner = $order->voucher->owner;
                $last = LockCommission::where('customer_id', $owner->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $commission = $order->getCommissionForCustomer($owner);
                $last = LockCommission::create([
                    'customer_id' => $owner->id,
                    'amount' => $commission - $codFee,
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'note' => 'Hoa hồng cho voucher#' . $order->voucher->code . ', đơn hàng #' . $order->code . ($codFee ? " ( Đã trừ cước tạm tính: " . number_format($codFee) . "đ)" : ''),
                    'balance' => ($last ? $last->balance : 0) + $commission - $codFee,
                ]);
            } else if (is_null($customer->getShippingSetupByPartner($order->cod_partner))) {
                $last = LockCommission::where('customer_id', $order->customer_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $commission = $order->getCommission();
                $last = LockCommission::create([
                    'customer_id' => $order->customer_id,
                    'amount' => $commission - $codFee,
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'note' => 'Hoa hồng cho đơn hàng #' . $order->code . ($codFee ? " ( Đã trừ cước tạm tính: " . number_format($codFee) . "đ)" : ''),
                    'balance' => ($last ? $last->balance : 0) + $commission - $codFee,
                ]);
            } else {
                $order->self_cod_service = 1;
                $order->save();
            }
            if ($order->voucher_id && $request->voucher_history_id) {
                $history = Voucherhistory::find($request->voucher_history_id);
                $history->customer_id = $customer->id;
                $history->used_at = Carbon::now();
                $history->order_id = $order->id;
                $history->save();
            } else if ($order->voucher_id) {
                $voucher = Voucher::find($order->voucher_id);
                Voucherhistory::create([
                    'voucher_id' => $order->voucher_id,
                    'order_id' => $order->id,
                    'code' => $voucher->code,
                    'customer_id' => $customer->id,
                    'used_at' => Carbon::now()
                ]);
            }

            //            app(DOrderRepository::class)->notify($order);
            $redirect = '';
            $paymentIndfo = [];
            $seriNumbers = 'N/A';
            if ($order->payment_method == Order::PAYMENT_METHOD_PAY_ONLINE) {
                $order = $order->fresh();
                if ($request->payment_provider == 'Viettel') {
                    $viettelSv = app(ViettelMoneyService::class);
                    $paymentHistory = PaymentHistory::create([
                        'provider' => 'Viettel',
                        'order_id' => $order->id,
                        'request' => json_encode([
                            'seri_numbers' => ProductSeri::where('order_id', $order->id)->get()->implode('seri_number', ','),
                            'total' => (int)$order->total,
                        ])
                    ]);

                    $redirect = $viettelSv->createRedirectLinkForOrder([
                        'order_id' => $paymentHistory->id,
                        'trans_amount' => (int)$order->total,
                        'return_url' => config('app.fe_url') . '/dat-hang-thanh-cong/' . $order->access_key,
                    ]);
                } else if ($request->payment_provider == 'Vnpay') {
                    $paymentHistory = PaymentHistory::create([
                        'provider' => 'Vnpay',
                        'order_id' => $order->id,
                        'request' => json_encode([
                            'seri_numbers' => ProductSeri::where('order_id', $order->id)->get()->implode('seri_number', ','),
                            'total' => (int)$order->total,
                        ])
                    ]);
                    $paymentHistory->response = $redirect;
                    $paymentHistory->save();
                    $redirect = app(VnpayService::class)->createRedirectLinkOrder($order, $paymentHistory->id);
                } else {
                    $redirect = app('payment')->createRedirectLinkForOrder($order, $request->payment_method == 'installment');
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Tạo đơn hàng thành công',
                'redirect_url' => $redirect,
                'payment_info' => $paymentIndfo,
                'access_key' => $order->access_key,
            ]);
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::error($exception->getMessage());
            \Log::error($exception->getTraceAsString());
            $messages = $exception instanceof CODException || $exception instanceof StoreProductException
                ? $exception->getMessage()
                : 'Đã có lỗi xảy ra, vui lòng thử lại sau';
            return response()->json([
                'status' => 'error',
                'message' => $messages
            ], 400);
        }
    }

    public function getShippingOptions($partner, Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $this->codOrderRp->loadCodServiceByStore($customer);
        try {
            switch ($partner) {
                case "ghn":
                    $options = $this->codOrderRp->GHNShippingOptionsForFE($request);
                    break;
                case "ghn_5":
                    $options = $this->codOrderRp->GHN5ShippingOptionsForFE($request);
                    break;
                case "vtp":
                    $options = $this->codOrderRp->VTPShippingOptionsForFE($request);
                    break;
                case "ghtk":
                    $options = $this->codOrderRp->GHTKShippingOptionsForFE($request);
                    break;
                case "vnpost":
                    $options = $this->codOrderRp->VNPOSTShippingOptionsForFE($request);
                    break;
                default:
                    $options = [];
            }
            return response()->json($options);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $isDraft = false;
        $oClass = $isDraft ? '\App\Models\DOrder' : '\App\Models\Order';
        $orders = $oClass::with('address', 'codOrder', 'products', 'orderProducts')
            ->search($request->all())
            ->where('customer_id', $customer->id)
            //            ->where('order_from', Order::ORDER_FROM_FE)
            ->where(function ($query) use ($request) {
                if ($request->has('order_code') && $request->order_code) {
                    $query->where('code', 'LIKE', '%' . $request->order_code . '%');
                }
                if ($request->has('cod_code') && $request->cod_code) {
                    $query->whereHas('codOrder', function ($q) use ($request) {
                        $q->where('order_code', 'LIKE', '%' . $request->cod_code . '%');
                    });
                }
                if ($request->has('phone') && $request->phone) {
                    $query->whereHas('address', function ($q) use ($request) {
                        $q->where('phone', 'LIKE', '%' . $request->phone . '%');
                    });
                }
                if ($request->has('cod_compare_status')) {
                    $query->where('approve', $request->cod_compare_status);
                }
                if ($request->type === 'shipping') {
                    $query->whereIn('status', [OrderStatus::PROCESSING, OrderStatus::PENDING_CANCEL]);
                }
                if ($request->type === 'refunded') {
                    $query->where('status', OrderStatus::REFUND);
                }
                if ($request->type === 'successfuled') {
                    $query->where('shipping_status', 'Thành công');
                    $query->where('status', OrderStatus::SUCCESS);
                }

                if ($request->has('include')) {
                    $query->whereIn('id', $request->include);
                }
            })
            // ->orWhere('customer_parent_id', $customer->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($order) use ($isDraft) {
                $codOrderStatus = @$order->codOrder->status;
                if (!$isDraft) {
                    $codOrder = $order->codOrder;
                    $shippingId = $codOrder ? $codOrder->so_id : 0;
                    $shipping = CODOrdersShipping::find($shippingId);
                    $isAbleToCancel = $order->status == 1 && (!$shipping || ($shipping && $shipping->status == 1));
                } else {
                    $isAbleToCancel = $order->status == 1;
                }
                return [
                    "id" => $order->id,
                    "code" => $order->code,
                    "created_at" => Carbon::parse($order->created_at)->format("d/m/y H:i:s"),
                    "total" => (int)$order->getOrderFeProductsPrice() - $order->discount,
                    "total_for_ctv" => (int)$order->getCTVPriceForOrderFromFE(),
                    "product_names" => $order->orderProducts->map(function ($op) {
                        return $op->product->name . ' x ' . $op->quantity;
                    })->implode(PHP_EOL),
                    "status" => $order->payment_method == Order::PAYMENT_METHOD_COD && @$codOrderStatus
                        ? $this->codOrder->getStatusMessages(@$codOrderStatus)
                        : $order->getStatus(),
                    "compare_status" => $order->approve,
                    "note" => $order->note,
                    'is_able_to_cancel' => $isAbleToCancel,
                ];
            });
        $page = $request->get("page", 1);
        $total = $orders->count();
        $perPage = $request->get("perpage", 15);
        $offSet = ($page - 1) * $perPage;
        $items = $orders->slice($offSet, $perPage)->values();
        $items = new LengthAwarePaginator($items, $total, $perPage, $page, [
            "path" => $request->url(),
            "query" => $request->query()
        ]);
        return response()->json([
            "last_page" => $items->lastPage(),
            "total" => $total,
            "has_more" => $items->hasMorePages(),
            "items" => $items->getCollection()
        ]);
    }

    public function deleteSeriForOrder($product_id, $order_id)
    {
        $order = Order::findOrFail($order_id);

        if (!$order || $order->status > 1) {
            return response()->json(['error' => 'Không thể cập nhật đơn hàng'], 400);
        }

        $product = Product::findOrFail($product_id);

        $seriId = ProductSeri::where('product_id', $product_id)
            ->where('order_id', $order_id)
            ->value('id');

        ProductSeri::where('id', $seriId)->update([
            'order_id' => null,
            'product_id' => null,
            'qr_code_status' => 0,
            'stock_status' => 0,
            'pasted_at' => null,
            'ordered_at' => null,
        ]);

        return response()->json(['success' => 'Xóa seri thành công'], 200);
    }

    public function getListSeri(Request $request)
    {
        $list_series = ProductSeri::where('order_id', null);
        if ($request->has('seri_number')) {
            $list_series = ProductSeri::where('seri_number', 'like', '%' . $request->seri_number . '%')->where('order_id', null);
        }
        $data = $list_series->paginate(10);
        return $data;
    }

    public function addSeriForOrder($product_id, $order_id, $seri_number)
    {
        $product = Product::findOrFail($product_id);
        if ($product) {
            ProductSeri::where('seri_number', $seri_number)->first()->update([
                'order_id' => $order_id,
                'product_id' => $product_id,
                'qr_code_status' => 1,
                'stock_status' => 1,
                'pasted_at' => Carbon::now(),
                'ordered_at' => Carbon::now(),
            ]);
        } else {
            return response()->json(['error' => 'Không thể cập nhật đơn hàng'], 400);
        }
    }


    public function getSubOrders($id)
    {
        $sub_customers = Customer::where('customer_parent_id', $id)->pluck('id');

        $sub_orders = Order::whereIn('customer_id', $sub_customers)->orderBy('id', 'asc')->get();

        return response()->json(['subOrders' => $sub_orders]);
    }


    public function getOrderById($type, $id)
    {
        $uploadSv = new Upload();
        $order = $type === "processing" ? DOrder::findOrFail($id) : Order::findOrFail($id);
        $codOrder = $order->codOrder;
        $address = $order->address;
        $ops = $order->orderProducts->map(function ($op) use ($uploadSv, $order) {
            $product = $op->product;
            $category_ids = $product->category_ids;
            return [
                'p_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'attrs' => $op->attr_texts,
                'featured_image' => $product->featured_image ? $uploadSv->getImagePath($product->featured_image) . '?s=80' : '',
                'quantity' => $op->quantity,
                'price_for_ctv' => (int)$product->getLastestPriceForCustomer($order->customer_id, true) ?? $op->price,
                'price' => (int)$product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true),
                'category_id' => $category_ids,
                'seri' => ProductSeri::where('product_id', $product->id)
                    ->where('order_id', $order->id)
                    ->get([
                        'seri_number',
                        'activation_code'
                    ])->map(function ($item) {
                        return [
                            'serial_number' => $item->seri_number,
                            'activation_code' => $item->activation_code
                        ];
                    }),
            ];
        });
        $customer = $order->customer;
        return response()->json([
            'id' => $order->id,
            'code' => $order->code,
            'created_at' => $order->created_at->format('d-m-Y H:i:s'),
            'discount' => (int)$order->discount,
            'discount_by_cate' => (int)$order->discount_by_cate,
            'subtotal' => (int)$order->subtotal,
            'total' => (int)$order->total,
            'paid' => (int)$order->paid,
            'status' => $order->isCODOrder() && $codOrder ? $codOrder->getStatusMessages() : $order->getStatus(),
            'items' => $ops,
            'shipping' => [
                'fee_amount' => $codOrder ? $codOrder->fee_amount : 0,
                'cod_partner' => $order->cod_partner ? trans('cod_order.' . $order->cod_partner) : "Tự đến lấy hàng",
                'address' => $address ? $address->address : $customer->address,
                'province' => $address ? $address->getProvinceName() : $customer->province,
                'district' => $address ? $address->getDistrictName() : $customer->district,
                'ward' => $address ? $address->getWardName() : $customer->ward,
                'receiver_name' => $address ? $address->name : $customer->name,
                'receiver_phone' => $address ? $address->phone : $customer->phone,
                'order_code' => $codOrder ? $codOrder->order_code : "",
                'email' => $order->customer->email,


            ],
            'payment' => !$order->isCODOrder() || $order->isCODOrderChargeDebt()
                ? ($order->payment_method == Order::PAYMENT_METHOD_PAY_ONLINE ? "Thanh toán online" : "Chuyển khoản ngân hàng")
                : "Thanh toán khi nhận hàng",
            'payment_method' => $order->payment_method,
            'note' => $order->note
        ]);
    }

    public function getOrderByAccessKey($accessKey)
    {
        $order = Order::where('access_key', $accessKey)->first();
        if ($order) {
            return $this->getOrderById(7, $order->id);
        } else {
            return response()->json('', 404);
        }
    }

    public function update($type, $id, Request $request)
    {
        // Currently only updating order note
        $order = $type === "processing" ? DOrder::findOrFail($id) : Order::findOrFail($id);
        $order->update($request->all());

        $event = new OrderSaved($order);
        event($event);

        return response()->json('');
    }

    public function cancel($id, Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $order = DOrder::where('id', $id)
            ->where('customer_id', $customer->id)
            ->where('status', 1)
            ->first();

        try {
            if ($order) {
                $order->status = 4;
                $order->save();
                $lockCommissionAmount = LockCommission::where('order_id', $order->id)
                    ->where('order_code', $order->code)
                    ->sum('amount');
                if ($lockCommissionAmount) {
                    $last = LockCommission::where('customer_id', $order->customer_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $lockCommissionAmount = -$lockCommissionAmount;

                    LockCommission::create([
                        'customer_id' => $order->customer_id,
                        'amount' => $lockCommissionAmount,
                        'order_id' => $order->id,
                        'order_code' => $order->code,
                        'note' => 'Huỷ đơn hàng #' . $order->code,
                        'balance' => ($last ? $last->balance : 0) + $lockCommissionAmount,
                    ]);
                }
                app(DOrderRepository::class)->notify($order);
            }
            return response()->json('OK');
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function cancelOrder($id, Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $order = Order::where('id', $id)
            ->where('customer_id', $customer->id)
            ->where('status', 1)
            ->first();

        try {
            if ($order) {
                $cod = $order->codOrder;
                if ($order->status == 1) {
                    $order->status = 7;
                    $order->save();
                    app(OrderRepository::class)->notify($order);
                    return response()->json('OK');
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể huỷ do đơn đang trên đường vận chuyển'
            ], 400);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function processPayment(Request $request, OrderTransactionRepository $otp, OrderRepository $orderRp)
    {
        $customer = Auth::guard('customer')->user();
        $order = DOrder::where('id', $request->order_id)
            ->where('customer_id', $customer->id)
            ->where('status', 1)
            ->where('payment_method', Order::PAYMENT_METHOD_PAY_ONLINE)
            ->first();

        try {
            if ($order) {
                $payment = app('payment');
                $paymentStatus = $payment->verifyPayment($request->all());
                PaymentHistory::create([
                    'provider' => get_class($payment),
                    'response' => json_encode($request->all()),
                    'message' => @$paymentStatus['mess'] . '',
                    'order_id' => $order->id,
                ]);
                if ($paymentStatus['status'] == 1 && $order->code == $paymentStatus['order_code']) {
                    $amount = $paymentStatus['amount'];
                    $store = $order->store;
                    $bank = Bank::find(@$store->setting['online_receiver_bank']);
                    if ($bank) {
                        $otp->create($order, [
                            [
                                'payment_type' => Transaction::RECEIVED_TYPE,
                                'code' => $paymentStatus['trans_id'],
                                'bank_id' => $bank->id,
                                'amount' => $paymentStatus['amount'],
                                'fee' => 0,
                            ]
                        ]);
                    }
                    $order->paid = $paymentStatus['amount'];
                    $order->save();
                } else {
                    $order->note .= @$paymentStatus['mess'];
                }
                return response()->json($paymentStatus);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể cập nhập đơn hàng'
            ], 400);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function processPayments(Request $request)
    {
        $order = Order::where('access_key', $request->access_key)
            ->where('payment_method', Order::PAYMENT_METHOD_PAY_ONLINE)
            ->first();
        try {
            if ($order) {
                $payment = app(VnpayService::class);
                $paymentStatus = $payment->verifyPayment($request->all());
                if ($paymentStatus['status'] == 1 && $request->vnp_ResponseCode == '00') {
                    //                        $customer = Customer::where('id', $order->customer_id)->first();
                    //                        if ($customer->email) {
                    //                            $data = [];
                    //                            $data['name'] = $customer->name;
                    //                            $data['key'] = $order->access_key;
                    //                            $data['address'] = $customer->address . ', ' . $customer->ward . ',' . $customer->district . ', ' . $customer->province;
                    //                            $html = view('emails.order', compact('data'))->render();
                    //                            CustomLAHelper::sendEmail($html, $customer->email, 'Xác nhận đơn đặt hàng tại Gocare.vn!', $customer->name);
                    //                        }
                } else {
                    $order->note .= @$paymentStatus['mess'];
                }
                return response()->json($paymentStatus);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể cập nhập đơn hàng'
            ], 400);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function getPaymentLink($id, Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $order = DOrder::where('id', $id)
            ->where('customer_id', $customer->id)
            ->where('status', 1)
            ->where('payment_method', Order::PAYMENT_METHOD_PAY_ONLINE)
            ->first();

        try {
            if ($order) {
                $payment = app('payment');
                $redirect = $payment->createRedirectLinkForOrder($order, $request->payment_method == 'installment');

                return response()->json([
                    'status' => 'success',
                    'redirect_url' => $redirect
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng không thể tiếp tục thanh toán. Vui lòng liên hệ hotline!'
            ], 400);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    public function initPayment(InitPaymentRequest $request): JsonResponse
    {
        // TODO: Optimize code
        return DBFacade::transaction(function () use ($request): JsonResponse {
            try {
                // update product seri
                $updateProductSeriData = $request->only(['name', 'phone', 'email', 'province', 'district', 'ward', 'address']);
                $updateProductSeriData['activated_at'] = \Carbon\Carbon::now();
                ProductSeri::query()->whereIn('id', data_get($request, 'ids'))->update($updateProductSeriData);

                // tạo mới paymenthistories
                $seriNumbers = data_get($request, 'seri_numbers');
                $seriNumbersArray = explode(',', $seriNumbers);
                $total = (int)ProductSeri::query()
                    ->whereIn('seri_number', $seriNumbersArray)
                    ->join('products', 'products.id', '=', 'product_series.product_id')
                    ->sum('retail_price');

                $provider = data_get($request, 'payment');
                $paymentHistory = PaymentHistory::create([
                    'provider' => $provider,
                    'request' => json_encode([
                        'seri_numbers' => $seriNumbers,
                        'total' => $total
                    ])
                ]);

                $method = "handlePayment{$provider}";
                return $this->{$method}($paymentHistory, $total);
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());
                return response()->json([
                    'message' => [
                        'payment' => ['Có lỗi xảy ra, vui lòng liên hệ admin']
                    ]
                ], 400);
            }
        });
    }

    private function handlePayment($paymentMethod)
    {
        if ($paymentMethod === 'vnpay') {
            // Giả sử bạn có một phương thức hoặc cách để lấy mã lỗi từ VNPay
            $vnpayResponseCode = $this->getVnPayResponseCode();
            $vnpaySuccessCodes = [0]; // Giả sử mã lỗi 0 là thành công cho VNPay
            if (in_array($vnpayResponseCode, $vnpaySuccessCodes)) {
                return true; // Thanh toán thành công
            } else {
                return false; // Thanh toán không thành công
            }
        } elseif ($paymentMethod === 'viettel') {
            // Giả sử bạn có một phương thức hoặc cách để lấy mã lỗi từ Viettel
            $viettelResponseCode = $this->getViettelResponseCode();
            $viettelSuccessCodes = ['00', '01']; // Giả sử mã lỗi '00' và '01' là thành công cho Viettel
            if (in_array($viettelResponseCode, $viettelSuccessCodes)) {
                return true; // Thanh toán thành công
            } else {
                return false; // Thanh toán không thành công
            }
        } else {
            // Phương thức thanh toán không hợp lệ
            return false;
        }
    }

    private function getVnPayResponseCode()
    {
        // Giả định rằng bạn có một phương thức để lấy mã lỗi từ VNPay
        // Code thực tế sẽ gọi API của VNPay hoặc xử lý kết quả trả về từ VNPay dựa trên cách tích hợp cụ thể
        // Ví dụ: 
        // $response = $vnpayApi->getResponse();
        // $responseCode = $response['response_code'];
        // return $responseCode;
        // Ở đây, tôi giả định mã lỗi được trả về từ VNPay
        return 0; // Mã lỗi 0 cho VNPay là thành công
    }

    private function getViettelResponseCode()
    {
        // Tương tự, bạn cũng cần một phương thức để lấy mã lỗi từ Viettel
        // Code thực tế sẽ gọi API của Viettel hoặc xử lý kết quả trả về từ Viettel dựa trên cách tích hợp cụ thể
        // Ví dụ:
        // $response = $viettelApi->getResponse();
        // $responseCode = $response['response_code'];
        // return $responseCode;
        // Ở đây, tôi giả định mã lỗi được trả về từ Viettel
        return '00'; // Mã lỗi '00' cho Viettel là thành công
    }


    public function vnpayPayment(Request $request)
    {
        //\Log::error($request->all());
        $paymentHistory = PaymentHistory::find($request->vnp_TxnRef);
        //dd($paymentHistory);
        if ($paymentHistory) {
            $paymentHistory->response = json_encode($request->all());
            $paymentHistory->save();
            $payment = app(VnpayService::class);
            $paymentStatus = $payment->verifyPayment($request->all());
            if (isset($paymentStatus) && $paymentStatus['status'] === 1) {
                $paymentHistory->trans_id = $paymentStatus['trans_id'];
                $paymentHistory->status = $paymentStatus['status'] == 1 ? 3 : 4;
            } else {
                $paymentHistory->message = 'Checksum không hợp lệ hoặc giao dịch không thành công';
            }
            $paymentHistory->save();
            if ($paymentHistory->status == 3) {
                if ($paymentHistory->order_id) {
                    $order = Order::find($paymentHistory->order_id);
                    $order->paid = $request->trans_amount;
                    $order->unpaid = $order->total - $order->paid;
                    $order->save();
                }
                $activationService = app(ViettelActivationService::class);
                $requested = json_decode($paymentHistory->request, true);

                if (@$requested['seri_numbers']) {
                    $codes = ProductSeri::whereIn('seri_number', explode(',', $requested['seri_numbers']))
                        ->pluck('activation_code')
                        ->toArray();
                    try {
                        $result = $activationService->activate($codes);
                        \Log::error($result);
                        foreach ($result as $item) {
                            if (@$item['status'] == 'ok') {
                                ProductSeri::where('activation_code', $item['code'])
                                    ->update([
                                        'status' => 1,
                                        'purchased_date' => Carbon::now(),
                                    ]);
                            }
                        }
                    } catch (\Exception $exception) {
                        \Log::error($exception->getMessage());
                        \Log::error($exception->getTraceAsString());
                    }
                }
            }
            return response()->json([
                'status' => '1',
                'mess' => ''
            ]);
        }
        return response()->json([
            'status' => '0',
            'mess' => 'Có lỗi xảy ra, vui lòng liên hệ admin'
        ]);
    }

    public function getPaymentHistory(Request $request)
    {
        $payment = PaymentHistory::find($request->payment_id);

        return [
            'status' => @$payment->status
        ];
    }

    public function getChildOrder(Request $request)
    {
    }
}
