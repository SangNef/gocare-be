<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Events\WarrantyOrderSaved;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\WarrantyOrdersRequest;
use DB;
use Carbon\Carbon;
use App\Datatable\Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\ProductSeri;
use App\Models\Bank;
use App\Models\CODOrder;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProductSeri;
use App\Repositories\CODOrderRepository;
use App\Repositories\WarrantyOrderRepository;
use App\Repositories\WarrantyOrderProductRepository;
use App\Repositories\WarrantyOrderProductSeriRepository;
use App\Services\CODPartners\VTPService;
use App\Services\CODPartners\GHNService;
use App\Services\CODPartners\GHN5Service;
use App\Services\CODPartners\GHTKService;

class WarrantyOrdersController extends Controller
{
	public $show_action = true;
	public $view_col = 'code';
	public $listing_cols = ['id', 'store_id', 'code', 'customer_id', 'type', 'status', 'created_at', 'returned_at'];

	protected $wOrderRp;
	protected $wopRepository;
	protected $wopsRepository;
	protected $codOrderRp;
	protected $ghtkSv;
	protected $vtpSv;
	protected $ghnSv;
    protected $ghn5Sv;
	protected $extendColumns = [
		'process' => [
			'colname' => 'process',
			'label' => 'Tình trạng xử lý'
		]
	];

	public function __construct(
		WarrantyOrderRepository $wOrderRp,
		WarrantyOrderProductRepository $wopRepository,
		WarrantyOrderProductSeriRepository $wopsRepository,
		CODOrderRepository $codOrderRp,
		VTPService $vtpSv,
		GHNService $ghnSv,
        GHN5Service $ghn5Sv,
		GHTKService $ghtkSv
	) {
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('WarrantyOrders', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('WarrantyOrders', $this->listing_cols);
		}

		$this->wOrderRp = $wOrderRp;
		$this->wopRepository = $wopRepository;
		$this->wopsRepository = $wopsRepository;
		$this->codOrderRp = $codOrderRp;
		$this->vtpSv = $vtpSv;
		$this->ghnSv = $ghnSv;
		$this->ghtkSv = $ghtkSv;
        $this->ghn5Sv = $ghn5Sv;
	}

	/**
	 * Display a listing of the WarrantyOrders.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('WarrantyOrders');

		if (Module::hasAccess($module->id)) {
			$this->listing_cols = array_merge($this->listing_cols, array_keys($this->extendColumns));
			$module->fields = array_merge($module->fields, $this->extendColumns);

			return View('la.warrantyorders.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new warrantyorder.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created warrantyorder in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(WarrantyOrdersRequest $request)
	{
		if (Module::hasAccess("WarrantyOrders", "create")) {
			try {
				DB::beginTransaction();
				$request->merge([
					'created_at' => Carbon::createFromFormat('Y/m/d', $request->created_at)->format('Y-m-d') . date(' H:i:s'),
				]);

				$insert_id = Module::insert("WarrantyOrders", $request);
				$wOrder = WarrantyOrder::find($insert_id);

				$this->wopRepository->create($wOrder, $request->products);
				$this->wopsRepository->create($wOrder, $request->series);

				DB::commit();
				event(new WarrantyOrderSaved($wOrder));

				return response()->json('OK');
			} catch (\Exception $exception) {
				DB::rollback();
				Log::error($exception->getMessage());
				Log::error($exception->getTraceAsString());
				return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
			}
		} else {
			return response()->json(['error' => ['Không thể tạo đơn hàng. Vui lòng liên hệ admin']], 422);
		}
	}

	/**
	 * Display the specified warrantyorder.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if (Module::hasAccess("WarrantyOrders", "view")) {

			$warrantyorder = WarrantyOrder::find($id);
			if (isset($warrantyorder->id)) {
				$module = Module::get('WarrantyOrders');
				$module->row = $warrantyorder;

				return view('la.warrantyorders.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('warrantyorder', $warrantyorder);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("warrantyorder"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified warrantyorder.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id, OrderStatus $orderStatus)
	{
		if (Module::hasAccess("WarrantyOrders", "edit")) {
			$warrantyorder = WarrantyOrder::find($id);
			if (isset($warrantyorder->id)) {
				$module = Module::get('WarrantyOrders');
				$module->row = $warrantyorder;
				$codOrder = $warrantyorder->codOrder;
				$selectedProducts = $warrantyorder->warrantyOrderProductSeries->map(function ($item) use ($codOrder) {
					$data['products'] = [$item->warrantyOrderProduct->product];
					$data['status'] = $item->status;
					$data['selected_series'] = $item->productSeri;
					$data['id'] = $item->id;
					$data['note'] = $item->note;
					$data['error_type'] = $item->error_type;
					$data['return_at'] = $item->return_at;
					$data['canCreateBillLading'] = !$codOrder && $item->isProcessed() && !$item->codOrder;
					$data['bill_lading'] = $item->codOrder;
					return $data;
				});
				$statusFormatted = $orderStatus->getWarrantyStatusHTMLFormatted($warrantyorder->status);

				return view('la.warrantyorders.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
					'selectedProducts' => $selectedProducts,
					'statusFormatted' => $statusFormatted,
					'codOrder' => $codOrder
				])->with('warrantyorder', $warrantyorder);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("warrantyorder"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified warrantyorder in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(WarrantyOrdersRequest $request, $id)
	{
		if (Module::hasAccess("WarrantyOrders", "edit")) {
			try {
				DB::beginTransaction();
				$request->merge([
					'created_at' => Carbon::createFromFormat('Y/m/d', $request->created_at)->format('Y-m-d') . date(' H:i:s'),
				]);

				$insert_id = Module::updateRow("WarrantyOrders", $request, $id);
				$wOrder = WarrantyOrder::find($insert_id);

				$this->wopRepository->update($wOrder, $request->products);
				$this->wopsRepository->update($wOrder, $request->series);

				DB::commit();
				event(new WarrantyOrderSaved($wOrder));

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
	 * Remove the specified warrantyorder from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("WarrantyOrders", "delete")) {
			$wOrder = WarrantyOrder::find($id);
			$this->wopRepository->remove($wOrder);
			$wOrder->delete();
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.warrantyorders.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(Request $request, OrderStatus $orderStatus)
	{
		$values = WarrantyOrder::select($this->listing_cols)
			->search($request->all())
			->whereNull('deleted_at')
			->orderBy('id', 'DESC')
			->where(function ($query) use ($request) {
				$returnedFrom = date($request->return_at['from']);
				$returnedTo = date($request->return_at['to']);
				if ($returnedFrom) {
					$query->where('returned_at', '>=', $returnedFrom)
						->orWhereHas('warrantyOrderProductSeries', function ($q) use ($returnedFrom) {
							$q->where('return_at', '>=', $returnedFrom);
						});
				}
				if ($returnedTo) {
					$query->where('returned_at', '<=', $returnedTo)
						->orWhereHas('warrantyOrderProductSeries', function ($q) use ($returnedTo) {
							$q->where('return_at', '<=', $returnedTo);
						});
				}
			})
			->whereHas('warrantyOrderProductSeries', function ($query) use ($request) {
				if ($request->seri_status) {
					$query->where('status', $request->seri_status);
				}
			});

		$datatable = Datatables::of($values);

		$datatable->filterColumn('customer_id', function ($query, $keyword) {
			$query->where('customer_id', $keyword);
		});

		$datatable->addColumn('process', function (WarrantyOrder $wOrder) {
			$processTotals = $this->wOrderRp->getProcessSeriesPercent($wOrder);
			return view('la.warrantyorders.process', compact('processTotals'))->render();
		});

		$out = $datatable->make();

		$queried = $datatable->getFilteredQuery();
		$cache = [
			'ids' => $queried ? $queried->pluck('id') : [],
			'status' => $request->seri_status,
			'return_at' => $request->return_at
		];
		Cache::put(auth()->user()->id . '_warrantyorders', json_encode($cache), 5 * 60);

		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('WarrantyOrders');

		for ($i = 0; $i < count($data->data); $i++) {
			$id = $data->data[$i][0];
			$wOrder = WarrantyOrder::find($id);
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/warrantyorders/' . $id) . '">' . $data->data[$i][$j] . '</a>';
				} else if ($col == 'id') {
					$disabled = !$wOrder->canCreateBillLadingAllProduct() || $wOrder->codOrder ? "disabled" : "";
					$data->data[$i][0] = "<input type='checkbox' {$disabled} class='row worder-id' value='{$id}'/>" . $id;
				} else if ($col == 'status') {
					$data->data[$i][$j] = $orderStatus->getWarrantyStatusHTMLFormatted($data->data[$i][$j]);
				} else if ($col == 'type') {
					$data->data[$i][$j] = $wOrder->getTypeHTMLFormatted();
				} else if (in_array($col, ['created_at', 'returned_at'])) {
					$date = $data->data[$i][$j];
					$data->data[$i][$j] = $date ? Carbon::parse($data->data[$i][$j])->format('d/m/Y H:i') : '';
				}
			}

			if ($this->show_action) {
				$output = '';
				if (Module::hasAccess("WarrantyOrders", "edit")) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/warrantyorders/' . $id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}

				if (Module::hasAccess("WarrantyOrders", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.warrantyorders.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function print(Bank $bank, Request $request)
	{
		$this->validate($request, [
			'customer_id' => 'required|exists:customers,id',
			'type' => 'required|in:single,multiple,row',
			'id' => 'required_if:type,single,row',
			'seri_id' => 'required_if:type,row',
			'seri_id.*' => 'exists:warranty_order_product_series,id'
		]);
		$customer = Customer::find($request->customer_id);
		$additionalData = [];

		if (in_array($request->type, ['single', 'multiple'])) {
			$additionalData['store'] = $customer->store;
			if ($request->type === 'single') {
				$wOrder = WarrantyOrder::find($request->id);
				$items = $this->wOrderRp->getDataForPrinting($request);
				$view = 'la.warrantyorders.print';
				$additionalData['wOrder'] = $wOrder;
			} else {
				$cache = json_decode(Cache::get(auth()->user()->id . '_warrantyorders'));
				$request->merge([
					'id' => $cache->ids,
					'status' => $cache->status,
					'return_at' => (array) $cache->return_at
				]);
				$items = $this->wOrderRp->getDataForPrinting($request);
				$view = 'la.warrantyorders.print-multiple';
			}
		} else {
			$request->merge([
				'seri_id' => explode(',', $request->seri_id)
			]);
			$items = $this->wOrderRp->getDataForPrinting($request);
			$view = 'la.warrantyorders.print-row';
		}


		return view($view, array_merge([
			'customer' => $customer,
			'items' => $items
		], $additionalData));
	}

	public function getBillLading($orderId, Request $request, CODOrderRepository $codOrderRp)
	{
		$this->validate($request, [
			'type' => 'required|in:all,some',
			'ids' => 'required_if:type,some'
		]);

		try {
			$warrantyorder = WarrantyOrder::with(['warrantyOrderProductSeries' => function ($query) use ($request) {
				$query->whereDoesntHave('codOrder')
					->where('status', WarrantyOrderProductSeri::STATUS_PROCESSED);
				if ($request->type === 'some') {
					$wops_ids = array_filter(array_map('trim', explode(',', $request->ids)));
					$query->whereIn('warranty_order_product_series.id', $wops_ids);
				}
			}])
				->whereDoesntHave('codOrder')
				->findOrFail($orderId);
			if ($warrantyorder->codOrder) {
				throw new \Exception("Đã tạo vận đơn lên {$warrantyorder->codOrder->partner} (Mã vận đơn: {$warrantyorder->codOrder->order_code})");
			}
			if (!$warrantyorder->warrantyOrderProductSeries->count()) {
				throw new \Exception("Không có sản phẩm đủ điều kiện vận đơn");
			}

			$html = $codOrderRp->getWarrantyOrderBillLadingHTML($warrantyorder, $request->type);
			return response()->json(['html' => $html]);
		} catch (\Exception $exception) {
			Log::error($exception->getMessage());
			Log::error($exception->getTraceAsString());
			return response()->json(['error' => [$exception->getMessage()]], 422);
		}
	}

	public function getDataForBillLading($orderId, Request $request)
	{
		$this->validate($request, [
			'partner' => 'required|in:vtp,ghn,ghtk'
		]);

		try {
			$warrantyorder = WarrantyOrder::with(['warrantyOrderProductSeries' => function ($query) {
				$query->whereDoesntHave('codOrder')
					->where('status', WarrantyOrderProductSeri::STATUS_PROCESSED);
			}])
				->whereDoesntHave('codOrder')
				->findOrFail($orderId);

			if (!$warrantyorder->canCreateBillLadingAllProduct()) {
				throw new \Exception("Đơn {$warrantyorder->code} không đủ điều kiện vận đơn");
			}
			$partner = $request->partner;

			switch ($partner) {
                case 'ghn_5':
                    $shipping = $this->ghn5Sv;
                    $storeFields = ['name', '_id'];
                    break;
				case 'ghn':
					$shipping = $this->ghnSv;
					$storeFields = ['name', '_id'];
					break;
				case 'ghtk':
					$shipping = $this->ghtkSv;
					$storeFields = ['pick_name', 'pick_address_id'];
					break;
				default:
					$shipping = $this->vtpSv;
					$storeFields = ['name', 'groupaddressId'];
					break;
			}

			$stores = collect($shipping->loadConnection($warrantyorder->customer, true)->getStores())->pluck($storeFields[0], $storeFields[1]);
			$products = $shipping->prepareWarrantyOrderAllProductsForBillLading($warrantyorder)
				->map(function ($product) use ($partner) {
					if ($partner === 'vtp' || $partner === 'ghn') {
						$weight = ($product['length'] * $product['height'] * $product['width'] / 6000) * 1000;
						$totalWeight = $weight > $product['weight'] ? $weight : $product['weight'];
						$product['weight'] = intval($totalWeight) * $product['quantity'];
					} else {
						$product['weight'] *= $product['quantity'] * 0.001;
					}

					return $product;
				});

			$html = view('la.warrantyorders.multiple-bill-lading-row', [
				'order' => $warrantyorder,
				'products' => $products,
				'partner' => $partner,
				'customer' => $warrantyorder->customer,
				'stores' => $stores
			])->render();
			$data = $this->wOrderRp->transformDataForCOD($warrantyorder, $partner, $products);
			$data['order_id'] = $warrantyorder->id;
			$data['type'] = "all";
			$data['cod_partner'] = $partner;
			$data['store_id'] = $warrantyorder->store_id;
			$data['charge_method'] = CODOrder::CHARGE_METHOD_COD;

			return response()->json([
				'data' => $data,
				'html' => $html
			]);
		} catch (\Exception $exception) {
			Log::error($exception->getMessage());
			Log::error($exception->getTraceAsString());
			return response()->json(['error' => [$exception->getMessage()]], 422);
		}
	}

	public function getCODServices($partner, Request $request)
	{
		try {
			$order = WarrantyOrder::findOrFail($request->order_id);
			switch ($partner) {
                case 'ghn_5':
                    $results = [
                        'order_id' => $request->order_id
                    ];
                    $this->ghn5Sv->loadConnection($order->customer, true);
                    $store = $this->ghn5Sv->getStoreById($request->inventory);
                    $services = $this->ghn5Sv->getServices([
                        'shop_id' => @$store['_id'],
                        'from_district' => @$store['district_id'],
                        'to_district' => $request->to_district_id
                    ]);
                    foreach ($services as $serviceId => $name) {
                        $params = $request->all();
                        $params['service_id'] = $serviceId;
                        $price = $this->ghn5Sv->getServicePrice($store['_id'], $params);
                        $amount = $this->ghn5Sv->applyDiscount($price);
                        $results['services'][$serviceId]['name'] = $name . ' - ' . number_format($amount) . ' đ';
                        $results['services'][$serviceId]['price'] = $amount;
                    }
                    break;
				case 'ghn':
					$results = [
						'order_id' => $request->order_id
					];
					$this->ghnSv->loadConnection($order->customer, true);
					$store = $this->ghnSv->getStoreById($request->inventory);
					$services = $this->ghnSv->getServices([
						'shop_id' => @$store['_id'],
						'from_district' => @$store['district_id'],
						'to_district' => $request->to_district_id
					]);
					foreach ($services as $serviceId => $name) {
						$params = $request->all();
						$params['service_id'] = $serviceId;
						$price = $this->ghnSv->getServicePrice($store['_id'], $params);
						$amount = $this->ghnSv->applyDiscount($price);
						$results['services'][$serviceId]['name'] = $name . ' - ' . number_format($amount) . ' đ';
						$results['services'][$serviceId]['price'] = $amount;
					}
					break;
				case 'vtp':
					$results = [
						'order_id' => $request->order_id
					];
					$this->vtpSv->loadConnection($order->customer, true);
					$services = $this->vtpSv->requestServicePrice($request);
					foreach ($services as $key => $value) {
						$amount = $this->vtpSv->applyDiscount($services[$key]['price']);
						$results['services'][$key]['name'] = substr($value['name'], 0, strpos($value['name'], '-')) . ' - ' . number_format($amount) . ' đ';
						$results['services'][$key]['price'] = $amount;
					}
					break;
				case 'ghtk':
					$results = [
						'order_id' => $request->order_id
					];
					$this->ghtkSv->loadConnection($order->customer, true);
					$customerAddress = $this->ghtkSv->getAddress($request->province, $request->district, $request->ward);
					$data = [
						'pick_address_id' => $request->inventory,
						'province' => $customerAddress['province'],
						'district' => $customerAddress['district'],
						'weight' => array_sum(array_column($request->products, 'weight')) * 1000,
						'value' => $request->total,
						'transport' => $request->transport,
						'deliver_option' => 'none',
						'products' => $request->products,
						'tags' => [1]
					];
					$service = $this->ghtkSv->getServicePrice($data);
					$amount = $this->ghtkSv->applyDiscount($service['fee']);
					$results['services'] = [
						$service['name'] => [
							'name' => $service['name']  . ' - ' . number_format($amount) . ' đ',
							'price' => $amount
						]
					];
					break;
				default:
					$results = [];
					break;
			}
			if (!isset($results['services']) || empty($results['services'])) {
				throw new \Exception('Đã có lỗi, vui lòng liên hệ admin');
			}

			return response()->json($results);
		} catch (\Exception $exception) {
			Log::error($exception->getMessage());
			Log::error($exception->getTraceAsString());
			return response()->json(['error' => [$exception->getMessage()]], 422);
		}
	}
}
