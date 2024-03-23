<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductSeri;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Draw;

class DrawsController extends Controller
{
	public $show_action = true;
	public $view_col = 'index';
	public $listing_cols = ['id', 'index', 'title', 'prize', 'prize_img', 'lists', 'winner'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Draws', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Draws', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Draws.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Draws');
		
		if(Module::hasAccess($module->id)) {
			return View('la.draws.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new draw.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created draw in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Draws", "create")) {
		
			$rules = Module::validateRules("Draws", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$insert_id = Module::insert("Draws", $request);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.draws.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified draw.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Draws", "view")) {
			
			$draw = Draw::find($id);
			if(isset($draw->id)) {
				$module = Module::get('Draws');
				$module->row = $draw;
				
				return view('la.draws.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('draw', $draw);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("draw"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified draw.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Draws", "edit")) {			
			$draw = Draw::find($id);
			if(isset($draw->id)) {	
				$module = Module::get('Draws');
				
				$module->row = $draw;
				
				return view('la.draws.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('draw', $draw);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("draw"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified draw in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Draws", "edit")) {
			
			$rules = Module::validateRules("Draws", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Draws", $request, $id);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.draws.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified draw from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Draws", "delete")) {
			Draw::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.draws.index');
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
		$values = DB::table('draws')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Draws');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/draws/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Draws", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/draws/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Draws", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.draws.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

    public  function updateLists(Request $request)
    {
        $productSeri = ProductSeri::whereNotNull('activated_at');
        if ($request->username) {
            $customerIds = Customer::whereIn('username', explode(',', $request->username))
                ->pluck('id');
            $productSeri->whereIn('activation_customer_id', $customerIds);
        }
        if ($request->activated_at_from) {
            $productSeri->where('activated_at', '>=', Carbon::createFromFormat('d/m/Y', substr($request->activated_at_from, 0, 10)));
        }

        if ($request->activated_at) {
            $productSeri->where('activated_at', '<=', Carbon::createFromFormat('d/m/Y', substr($request->activated_at, 0, 10)));
        }

        echo $productSeri->get()->implode('id', ',');
        die;
    }
}
