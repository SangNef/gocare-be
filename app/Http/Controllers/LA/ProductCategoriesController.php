<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\ProductCategory;

class ProductCategoriesController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'name', 'is_devices', 'commission'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('ProductCategories', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('ProductCategories', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the ProductCategories.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('ProductCategories');

		if (Module::hasAccess($module->id)) {
			return View('la.productcategories.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new productcategory.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created productcategory in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if (Module::hasAccess("ProductCategories", "create")) {

			$rules = Module::validateRules("ProductCategories", $request);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$insert_id = Module::insert("ProductCategories", $request);
				$category = ProductCategory::find($insert_id);
				$category->slug = $request->name;
				$category->use_at_fe = $request->has('use_at_fe');
				$category->save();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.productcategories.index');
			} catch (\Exception $exception) {
				\Illuminate\Support\Facades\DB::rollback();
				\Log::error($exception->getMessage());
				\Log::error($exception->getTraceAsString());
				return redirect()->back()->withErrors($exception->getMessage());
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Display the specified productcategory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if (Module::hasAccess("ProductCategories", "view")) {

			$productcategory = ProductCategory::find($id);
			if (isset($productcategory->id)) {
				$module = Module::get('ProductCategories');
				$module->row = $productcategory;

				return view('la.productcategories.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('productcategory', $productcategory);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("productcategory"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified productcategory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if (Module::hasAccess("ProductCategories", "edit")) {
			$productcategory = ProductCategory::find($id);
			if (isset($productcategory->id)) {
				$module = Module::get('ProductCategories');

				$module->row = $productcategory;

				return view('la.productcategories.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('productcategory', $productcategory);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("productcategory"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified productcategory in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if (Module::hasAccess("ProductCategories", "edit")) {

			$rules = Module::validateRules("ProductCategories", $request, true);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}
			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$insert_id = Module::updateRow("ProductCategories", $request, $id);
				$category = ProductCategory::find($insert_id);
				$category->slug = $request->name;
				$category->use_at_fe = $request->has('use_at_fe');
				$category->save();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.productcategories.index');
			} catch (\Exception $exception) {
				\Illuminate\Support\Facades\DB::rollback();
				\Log::error($exception->getMessage());
				\Log::error($exception->getTraceAsString());
				return redirect()->back()->withErrors($exception->getMessage());
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Remove the specified productcategory from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("ProductCategories", "delete")) {
			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$category = ProductCategory::findOrFail($id);
				$category->delete();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.productcategories.index');
			} catch (\Exception $exception) {
				\Illuminate\Support\Facades\DB::rollback();
				\Log::error($exception->getMessage());
				\Log::error($exception->getTraceAsString());
				return redirect()->back()->withErrors($exception->getMessage());
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax()
	{
		$values = DB::table('productcategories')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('ProductCategories');

		for ($i = 0; $i < count($data->data); $i++) {
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/productcategories/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}
				 else if($col == "is_devices") {
				    $data->data[$i][$j] = $data->data[$i][$j] ? '<input type="checkbox" checked disabled/>' : '<input type="checkbox" disabled/>';
				 }
			}

			if ($this->show_action) {
				$output = '';
				if (Module::hasAccess("ProductCategories", "edit")) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/productcategories/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}

				if (Module::hasAccess("ProductCategories", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.productcategories.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
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
