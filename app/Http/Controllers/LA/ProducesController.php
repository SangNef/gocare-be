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

use App\Models\Produce;
use App\Models\Product;
use App\Models\ProduceProduct;
use App\Models\OrderStatus;

class ProducesController extends Controller
{
	public $show_action = true;
	public $view_col = 'description';
	public $listing_cols = ['id', 'store_id', 'group_id', 'description', 'product_id', 'quantity', 'status', 'created_at'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Produces', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Produces', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Produces.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Produces');
		
		if(Module::hasAccess($module->id)) {
			return View('la.produces.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new produce.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created produce in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Produces", "create")) {
		
			$rules = Module::validateRules("Produces", $request);
			$rules = array_merge($rules, [
				'products' => 'required',
				'products.*.quantity' => 'required|integer|min:1',
				'products.*.product_id' => 'required|exists:products,id',
			]);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			$insert_id = Module::insert("Produces", $request);
			ProduceProduct::insert(array_map(function($product) use($insert_id) {
				$product = array_only($product, [
					'product_id',
					'quantity',
					'attrs_value'
				]);
				$product['produce_id'] = $insert_id;
				$product['attrs_value'] = json_encode(@$product['attrs_value']);

				return $product;
			},$request->products));
			
			return redirect()->route(config('laraadmin.adminRoute') . '.produces.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified produce.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("Produces", "view")) {
			
			$produce = Produce::find($id);
			if(isset($produce->id)) {
				$module = Module::get('Produces');
				$module->row = $produce;
				
				return view('la.produces.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('produce', $produce);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("produce"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified produce.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Produces", "edit")) {			
			$produce = Produce::find($id);
			if(isset($produce->id)) {	
				$module = Module::get('Produces');
				
				$module->row = $produce;
				$attrs = [];
				if ($produce->attrs_value) {
					$product = $produce->product;
					$attrs = $product->attrs->map(function ($productAttribute, $index) use($produce) {
						$selected = @$produce->attrs_value[$index];
						return [
							'id' => $productAttribute->attribute_id,
							'text' => $productAttribute->attr->name,
							'values' => $productAttribute->getValues()->map(function ($value) use($selected) {
								return [
									'id' => $value->id,
									'value' => $value->value,
									'selected' => $selected == $value->id
								];
							}),
						];
					});
				}
				
				return view('la.produces.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
					'attrs' => $attrs,
				])->with('produce', $produce);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("produce"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified produce in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Produces", "edit")) {
			
			$rules = Module::validateRules("Produces", $request, true);
			$rules = array_merge($rules, [
				'products' => 'required',
				'products.*.quantity' => 'required|integer|min:1',
				'products.*.product_id' => 'required|exists:products,id',
			]);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			
			$insert_id = Module::updateRow("Produces", $request, $id);
			ProduceProduct::where('produce_id', $insert_id)->forceDelete();
			ProduceProduct::insert(array_map(function($product) use($insert_id) {
				$product = array_only($product, [
					'product_id',
					'quantity',
					'attrs_value'
				]);
				$product['produce_id'] = $insert_id;
				$product['attrs_value'] = json_encode(@$product['attrs_value']);

				return $product;
			},$request->products));
			return redirect()->route(config('laraadmin.adminRoute') . '.produces.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified produce from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Produces", "delete")) {
			Produce::find($id)->delete();
			
			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.produces.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(OrderStatus $orderStatus)
	{
		$values = DB::table('produces')
			->select($this->listing_cols)
			->orderBy('id', 'desc')
			->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Produces');
		
		for($i=0; $i < count($data->data); $i++) {
			$produce = Produce::find($data->data[$i][0]);
			$products = ProduceProduct::where('produce_id', $data->data[$i][0])->get();
			$isAbleToProduce = $products->filter(function($product) {
					return $product->isAbleToProduce();
				})->count() == $products->count();
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == "status") {
				   $data->data[$i][$j] = $orderStatus->getStatusHTMLFormatted($data->data[$i][$j]) . '<br>' . ($data->data[$i][$j] != 2 ? '<small>' . (!$isAbleToProduce ? 'Không đủ số lượng' : '') . '</small>' : '');
				}
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Produces", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/produces/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/produces/' . $data->data[$i][0] . '/print') . '" class="btn btn-warning btn-xs print-produce onetime-click" style="display:inline;padding:2px 5px 3px 5px;">IN</a>';
				if ($produce->p_seris) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/produces/' . $data->data[$i][0] . '/print-qr') . '" data-product="'. $produce->product_id .'" data-seris="' . $produce->p_seris . '" class="btn btn-warning btn-xs print-produce-qr onetime-click" style="display:inline;padding:2px 5px 3px 5px;">IN QR</a>';
				}
				if ($produce->status == 1) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/produces/' . $data->data[$i][0] . '/success') . '" class="btn btn-success btn-xs" style="display:inline;padding:2px 5px 3px 5px;">Hoàn thành</a>';
				}
				$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/produces/' . $data->data[$i][0] . '/copy') . '" class="btn btn-success btn-xs" style="display:inline;padding:2px 5px 3px 5px;">Copy</a>';
				if(Module::hasAccess("Produces", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.produces.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function print($id)
    {
        $produce = Produce::find($id);
        if ($produce) {
            $produce->products = $produce->products->map(function($product) use($produce) {
				$price = $product->product->getPriceForCustomerGroup($produce->group ? $produce->group->name : '', false);
				$product->total = $product->quantity * $produce->quantity;
				$product->store_quantity = @$product->stock_history['stock_quantity'] ?: $product->storeQuantity();
				$product->remain = @$product->stock_history['remain'] ?: $product->storeQuantity() - $product->quantity * $produce->quantity;
				$product->amount = $product->total * $price;

				return $product;
			});
            return view('la.produces.print', [
                'orderStatus' => app(OrderStatus::class),
				'store' => $produce->store
            ])->with('produce', $produce);
        } else {
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("order"),
            ]);
        }
    }

	public function success($id)
	{
		if(Module::hasAccess("Produces", "edit")) {
			$produce = Produce::where('id', $id)
				->where('status', 1)
				->first();
			if ($produce) {
				$produce->status = 2;
				$produce->save();
				
				return back();
			}
		}
		return view('errors.404', [
			'record_id' => $id,
			'record_name' => ucfirst("order"),
		]);
	}

	/**
	 * Show the form for editing the specified produce.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function copy($id)
	{
		if(Module::hasAccess("Produces", "edit")) {			
			$produce = Produce::find($id);
			if(isset($produce->id)) {	
				$module = Module::get('Produces');
				
				$module->row = $produce;
				return view('la.produces.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
					'is_coping' => 1
				])->with('produce', $produce);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("produce"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	public function getProductAttributes(Request $request)
	{
		$product = Product::find($request->p_id);
		
		return $product->attrs->map(function ($productAttribute) {
			return [
				'id' => $productAttribute->attribute_id,
				'text' => $productAttribute->attr->name,
				'values' => $productAttribute->getValues()->map(function ($value) {
					return [
						'id' => $value->id,
						'value' => $value->value
					];
				}),
			];
		});
	}
}
