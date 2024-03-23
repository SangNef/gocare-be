<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Revenue;
use App\Models\ProductCategory;

class RevenuesController extends Controller
{
	public $show_action = true;
	public $view_col = 'total';
	public $listing_cols = ['id', 'store_id', 'total', 'product_amount', 'bank_amount', 'customer_amount', 'reported_at'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Revenues', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Revenues', $this->listing_cols);
		}

		if (!auth()->check() || !in_array(auth()->user()->id, explode(',', config('app.revenue_accessible')))) {
			redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Display a listing of the Revenues.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Revenues');
		
		if(Module::hasAccess($module->id)) {
			return View('la.revenues.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new revenue.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created revenue in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Revenues", "create")) {
		
			$rules = Module::validateRules("Revenues", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("Revenues", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.revenues.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified revenue.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Revenues", "view")) {
			
			$revenue = Revenue::find($id);
			if(isset($revenue->id)) {
				$module = Module::get('Revenues');
				$module->row = $revenue;
				$categories = ProductCategory::all()->pluck('name', 'id');
				return view('la.revenues.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding",
					'categories' => $categories,
				])->with('revenue', $revenue);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("revenue"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified revenue.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Revenues", "edit")) {			
			$revenue = Revenue::find($id);
			if(isset($revenue->id)) {	
				$module = Module::get('Revenues');
				
				$module->row = $revenue;
				
				return view('la.revenues.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('revenue', $revenue);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("revenue"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified revenue in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Revenues", "edit")) {
			
			$rules = Module::validateRules("Revenues", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Revenues", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.revenues.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified revenue from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Revenues", "delete")) {
			Revenue::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.revenues.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax()
	{
		$values = DB::table('revenues')->select($this->listing_cols)->whereNull('deleted_at')->orderBy('id', 'desc');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Revenues');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if(in_array($col, ['product_amount', 'total', 'bank_amount', 'customer_amount'])) {
				   $data->data[$i][$j] = number_format($data->data[$i][$j]) . 'd';
				}
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Revenues", "view")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/revenues/'.$data->data[$i][0]).'" class="btn btn-light btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-eye"></i></a>';
				}
				if(Module::hasAccess("Revenues", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/revenues/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Revenues", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.revenues.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}
}
