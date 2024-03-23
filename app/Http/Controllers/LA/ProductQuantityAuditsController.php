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
use App\Models\Store;
use App\Models\Product;
use App\User;
use App\Models\Order;

use App\Models\ProductQuantityAudit;

class ProductQuantityAuditsController extends Controller
{
	public $show_action = false;
	public $view_col = 'attrs_value';
	public $listing_cols = ['id', 'store_id', 'product_id', 'attrs_value', 'amount', 'left', 'updated_type', 'order_id', 'creator_id', 'created_at'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('ProductQuantityAudits', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('ProductQuantityAudits', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the ProductQuantityAudits.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('ProductQuantityAudits');
		
		if(Module::hasAccess($module->id)) {
			return View('la.productquantityaudits.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new productquantityaudit.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created productquantityaudit in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("ProductQuantityAudits", "create")) {
		
			$rules = Module::validateRules("ProductQuantityAudits", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("ProductQuantityAudits", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.productquantityaudits.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified productquantityaudit.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("ProductQuantityAudits", "view")) {
			
			$productquantityaudit = ProductQuantityAudit::find($id);
			if(isset($productquantityaudit->id)) {
				$module = Module::get('ProductQuantityAudits');
				$module->row = $productquantityaudit;
				
				return view('la.productquantityaudits.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('productquantityaudit', $productquantityaudit);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("productquantityaudit"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified productquantityaudit.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("ProductQuantityAudits", "edit")) {			
			$productquantityaudit = ProductQuantityAudit::find($id);
			if(isset($productquantityaudit->id)) {	
				$module = Module::get('ProductQuantityAudits');
				
				$module->row = $productquantityaudit;
				
				return view('la.productquantityaudits.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('productquantityaudit', $productquantityaudit);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("productquantityaudit"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified productquantityaudit in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("ProductQuantityAudits", "edit")) {
			
			$rules = Module::validateRules("ProductQuantityAudits", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("ProductQuantityAudits", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.productquantityaudits.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified productquantityaudit from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("ProductQuantityAudits", "delete")) {
			ProductQuantityAudit::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.productquantityaudits.index');
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
		$values = ProductQuantityAudit::select($this->listing_cols)->whereNull('deleted_at')->orderBy('id', 'desc');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('ProductQuantityAudits');
		$stores = Store::whereNull('deleted_at')->pluck('name', 'id');
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == "store_id") {
				   $data->data[$i][$j] = @$stores[$data->data[$i][$j]];
				}
				else if($col == "product_id") {
					$product = Product::find($data->data[$i][$j]);
					$data->data[$i][$j] = $product ? $product->name . ' - ' . $product->sku : '';
				 }
				 else if($col == "creator_id") {
					$product = User::find($data->data[$i][$j]);
					$data->data[$i][$j] = $product ? $product->name : '';
				 }else if($col == "order_id") {
					$product = Order::find($data->data[$i][$j]);
					$data->data[$i][$j] = $product ? $product->code : '';
				 }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("ProductQuantityAudits", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/productquantityaudits/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("ProductQuantityAudits", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.productquantityaudits.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
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
