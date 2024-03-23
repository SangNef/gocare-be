<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Models\StoreObserve;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Audit;

class AuditsController extends Controller
{
	public $show_action = true;
	public $view_col = 'customer_id';
	public $listing_cols = ['id', 'store_id', 'customer_id', 'order_id', 'trans_id', 'amount', 'balance'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Audits', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Audits', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Audits.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Audits');
		
		if(Module::hasAccess($module->id)) {
			return View('la.audits.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new audit.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created audit in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Audits", "create")) {
		
			$rules = Module::validateRules("Audits", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("Audits", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.audits.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified audit.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Audits", "view")) {
			
			$audit = Audit::find($id);
			if(isset($audit->id)) {
				$module = Module::get('Audits');
				$module->row = $audit;
				
				return view('la.audits.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('audit', $audit);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("audit"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified audit.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Audits", "edit")) {			
			$audit = Audit::find($id);
			if(isset($audit->id)) {	
				$module = Module::get('Audits');
				
				$module->row = $audit;
				
				return view('la.audits.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('audit', $audit);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("audit"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified audit in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Audits", "edit")) {
			
			$rules = Module::validateRules("Audits", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Audits", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.audits.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified audit from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Audits", "delete")) {
			Audit::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.audits.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(Request $request)
	{
		$values = Audit::select($this->listing_cols)
            ->search($request->all())
            ->orderBy('id', 'desc')
            ->whereNull('deleted_at');
        $observers = StoreObserve::all()->pluck('customer_id')->toArray();
        $values->whereNotIn('customer_id', $observers);
        if (auth()->user()->store_id)
        {
            $values->where('store_id', auth()->user()->store_id);
        }

        $datatable = Datatables::of($values);
        $datatable->filterColumn('store_id', function ($query, $keyword) {
            $query->where('store_id', $keyword);
        });
        $datatable->filterColumn('customer_id', function ($query, $keyword) {
            $query->where('customer_id', $keyword);
        });
        $datatable->filterColumn('order_id', function ($query, $keyword) {
            $orders = Order::where('code', $keyword)->pluck('id')->toArray();
            $query->whereIn('order_id', $orders);
        });
        $datatable->filterColumn('trans_id', function ($query, $keyword) {
            $query->where('trans_id', $keyword);
        });
        $datatable->filterColumn('id', function ($query, $keyword) {
            $query->where('id', $keyword);
        });
        $out = $datatable->make();
        $data = $out->getData();
        $total = [
            'total_amount' => number_format($values->sum('amount'))
        ];
		$fields_popup = ModuleFields::getModuleFields('Audits');
		$stores = Store::all()->pluck('name', 'id')->toArray();
		for($i=0; $i < count($data->data); $i++) {
		    $audit = Audit::find($data->data[$i][0]);
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
                if($col == 'store_id') {
                    $data->data[$i][$j] = @$stores[$data->data[$i][$j]];
                } else if($col == 'customer_id') {
                    $data->data[$i][$j] = $audit->customer->name;
				} else if($col == 'order_id') {
                    $data->data[$i][$j] = '<a target="_blank" href="'. url(config('laraadmin.adminRoute') . '/orders/' . $audit->order_id) .'">' . ($audit->order ? $audit->order->code : '') . '</a>';
                } else if($col == 'trans_id') {
                    $data->data[$i][$j] = '<a target="_blank" href="'. url(config('laraadmin.adminRoute') . '/transactions/' . $audit->trans_id) .'">' . ($audit->transaction ? $audit->transaction->id : '') . '</a>';
                }
                 else if($col == "amount" || $col == "balance") {
                    $data->data[$i][$j] = number_format($data->data[$i][$j]) . 'đ';
                 }
			}
		}
        $data->total = $total;
		$out->setData($data);
		return $out;
	}
}
