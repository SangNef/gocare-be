<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Product;
use App\Repositories\ProductComboRepository;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\ProductCombo;

class ProductCombosController extends Controller
{
	public $show_action = true;
	public $view_col = 'note';
	public $listing_cols = ['id', 'product_id', 'quantity', 'discount', 'note', 'status', 'related'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('ProductCombos', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('ProductCombos', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the ProductCombos.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('ProductCombos');
		
		if(Module::hasAccess($module->id)) {
		    $groups = Group::all();

			return View('la.productcombos.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module,
                'groups' => $groups
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new productcombo.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created productcombo in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request, ProductComboRepository $repository)
	{
		if(Module::hasAccess("ProductCombos", "create")) {
		
			$rules = Module::validateRules("ProductCombos", $request);
			$rules += [
			    'products' => 'required',
                'quantities' => 'required',
                'quantities.*' => 'required|integer|min:1',
                'group.*.group_id' => 'sometimes|exists:groups,id',
                'group.*.discount' => 'sometimes|integer|min:0'
            ];
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
            $quantities = $request->quantities;
            $products = [];
			foreach ($request->products as $key => $productId) {
                $products[] = [
                    $productId,
                    $quantities[$key]
                ];
            }
            $request->request->add([
                'related' => json_encode($products)
            ]);
            $insert_id = Module::insert("ProductCombos", $request);
            $productCombo = ProductCombo::find($insert_id);
            $repository->syncGroups($productCombo, $request->group);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.productcombos.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified productcombo.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if(Module::hasAccess("ProductCombos", "view")) {
			
			$productcombo = ProductCombo::find($id);
			if(isset($productcombo->id)) {
				$module = Module::get('ProductCombos');
				$module->row = $productcombo;
				
				return view('la.productcombos.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('productcombo', $productcombo);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("productcombo"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified productcombo.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("ProductCombos", "edit")) {			
			$productcombo = ProductCombo::find($id);
			if(isset($productcombo->id)) {	
				$module = Module::get('ProductCombos');
				
				$module->row = $productcombo;
				$related = json_decode($productcombo->related, true);
                $selectedIds = array_map(function ($item) {
                    return $item[0];
                }, $related);
                $selectedProducts = Product::whereIn('id', $selectedIds)
                    ->select(['name', 'id'])
                    ->get();
                $selectedGroups = $productcombo->groups();
                $groups = Group::where('display_name', 'like', '%Điện Tử%')->get();

				return view('la.productcombos.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
                    'related' => $related,
                    'selectedProducts' => $selectedProducts,
                    'selectedGroups' => $selectedGroups,
                    'groups' => $groups,
				])->with('productcombo', $productcombo);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("productcombo"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified productcombo in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id, ProductComboRepository $repository)
	{
		if(Module::hasAccess("ProductCombos", "edit")) {
			
			$rules = Module::validateRules("ProductCombos", $request, true);
            $rules += [
                'products' => 'required',
                'quantities' => 'required',
                'quantities.*' => 'required|integer|min:1',
                'group.*.group_id' => 'sometimes|exists:groups,id',
                'group.*.discount' => 'sometimes|integer|min:0'
            ];
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
            $quantities = $request->quantities;
            $products = [];
            foreach ($request->products as $key => $productId) {
                $products[] = [
                    $productId,
                    $quantities[$key]
                ];
            }
            $request->request->add([
                'related' => json_encode($products)
            ]);
			$insert_id = Module::updateRow("ProductCombos", $request, $id);
            $productCombo = ProductCombo::find($insert_id);
            $repository->syncGroups($productCombo, $request->group);
			
			return redirect()->route(config('laraadmin.adminRoute') . '.productcombos.index');
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified productcombo from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("ProductCombos", "delete")) {
			ProductCombo::find($id)->delete();

			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.productcombos.index');
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(ProductComboRepository $repository)
	{
		$values = DB::table('productcombos')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('ProductCombos');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/productcombos/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				 else if($col == "discount") {
				    $data->data[$i][$j] = number_format($data->data[$i][$j]) . 'đ';
				 }else if($col == "status") {
                     $data->data[$i][$j] = $data->data[$i][$j] == 1
                         ? '<span class="label label-success">Đang bật</span>'
                         : '<span class="label label-success">Đang tắt</span>';
                 }else if($col == "related") {
				     $related = json_decode($data->data[$i][$j]);
				     if (count($related) > 0) {
                         $selectedIds = array_map(function ($item) {
                             return $item[0];
                         }, $related);
				         $products = Product::whereIn('id', $selectedIds)->get();
                         $data->data[$i][$j] = implode("<br />", array_map(function ($product) use ($products) {
                            $quantity = $product[1];
                            $productName = $products->filter(function ($p) use ($product) {
                                    return $p->id == $product[0];
                                })
                                ->first()
                                ->name;

                            return "$productName";
                         }, $related));
                     }
                 }
			}
//			$productCombo = ProductCombo::find($data->data[$i][0]);
//			$groups = $productCombo->groupsWithName();
//			$groupOutput = '';
//			foreach ($groups as $group) {
//                $groupOutput .=  $group->display_name . ': ' . number_format($group->discount) . "đ<br \>";
//            }
//            $data->data[$i][] = $groupOutput;
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("ProductCombos", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/productcombos/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("ProductCombos", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.productcombos.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
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
