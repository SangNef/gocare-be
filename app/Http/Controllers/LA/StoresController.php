<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\Audit;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Bank;
use App\Models\GroupProductDiscount;
use App\Models\Product;
use App\Models\StoreObserve;
use App\Models\StoreProduct;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\StoreShipping;
use App\Repositories\CODOrderRepository;
use App\Repositories\ProductRepository;
use App\Services\CODPartners\StoreShippingService;
use Dwij\Laraadmin\Models\LAConfigs;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\CODOrder;
use Auth;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Store;
use App\Services\Generator;

class StoresController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'name', 'address', 'started_at', 'owner_id', 'status'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Stores', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Stores', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the Stores.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Stores');
		if (auth()->user()->store_id) {
			return redirect(config('laraadmin.adminRoute') . "/stores/" . auth()->user()->store_id);
		}
		if (Module::hasAccess($module->id)) {
			return View('la.stores.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new store.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created store in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if (Module::hasAccess("Stores", "create")) {

			$rules = Module::validateRules("Stores", $request);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			$insert_id = Module::insert("Stores", $request);

			return redirect()->route(config('laraadmin.adminRoute') . '.stores.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Display the specified store.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id, Request $request, CODOrderRepository $repository, ProductRepository $productRepository)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {

			$store = Store::find($id);
			if (isset($store->id)) {
				$module = Module::get('Stores');
				$module->row = $store;
				$request->session()->put('sp_filters', json_encode($request->all()));

				$providers = $repository->getAvailableProvider();
				$shippings = $store->shippings()->get()->keyBy('provider')->toArray();
				array_walk($providers, function (&$provide, $key) use ($shippings) {
					$service = StoreShippingService::getProvider($key);
					$apiconnection = $service ? $service->apiConnection() : [];
					$existedValue = isset($shippings[$key]) ? json_decode($shippings[$key]['api_connection'], true) : [];
					$result = [];
					foreach ($apiconnection as $cKey) {
						$result[$cKey] = @$existedValue[$cKey];
					}

					$provide = [
						'name' => $provide,
						'status' => isset($shippings[$key]) ? $shippings[$key]['status'] : null,
						'api_connection' => $result
					];
				});
				$setting = $store->setting;
				$selectedCommissionGroup = Group::whereIn('id', $setting['commission_groups'])
					->pluck('display_name', 'id')
					->toArray();
				$selectedBank = Bank::find(@$setting['online_receiver_bank']);
				return view('la.stores.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding",
					'providers' => $providers,
					'shippings' => $shippings,
					'setting' => $setting,
					'selectedCommissionGroup' => $selectedCommissionGroup,
					'selectedBank' => $selectedBank
				])->with('store', $store);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("store"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified store.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if (Module::hasAccess("Stores", "edit") && (!auth()->user()->store_id || auth()->user()->store_id == $id)) {
			$store = Store::find($id);
			if (isset($store->id)) {
				$module = Module::get('Stores');

				$module->row = $store;

				return view('la.stores.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('store', $store);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("store"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified store in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if (Module::hasAccess("Stores", "edit") && (!auth()->user()->store_id || auth()->user()->store_id == $id)) {

			$rules = Module::validateRules("Stores", $request, true);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}

			$insert_id = Module::updateRow("Stores", $request, $id);

			return redirect()->route(config('laraadmin.adminRoute') . '.stores.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Remove the specified store from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("Stores", "delete") && (!auth()->user()->store_id || auth()->user()->store_id == $id)) {
			Store::find($id)->delete();

			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.stores.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax()
	{
		$values = DB::table('stores')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Stores');
		$quantities = StoreProduct::select(\DB::raw('SUM(n_quantity) as n_quantity, SUM(w_quantity) as w_quantity, count(*) as total_product, store_id'))
			->groupBy('store_id')
			->get()
			->keyBy('store_id')
			->toArray();

		for ($i = 0; $i < count($data->data); $i++) {
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/stores/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}
			if (isset($quantities[$data->data[$i][0]])) {
				$data->data[$i][] = $quantities[$data->data[$i][0]]['total_product'];
				$data->data[$i][] = $quantities[$data->data[$i][0]]['n_quantity'];
				$data->data[$i][] = $quantities[$data->data[$i][0]]['w_quantity'];
			} else {
				$data->data[$i][] = 0;
				$data->data[$i][] = 0;
				$data->data[$i][] = 0;
			}
			if ($this->show_action) {
				$output = '';
				if (Module::hasAccess("Stores", "edit")) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/stores/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}

				if (Module::hasAccess("Stores", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.stores.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function updateQuantity($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$this->validate($request, [
				'product_id' => 'required|exists:products,id',
				'n_quantity' => 'required|integer',
				'w_quantity' => 'required|integer',
			]);
			$ids = [$id];
			$stores = LAConfigs::where('key', 'dong_bo_so_luong_san_pham')->first();
			if ($stores) {
				$stores = explode(',', $stores->value);
				if (in_array($id, $stores)) {
					$ids = $stores;
				}
			}
			foreach ($ids as $id) {
				StoreProduct::updateOrCreate([
					'store_id' => $id,
					'product_id' => $request->product_id
				], [
					'n_quantity' => $request->n_quantity,
					'w_quantity' => $request->w_quantity
				]);
			}
		}

		return back();
	}

	public function updateShipping($id, $provider, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$service = StoreShippingService::getProvider($provider);
			if ($service) {
				$apiConnection = $service->apiConnection();
				$result = $request->only($apiConnection);
				StoreShipping::updateOrCreate([
					'store_id' => $id,
					'provider' => $provider
				], [
					'api_connection' => json_encode($result),
					'status' => 1,
				]);

				return response()->json('OK');
			}
		}

		return response()->json('ERROR', 422);
	}

	public function exportProducts($id, Request $request, ProductRepository $productRepository)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {

			$filters = json_decode($request->session()->get('sp_filters'), true);
			$store = Store::find($id);
			$products = $productRepository->getProductsByStore($store->id, $filters)->get();
			$products = $productRepository->getProductQuantityByProducts($products, $store->id);

			$fileName = 'Kho ' . $store->name . ' - Sản phẩm  ' . date('d/m/Y');
			$data = [];
			foreach ($products as $product) {
				$data[] = [
					'ID' => $product->id,
					'sku' => $product->name,
					'Tên sản phẩm' => $product->name,
					'Trạng thái' => $product->quantity >= 1 ? 'Còn hàng' : 'Hết hàng',
					'Số lượng' => (int) $product->quantity,
					'Hàng mới ' => (int) $product->n_quantity,
					'Hàng cũ' => (int) $product->w_quantity,
				];
			}

			return Excel::create($fileName, function ($excel) use ($data) {
				$excel->sheet('Sản phẩm', function ($sheet) use ($data) {
					$sheet->fromArray($data);
				});
			})->export('xlsx');
		}

		return back();
	}

	public function getExcludedCustomers($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$store = Store::find($id);
			$excludedCustomers = explode(',', $store->shipping_discount_excludes);
			$customers = Customer::whereIn('id', $excludedCustomers)->paginate();

			return view('la.stores.excluded-customers', [
				'customers' => $customers,
			])->render();
		}

		return response()->json('ERROR', 422);
	}

	public function excludeCustomer($id, Request $request)
	{
		$this->validate($request, [
			'customer_id' => 'required|exists:customers,id,store_id,' . $id,
		]);
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$store = Store::find($id);
			$excludedCustomers = explode(',', $store->shipping_discount_excludes);

			if (!in_array($request->customer_id, $excludedCustomers)) {
				$excludedCustomers[] = $request->customer_id;
			}
			$store->shipping_discount_excludes = implode(',', $excludedCustomers);
			$store->save();

			return response()->json('');
		}

		return response()->json('ERROR', 422);
	}

	public function removeExcludedCustomer($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$store = Store::find($id);
			$excludedCustomers = explode(',', $store->shipping_discount_excludes);
			if (($key = array_search($request->customer_id, $excludedCustomers)) !== false) {
				unset($excludedCustomers[$key]);
			}
			$store->shipping_discount_excludes = implode(',', $excludedCustomers);
			$store->save();

			return response()->json('');
		}

		return response()->json('ERROR', 422);
	}

	public function getProducts($id, Request $request, CODOrderRepository $repository, ProductRepository $productRepository)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$store = Store::find($id);
			if (isset($store->id)) {
				$request->session()->put('sp_filters', json_encode($request->all()));

				$products = $productRepository->getProductsByStore($store->id, $request->all())->paginate(50);

				$productCollection = $products->getCollection();
				$productCollection = $productRepository->getProductQuantityByProducts($productCollection, $store->id);
				$products->setCollection($productCollection);

				return view('la.stores.products', [
					'products' => $products,
				])->render();
			}
		}
		return response()->json('ERROR', 422);
	}

	public function getObserves($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$store = Store::find($id);
			$observes = $store->observes;

			return view('la.stores.observes', [
				'observes' => $observes,
			])->render();
		}

		return response()->json('ERROR', 422);
	}

	public function addObserver($id, Request $request)
	{
		$this->validate($request, [
			'customer_id' => 'required|exists:customers,id',
		]);
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeObserve = StoreObserve::where('store_id', $id)
				->where('customer_id', $request->customer_id)
				->first();
			if ($storeObserve) {
				return response()->json([
					'errors' => [
						'customer_id' => ['Khách hàng không hợp lệ']
					]
				], 422);
			}
			StoreObserve::create([
				'customer_id' => $request->customer_id,
				'store_id' => $id,
				//                'balance' => $request->balance
			]);

			return response()->json('');
		}

		return response()->json('ERROR', 422);
	}

	public function removeObserver($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeObserve = StoreObserve::where('store_id', $id)
				->where('id', $request->observer_id)
				->first();
			if (!$storeObserve) {
				return response()->json('ERROR', 422);
			}
			$storeObserve->forceDelete();

			return response()->json('');
		}

		return response()->json('ERROR', 422);
	}

	public function getObserverAudit($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeObserve = StoreObserve::where('store_id', $id)
				->where('id', $request->observer_id)
				->first();
			if (!$storeObserve) {
				return response()->json('ERROR', 422);
			}

			$audits = Audit::where('customer_id', $storeObserve->customer_id)
				->orderBy('created_at', 'desc');

			return view('la.stores.audit', [
				'audits' => $audits->paginate(50),
			])->render();
		}

		return response()->json('ERROR', 422);
	}

	public function updateProductMinimum($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$this->validate($request, [
				'product_id' => 'required|exists:products,id',
			]);

			StoreProduct::where('product_id', $request->product_id)
				->where('store_id', $id)
				->update([
					'min' => (int) $request->min
				]);

			return response()->json('OK');
		}

		return response()->json('ERROR', 422);
	}
	public function setting($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$store = Store::find($id);
			$setting = $store->setting ?: [];
			$setting['email_product_min_notification'] = $request->email_product_min_notification;
			$setting['tele_product_min_notification'] = $request->tele_product_min_notification;
			$setting['tele_fe_order_notification'] = $request->tele_fe_order_notification;
			$setting['tele_be_order_notification'] = $request->tele_be_order_notification;
			$setting['commission_groups'] = $request->commission_groups;
			$setting['tele_warranty_order_notification'] = $request->tele_warranty_order_notification;
			$setting['online_receiver_bank'] = $request->online_receiver_bank;
			$store->setting = $setting;
			$store->save();

			return back();
		}

		return response()->json('ERROR', 422);
	}

	public function getGroupAttributeExtra($id, Request $request, ProductRepository $repository)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$product = Product::find($request->product_id);
			if ($product) {
				$groups = StoreProductGroupAttributeExtra::where('store_id', $id)
					->where('product_id', $request->product_id)
					->get();
				if (!$groups->count()) {
					$groups = collect();
					$attrs = $product->attrs
						->map(function ($attr) {
							return $attr->getValues()->pluck('id');
						})
						->toArray();
					$combinations = $repository->combinations($attrs);
					foreach ($combinations as $combination) {
						if (!is_array($combination)) $combination = [$combination];
						$ids = implode(',', $combination);
						$texts = AttributeValue::whereIn('id', $combination)
							->get()
							->implode('value', 'text');

						$group = new \stdClass();
						$group->attribute_value_ids = $ids;
						$group->attribute_value_texts = $texts;
						$group->n_quantity = 0;
						$group->w_quantity = 0;
						$groups->push($group);
					}
				}
				$storeQuantity = StoreProduct::where('store_id', $id)
					->where('product_id', $request->product_id)
					->first();
				if (!$storeQuantity) {
					$storeQuantity = new \stdClass();
					$storeQuantity->n_quantity = 0;
					$storeQuantity->w_quantity = 0;
				}

				return view('la.stores.group-attribute-extra', [
				    'product' => $product,
					'groups' => $groups,
					'storeQuantity' => $storeQuantity
				])->render();
			}
		}

		return response()->json('ERROR', 422);
	}

	public function saveGroupAttributeExtra($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$quantity = $request->quantity;
			$stores = LAConfigs::where('key', 'dong_bo_so_luong_san_pham')->first();
			$ids = [$id];
			if ($stores) {
				$stores = explode(',', $stores->value);
				if (in_array($id, $stores)) {
					$ids = $stores;
				}
			}
			session([
				'quantity_audit_type' => 'Trực tiếp',
				'quantity_audit_order_id' => '0'
			]);
			if (!empty($quantity)) {
				// $request->session->put
				$nQuantity = $wQuantity = 0;
				foreach ($quantity as $value) {
					$nQuantity += (int)@$value['n_quantity'];
					$wQuantity += (int)@$value['w_quantity'];
					foreach ($ids as $storeId) {
						StoreProductGroupAttributeExtra::updateOrCreate([
							'product_id' => $request->product_id,
							'attribute_value_ids' => @$value['attribute_value_ids'],
							'store_id' => $storeId
						], [
							'n_quantity' => (int) @$value['n_quantity'],
							'w_quantity' => (int) @$value['w_quantity'],
							'attribute_value_texts' => @$value['attribute_value_texts'],
						]);
					}
				}

				foreach ($ids as $storeId) {
					StoreProduct::updateOrCreate([
						'store_id' => $storeId,
						'product_id' => $request->product_id
					], [
						'n_quantity' => $nQuantity,
						'w_quantity' => $wQuantity
					]);
				}
			} else {
				foreach ($ids as $storeId) {
					StoreProduct::updateOrCreate([
						'store_id' => $storeId,
						'product_id' => $request->product_id
					], [
						'n_quantity' => $request->n_quantity,
						'w_quantity' => $request->w_quantity,
					]);
				}
			}

			return response()->json('OK');
		}

		return response()->json('ERROR', 422);
	}

	public function getProductPrice($id, Request $request, ProductRepository $repository)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$product = Product::find($request->product_id);
			if ($product) {
				$groupPrices = \DB::table('groups')
					->select(\DB::raw('groups.id, groups.display_name, group_product_discounts.discount'))
					->leftJoin('group_product_discounts', 'group_product_discounts.group_id', '=', 'groups.id')
					->where('group_product_discounts.product_id', $request->product_id)
					->where('groups.store_id', $id)
					->whereNull('groups.deleted_at')
					->get();


				return view('la.stores.product-price', [
					'groupPrices' => $groupPrices,
					'product' => $product
				])->render();
			}
		}

		return response()->json('ERROR', 422);
	}

	public function saveProductPrice($id, Request $request, ProductRepository $repository)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$product = Product::find($request->product_id);
			if ($product) {
				foreach ($request->groups as $group) {
					if (Group::where('id', $group['id'])->where('store_id', $id)->exists()) {
						GroupProductDiscount::updateOrCreate([
							'group_id' => $group['id'],
							'product_id' => $request->product_id,
						], [
							'discount' => $group['price'],
							'creator_id' => auth()->user()->id
						]);
					}
				}

				return response()->json('OK');
			}
		}

		return response()->json('ERROR', 422);
	}

	public function saveSharing($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			Store::find($id)->update([
				'sharing' => (int) $request->sharing
			]);
			return response()->json('OK');
		}

		return response()->json('ERROR', 422);
	}

	public function getVnpostSenders($id, Request $request)
	{
		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeShipping = StoreShipping::where('provider', CODOrder::PARTNER_VNPOST)
				->where("store_id", $id)
				->first();
			$storeShipping = $storeShipping ? json_decode($storeShipping["api_connection"], true) : [];
			$defaultId = @$storeShipping['vnpostDefaultStoreId'];
			$senders = collect(@$storeShipping["sender_list"] ?? [])
				->sortByDesc(function ($sender) use ($defaultId) {
					return $sender['SenderId'] == $defaultId;
				})
				->values();

			$page = $request->get("page", 1);
			$perPage = $request->get("perpage", 10);
			$offSet = ($page - 1) * $perPage;
			$items = $senders->slice($offSet, $perPage)->values();
			$items = new LengthAwarePaginator($items, $senders->count(), $perPage, $page, [
				"path" => $request->url(),
				"query" => $request->query()
			]);

			return view('la.stores.vnpost-senders', [
				'senders' => $items,
				'defaultId' => $defaultId
			])->render();
		}

		return response()->json('ERROR', 422);
	}

	public function createVnpostSender($id, Request $request, Generator $generator)
	{
		$this->validate($request, [
			'SenderFullname' => 'required',
			'SenderTel' => 'required',
			'SenderAddress' => 'required',
			'SenderProvinceId' => 'required',
			'SenderDistrictId' => 'required',
			'SenderWardId' => 'required',
		]);

		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeShipping = StoreShipping::where('provider', CODOrder::PARTNER_VNPOST)
				->where("store_id", $id)
				->first();
			if (!$storeShipping) {
				return response()->json('Chưa thiết lập tài khoản VNPOST', 422);
			}
			$apiConnection = json_decode($storeShipping['api_connection'], true);
			$senders = @$apiConnection["sender_list"] ?? [];

			$senderId = '';
			do {
				$senderId = $generator->generate(5);
			} while (in_array($senderId, array_column($senders, 'SenderId')));

			$data = array_merge($request->except(['_token', 'default']), [
				'SenderId' => $senderId,
			]);

			array_push($senders, $data);
			$apiConnection['sender_list'] = $senders;
			if (count($senders) == 1 || $request->has('default')) {
				$apiConnection['vnpostDefaultStoreId'] = $senderId;
			}
			$storeShipping->update([
				'api_connection' => json_encode($apiConnection)
			]);

			return response()->json($data);
		}

		return response()->json('ERROR', 422);
	}

	public function removeVnpostSender($id, Request $request)
	{
		$this->validate($request, [
			'sender_id' => 'required',
		]);

		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeShipping = StoreShipping::where('provider', CODOrder::PARTNER_VNPOST)
				->where("store_id", $id)
				->first();
			if (!$storeShipping) {
				return response()->json('Chưa thiết lập tài khoản VNPOST', 422);
			}

			$apiConnection = json_decode($storeShipping['api_connection'], true);
			$senders = @$apiConnection["sender_list"] ?? [];
			$defaultId = @$apiConnection['vnpostDefaultStoreId'];
			$senders = array_values(array_filter($senders, function ($sender) use ($request) {
				return $sender['SenderId'] !== $request->sender_id;
			}));
			$apiConnection['sender_list'] = $senders;
			if ($request->sender_id == $defaultId) {
				$apiConnection['vnpostDefaultStoreId'] = $senders[0]['SenderId'];
			}
			$storeShipping->update([
				'api_connection' => json_encode($apiConnection)
			]);

			return response()->json('OK');
		}

		return response()->json('ERROR', 422);
	}

	public function updateVnpostSender($id, Request $request)
	{
		$this->validate($request, [
			'SenderId' => 'required',
			'SenderFullname' => 'required',
			'SenderTel' => 'required',
			'SenderAddress' => 'required',
			'SenderProvinceId' => 'required',
			'SenderDistrictId' => 'required',
			'SenderWardId' => 'required',
		]);

		if (
			Module::hasAccess("Stores", "view")
			&& (!auth()->user()->store_id || auth()->user()->store_id == $id)
		) {
			$storeShipping = StoreShipping::where('provider', CODOrder::PARTNER_VNPOST)
				->where("store_id", $id)
				->first();
			if (!$storeShipping) {
				return response()->json('Chưa thiết lập tài khoản VNPOST', 422);
			}
			$apiConnection = json_decode($storeShipping['api_connection'], true);
			$senders = @$apiConnection["sender_list"] ?? [];
			$senders = array_filter($senders, function ($sender) use ($request) {
				return $sender['SenderId'] !== $request->SenderId;
			});
			$data = $request->except(['_token', 'default']);
			array_push($senders, $data);

			$apiConnection['sender_list'] = $senders;
			if ($request->has('default')) {
				$apiConnection['vnpostDefaultStoreId'] = $request->SenderId;
			}
			$storeShipping->update([
				'api_connection' => json_encode($apiConnection)
			]);

			return response()->json('OK');
		}

		return response()->json('ERROR', 422);
	}
}
