<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Customer;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Transactionhistory;

class TransactionhistoriesController extends Controller
{
	public $show_action = true;
	public $view_col = 'customer_id';
	public $listing_cols = ['id','customer_id', 'order_id', 'transaction_id', 'amount', 'balance'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Transactionhistories', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Transactionhistories', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Transactionhistories.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Transactionhistories');
		
		if(Module::hasAccess($module->id)) {
			return View('la.transactionhistories.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Display the specified transactionhistory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Transactionhistories", "view")) {
			
			$transactionhistory = Transactionhistory::find($id);
			if(isset($transactionhistory->id)) {
				$module = Module::get('Transactionhistories');
				$module->row = $transactionhistory;
				
				return view('la.transactionhistories.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('transactionhistory', $transactionhistory);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("transactionhistory"),
				]);
			}
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
		$values = Transactionhistory::select($this->listing_cols)->whereNull('deleted_at')->orderBy('id', 'desc');
		$datatable = Datatables::of($values)->filterColumn('customer_id', function ($query, $keyword) {
			$query->where('customer_id', $keyword);
		});
		$out = $datatable->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Transactionhistories');
		
		for($i=0; $i < count($data->data); $i++) {
			$history = Transactionhistory::find($data->data[$i][0]);
			$customer = \App\Models\Customer::find($data->data[$i][1]);
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/transactionhistories/'.$data->data[$i][0]).'">'.$history->customer->name.'</a>';
				}
			}
			$data->data[$i][2] = $customer->username;
			$data->data[$i][3] = $history->order()->exists()
				? '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $history->order->id) . '">' . $history->order->code . '</a>'
				: '';
			$data->data[$i][4] = $data->data[$i][$j] = $history->transaction()->exists()
				? '<a href="' . url(config('laraadmin.adminRoute') . '/transactions/' . $history->transaction->id) . '">' . $history->transaction->id . '</a>'
				: '';
			$data->data[$i][5] = number_format($history->amount) . 'đ';
			$data->data[$i][6] = number_format($history->balance) . 'đ';
			$data->data[$i][7] = $history->created_at->format('Y/m/d H:i');
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Transactionhistories", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.transactionhistories.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
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
