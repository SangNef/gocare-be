<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use App\Models\Bank;
use App\Models\CODOrder;
use App\Models\CustomerBacklog;
use App\Models\CustomerRevenue;
use App\Repositories\GroupRepository;
use Illuminate\Http\Request;
use Auth;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Customer;
use App\Models\Group;
use App\Repositories\CODOrderRepository;
use App\Services\CODPartners\GHNService;
use App\Services\CODPartners\GHN5Service;
use App\Services\CODPartners\GHTKService;
use App\Services\CODPartners\StoreShippingService;
use App\Services\CODPartners\VNPostService;
use App\Services\CODPartners\VTPService;
use App\Repositories\CustomerRepository;
use App\Models\TransferOrder;
use App\Models\Order;
use Carbon\Carbon;

class CustomersController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'code', 'store_id', 'name', 'email', 'group_id', 'phone', 'address', 'parent_id', 'debt_in_advance', 'debt_total'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Customers', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Customers', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the Customers.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Customers');
		$provinces = \App\Models\Province::get(['name', 'id']);

		if (Module::hasAccess($module->id)) {
			return View('la.customers.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module,
				'provinces' => $provinces,
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new customer.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created customer in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if (Module::hasAccess("Customers", "create")) {

			$rules = Module::validateRules("Customers", $request);
			$rules = array_merge($rules, [
				'password' => 'required|confirmed|min:6',
				'email' => 'max:250|required|unique:customers,email',
				'username' => 'max:250|required|unique:customers,username',
				'phone' => 'max:10|required|unique:customers,phone',
			]);
			if (!auth()->user()->store_id) {
				$rules['store_id'] = 'required';
			}
			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$request->merge(['debt_total' => $request->debt_in_advance]);
				$insert_id = Module::insert("Customers", $request);
				$customer = Customer::find($insert_id);
				$customer->password = $request->password;
				$customer->save();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.customers.index');
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
	 * Display the specified customer.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id, Request $request)
	{
		if (Module::hasAccess("Customers", "view")) {

			$customer = Customer::find($id);
			if (isset($customer->id)) {
				$module = Module::get('Customers');
				$module->row = $customer;
                $reports = CustomerRevenue::select(['id', 'accepted_at', 'month'])
                    ->orderBy('created_at', 'desc')
                    ->where('customer_id', $customer->id)
                    ->get();
                if ($request->has('report_id')) {
                    $report = CustomerRevenue::where('customer_id', $customer->id)
                        ->where('id', $request->get('report_id'))
                        ->first();
                } else {
                    $report = CustomerRevenue::where('customer_id', $customer->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }

				return view('la.customers.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('customer', $customer)
                    ->with('reports', $reports)
                    ->with('report', $report);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("customer"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified customer.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id, CODOrderRepository $codOrderRepository)
	{
		if (Module::hasAccess("Customers", "edit")) {
			$customer = Customer::find($id);
			if (isset($customer->id)) {
				$module = Module::get('Customers');
				$module->row = $customer;
				$currentProvince = \App\Models\Province::where('name', $customer->province)->first();
				$currentDistrict = \App\Models\District::where('name', $customer->district)->first();
				$currentWard = \App\Models\Ward::where('name', $customer->ward)->first();
				$provinces = \App\Models\Province::where('id', '<>', $currentProvince ? $currentProvince->id : 0)->pluck('name', 'id');
				$districts = \App\Models\District::where('province_id', $currentProvince ? $currentProvince->id : 0)
					->where('id', '<>', $currentDistrict ? $currentDistrict->id : 0)->pluck('name', 'id');
				$wards = \App\Models\Ward::where('district_id', $currentDistrict ? $currentDistrict->id : 0)
					->where('id', '<>', $currentWard ? $currentWard->id : 0)->pluck('name', 'id');
				$providers = $codOrderRepository->getAvailableProvider();
				$shippingSetups = $customer->shippingSetups->keyBy('partner')->toArray();
				array_walk($providers, function (&$provider, $partner) use ($shippingSetups, $customer) {
					$service = StoreShippingService::getProvider($partner);
					$apiconnection = $service ? $service->apiConnection() : [];
					$existedValue = @$shippingSetups[$partner] ?? [];
					$isActive = @$existedValue['is_active'] ?? false;
					$connection =  [];
					foreach ($apiconnection as $cKey) {
						if (in_array($cKey, ['username', 'password', 'token'])) {
							$connection[$cKey] = @$existedValue['connection'][$cKey];
						}
					}
					$partnerSv = app(get_class($service), $connection);
					switch ($partner) {
                        case 'ghn_5':
						case 'ghn':
							$storeFields = ['name', '_id'];
							break;
						case 'ghtk':
							$storeFields = ['pick_name', 'pick_address_id'];
							break;
						case 'vtp':
							$storeFields = ['name', 'groupaddressId'];
							break;
						default:
							$storeFields = [];
					}
					$stores = [];
					if ($isActive) {
						if ($partner == CODOrder::PARTNER_VNPOST) {
							$vnpostStoreShipping = $customer->store->shippings()
								->where('provider', CODOrder::PARTNER_VNPOST)
								->first();
							if ($vnpostStoreShipping) {
								$vnpostStoreShipping = json_decode($vnpostStoreShipping->api_connection, true);
								$senderLists = @$vnpostStoreShipping['sender_list'] ?? [];
								foreach ($senderLists as $sender) {
									$stores[$sender['SenderId']] = implode(' - ', [$sender['SenderFullname'], $sender['SenderTel'], $sender['SenderAddress']]);
								}
							}
						} else {
							$stores = collect($partnerSv->getStores())->pluck($storeFields[0], $storeFields[1]);
						}
					}

					$provider = [
						'name' => $provider,
						'key' => $partner,
						'connection' => $connection,
						'is_active' => $isActive,
						'inventory' => @$existedValue['inventory'],
						'stores' => $stores,
					];
				});


				
				return view('la.customers.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
					'currentProvince' => $currentProvince,
					'currentDistrict' => $currentDistrict,
					'currentWard' => $currentWard,
					'provinces' => $provinces,
					'districts' => $districts,
					'wards' => $wards,
					'providers' => $providers,
				])->with('customer', $customer);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("customer"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified customer in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if (Module::hasAccess("Customers", "edit")) {

			$rules = Module::validateRules("Customers", $request, true);
			$rules = array_merge($rules, [
				'email' => 'max:250|required|unique:customers,email,' . $id,
				'name' => 'max:250|required|unique:customers,name,' . $id,
				'password' => 'confirmed|min:6',
				'shipping_setups.vtp.connection.username' => 'required_if:shipping_setups.vtp.is_active,on',
				'shipping_setups.vtp.connection.password' => 'required_if:shipping_setups.vtp.is_active,on',
				'shipping_setups.ghtk.connection.token' => 'required_if:shipping_setups.ghtk.is_active,on',
				'shipping_setups.ghn.connection.token' => 'required_if:shipping_setups.ghn.is_active,on',
			]);
			$messages = [
				'shipping_setups.vtp.connection.username.required_if' => '(VTP) Username không được để trống',
				'shipping_setups.vtp.connection.password.required_if' => '(VTP) Password không được để trống',
				'shipping_setups.ghtk.connection.token.required_if' => '(GHTK) Token không được để trống',
				'shipping_setups.ghn.connection.token.required_if' => '(GHN) Token không được để trống',
			];
			$validator = Validator::make($request->all(), $rules, $messages);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}

			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$insert_id = Module::updateRow("Customers", $request, $id);
				$customer = Customer::find($insert_id);
				if ($request->password) {
					$customer->password = $request->password;
				}
				if ($request->has('cccd')) {
					$customer->cccd = $request->input('cccd');
				}
		
				if ($request->has('bank_name')) {
					$customer->bank_name = $request->input('bank_name');
				}
		
				if ($request->has('bank_acc')) {
					$customer->bank_acc = $request->input('bank_acc');
				}
		
				if ($request->has('bank_acc_name')) {
					$customer->bank_acc_name = $request->input('bank_acc_name');
				}
				$customer->can_create_sub = !@$request->can_create_sub ? 0 : 1;
				foreach ($request->shipping_setups as $partner => $setup) {
					if (isset($setup['is_active'])) {
						if ($partner == 'vtp') {
							$token = (new VTPService($setup['connection']['username'], $setup['connection']['password']))->ownerToken();
							if (!$token) {
								throw new \Exception('Tài khoản VTP không đúng');
							}
						} else if ($partner == 'ghn') {
							(new GHNService($setup['connection']['token']))->getStores();
						} else if ($partner == 'ghn_5') {
                            (new GHN5Service($setup['connection']['token']))->getStores();
                        }  else if ($partner == 'ghtk') {
							(new GHTKService($setup['connection']['token']))->getStores();
						} else {
							(new VNPostService($setup['connection']['username'], $setup['connection']['password']))->login();
						}
					}
					$customer->shippingSetups()->updateOrCreate([
						'partner' => $partner
					], [
						'is_active' => isset($setup['is_active']),
						'connection' => $setup['connection'],
						'inventory' => @$setup['inventory']
					]);
				}
				if ($request->has('create_accesstoken')) {
					$token = AccessToken::create([
						'name' => $customer->username . '_vtp_tracking_order',
						'status' => 1
					]);
					$customer->accesstoken_id = $token->id;
				}
				$customer->save();
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
	 * Remove the specified customer from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("Customers", "delete")) {
			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$customer = Customer::findOrFail($id);
				$customer->delete();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.customers.index');
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
	public function dtajax(Request $request)
	{
		$type = $request->input('type');

		$cols = array_map(function ($value) {
			return 'cu.' . $value;
		}, ['id', 'code', 'store_id', 'name', 'email', 'group_id', 'phone', 'address', 'parent_id', 'debt_in_advance', 'debt_total']);
		
		$values = DB::table('customers as cu')->select($cols)->whereNull('deleted_at')->orderBy('id', 'desc');

		if ($type == 'ctv') {
			$values->where('group_id', 52);
		} elseif ($type == 'daily') {
			$values->where('group_id', 6);
		} elseif ($type == 'khachhang') {
			$values->where('group_id', 51);
		};

		switch ($request->debt) {
			case 1:
				$values->where('debt_total', '<', 0);
				break;
			case 2:
				$values->where('debt_total', '>', 0);
				break;
		}
		if (auth()->user()->store_id) {
			$values->where('store_id', auth()->user()->store_id);
		}
		$datatable = app(\App\Datatable\Datatables::class)->of($values);
		$datatable->filterColumn('cu.address', function ($query, $keyword) {
			$query->where('address', 'LIKE', '%' . $keyword . '%')
				->orWhere('province', 'LIKE', '%' . $keyword . '%')
				->orWhere('district', 'LIKE', '%' . $keyword . '%')
				->orWhere('ward', 'LIKE', '%' . $keyword . '%');
		});
		$datatable->filterColumn('cu.store_id', function ($query, $keyword) {
			$query->where('store_id', $keyword);
		});
		$datatable->filterColumn('cu.group_id', function ($query, $keyword) {
			$query->where('group_id', $keyword);
		});
		$out = $datatable->make();
		$data = $out->getData();
		session(['filter_customer_' . auth()->user()->id => json_encode($datatable->getFilteredQuery() ? $datatable->getFilteredQuery()->limit($datatable->totalCount())->pluck('id') : [])]);

		$values = $values->leftJoin('customer_backlogs as cb', 'cb.customer_id', '=', 'cu.id')
			->where('cb.debt_type', CustomerBacklog::BEGINING)
			->addSelect('cb.money_in', 'cb.money_out', 'cb.has', 'cb.debt')
			->get();

		$total = [
			'import' => array_sum(array_column($values, 'money_in')),
			'export' => array_sum(array_column($values, 'money_out')),
			'has' => array_sum(array_column($values, 'has')),
			'debt' => array_sum(array_column($values, 'debt')),
			'debt_total' => array_sum(array_column($values, 'debt_total'))
		];

		$fields_popup = ModuleFields::getModuleFields('Customers');

		for ($i = 0; $i < count($data->data); $i++) {
			$customer = Customer::findOrFail($data->data[$i][0]);
			for ($j = 0; $j < count(['id', 'code', 'store_id', 'name', 'email', 'group_id', 'phone', 'address', 'parent_id', 'debt_in_advance', 'debt_total']); $j++) {
				$col = ['id', 'code', 'store_id', 'name', 'email', 'group_id', 'phone', 'address', 'parent_id', 'debt_in_advance', 'debt_total'][$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col || $col == 'phone') {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/customers/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}
				if (in_array($col, ['debt_in_advance', 'debt_total'])) {
					$symbol = $customer->customer_currency == Bank::CURRENCY_NDT ? ' NDT' : ' đ';
					$data->data[$i][$j] = number_format($data->data[$i][$j], 2) . $symbol;
				}
				// if ($col == 'group_id') {
				// 	$group = Group::where('name', $data->data[$i][$j])->first();
				// 	$data->data[$i][$j] = $group ? $group->display_name : '';
				// }
				if ($col == 'name') {
					$data->data[$i][$j] = $customer->name . ' - <strong style="color: red">' . \App\Models\Bank::availableCurrency()[$customer->customer_currency] . '</strong>';
				}
				if ($col == 'address') {
                    $data->data[$i][$j] = $customer->getFullAddress();
                }
			}

			$data->data[$i][] = $customer->cccd;
            $data->data[$i][] = $customer->bank_name;
            $data->data[$i][] = $customer->bank_acc;
            $data->data[$i][] = $customer->bank_acc_name;

			if ($this->show_action) {
				$output = '';
				$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/customers/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.customers.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
				$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
				$output .= Form::close();
				$data->data[$i][] = (string)$output;
			}
		}
		$total = array_map(function ($item) {
			return number_format($item);
		}, $total);
		$data->total = $total;

		$out->setData($data);
		return $out;
	}

	protected function renderUserBacklogs($id)
	{
		$customer = Customer::find($id);
		$backlogs = $customer->backlogs;
		$symbol = $customer->customer_currency == Bank::CURRENCY_NDT ? ' NDT' : ' đ';

		return \View::make('la.customers.customer-backlog', compact('backlogs', 'symbol'))->render();
	}

	public function getAddress(Request $request)
	{
		switch ($request->model) {
			case 'province':
				$data = \App\Models\Province::find($request->id);
				$relation = 'districts';
				break;
			case 'district':
				$data = \App\Models\District::find($request->id);
				$relation = 'wards';
				break;
		}
		$results = [];
		if ($data && $data->{$relation}->count() > 0) {
			$results = $data->{$relation}->pluck('name', 'id');
		}

		return response()->json($results);
	}

	public function export()
	{
		$ids = json_decode(session('filter_customer_' . auth()->user()->id));
		$customers = Customer::with(['backlogs' => function ($query) {
			return $query->where('debt_type', CustomerBacklog::BEGINING);
		}])->whereIn('id', $ids)->orderBy('id', 'desc')->get();
		$fileName = 'Khách hàng ' . date('d/m/Y');
		$data = [];
		foreach ($customers as $customer) {
			if ($customer) {
				$data[] = [
					'ID' => $customer->id,
					'Tên' => $customer->name,
					'Email' => $customer->email,
					'SĐT' => $customer->phone,
					'Địa chỉ' => $customer->getFullAddress(),
					'Nợ trước' => number_format($customer->debt_in_advance) . ' đ',
					'Nhập' => number_format($customer->backlogs[0]->money_in) . ' đ',
					'Xuất' => number_format($customer->backlogs[0]->money_out) . ' đ',
					'Có' => number_format($customer->backlogs[0]->has) . ' đ',
					'Nợ' => number_format($customer->backlogs[0]->debt) . ' đ',
					'Tổng nợ' => number_format($customer->debt_total) . ' đ'
				];
			}
		}

		Excel::create($fileName, function ($excel) use ($data) {
			$excel->sheet('Customers', function ($sheet) use ($data) {
				$sheet->fromArray($data);
			});
		})->export('csv');
	}

	public function createUserFromOrder(Request $request)
	{
		$rules = [
			'username' => 'required|unique:customers,username',
			'name' => 'required',
			'group_id' => 'required',
			'email' => 'required|unique:customers,email'
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return response()->json($validator->messages(), 400);
		}
		$insert_id = Module::insert("Customers", $request);
		$customer = Customer::find($insert_id);
		if (!$customer->store_id && $customer->group) {
			$customer->store_id = $customer->group->store_id;
			$customer->save();
		}
		return response()->json([
			'id' => $customer->id,
			'name' => $customer->name ?: $customer->username
		]);
	}

	public function getStoreCustomer($id)
	{
		$customer = Customer::find($id);

		return response()->json($customer ?  [
			'id' => $customer->store_id,
			'text' => $customer->store->name
		] : []);
	}

	public function getCustomerByUsername(Request $request)
	{
		$customer = Customer::find($request->customer_id);
		return [
			'name' => @$customer->username,
			'phone' => @$customer->phone,
			'address' => @$customer->address,
			'province' => $customer ? $customer->getProvince() : null,
			'district' => $customer ? $customer->getDistrict() : null,
			'ward' => $customer ? $customer->getWard() : null
		];
	}

	public function getSubCustomers($id, CustomerRepository $rp, Request $request)
	{
		$customer = Customer::find($id);
		
		$subs = $rp->getSubCustomers($id, [])
			->paginate();
		$customers = $subs->getCollection()->map(function ($sub) use ($request) {
			$orders = TransferOrder::where('customer_id', $sub->id)
				->whereBetween('created_at', [
					$request->from ? Carbon::createFromFormat('Y/m/d', $request->from) : Carbon::now()->subDays(7)->startOfDay(),
					$request->to ? Carbon::createFromFormat('Y/m/d', $request->to) : Carbon::now()->endOfDay()
				]);
			$sub->number_of_orders = $orders->count();
			$sub->total_amount = $orders->sum('amount');

			return $sub;
		});
		if ($customer->customer_parent_id) {
			$orders = TransferOrder::where('customer_id', $customer->id)
				->whereBetween('created_at', [
					$request->from ? Carbon::createFromFormat('Y/m/d', $request->from) : Carbon::now()->subDays(7)->startOfDay(),
					$request->to ? Carbon::createFromFormat('Y/m/d', $request->to) : Carbon::now()->endOfDay()
				]);
			$current = [
				'number_of_orders' => $orders->count(),
				'total_amount' => $orders->sum('amount')
			];
		} else {
			$orders = Order::where('customer_id', $customer->id)
				->whereIn('status', [1,2])
				->whereBetween('created_at', [
					$request->from ? Carbon::createFromFormat('Y/m/d', $request->from) : Carbon::now()->subDays(7)->startOfDay(),
					$request->to ? Carbon::createFromFormat('Y/m/d', $request->to) : Carbon::now()->endOfDay()
				]);
			$current = [
				'number_of_orders' => $orders->count(),
				'total_amount' => $orders->sum('total')
			];
		}

		return view('la.customers.sub-customers', [
			'pagination' => $subs,
			'subs' => $customers,
			'current' => $current
		])->render();
	}

	public function acceptReport($id, $reportId)
    {
        $report = CustomerRevenue::where('customer_id', $id)
            ->where('id', $reportId)
            ->first();

        if ($report && !$report->accepted_at) {
            $report->accepted_at = Carbon::now()->format('d/m/Y H:i');
            $report->save();
            return back()->with('success', 'Cập nhập thành công');
        }

        return back()->with('error', 'Cập nhập thất bại.');
    }
    
	public function getGroupDiscount($id)
    {
        $customer = Customer::find($id);
        $rp = app(GroupRepository::class);

        $discounts = $rp->getDiscounts($customer->group_id);

        if (!empty($discounts)) {
            return array_values($discounts)[0]['discount'];
        }

        return [];
    }
}
