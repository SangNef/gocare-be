<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use App\Helper\CustomLAHelper;
use App\Models\OnlineCustomer;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Group;

class OnlineCustomersController extends Controller
{
	public $show_action = true;
	public $view_col = 'order_id';
	public $listing_cols = ['id', 'order_id', 'store_id', 'name', 'email', 'phone', 'address'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('OnlineCustomers', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('OnlineCustomers', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the OnlineCustomers.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('OnlineCustomers');

		if (Module::hasAccess($module->id)) {
			return View('la.onlinecustomers.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new onlinecustomer.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created onlinecustomer in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
	}

	/**
	 * Display the specified onlinecustomer.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
	}

	/**
	 * Show the form for editing the specified onlinecustomer.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
	}

	/**
	 * Update the specified onlinecustomer in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
	}

	/**
	 * Remove the specified onlinecustomer from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("OnlineCustomers", "delete")) {
			try {
				DB::beginTransaction();
				$oCustomer = OnlineCustomer::findOrFail($id);
				$oCustomer->delete();

				DB::commit();
				return redirect()->back();
			} catch (\Exception $exception) {
				DB::rollback();
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
	public function dtajax()
	{
		$values = DB::table('onlinecustomers')
			->select($this->listing_cols)
			->whereNull('deleted_at')
			->orderBy('id', 'desc');

		$datatable = Datatables::of($values);

		$datatable->filterColumn('order_id', function ($query, $keyword) {
			$orders = Order::where('code', $keyword)->pluck('id')->toArray();
			$query->whereIn('order_id', $orders);
		});

		$out = $datatable->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('OnlineCustomers');

		for ($i = 0; $i < count($data->data); $i++) {
			$id = $data->data[$i][0];
			$oCustomer = OnlineCustomer::find($id);
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $oCustomer->order_id . '/edit') . '">' . $data->data[$i][$j] . '</a>';
				}
			}

			if ($this->show_action) {
				$output = '';
				$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.onlinecustomers.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']);
				$output .= ' <button class="btn btn-danger btn-xs form-confirmation" type="submit"><i class="fa fa-times"></i></button>';
				$output .= Form::close();
				$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/onlinecustomers/' . $id . '/create-customer') . '" class="btn btn-success btn-xs" style="display:inline;padding:2px 5px 3px 5px;">Tạo khách hàng</a>';
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function createCustomer($id)
	{
		try {
			DB::beginTransaction();
			$oCustomer = OnlineCustomer::findOrFail($id);
			if (Customer::where('phone', $oCustomer->phone)->exists()) {
				throw new \Exception('Khách hàng đã tồn tại');
			}

			$address = $oCustomer->mainAddress;
			Customer::create([
				'name' => $oCustomer->name,
				'email' => $oCustomer->email,
				'phone' => $oCustomer->phone,
				'address' => $oCustomer->address,
				'username' => preg_replace('/\s+/', '', strtolower(CustomLAHelper::removeAccents($oCustomer->name))),
				'province' => $address->province,
				'district' => $address->district,
				'ward' => $address->ward,
				'store_id' => $oCustomer->store_id,
				'group_id' => Group::getFECustomerGroup()->id,
			]);

			DB::commit();
			return redirect()->back();
		} catch (\Exception $exception) {
			DB::rollback();
			\Log::error($exception->getMessage());
			\Log::error($exception->getTraceAsString());

			return redirect()->back()->withErrors($exception->getMessage());
		}
	}
}
