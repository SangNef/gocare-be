<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use App\Models\Bank;
use App\Models\Commission;
use App\Models\CustomerBacklog;
use App\Models\LockCommission;
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
use App\Services\CODPartners\GHTKService;
use App\Services\CODPartners\VTPService;

class CTVController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'store_id', 'username', 'email', 'phone', 'address'];

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
			return View('la.ctv.index', [
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
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(Request $request)
	{
		$cols = array_map(function ($value) {
			return 'cu.' . $value;
		}, $this->listing_cols);
		$values = DB::table('customers as cu')->select($cols)->whereNull('deleted_at')->orderBy('id', 'desc');
		if (auth()->user()->store_id) {
			$values->where('store_id', auth()->user()->store_id);
		}
		$values->where('group_id', Group::where('name', 'ctv_dien_tu')->first()->id);
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
		$out = $datatable->make();
		$data = $out->getData();
		session(['filter_customer_' . auth()->user()->id => json_encode($datatable->getFilteredQuery() ? $datatable->getFilteredQuery()->limit($datatable->totalCount())->pluck('id') : [])]);

		$lockCommission = LockCommission::whereIn('id', LockCommission::select(\Illuminate\Support\Facades\DB::raw('max(id) as id'))
                ->whereNull('deleted_at')
                ->groupBy('customer_id')
                ->get()
                ->pluck('id')
            )
            ->sum('balance');
        $commission = Commission::whereIn('id', Commission::select(\Illuminate\Support\Facades\DB::raw('max(id) as id'))
                ->whereNull('deleted_at')
                ->groupBy('customer_id')
                ->get()
                ->pluck('id')
            )
            ->sum('balance');

		$total = [
			'lock_commission' => $lockCommission,
			'commission' => $commission,
		];

		$fields_popup = ModuleFields::getModuleFields('Customers');

		for ($i = 0; $i < count($data->data); $i++) {
			$customer = Customer::findOrFail($data->data[$i][0]);
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/customers/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}

				if ($col === 'username') {
					$data->data[$i][$j] = $data->data[$i][$j] . ' - <strong style="color: #ff0000">' . \App\Models\Bank::availableCurrency()[$customer->customer_currency] . '</strong>';
				}
			}
			$data->data[$i][5] = $customer->getFullAddress();
			$lc = LockCommission::where('customer_id', $data->data[$i][0])->orderBy('created_at', 'desc')->first();
			$c = Commission::where('customer_id', $data->data[$i][0])->orderBy('created_at', 'desc')->first();
            $data->data[$i][] = ($lc ? number_format($lc->balance) : 0) . 'đ';
            $data->data[$i][] = ($c ? number_format($c->balance) : 0) . 'đ';
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
}
