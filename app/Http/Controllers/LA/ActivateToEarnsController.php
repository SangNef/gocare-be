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

use App\Models\ActivateToEarn;
use App\Models\ProductSeri;

class ActivateToEarnsController extends Controller
{
	public $show_action = true;
	public $view_col = 'phone';
	public $listing_cols = ['id', 'product_seri_id', 'order_id', 'name', 'phone', 'phone_info', 'amount', 'activated_at', 'status', 'result'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('ActivateToEarns', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('ActivateToEarns', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the ActivateToEarns.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('ActivateToEarns');
		
		if(Module::hasAccess($module->id)) {
			return View('la.activatetoearns.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new activatetoearn.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created activatetoearn in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("ActivateToEarns", "create")) {
		
			$rules = Module::validateRules("ActivateToEarns", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("ActivateToEarns", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.activatetoearns.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified activatetoearn.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("ActivateToEarns", "view")) {
			
			$activatetoearn = ActivateToEarn::find($id);
			if(isset($activatetoearn->id)) {
				$module = Module::get('ActivateToEarns');
				$module->row = $activatetoearn;
				
				return view('la.activatetoearns.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('activatetoearn', $activatetoearn);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("activatetoearn"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified activatetoearn.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("ActivateToEarns", "edit")) {			
			$activatetoearn = ActivateToEarn::find($id);
			if(isset($activatetoearn->id)) {	
				$module = Module::get('ActivateToEarns');
				
				$module->row = $activatetoearn;
				
				return view('la.activatetoearns.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('activatetoearn', $activatetoearn);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("activatetoearn"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified activatetoearn in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("ActivateToEarns", "edit")) {
			
			$rules = Module::validateRules("ActivateToEarns", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("ActivateToEarns", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.activatetoearns.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified activatetoearn from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("ActivateToEarns", "delete")) {
			ActivateToEarn::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.activatetoearns.index');
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
		$values = DB::table('activatetoearns')->select($this->listing_cols)->whereNull('deleted_at')->orderBy('id', 'desc');
		$out = Datatables::of($values)->make();
		$data = $out->getData();
		$total = [
            'total_successful' => number_format(DB::table('activatetoearns')->where('status', 2)->sum('amount')),
        ];

		$fields_popup = ModuleFields::getModuleFields('ActivateToEarns');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && $col != 'product_seri_id' && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/activatetoearns/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				else if($col == "product_seri_id") {
					$seri = ProductSeri::find($data->data[$i][$j]);
				   	$data->data[$i][$j] = $seri ? $seri->seri_number : '';
				}
				if ($col == 'status') {
					if (!$data->data[$i][$j]) {
						$data->data[$i][$j] = 'Chưa xử lý';
					} else if ($data->data[$i][$j] == 1) {
						$data->data[$i][$j] = 'Đang xử lý';
					} else if ($data->data[$i][$j] == 2) {
						$data->data[$i][$j] = 'Thành công';
					} else if ($data->data[$i][$j] == 3) {
						$data->data[$i][$j] = 'Thất bại';
					}
				}
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("ActivateToEarns", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/activatetoearns/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("ActivateToEarns", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.activatetoearns.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$data->total = $total;
		
		$out->setData($data);
		return $out;
	}
}
