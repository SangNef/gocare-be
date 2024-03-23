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

use App\Models\AccessToken;

class AccessTokensController extends Controller
{
	public $show_action = true;
	public $view_col = 'api_key';
	public $listing_cols = ['id', 'name', 'api_key', 'status'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('AccessTokens', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('AccessTokens', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the AccessTokens.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('AccessTokens');

		if (Module::hasAccess($module->id)) {
			return View('la.accesstokens.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new accesstoken.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created accesstoken in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if (Module::hasAccess("AccessTokens", "create")) {

			$rules = Module::validateRules("AccessTokens", $request);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			$insert_id = Module::insert("AccessTokens", $request);
			$accesstoken = AccessToken::find($insert_id);

			return redirect()->route(config('laraadmin.adminRoute') . '.accesstokens.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Display the specified accesstoken.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if (Module::hasAccess("AccessTokens", "view")) {

			$accesstoken = AccessToken::find($id);
			if (isset($accesstoken->id)) {
				$module = Module::get('AccessTokens');
				$module->row = $accesstoken;

				return view('la.accesstokens.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('accesstoken', $accesstoken);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("accesstoken"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified accesstoken.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if (Module::hasAccess("AccessTokens", "edit")) {
			$accesstoken = AccessToken::find($id);
			if (isset($accesstoken->id)) {
				$module = Module::get('AccessTokens');

				$module->row = $accesstoken;

				return view('la.accesstokens.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('accesstoken', $accesstoken);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("accesstoken"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified accesstoken in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if (Module::hasAccess("AccessTokens", "edit")) {

			$rules = Module::validateRules("AccessTokens", $request, true);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}

			$insert_id = Module::updateRow("AccessTokens", $request, $id);

			return redirect()->route(config('laraadmin.adminRoute') . '.accesstokens.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Remove the specified accesstoken from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("AccessTokens", "delete")) {
			AccessToken::find($id)->delete();

			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.accesstokens.index');
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
		$values = DB::table('accesstokens')->select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('AccessTokens');

		for ($i = 0; $i < count($data->data); $i++) {
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/accesstokens/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}

			if ($this->show_action) {
				$output = '';
				if (Module::hasAccess("AccessTokens", "edit")) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/accesstokens/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}

				if (Module::hasAccess("AccessTokens", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.accesstokens.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
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
