<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Events\OrderSaved;
use App\Exceptions\CODException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Address;
use App\Models\AttributeValue;
use App\Models\Bank;
use App\Models\CODOrder;
use App\Models\Config;
use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Models\DOrder;
use App\Models\DraftOrder;
use App\Models\DraftOrderProduct;
use App\Models\OrderProduct;
use App\Models\Group;
use App\Models\LockCommission;
use App\Models\Commission;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCombo;
use App\Models\ProductGroupAttributeMedia;
use App\Models\ProductSeri;
use App\Models\StoreProduct;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\CODOrdersShipping;
use App\Models\PaymentHistory;
use App\Repositories\AttributeValueRepository;
use App\Repositories\DOrderRepository;
use App\Repositories\StoreRepository;
use App\Services\SwitchProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Datatable\Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Order;
use App\Models\Store;
use App\Repositories\CODOrderRepository;
use App\User;
use App\Repositories\OrderProductRepository;
use App\Repositories\CustomerProductDiscountRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderTransactionRepository;
use App\Repositories\CustomerBacklogRepository;
use App\Repositories\ProductSeriesRepository;
use App\Repositories\TransportOrderProductRepository;
use App\Repositories\TransportOrderRepository;
use Illuminate\Support\Facades\View;
use Zizaco\Entrust\Entrust;
use App\Observes\OrderProductObserve;

class OrdersController extends Controller
{
    protected $orderRp;
    protected $orderProductRp;
    protected $cpdRp;
    protected $orderTransactionRp;
    protected $productSeriesRp;
    protected $customerBacklogRp;
    protected $codOrderRp;
    protected $storeRp;
    protected $orderFrom;
    protected $transportOrderProductRp;
    protected $transportOrderRp;
    public $show_action = true;
    public $view_col = 'code';
    public $listing_cols = ['id', 'store_id', 'code', 'customer_id', 'type', 'sub_type', 'number_of_products', 'subtotal', 'fee', 'discount', 'total', 'paid', 'unpaid', 'current_debt', 'status', 'approve', 'approver_id', 'shipping_status', 'note', 'created_at', 'cod_compare_status'];
    protected $extendColumns = [
        'cod_status' => [
            'colname' => 'cod_status',
            'label' => 'Trạng thái vận chuyển'
        ]
    ];

    public function __construct(
        OrderRepository $orderRp,
        OrderProductRepository $orderProductRp,
        CustomerProductDiscountRepository $cpdRp,
        OrderTransactionRepository $orderTransactionRp,
        ProductSeriesRepository $productSeriesRp,
        CustomerBacklogRepository $customerBacklogRp,
        CODOrderRepository $codOrderRp,
        StoreRepository $storeRepository,
        TransportOrderProductRepository $transportOrderProductRp,
        TransportOrderRepository $transportOrderRp
    ) {
        // Field Access of Listing Columns
        if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
            $this->middleware(function ($request, $next) {
                $this->listing_cols = ModuleFields::listingColumnAccessScan('Orders', $this->listing_cols);
                return $next($request);
            });
        } else {
            $this->listing_cols = ModuleFields::listingColumnAccessScan('Orders', $this->listing_cols);
        }
        $this->orderRp = $orderRp;
        $this->orderProductRp = $orderProductRp;
        $this->cpdRp = $cpdRp;
        $this->orderTransactionRp = $orderTransactionRp;
        $this->productSeriesRp = $productSeriesRp;
        $this->customerBacklogRp = $customerBacklogRp;
        $this->codOrderRp = $codOrderRp;
        $this->storeRp = $storeRepository;
        $this->transportOrderProductRp = $transportOrderProductRp;
        $this->transportOrderRp = $transportOrderRp;
        $this->orderFrom = request('from');
        if ($this->orderFrom) {
            $this->listing_cols = ['id', 'store_id', 'code', 'customer_id', 'type', 'sub_type', 'number_of_products', 'subtotal', 'fee', 'discount', 'total', 'paid', 'unpaid', 'amount_charged_to_debt', 'current_debt', 'status', 'approve', 'approver_id', 'shipping_status', 'note', 'created_at', 'cod_compare_status'];
        }
    }

    protected function hasAccess($permisson = '')
    {
        $module = Module::get('Orders');
        $dOrder = Module::get('dorders');

        return \request('d') == 1
            ? Module::hasAccess($dOrder->id)
            : Module::hasAccess($module->id);
    }

    /**
     * Display a listing of the Orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrderStatus $orderStatus)
    {
        $module = Module::get('Orders');
        if ($this->hasAccess()) {
            $approve = $orderStatus->getApprove();
            $orderStatus = $orderStatus->get();
            $provinces = \App\Models\Province::get(['name', 'id']);
            if ($this->orderFrom) {
                $this->listing_cols = array_merge($this->listing_cols, array_keys($this->extendColumns));
                $module->fields = array_merge($module->fields, $this->extendColumns);
            }
            $sessionKey = uniqid();
            return View('la.orders.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => $this->listing_cols,
                'module' => $module,
                'orderStatus' => $orderStatus,
                'approve' => $approve,
                'provinces' => $provinces,
                'sessionKey' => $sessionKey
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created order in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {
        if ($this->hasAccess('create')) {
            try {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $errors = [];
                    if ($customer->ownedStore) {
                        $from = $request->type == Order::TYPE_IMPORT
                            ? $customer->ownedStore->id
                            : $customer->store_id;
                    } else {
                        $from = $request->type == Order::TYPE_EXPORT
                            ? $customer->store_id
                            : 0;
                    }
                    $groupAttrErrors = [];
                    if ($from) {
                        $combos = [];
                        foreach ($request->products as $product) {
                            $productId = $product['product_id'];
                            if (!empty(@$product['attr_ids'])) {
                                $ave = StoreProductGroupAttributeExtra::where('attribute_value_ids', implode(',', $product['attr_ids']))
                                    ->where('store_id', $customer->store_id)
                                    ->where('product_id', $productId)
                                    ->first();
                                if ($ave && $ave->n_quantity < @$product['n_quantity']) {
                                    $groupAttrErrors[] = ['product_id' => $product['product_id'], 'attrs' => $ave->attribute_value_texts];
                                }
                            } else {
                                $left = StoreProduct::where('store_id', $from)
                                    ->where('product_id', $productId)
                                    ->where(function ($q) use ($product) {
                                        $q->where('n_quantity', '<', (int) @$product['n_quantity'])
                                            ->orWhere('w_quantity', '<', (int) @$product['w_quantity']);
                                    })->exists();
                                if ($left) {
                                    $errors[] = $productId;
                                }
                            }
                            if (@$product['combo_id']) {
                                $combos[$product['combo_id']][] = $product;
                            }
                        }
                    }
                    if (!empty($groupAttrErrors)) {
                        $mess = [];
                        foreach ($groupAttrErrors as $groupAttrError) {
                            $p = Product::find($groupAttrError['product_id']);
                            $mess[] = 'Sản phẩm ' .  $p->name . ' ( ' . $groupAttrError['attrs'] . ' ) số lượng trong kho không đủ';
                        }

                        return response()->json(['products' => $mess], 422);
                    }

                    if (!empty($errors)) {
                        $products = Product::whereIn('id', $errors)
                            ->pluck('name', 'id')
                            ->map(function ($name) {
                                return 'Sản phẩm ' . $name . ' số lượng trong kho không đủ';
                            })
                            ->toArray();

                        return response()->json(['products' => array_values($products)], 422);
                    }

                    $series = [];
                    foreach ($request->products as $product) {
                        if (!empty($product['series'])) {
                            $series = array_merge($series, $product['series']);
                        }
                    }
                    $seriesWithoutDuplicates = array_unique($series);

                    if (count($seriesWithoutDuplicates) != count($series)) {
                        $duplicates = array_intersect($series, $seriesWithoutDuplicates);
                        $duplicates = array_unique($duplicates);
                        $series = ProductSeri::whereIn('id', $duplicates)
                            ->get()
                            ->implode('seri_number', ',');

                        return response()->json(['series' => ['Mã Serie ' . $series . ' đang được sử dụng nhiều lần']], 422);
                    }
                }
                \Illuminate\Support\Facades\DB::beginTransaction();
                $request->merge([
                    'created_at' => Carbon::createFromFormat('Y/m/d', $request->created_at)->format('Y-m-d') . date(' H:i:s'),
                ]);
                $insert_id = Module::insert("Orders", $request);
                $order = Order::find($insert_id);
                $this->orderProductRp->createForOrder($request->products, $order);
                $this->productSeriesRp->processForNewOrder($order, $request->products);
                $this->cpdRp->save($request->products, $request->customer_id);

                $this->orderRp->updateAmount($order);
                $this->orderRp->updateCODPriceStatement($order);
                $order = $order->fresh();
                $this->customerBacklogRp->processForCreateOrder($order);
                $order->current_debt = $order->customer->debt_total;
                if ($request->get('d_order_id')) {
                    $dOrderId = DOrder::find($request->get('d_order_id'));
                    $address = Address::find($dOrderId->address_id);
                    $order->order_from = $dOrderId->order_from;
                    $order->address_id = @$address->id;
                    $order->cod_price_statement = $dOrderId->cod_price_statement;
                    $order->voucher_id = $dOrderId->voucher_id;
                    $order->save();
                    $crossStore = json_decode($dOrderId->cross_store ?: '', true);
                    if (@$crossStore['from_store_id']) {
                        $order->cross_store = $order->id;
                        $order->save();
                        $this->orderRp->createImportOrderForOnlineOrder($crossStore['from_store_id'], $customer->store_id, $dOrderId, $order->id);
                    }
                    if ($address) {
                        $request->merge($address->toArray());
                        $request->merge([
                            'service_id' => $dOrderId->cod_service_id,
                            'countFeeCustomer' => $dOrderId->cod_charge_fee_customer,
                            'payment_method' => $dOrderId->payment,
                            'cod_partner_store_id' => @$crossStore['cod_partner_store_id'],
                            'cod_tag' => $dOrderId->cod_tag,
                        ]);
                        if ($dOrderId->cod_partner == 'other' && $dOrderId->codOrder) {
                            $codData = array_except($dOrderId->codOrder->toArray(), [
                                'id',
                                'order_type',
                                'created_at',
                                'updated_at',
                            ]);
                            $codData['real_amount'] = $dOrderId->codOrder['fee_amount'];
                            $codData['customer_id'] = $order->customer_id;
                            $codData['additional_data'] = '';
                            $order->codOrder()->create($codData);
                            $dOrderId->codOrder->delete();
                        } else {
                            $this->codOrderRp->createBillLadingForFE($order, $request);
                        }
                        // Create online customer
                        $order->onlineCustomer()->create([
                            'address_id' => $address->id,
                            'store_id' => $order->store_id,
                            'name' => $address->name,
                            'phone' => $address->phone,
                            'email' => $address->phone . '@azpro.net.vn',
                            'address' => $address->full_address
                        ]);
                    }
                    if (!$dOrderId->self_cod_service) {
                        $receiver = $dOrderId->getCommissionReceiver();
                        $lockCommission = LockCommission::where('customer_id', $receiver)
                            ->where('order_id', $dOrderId->id)
                            ->where('order_code', $dOrderId->code)
                            ->first();
                        if ($lockCommission) {
                            $order->save();
                            $commission = $order->getCommission();
                            $codFee = $order->codOrder ? $order->codOrder->real_amount : 0;

                            $lockCommission->update([
                                'order_id' => $order->id,
                                'order_code' => $order->code,
                            ]);
                            if ($lockCommission->amount != ($commission - $codFee)) {
                                $receiver = $order->getCommissionReceiver();
                                $last = LockCommission::where('customer_id',$receiver)
                                    ->orderBy('created_at', 'desc')
                                    ->orderBy('id', 'desc')
                                    ->first();
                                LockCommission::create([
                                    'customer_id' => $receiver,
                                    'amount' => ($commission - $codFee) - $lockCommission->amount,
                                    'order_id' => $order->id,
                                    'order_code' => $order->code,
                                    'note' => 'Cập nhập hoa hồng và vận chuyển cho đơn hàng #' . $order->code . ' ( Vận chuyển: ' . number_format($codFee) . 'đ)',
                                    'balance' => ($last ? $last->balance : 0) + ($commission - $codFee) - $lockCommission->amount,
                                ]);
                            }
                        }
                    } else {
                        $last = Commission::where('customer_id', $dOrderId->customer_id)
                            ->orderBy('created_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();
                        Commission::create([
                            'customer_id' => $order->customer_id,
                            'amount' => -$order->subtotal,
                            'order_id' => $order->id,
                            'note' => 'Tiền hàng từ đơn hàng #' . $order->code,
                            'balance' => ($last ? $last->balance : 0) - $order->subtotal,
                        ]);
                    }
                    $order->self_cod_service = $dOrderId->self_cod_service;
                    $dOrderId->delete();
                }
                $order->save();
                $this->orderTransactionRp->create($order, $request->payment);
                if ($request->has('draft_order_id') && $draftOrder = DraftOrder::find($request->draft_order_id)) {
                    $draftOrder->deleteDraftOrder();
                }
                if ($request->has('use_transport')) {
                    $transport = $request->transport;
                    $transportOrder = $this->transportOrderRp->updateOrCreate($order, $transport);
                    if ($transportOrder) {
                        $this->transportOrderProductRp->create($transportOrder, $transport['products']);
                    }
                }
                \Illuminate\Support\Facades\DB::commit();

                if ($order->customer->ownedStore) {
//                    $this->orderRp->syncCopier($order);
                }
                $event = new OrderSaved($order);
                event($event);
                return response()->json('OK');
            } catch (CODException $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return response()->json(['error' => [$exception->getMessage()]], 422);
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
            }
        } else {
            return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
        }
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Module::hasAccess("Orders", "view")) {

            $order = Order::find($id);
            if (isset($order->id)) {
                $module = Module::get('Orders');
                $module->row = $order;
                $payment = $order->getPaymentInfo();
                $productSeries = ProductSeri::where('order_id', $order->id)->paginate();
                $transactions = $order->transactions;
                return view('la.orders.show', [
                    'module' => $module,
                    'view_col' => $this->view_col,
                    'no_header' => true,
                    'no_padding' => "no-padding",
                    'orderStatus' => app(OrderStatus::class),
                    'payment' => $payment,
                    'productSeries' => $productSeries,
                    'transactions' => $transactions,
                ])->with('order', $order);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("order"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, OrderStatus $orderStatus, Request $request)
    {
        if ($this->hasAccess('edit')) {
            $order = $request->get('d') ? DOrder::find($id) : Order::find($id);
            if ($order->copier_id) {
                return redirect(config('laraadmin.adminRoute') . "/orders/" . $order->copier_id . '/edit');
            }
            if (isset($order->id)) {
                $module = Module::get('Orders');
                $module->row = $order;
                $approve = $orderStatus->getApprove();
                $orderStatus = $orderStatus->get();
                $payment = $order->getPaymentInfo();
                $transactions = $order->transactions;
                $provinces = \App\Models\Province::get(['name', 'id']);
                $codOrder = $order->codOrder;
                if ($order->isCODOrder() && !$codOrder) {
                    $billLadingHtml = $this->codOrderRp->getBillLadingHTML($order);
                } elseif ($order->isCODOrder() && $codOrder) {
                    $billLadingHtml = $this->codOrderRp->renderUpdateStatusForm($codOrder);
                } else {
                    $billLadingHtml = "";
                }
                $transport = $order->transportOrder;
                $existedIndex = $order->orderProducts->map(function () {
                    return uniqid();
                });

                return view('la.orders.edit', [
                    'module' => $module,
                    'view_col' => $this->view_col,
                    'orderStatus' => $orderStatus,
                    'payment' => $payment,
                    'transactions' => $transactions,
                    'approve' => $approve,
                    'provinces' => $provinces,
                    'codOrder' => $codOrder,
                    'billLadingHtml' => $billLadingHtml,
                    'transport' => $transport,
                    'existedIndex' => $existedIndex
                ])->with('order', $order);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("order"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, OrderRequest $request)
    {
        if ($request->get('d')) {
            if ($request->get('save_draft')) {
                $result = $this->updateDraft($id, $request);
                if ($result->getStatusCode() == 200) {
                    return redirect(config('laraadmin.adminRoute') . "/orders?from=2&d=1");
                } else {
                    return $result;
                }
            } else {
                $dOrder = DOrder::find($id);
                if ($dOrder->status == 1 && $dOrder->isReadyCreateOrder()) {
                    $request->merge([
                        'd_order_id' => $id,
                        'cod_partner' => $dOrder->cod_partner,
                        'order_from' => $dOrder->order_from
                    ]);
                    $result = $this->store($request);
                    if ($result->getStatusCode() == 200) {
                        $orderId = Order::orderBy('id', 'desc')
                            ->first();
                        return redirect(config('laraadmin.adminRoute') . "/orders/" . $orderId->id);
                    }
                }
            }

            return redirect()->back()->withErrors(json_decode($result->getContent(), true));
        }
        if (Module::hasAccess("Orders", "edit")) {
            try {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $errors = [];
                    $order = Order::find($id);
                    $currentProducts = $order->orderProducts->keyBy('product_id');
                    $ownerStore = $customer->ownedStore;
                    $quantityColumn = $order->sub_type == 1 ? 'n_quantity' : 'w_quantity';
                    foreach ($request->products as $product) {
                        $productId = $product['product_id'];
                        $old = @$currentProducts[$productId];
                        $oldQuantity = $quantityColumn == 'n_quantity' ? 'quantity' : $quantityColumn;
                        $changed = $old ? $product[$quantityColumn] - $old->{$oldQuantity} : $product[$quantityColumn];
                        if ($ownerStore) {
                            $from = $request->type == Order::TYPE_IMPORT
                                ? $ownerStore->id
                                : $customer->store_id;


                            if ($request->status == 3) {
                                $from = $ownerStore->id ? $customer->store_id : $ownerStore->id;
                                $changed = $old->{$oldQuantity};
                            } else if ($changed < 0) {
                                $from = $ownerStore->id ? $customer->store_id : $ownerStore->id;
                            }
                        } else {
                            $from = $request->type == Order::TYPE_EXPORT
                                ? $customer->store_id
                                : 0;
                        }
                        $left = StoreProduct::where('store_id', $from)
                            ->where('product_id', $productId)
                            ->where(function ($q) use ($quantityColumn, $changed) {
                                $q->where($quantityColumn, '<', $changed);
                            })->exists();
                        if ($left) {
                            $errors[] = $productId;
                        }
                    }

                    if (!empty($errors)) {
                        $products = Product::whereIn('id', $errors)
                            ->pluck('name', 'id')
                            ->map(function ($name) {
                                return 'Sản phẩm ' . $name . ' số lượng trong kho không đủ';
                            })
                            ->toArray();
                        return redirect()->back()->withErrors(['products' => implode("\n", array_values($products))]);
                    }
                }

                \Illuminate\Support\Facades\DB::beginTransaction();
                $request->merge([
                    'created_at' => Carbon::createFromFormat('Y/m/d', $request->created_at)->format('Y-m-d') . date(' H:i:s'),
                ]);
                $payment = $request->payment ?: [];
                $original = Order::find($id);
                $status = $request->status;
                $request->request->remove('status');
                $insert_id = Module::updateRow("Orders", $request, $id);
                $order = Order::find($insert_id);
                $this->productSeriesRp->processForUpdateOrder($order, $request->products);
                $this->orderProductRp->updateForOrder($request->products, $order);
                $this->cpdRp->save($request->products, $request->customer_id);
                $this->orderRp->updateAmount($order);
                $this->orderRp->updateCODPriceStatement($order);
                $order->status = $status;
                $order->save();
                $this->customerBacklogRp->processForUpdateOrder($order);

                if (!empty($payment)) {
                    $this->orderTransactionRp->update($order, $request->payment);
                }
                if ($request->has('use_transport')) {
                    $transport = $request->transport;
                    $transportOrder = $this->transportOrderRp->updateOrCreate($order, $transport);
                    $this->transportOrderProductRp->update($transportOrder, $transport['products']);
                } else {
                    $this->transportOrderRp->delete($order);
                }

                if ($order->customer->ownedStore) {
                    $this->orderRp->syncCopier($order);
                }

                if (
                    $original->status != $order->status
                    && $order->status == OrderStatus::SUCCESS
                    && !$order->customer->ownedStore
                    && $order->store->observes()->count() > 0
                ) {
                    $orp = app(OrderRepository::class);
                    $orp->cloneOrderForObserver($order);
                }

                $event = new OrderSaved($order);
                event($event);

                \Illuminate\Support\Facades\DB::commit();
                return redirect()->back();
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Remove the specified order from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, OrderStatus $orderStatus, Request $request)
    {
        if (Module::hasAccess("Orders", "delete")) {
            try {
                \Illuminate\Support\Facades\DB::beginTransaction();
                if ($request->get('d')) {
                    $dOrder = DOrder::find($id);
                    $dOrder->delete();
                } else {
                    $order = Order::find($id);

                    if ($orderStatus->isProcessing($order->status)) {
                        return redirect()->back()->withErrors(trans('messages.cannot_delete_processing_order'));
                    }

                    $this->orderTransactionRp->removeExistTransaction($order, []);
                    $this->customerBacklogRp->processForDeleteOrder($order);
                    $this->orderProductRp->removeExistProducts($order, [], $orderStatus->isCancel($order->status));
                    $this->productSeriesRp->processForDeleteOrder($order);
                    $this->transportOrderRp->delete($order);
                    if ($order->isCODOrder()) {
                        $order->codOrder()->delete();
                    }
                    $order->delete();
                }
                \Illuminate\Support\Facades\DB::commit();

                return redirect()->back();
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                \Log::error($exception->getMessage());
                \Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Datatable Ajax fetch
     *
     * @return
     */
    public function dtajax(Request $request, OrderStatus $orderStatus, Order $order)
    {
        Cache::put($request->get('sessionKey'), json_encode($request->all()), 300);

        $values = $this->buildFilterQuery($request);
        $datatable = $this->makeDatatable($values);
        $oClass = $request->get('d') == 1 ? '\App\Models\DOrder' : '\App\Models\Order';
        $orderFrom = $this->orderFrom;
        $out = $datatable->make();
        $data = $out->getData();

        $total = [
            'total_amount' => number_format($values->sum('total')),
            'total_output' => 0
        ];

        if ($request->pc_ids) {
            $total['total_output'] = number_format($values->get()->reduce(function ($total, $order) use ($request) {
                $opTotal = $order->orderProducts()
                    ->whereHas('product.categories', function ($q) use ($request) {
                        $q->whereIn('productcategories.id', $request->pc_ids);
                    })
                    ->sum('total');
                return $total + $opTotal;
            }));
        }


        $fields_popup = ModuleFields::getModuleFields('Orders');

        for ($i = 0; $i < count($data->data); $i++) {
            $id = $data->data[$i][0];
            $order = $oClass::find($id);
            $codOrder = @$order->codOrder;
            for ($j = 0; $j < count($this->listing_cols); $j++) {
                $col = $this->listing_cols[$j];
                if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
                    $data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
                }
                if ($col == $this->view_col && !$request->get('d')) {
                    $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id) . '">' . $data->data[$i][$j] . '</a>';
                }
                if ($col === 'id') {
                    $data->data[$i][0] = '<input type="checkbox" class="row" value="' . $id . '"/>' . $id;
                } else if ($col == "type") {
                    $data->data[$i][$j] = $order->getTypeHTMLFormatted($data->data[$i][$j]);
                } else if ($col == "sub_type") {
                    $data->data[$i][$j] = $order->getSubTypeHTMLFormatted($data->data[$i][$j]);
                } else if ($col == "created_at") {
                    $data->data[$i][$j] = Carbon::parse($data->data[$i][$j])->format('d/m/Y H:i');
                } else if (in_array($col, ['fee', 'total', 'subtotal', 'discount', 'paid', 'unpaid', 'current_debt', 'amount_charged_to_debt'])) {
                    $currency = $order->currency_type;
                    $decimal = $currency == Bank::CURRENCY_NDT ? 2 : 0;
                    $symbol = $currency == Bank::CURRENCY_NDT ? ' NDT' : 'đ';
                    $colVal = $data->data[$i][$j];
                    if ($order->isFromFE()) {
                        if ($col == "fee" && $codOrder) {
                            $colVal = $codOrder->fee_amount;
                        }
                        if ($col == "total") {
                            $colVal = $order->getOrderFeProductsPrice() - $order->discount;
                        }
                    }
                    $data->data[$i][$j] = number_format($colVal, $decimal) . $symbol;

                    if ($order->isFromFE() 
                        && $order->payment_method == Order::PAYMENT_METHOD_PAY_ONLINE
                        && $col == 'paid'
                        && $colVal == 0 
                        && $request->get('d') 
                        && $paymentHistory = PaymentHistory::where('order_id', $order->id)->get()->implode('message', "\n")) {
                            $data->data[$i][$j] = '<span class="text-danger">Thanh toán Online thất bại</span> <button class="btn btn-secondary btn-xs" type="button" data-content="'. $paymentHistory .'" data-toggle="modal" data-target="#PaymentResult" ><i class="fa fa-eye"></i></button>';
                    }
                } else if ($col == "status") {
                    if (!$request->get('d')) {
                        if ($orderStatus->isEditable($data->data[$i][$j])) {
                            $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/approve-order?from=' . $orderFrom . '&type=status&ids=' . $id) . '">' . $orderStatus->getStatusHTMLFormatted($data->data[$i][$j]) . "</a>";
                        } else {
                            $data->data[$i][$j] = $orderStatus->getStatusHTMLFormatted($data->data[$i][$j]);
                        }
                    } else {
                        $data->data[$i][$j] = $data->data[$i][$j] == 1 ? 'Đơn nháp' : 'Đã huỷ';
                    }
                } else if ($col == "approve") {
                    if ($orderStatus->isApproveable($data->data[$i][$j])) {
                        $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/approve-order?type=approve&ids=' . $id) . '">' . $orderStatus->getApproveHTMLFormatted($data->data[$i][$j]) . "</a>";
                    } else {
                        $data->data[$i][$j] = $orderStatus->getApproveHTMLFormatted($data->data[$i][$j]);
                    }
                } else if ($col == "approver_id") {
                    $user = User::find($data->data[$i][$j]);
                    $data->data[$i][$j] = $user ? $user->name : null;
                } else if ($col == "cod_compare_status") {
                    if ($codOrder) {
                        $data->data[$i][$j] = $codOrder->getCompareStatusLabelHTML();
                    }
                } else if ($col == "shipping_status" && $request->get('d') && $order->cross_store) {
                    $crossStore = json_decode($order->cross_store, true);
                    $store = Store::find(@$crossStore['from_store_id']);
                    $data->data[$i][$j] = 'Gửi từ ' . $store->name . '-' . $order->cod_partner . '-' . $crossStore['cod_partner_store_id'];
                } else if ($col == "shipping_status" && $request->get('d') && $order->cod_partner == 'other') {
                    $data->data[$i][$j] = 'Vận chuyển khác';
                }
            }

            if ($this->show_action) {
                $output = '';
                if (!$request->get('d')) {
                    if ($order->self_cod_service && $orderStatus->isSuccess($order->status) && !Order::where('copier_id', $order->id)->first()) {
                        $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id . '/create-refund-order') . '" class="btn btn-warning btn-xs onetime-click" style="display:inline;padding:2px 5px 3px 5px;">Tạo đơn hoàn</a>';
                    }
                    $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id . '/print') . '" class="btn btn-warning btn-xs print-order onetime-click" style="display:inline;padding:2px 5px 3px 5px;">IN</a>';
                    $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                    if (!$orderStatus->isProcessing($order->status)) {
                        $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.orders.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']);
                        $output .= ' <button class="btn btn-danger btn-xs form-confirmation" type="submit"><i class="fa fa-times"></i></button>';
                        $output .= Form::close();
                    }
                    $data->data[$i][] = (string)$output;
                } else {
                    $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id . '/print?d=1') . '" class="btn btn-warning btn-xs print-order onetime-click" style="display:inline;padding:2px 5px 3px 5px;">IN</a>';
                    if ($order->status == 1) {
                        $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id . '/edit?d=1') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                        if ($order->isReadyCreateOrder()) {
                            $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $id . '/create-from-draft?d=1') . '" class="btn btn-success btn-xs" style="display:inline;padding:2px 5px 3px 5px;">Tạo đơn</a>';
                        }
                    }
                    if (Module::hasAccess("Orders", "delete")) {
                        $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.orders.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']);
                        $output .= '<input type="hidden" name="d" value="1"/><button class="btn btn-danger btn-xs form-confirmation" type="submit"><i class="fa fa-times"></i></button>';
                        $output .= Form::close();
                    }
                    $data->data[$i][] = (string)$output;
                }
            }
        }
        $data->total = $total;

        $out->setData($data);
        return $out;
    }

    protected function buildFilterQuery(Request $request, $orderBy = 'desc')
    {
        $orderFrom = $this->orderFrom;
        $electronicGroups = Group::getElectronicGroup();
        $oClass = $request->get('d') == 1 ? '\App\Models\DOrder' : '\App\Models\Order';
        $values = $oClass::select($this->listing_cols)
            ->search($request->all())
            ->orderBy('id', $orderBy)
            ->whereNull('deleted_at')
            ->where('code', 'not like', '%_hh_%');
        $crossStore = $request->cross_store;
        if ($crossStore == 1) {
            $values->whereNotNull('cross_store');
        } else if (!$crossStore && $this->orderFrom == 1) {
            $values->whereNull('cross_store');
        }

        if (!$request->get('d') && !$crossStore) {
            $values->where(function ($query) use ($orderFrom, $electronicGroups, $crossStore) {
                // split electronic orders from admin orders
                // if orders from frontend => get orders from electronic groups
                // else exclude orders from frontend and electronic groups
                $clause = 'whereDoesntHave';
                if ($orderFrom) {
                    $clause = 'whereHas';
                    $query->where('order_from', $orderFrom);
                }
                $query->{$clause}('customer', function ($q) use ($electronicGroups) {
                    $q->whereIn('group_id', $electronicGroups);
                });
            });
        }

        if (!\Zizaco\Entrust\EntrustFacade::hasRole('SUPER_ADMIN')) {
            $customerIds = Customer::where('customer_currency', '<>', 1)->pluck('id')->toArray();
            $values->whereNotIn('customer_id', $customerIds);
        }

        if ($request->products) {
            $values->whereHas('orderProducts', function ($q) use ($request) {
                $q->whereIn('product_id', $request->products);
            });
        }

        if ($request->azpro) {
            $values->whereHas('azOrder', function ($q) use ($request) {
                $q->where('type', $request->azpro);
            });
        }

        if ($request->cod_partner) {
            $values->where('cod_partner', $request->cod_partner);
        }

        if ($request->payment_method) {
            $values->where('payment_method', $request->payment_method);
        }

        if ($request->cod_order_code) {
            $billCods = array_map('trim', explode(',', $request->cod_order_code));
            $billCods = array_filter($billCods);
            $values->whereHas('codOrder', function ($q) use ($billCods) {
                $q->whereIn('order_code', $billCods);
            });
        }

        if ($request->pc_ids) {
            $values->whereHas('products.categories', function ($q) use ($request) {
                $q->whereIn('productcategories.id', $request->pc_ids);
            });
        }

        return $values;
    }

    protected function makeDatatable($queryBuilder)
    {
        $orderFrom = $this->orderFrom;
        $datatable = Datatables::of($queryBuilder);
        if ($orderFrom) {
            $datatable->addColumn('cod_status', function (Order $order) {
                $codOrder = $order->codOrder;
                return $codOrder && $codOrder->status
                    ? $codOrder->getStatusMessages()
                    : "";
            });
            $datatable->filterColumn('cod_compare_status', function ($query, $keyword) {
                if ($keyword !== 'none') {
                    $clause = $keyword != 2 ? 'whereExists' : 'whereNotExists';
                    $query->{$clause}(function ($q) use ($keyword) {
                        $q->select(DB::raw(1))
                            ->from('cod_orders')
                            ->whereRaw('orders.id = cod_orders.order_id');
                        if ($keyword != 2) {
                            $q->where('cod_orders.compare_status', $keyword);
                        }
                    });
                }
            });
        }
        $datatable->filterColumn('cu.store_id', function ($query, $keyword) {
            $query->where('store_id', $keyword);
        });
        $datatable->filterColumn('customer_id', function ($query, $keyword) {
            $query->where('customer_id', $keyword);
        });

        return $datatable;
    }

    public function updateStatus($id, Request $request, OrderStatus $orderStatus)
    {
        $this->validate($request, [
            'status' => 'sometimes|required|in:' . implode(',', array_keys($orderStatus->get())),
            'approve' => 'sometimes|required|in:' . implode(',', array_keys($orderStatus->getApprove()))
        ]);
        $order = Order::find($id);
        if ($order) {
            if (isset($request->status) && $this->authorize('update-status', $order)) {
                $order->status = $request->status;
            }
            if (isset($request->approve) && $this->authorize('approve-order', $order)) {
                $order->approve = $request->approve;
            }

            $order->save();
            return back();
        }

        abort(401);
    }

    public function switchProduct(Request $request, SwitchProduct $switchProduct)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id,status,1',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:1,2',
            'fee_type' => 'required|in:1,2',
        ]);

        $product = Product::find($request->product_id);
        if (($request->type == 1 && $product->n_quantity < $request->quantity)
            || ($request->type == 2 && $product->w_quantity < $request->quantity)
        ) {
            return back()->withErrors([
                'product' => [trans('messages.product_quantity_not_enough')]
            ])->withInput($request->all());
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $switchProduct->switchProductForCustomer(
                $request->customer_id,
                $request->product_id,
                $request->quantity,
                $request->type,
                $request->fee_type == 2 ? $request->payment : [],
                $request->transaction_type,
                $request->note
            );
            \Illuminate\Support\Facades\DB::commit();
            //delete all order products
            return redirect()->route(config('laraadmin.adminRoute') . '.orders.index');
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollback();
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            return redirect()->back()->withErrors(trans('messages.cannot_save'));
        }
    }

    public function printOrder($id, Bank $bank, Request $request)
    {
        $order = $request->get('d') == 1 ? DOrder::find($id) : Order::find($id);
        if (isset($order->id)) {
            $store = $order->store;
            $customer = $order->customer;
            $banks = $bank->getBankByAccName([], $customer->store_id);
            $symbol = $customer->customer_currency == Bank::CURRENCY_NDT ? ' NDT' :  'đ';
            return view('la.orders.print', [
                'orderStatus' => app(OrderStatus::class),
                'store' => $store,
                'banks' => $banks,
                'customer' => $customer,
                'symbol' => $symbol
            ])->with('order', $order);
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("order"),
            ]);
        }
    }

    public function printTransport(Request $request)
    {
        $this->validate($request, [
            'store_id' => 'required|exists:stores,id,deleted_at,NULL',
            'receiver_id' => 'required|exists:customers,id,deleted_at,NULL',
            'products' => 'required'
        ]);
        $store = Store::find($request->store_id);
        $transportPartner = Customer::find(@$request->partner_id);
        $receiver = Customer::find($request->receiver_id);
        $transport = (object) [
            'total' => $request->total,
            'transport_price' => $request->transport_price
        ];
        $products = array_map(function ($product) {
            $product['total_cubic_meter'] = round(($product['width'] * $product['length'] * $product['height']) * $product['packages'], 3);
            $product['total_weight'] = $product['weight'] * $product['packages'];
            return $product;
        }, $request->products);

        $html = View::make('la.orders.print-transport', [
            'store' => $store,
            'transport' => $transport,
            'transportProducts' => $products,
            'transportPartner' => $transportPartner,
            'receiver' => $receiver,
            'symbol' => 'đ'
        ])->render();
        return response()->json(['html' => $html]);
    }

    public function approve(Request $request)
    {
        $ids = explode(',', $request->ids);
        $type = $request->type;
        $orders = Order::whereIn('id', $ids)
            ->orderBy('id', 'desc')
            ->each(function ($order) use ($type) {
                if ($type === 'status' && $this->authorize('update-status', $order)) {
                    $order->update(['status' => 2]);
                }
                if ($type === 'approve' && $this->authorize('approve-order', $order)) {
                    $order->update(['approve' => 1]);
                }
            });
        return redirect()->back();
    }

    public function getCustomer(Request $request)
    {
        $customers = DB::table('customer_product_discount as cpd')->where('cpd.product_id', $request->product_id)
            ->join('customers as cu', 'cpd.customer_id', '=', 'cu.id')
            ->select(DB::raw('cu.name, cpd.discount, cpd.id'))
            ->paginate(10);

        return view('la.orders.list-user', compact('customers'))->render();
    }

    public function modifyPrice(Request $request)
    {
        CustomerProductDiscount::where('id', $request->id)->update(['discount' => $request->price]);
        return response()->json('');
    }

    public function orderProductSelectedListSeri(Request $request)
    {
        $query = ProductSeri::where('product_id', $request->product_id);
        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->type == Order::TYPE_EXPORT && $request->sub_type == Order::SUB_TYPE_NEW) {
            $query->whereDoesntHave('order', function ($query) {
                $query->where('type', Order::TYPE_EXPORT)->where('sub_type', Order::SUB_TYPE_NEW);
            });
        }
        if ($request->get('q')) {
            $query->where('seri_number', 'LIKE', '%' . $request->get('q') . '%');
        }
        if ($request->get('attr_ids')) {
            $avm = StoreProductGroupAttributeExtra::where('attribute_value_ids', implode(',', $request->get('attr_ids')))
                ->where('product_id', $request->product_id)
                ->first();

            if ($avm) {
                $query->where('group_attribute_id', $avm->id);
            }
        }
        $param = $request->all();
        $isDevice = $request->product_id ? Product::find($request->product_id)->categories->first()->is_devices : 0;
        if (isset($param['qr_code_status']) && !$isDevice) {
            $query->where('qr_code_status', $param['qr_code_status']);
        }
        if (isset($param['status']) && !$isDevice) {
            $query->where('status', $param['status']);
        }
        if (isset($param['stock_status'])) {
            $query->where('stock_status', $param['stock_status']);
        }
        if ($request->excludes) {
            $excludes = explode(',', $request->excludes);
            $query->whereNotIn('id', $excludes);
        }
        if (isset($param['includes'])) {
            $includes = explode(',', $request->get('includes', ''));
            if (!empty($includes)) {
                $query->whereIn('id', $includes);
            } else {
                $query->where('id', -1);
            }
        }

        if ($request->has('order_series_type') && !in_array($request->order_series_type, [1, 3])) {
            return [];
        }

        if ($request->view = 'datatable') {
            $cols = [
                'id', 'seri_number', 'activation_code'
            ];
            $query->select($cols);
            $datatable = Datatables::of($query);
            $out = $datatable->make();
            $data = $out->getData();
            for($i=0; $i < count($data->data); $i++) {
                $row = [];
                for ($j=0; $j < count($cols); $j++) {
                    if ($cols[$j] == 'id') {
                        $data->data[$i][$j] = '<input type="checkbox" value="'. $data->data[$i][$j] .'" />' . $data->data[$i][$j];
                    }
                    $row[$j] = $data->data[$i][$j];
                }
                $data->data[$i] = $row;
            }
            $out->setData($data);

            return $out;
        }

        return $query->select(['seri_number as text', 'id'])->paginate();
    }

    public function orderProductSelectedSeri(Request $request, AttributeValueRepository $repository)
    {
        $view = isset($request->view) ? $request->view : 'la.products_selecting.order_selected_product_seri';
        $requestedProductId = explode(',', $request->product_id);
        $products = Product::whereIn('id', $requestedProductId)->get();
        $selected_series = isset($request->seri)
            ? ProductSeri::whereIn('product_id', $requestedProductId)
            ->whereIn('seri_number', explode(',', $request->seri))
            ->pluck('seri_number', 'id')
            ->toArray()
            : [];
        $selectedAttrs = explode(',', $request->get('attr_ids', ''));
        $attrs = $repository->getAttrs($selectedAttrs);

        return View($view, [
            'index' => $request->index,
            'products' => $products,
            'selected_series' => $selected_series,
            'attrs' => $attrs,
            'selectedAttrs' => $selectedAttrs
        ]);
    }

    public function fetchDraftOrder($id)
    {
        $draftOrder = DraftOrder::find($id);
        if ($draftOrder) {
            $results = $draftOrder->getOrderProductData();
            return response()->json($results);
        }
        return response()->json('Đơn nháp không tồn tại', 404);
    }

    public function saveDraft(Request $request)
    {
        if (Module::hasAccess("Orders", "create")) {
            try {
                \Illuminate\Support\Facades\DB::beginTransaction();
                $request->merge([
                    'created_at' => Carbon::createFromFormat('Y/m/d', $request->created_at)->format('Y-m-d') . date(' H:i:s'),
                ]);
                $insert_id = Module::insert("Orders", $request);
                $order = Order::find($insert_id);
                $order = DOrder::create($order->toArray());
                Order::find($insert_id)->delete();
                $this->orderProductRp->createForOrder($request->products, $order);
                $this->orderRp->updateAmount($order);
                $this->orderRp->updateCODPriceStatement($order);
                $order->save();
                $this->orderTransactionRp->create($order, $request->payment);
                if ($request->has('draft_order_id') && $draftOrder = DraftOrder::find($request->draft_order_id)) {
                    $draftOrder->deleteDraftOrder();
                }
                if ($request->has('use_transport')) {
                    $transport = $request->transport;
                    $transportOrder = $this->transportOrderRp->updateOrCreate($order, $transport);
                    if ($transportOrder) {
                        $this->transportOrderProductRp->create($transportOrder, $transport['products']);
                    }
                }
                \Illuminate\Support\Facades\DB::commit();

                return response()->json('OK');
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
            }
        } else {
            return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
        }
    }

    public function updateDraft($id, Request $request)
    {
        if (Module::hasAccess("Orders", "create")) {
            try {
                \Illuminate\Support\Facades\DB::beginTransaction();
                $request->merge([
                    'created_at' => Carbon::createFromFormat('Y/m/d', $request->created_at)->format('Y-m-d') . date(' H:i:s'),
                ]);
                $insert_id = Module::insert("Orders", $request);
                $newOrder = Order::find($insert_id);
                $order = DOrder::find($id);
                $order->update(array_except($newOrder->toArray(), 'id'));
                Order::find($insert_id)->delete();
                $this->orderProductRp->updateForOrder($request->products, $order);
                $this->orderRp->updateAmount($order);
                $this->orderRp->updateCODPriceStatement($order);
                $order->save();
                $repo = app(DOrderRepository::class);
                $repo->notify($order);
                $this->orderTransactionRp->create($order, $request->payment);
                if ($request->has('draft_order_id') && $draftOrder = DraftOrder::find($request->draft_order_id)) {
                    $draftOrder->deleteDraftOrder();
                }
                if ($request->has('use_transport')) {
                    $transport = $request->transport;
                    $transportOrder = $this->transportOrderRp->updateOrCreate($order, $transport);
                    if ($transportOrder) {
                        $this->transportOrderProductRp->create($transportOrder, $transport['products']);
                    }
                } else {
                    $this->transportOrderRp->delete($order);
                }
                \Illuminate\Support\Facades\DB::commit();

                return response()->json('OK');
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
            }
        } else {
            return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
        }
    }

    public function createFromDraft($id)
    {
        if ($this->hasAccess("create")) {
            try {
                $dOrder = DOrder::find($id);
                if ($dOrder) {
                    request()->session()->flash('auto_submit', 1);
                    return redirect(url(config('laraadmin.adminRoute') . '/orders/' . $id . '/edit?d=1'));
                }
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
            }
        }

        return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
    }

    public function printOrders(Bank $bank, Request $request)
    {
        $filter = json_decode(Cache::get($request->get('sessionKey')), true);
        $request->merge($filter);
        $values = $this->buildFilterQuery($request, $request->get('print_type') == 2 ? 'asc' : 'desc');
        $values->where("customer_id", $request->get('customer_id'));
        $datatable = $this->makeDatatable($values);
        $datatable->make();
        $query = $datatable->getFilteredQuery();
        if ($query->count() > 0) {
            $orders = $query->orderBy('id', 'asc')
                ->get();
            $store = $orders->first()->store;
            $customer = $orders->first()->customer;
            $banks = $bank->getBankByAccName([], $customer->store_id);
            $symbol = $customer->customer_currency == Bank::CURRENCY_NDT ? ' NDT' :  'đ';
            $pcIds = is_array(@$filter['pc_ids']) ? $filter['pc_ids'] : [];
            if (!empty($pcIds)) {
                $products = collect(DB::table('products_product_category')
                    ->where('product_category_id', $pcIds)
                    ->get())->pluck('product_id')
                    ->unique()
                    ->toArray();
            } else {
                $pids = OrderProduct::whereIn('order_id', $orders->pluck('id'))
                    ->pluck('product_id');
                $products = collect(DB::table('products_product_category')
                    ->whereIn('product_id', $pids)
                    ->get())->pluck('product_id')
                    ->unique()
                    ->toArray();
            }
            $format = $request->get('print_type') == 2 ? 'la.orders.print-selected-orders' : 'la.orders.print-orders';

            return view($format, [
                'orderStatus' => app(OrderStatus::class),
                'customer' => $customer,
                'store' => $store,
                'symbol' => $symbol,
                'banks' => $banks,
                'validProducts' => $products
            ])->with('orders', $orders)
                ->with('order', $orders->first());
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("order"),
            ]);
        }
    }

    public function createRefundOrder($id)
    {
        if ($this->hasAccess('create')) {
            try {
                $order = Order::where('order_from', 2)
                    ->where('self_cod_service', 1)
                    ->whereNull('copier_id')
                    ->where('status', OrderStatus::SUCCESS)
                    ->first();
                if ($order) {
                    $clone = $order->replicate();
                    $orderEvent = Order::getEventDispatcher();
                    $orderProductEvent = OrderProduct::getEventDispatcher();
                    Order::unsetEventDispatcher();
                    OrderProduct::unsetEventDispatcher();
                    unset($clone->id);
                    $clone->code = app(\App\Services\Generator::class)->generateOrderCode();
                    $clone->status = OrderStatus::REFUND;
                    $clone->self_cod_service = 0;
                    $clone->copier_id = $order->id;
                    $clone->push();
                    $ops = $order->orderProducts;
                    $ob = app(OrderProductObserve::class);
                    foreach ($ops as $op) {
                        $opClone = $op->replicate();
                        $op->order_id = $clone->id;
                        unset($op->id);
                        $op->push();
                        $ob->deleted($opClone);
                    }
                    Order::setEventDispatcher($orderEvent);
                    OrderProduct::setEventDispatcher($orderProductEvent);
                    $last = Commission::where('customer_id', $clone->customer_id)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    Commission::create([
                        'customer_id' => $clone->customer_id,
                        'order_id' => $clone->id,
                        'trans_id' => 0,
                        'amount' => $clone->subtotal,
                        'balance' => ($last ? $last->balance : 0) + $clone->subtotal,
                        'note' => 'Hoàn lại từ đơn hàng #' . $order->code
                    ]);
                    return redirect()->back();
                }
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
            }
        }
        return redirect()->back()->withErrors(trans('messages.cannot_save'));
    }

    public function cancelOrder($id)
    {
        if ($this->hasAccess('create')) {
            try {
                $order = Order::where('order_from', 2)
                    ->where('id', $id)
                    ->where('status', OrderStatus::PENDING_CANCEL)
                    ->first();
                if ($order) {
                    $codOrder = $order->codOrder;
                    if ($codOrder) {
                        app(CODOrderRepository::class)->cancelOrder($codOrder);
                        if ($shipping = CODOrdersShipping::find($codOrder->so_id)) {
                            $shipping->delete();
                        }
                    }
                    $order->status = 4;
                    $order->save();

                    return redirect()->back();
                }
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
            }
        }
        return redirect()->back()->withErrors(trans('messages.cannot_save'));
    }
}
