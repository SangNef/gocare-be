<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\ImportProduct;
use App\Models\OrderStatus;
use App\Models\Produce;
use App\Models\ProduceProduct;
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

use App\Models\Import;

class ImportsController extends Controller
{
	public $show_action = true;
	public $view_col = 'code';
	public $listing_cols = ['id', 'store_id', 'customer_id', 'code', 'status', 'imported_at'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Imports', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Imports', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Imports.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Imports');
		
		if(Module::hasAccess($module->id)) {
			return View('la.imports.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new import.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created import in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Imports", "create")) {
		
			$rules = Module::validateRules("Imports", $request);
            $rules = array_merge($rules, [
                'products' => 'required',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.product_id' => 'required|exists:products,id',
            ]);

			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			$insert_id = Module::insert("Imports", $request);
			$import = Import::find($insert_id);
			$import->imported_at = Carbon::now();
            $import->status = "Đang xử lý";
            $import->save();
            foreach ($request->products as $product) {
                $data = array_only($product, [
                    'quantity',
                    'attrs_value',
                    'note'
                ]);
                ImportProduct::updateOrCreate([
                    'import_id' => $insert_id,
                    'product_id' => $product['product_id'],
                    'attrs_value' => json_encode(@$product['attrs_value']),
                ], $data);
            }
			
			return redirect()->route(config('laraadmin.adminRoute') . '.imports.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified import.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Imports", "view")) {
			
			$import = Import::find($id);
			if(isset($import->id)) {
				$module = Module::get('Imports');
				$module->row = $import;
				
				return view('la.imports.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('import', $import);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("import"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified import.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Imports", "edit")) {			
			$import = Import::find($id);
			if(isset($import->id)) {	
				$module = Module::get('Imports');
				
				$module->row = $import;
				
				return view('la.imports.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('import', $import);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("import"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified import in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Imports", "edit")) {
			
			$rules = Module::validateRules("Imports", $request, true);
            $rules = array_merge($rules, [
                'products' => 'required',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.product_id' => 'required|exists:products,id',
            ]);

			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Imports", $request, $id);
            $new = [];
            foreach ($request->products as $product) {
                $data = array_only($product, [
                    'quantity',
                    'attrs_value',
                    'note'
                ]);
                $ip = ImportProduct::updateOrCreate([
                    'import_id' => $insert_id,
                    'product_id' => $product['product_id'],
                    'attrs_value' => json_encode(@$product['attrs_value']),
                ], $data);
                $new[] = $ip->id;
            }
            ImportProduct::where('import_id', $insert_id)
                ->whereNotIn('id', $new)
                ->get()
                ->map(function ($importProduct) {
                    $importProduct->delete();
                });
			return redirect()->route(config('laraadmin.adminRoute') . '.imports.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified import from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Imports", "delete")) {
			Import::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.imports.index');
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
		$values = DB::table('imports')->select($this->listing_cols)
            ->orderBy('id', 'desc')
            ->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Imports');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/imports/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Imports", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/imports/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
                $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/imports/' . $data->data[$i][0] . '/print') . '" class="btn btn-warning btn-xs print-import onetime-click" style="display:inline;padding:2px 5px 3px 5px;">IN</a>';
				if(Module::hasAccess("Imports", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.imports.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function updateDone($id, Request $request)
    {
        if(Module::hasAccess("Imports", "edit")) {
            $rules = [
                'quantity' => 'required',
                'quantity.*' => 'integer|min:0'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            foreach ($request->quantity as $ipd => $done) {
                $importProduct = ImportProduct::where('id', $ipd)
                    ->where('import_id', $id)
                    ->first();
                $importProduct->done = $done;
                $importProduct->save();
            }
            return back();
        } else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
    }

    public function print($id)
    {
        $import = Import::find($id);
        if ($import) {
            $import->products = $import->products->map(function($product) use($import) {
                $product->name = $product->product->name;
                $product->attr_name = $product->attrsName();

                return $product;
            });
            $line = $import->products->count();
            return view('la.imports.print', [
                'line' => $line
            ])->with('import', $import);
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("order"),
            ]);
        }
    }

    public function printProductSeries($id, Request $request)
    {
        $importProducts = ImportProduct::where('import_id', $id);
        if ($request->ip_id) {
            $importProducts->where('id', $request->ip_id);
        }
        $series = [];
        $importProducts->get()
            ->map(function ($importProduct) use (&$series) {
                $series += explode(',', $importProduct->pseri_ids);
            });
        $codes = ProductSeri::whereIn('id', $series)
            ->select(['seri_number', 'activation_code', 'id'])
            ->get();

        return view('la.products.series.print', compact('codes'));
    }
}
