<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Http\Requests\CODOrderShippingRequest;
use App\Models\Bank;
use App\Models\CODOrder;
use App\Models\Config;
use Illuminate\Http\Request;
use DB;
use Log;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use App\Models\CODOrdersShipping;
use App\Repositories\CODOrderShippingRepository;

class CODOrdersShippingController extends Controller
{
    protected $type;
    protected $codOrdersShipping;
    protected $codOrderShippingRp;
    public $show_action = true;
    public $view_col = 'title';
    public $listing_cols = ['id', 'partner', 'status', 'type', 'note'];

    public function __construct(CODOrdersShipping $codOrdersShipping, CODOrderShippingRepository $codOrderShippingRp)
    {
        // Field Access of Listing Columns
        if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
            $this->middleware(function ($request, $next) {
                $this->listing_cols = ModuleFields::listingColumnAccessScan('CODOrdersShipping', $this->listing_cols);
                return $next($request);
            });
        } else {
            $this->listing_cols = ModuleFields::listingColumnAccessScan('CODOrdersShipping', $this->listing_cols);
        }
        $this->codOrdersShipping = $codOrdersShipping;
        $this->codOrderShippingRp = $codOrderShippingRp;
        $this->type = request('type');
    }

    /**
     * Display a listing of the Shipping Orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $module = Module::get('CODOrdersShipping');

        if (Module::hasAccess($module->id)) {
            $statusList = $this->type
                ? $this->codOrdersShipping->getStatusList($this->type)
                : $this->codOrdersShipping->availableStatus();
            $typeList = !$this->type
                ? $this->codOrdersShipping->availableType()
                : [];

            return View('la.cod_orders_shipping.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => $this->listing_cols,
                'module' => $module,
                'statusList' => $statusList,
                'typeList' => $typeList
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Show the form for creating a new Shipping Orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created Shipping Orders in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CODOrderShippingRequest $request)
    {
        if (Module::hasAccess("CODOrdersShipping", "create")) {
            DB::beginTransaction();
            try {
                $module = Module::get("CODOrdersShipping");
                $insert_id = Module::insert($module->model, $request);
                $sOrder = $this->codOrdersShipping->find($insert_id);
                $sOrder->bill_data = [
                    'total_cod' => collect($request->bill_data)->sum('cod_amount'),
                    'total_fee' => collect($request->bill_data)->sum('fee_amount')
                ];
                $sOrder->save();
                $this->codOrderShippingRp->create($sOrder, $request->partner_ids, $request->bill_data);

                DB::commit();
                return redirect()->back();
            } catch (\Exception $exception) {
                DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Display the specified Shipping Orders.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified Shipping Orders.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Module::hasAccess("CODOrdersShipping", "edit")) {
            $sOrder = $this->codOrdersShipping->find($id);
            if (isset($sOrder->id)) {
                $module = Module::get('CODOrdersShipping');
                $module->row = $sOrder;
                $selectedOrders = $sOrder->codOrder->map(function ($cOrder) {
                    $customer = $cOrder->customer();
                    $cOrder->customer_name = @$customer->name ?? "";

                    return $cOrder;
                });
                $statusList = $this->codOrdersShipping->getStatusList($sOrder->type);

                return view('la.cod_orders_shipping.edit', [
                    'module' => $module,
                    'view_col' => $this->view_col,
                    'selectedOrders' => $selectedOrders,
                    'statusList' => $statusList
                ])->with('sOrder', $sOrder);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("CODOrdersShipping"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Update the specified Shipping Orders in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(codOrderShippingRequest $request, $id)
    {
        if (Module::hasAccess("CODOrdersShipping", "edit")) {
            DB::beginTransaction();
            try {
                $insert_id = Module::updateRow("CODOrdersShipping", $request, $id);
                $sOrder = $this->codOrdersShipping->find($insert_id);
                $sOrder->bill_data = [
                    'total_cod' => collect($request->bill_data)->sum('cod_amount'),
                    'total_fee' => collect($request->bill_data)->sum('fee_amount')
                ];
                $sOrder->save();
                $this->codOrderShippingRp->update($sOrder, $request->partner_ids, $request->bill_data);

                DB::commit();
                return redirect(config('laraadmin.adminRoute') . "/cod-orders-shipping?type=" . $sOrder->type);
            } catch (\Exception $exception) {
                DB::rollback();
                Log::error($exception->getMessage());
                Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Remove the specified Shipping Orders from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sOrder = $this->codOrdersShipping->find($id);
        if (Module::hasAccess("CODOrdersShipping", "delete") && $sOrder->canEdit()) {
            $this->type = $sOrder->type;
            $this->codOrderShippingRp->removeOrder($sOrder->id);
            $sOrder->delete();

            // Redirecting to index() method
            return redirect(config('laraadmin.adminRoute') . "/cod-orders-shipping?type=" . $this->type);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Datatable Ajax fetch
     *
     * @return
     */
    public function dtajax(Request $request)
    {
        $values = CODOrdersShipping::query()
            ->where(function ($q) {
                if ($this->type) {
                    $q->where('type', $this->type);
                }
            })
            ->select($this->listing_cols)
            ->orderBy('id', 'desc');
        if (!empty($request->seri_number)) {
            $values->whereHas('codOrder.order.productSeries', function ($query) use ($request) {
                $query->where('seri_number', $request->seri_number);
            });
        }

        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = ModuleFields::getModuleFields('CODOrdersShipping');
        for ($i = 0; $i < count($data->data); $i++) {
            $id = $data->data[$i][0];
            $sOrder = $this->codOrdersShipping->findOrFail($id);
            for ($j = 0; $j < count($this->listing_cols); $j++) {
                $col = $this->listing_cols[$j];
                if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
                    $data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
                }
                if ($col == 'status') {
                    $data->data[$i][$j] = $sOrder->getStatusHTMLFormatted();
                }
                if ($col == 'partner') {
                    $data->data[$i][$j] = $sOrder->getPartnerHTMLFormatted();
                }
                if ($col == 'type') {
                    $data->data[$i][$j] = $sOrder->getTypeHTMLFormatted();
                }
            }
            $data->data[$i][4] = number_format($sOrder->bill_data['total_cod']) . ' đ';
            $data->data[$i][5] = number_format($sOrder->bill_data['total_fee']) . ' đ';
            $data->data[$i][6] = $sOrder->created_at->format('d-m-Y H:i');
            $data->data[$i][7] = html_entity_decode($sOrder->note);

            if ($this->show_action) {
                $output = '';
                $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/cod-orders-shipping/' . $id . '/print') . '" class="btn btn-warning btn-xs print-order onetime-click" style="display:inline;padding:2px 5px 3px 5px;">IN</a>';
                if (Module::hasAccess("CODOrdersShipping", "edit")) {
                    $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/cod-orders-shipping/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                }

                if (Module::hasAccess("CODOrdersShipping", "delete") && $sOrder->canEdit()) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.cod-orders-shipping.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i][] = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }

    public function searchOrder(Request $request)
    {
        $results = CODOrder::query()
            ->where('partner', $request->partner)
            ->where(function ($query) {
                if ($q = request('q')) {
                    $query->where('order_code', 'like', '%' . $q . '%');
                }
            })
            ->excludeCancel()
            ->paginate();
        $results->getCollection()->transform(function ($value) {
            return [
                'id' => $value->order_code,
                'text' => $value->order_code
            ];
        });
        return $results;
    }

    public function checkCODCode(Request $request)
    {
        $rules = [
            'code' => 'exists:cod_orders,order_code,partner,' . $request->partner
        ];
        $messages = [
            'code.exists' => 'Mã vận đơn ' . $request->code . ' không tồn tại trong hệ thống'
        ];
        return $this->validate($request, $rules, $messages);
    }

    public function getOrder(Request $request)
    {
        $codes = array_filter(explode(',', $request->codes));
        $cOrders = CODOrder::where('partner', $request->partner)
            ->whereIn('order_code', $codes)
            ->get();
        return view('la.cod_orders_shipping.selected_order', ['cOrders' => $cOrders]);
    }

    public function print($id, Bank $bank)
    {
        $sOrder = $this->codOrdersShipping->find($id);
        if (isset($sOrder->id)) {
            $configs = Config::all()->pluck('value', 'key');
            $orders = collect();
            foreach ($sOrder->codOrder as $cOrder) {
                $orderProducts = $cOrder->orderProducts();
                $data = [
                    'products' => $orderProducts,
                    'bill_code' => $cOrder->order_code,
                    'cod_amount' => $cOrder->cod_amount,
                    'fee_amount' => $cOrder->fee_amount,
                    'customer_name' => @$cOrder->customer->name ?? "",
                    'row_span' => count($orderProducts)
                ];
                $orders->push((object)$data);
            }

            return view('la.cod_orders_shipping.print', [
                'configs' => $configs,
                'orders' => $orders
            ])->with('sOrder', $sOrder);
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("CODOrdersShipping"),
            ]);
        }
    }
}
