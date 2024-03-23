<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerBacklog;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Voucherhistory;

class VoucherhistoriesController extends Controller
{
	public $show_action = true;
	public $view_col = 'code';
	public $listing_cols = ['id', 'voucher_id', 'customer_id', 'used_at', 'code'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Voucherhistories', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Voucherhistories', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Voucherhistories.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Voucherhistories');
		
		if(Module::hasAccess($module->id)) {
			return View('la.voucherhistories.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new voucherhistory.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created voucherhistory in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Voucherhistories", "create")) {
		
			$rules = Module::validateRules("Voucherhistories", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("Voucherhistories", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.voucherhistories.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified voucherhistory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Voucherhistories", "view")) {
			
			$voucherhistory = Voucherhistory::find($id);
			if(isset($voucherhistory->id)) {
				$module = Module::get('Voucherhistories');
				$module->row = $voucherhistory;
				
				return view('la.voucherhistories.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('voucherhistory', $voucherhistory);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("voucherhistory"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified voucherhistory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Voucherhistories", "edit")) {			
			$voucherhistory = Voucherhistory::find($id);
			if(isset($voucherhistory->id)) {	
				$module = Module::get('Voucherhistories');
				
				$module->row = $voucherhistory;
				
				return view('la.voucherhistories.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('voucherhistory', $voucherhistory);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("voucherhistory"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified voucherhistory in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Voucherhistories", "edit")) {
			
			$rules = Module::validateRules("Voucherhistories", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Voucherhistories", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.voucherhistories.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified voucherhistory from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Voucherhistories", "delete")) {
			Voucherhistory::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.voucherhistories.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(Request  $request)
	{
        if ($request->listing_cols) {
            $this->listing_cols = explode(',', $request->listing_cols);
            $this->show_action = false;
        }
		$values = DB::table('voucherhistories')->select($this->listing_cols)->whereNull('deleted_at');
		if ($request->voucher_id) {
		    $values->where('voucher_id', $request->voucher_id);
        }

        $datatable = app(\App\Datatable\Datatables::class)->of($values);
        $out = $datatable->make();
		$data = $out->getData();
        session(['filter_voucher_' . auth()->user()->id => json_encode($datatable->getFilteredQuery() ? $datatable->getFilteredQuery()->limit($datatable->totalCount())->pluck('id') : [])]);
		$fields_popup = ModuleFields::getModuleFields('Voucherhistories');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/voucherhistories/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				 else if($col == "customer_id") {
				     if (!$data->data[$i][$j]) {
                         $data->data[$i][$j] = '';
                     } else {
                         $data->data[$i][$j] = Customer::find($data->data[$i][$j])->name;
                     }
				 }
                 else if($col == "used_at" && $data->data[$i][$j] == '0000-00-00 00:00:00') {
                     $data->data[$i][$j] = '';
                 }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Voucherhistories", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/voucherhistories/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Voucherhistories", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.voucherhistories.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

    public function export()
    {
        $ids = json_decode(session('filter_voucher_' . auth()->user()->id));

        $histories = Voucherhistory::whereIn('id', $ids)->get();
        $fileName = 'Voucher ' . date('d/m/Y');
        $data = [];
        $voucher = '';
        foreach ($histories as $history) {
            if ($history) {
                if (!$voucher) {
                    $voucher = $history->voucher;
                }
                $data[] = [
                    'ID' => $history->id,
                    'Tên' => $voucher->name,
                    'Mã' => $history->code,
                    'Ngày bắt đầu' => $voucher->started_at,
                    'Ngày kết thúc' => $voucher->ended_at,
                    'Đơn hàng tối thiểu' => number_format($voucher->min_order_amount) . ' đ',
                    'Giảm giá' => $voucher->percent . '%',
                    'Giảm tối đa' => number_format($voucher->max) . ' đ',
                ];
            }
        }

        Excel::create($fileName, function ($excel) use ($data) {
            $excel->sheet('Vouchers', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download('xlsx');
    }
}
