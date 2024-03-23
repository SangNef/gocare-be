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

use App\Models\Voucher;

class VouchersController extends Controller
{
	public $show_action = true;
	public $view_col = 'code';
	public $listing_cols = ['id', 'name', 'code', 'percent', 'min_order_amount', 'started_at', 'ended_at', 'owner_id'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Vouchers', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Vouchers', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Vouchers.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Vouchers');
		
		if(Module::hasAccess($module->id)) {
			return View('la.vouchers.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new voucher.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created voucher in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Vouchers", "create")) {
		
			$rules = Module::validateRules("Vouchers", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("Vouchers", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.vouchers.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified voucher.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Vouchers", "view")) {
			
			$voucher = Voucher::find($id);
			if(isset($voucher->id)) {
				$module = Module::get('Vouchers');
				$module->row = $voucher;
				
				return view('la.vouchers.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('voucher', $voucher);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("voucher"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified voucher.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Vouchers", "edit")) {			
			$voucher = Voucher::find($id);
			if(isset($voucher->id)) {	
				$module = Module::get('Vouchers');
				
				$module->row = $voucher;
				
				return view('la.vouchers.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('voucher', $voucher);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("voucher"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified voucher in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Vouchers", "edit")) {
			
			$rules = Module::validateRules("Vouchers", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			$insert_id = Module::updateRow("Vouchers", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.vouchers.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified voucher from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Vouchers", "delete")) {
			Voucher::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.vouchers.index');
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
		$values = DB::table('vouchers')->select($this->listing_cols)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Vouchers');
		
		for($i=0; $i < count($data->data); $i++) {
			$voucher = Voucher::find($data->data[$i][0]);
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/vouchers/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				else if($col == "percent") {
				   	$data->data[$i][$j] = $voucher->percent > 0 
					   	? $voucher->percent . '% - Tối đa :' . number_format($voucher->max) . 'đ'
						: number_format($voucher->amount) . 'đ' ;
				}

				if($col == "min_order_amount") {
					$data->data[$i][$j] = number_format($data->data[$i][$j]) . 'đ' ;
				}

				if($col == "status") {
					$data->data[$i][$j] = $data->data[$i][$j] == 1 ? "<span class='label label-success'>Đang bật</span>" : "<span class='label label-success'>Đang tắt</span>";
				}
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Vouchers", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/vouchers/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Vouchers", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.vouchers.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
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
