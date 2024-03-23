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

use App\Models\Bank;
use App\Models\Setting;

class BanksController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'store_id', 'name', 'branch', 'acc_name', 'acc_id', 'printing', 'first_balance', 'last_balance'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Banks', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Banks', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the Banks.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Banks');

		if (Module::hasAccess($module->id)) {
			return View('la.banks.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new bank.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created bank in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if (Module::hasAccess("Banks", "create")) {

			$rules = Module::validateRules("Banks", $request);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			$request->merge(['last_balance' => $request->first_balance]);
			$insert_id = Module::insert("Banks", $request);

			return redirect()->route(config('laraadmin.adminRoute') . '.banks.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Display the specified bank.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if (Module::hasAccess("Banks", "view")) {

			$bank = Bank::find($id);
			if (isset($bank->id)) {
				$module = Module::get('Banks');
				$module->row = $bank;

				return view('la.banks.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('bank', $bank);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("bank"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified bank.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if (Module::hasAccess("Banks", "edit")) {
			$bank = Bank::find($id);
			if (isset($bank->id)) {
				$module = Module::get('Banks');

				$module->row = $bank;

				return view('la.banks.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('bank', $bank);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("bank"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified bank in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if (Module::hasAccess("Banks", "edit")) {

			$rules = Module::validateRules("Banks", $request, true);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}

			$insert_id = Module::updateRow("Banks", $request, $id);

			return redirect()->route(config('laraadmin.adminRoute') . '.banks.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Remove the specified bank from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("Banks", "delete")) {
			Bank::find($id)->delete();

			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.banks.index');
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
		$values = Bank::select($this->listing_cols)->whereNull('deleted_at')->orderBy('id', 'desc');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$total = [
			'last_balance' => number_format($values->whereNotIn('id', Setting::getIgnoreBank())->sum('last_balance')),
		];

		$fields_popup = ModuleFields::getModuleFields('Banks');
		for ($i = 0; $i < count($data->data); $i++) {
			$bank = Bank::findOrFail($data->data[$i][0]);
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];
				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/banks/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}
				if (in_array($col, ['first_balance', 'last_balance'])) {
					$symbol = $bank->currency_type == Bank::CURRENCY_NDT ? ' NDT' : ' đ';
					$data->data[$i][$j] = number_format($data->data[$i][$j]) . $symbol;
				}
				if ($col === 'acc_id') {
					$data->data[$i][$j] = $data->data[$i][$j] . ' - <strong style="color: red">' . Bank::availableCurrency()[$bank->currency_type] . '</strong>';
				}
				if ($col === 'printing') {
					$data->data[$i][$j] = $data->data[$i][$j] == 1 ? '<span style="color: green">Có</span>' : '<span>Không</span>';
				}
			}
			$data->data[$i][] = $this->renderBankBacklogs($bank->id);
			if ($this->show_action) {
				$output = '';
				if (Module::hasAccess("Banks", "edit")) {
					$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/banks/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}

				if (Module::hasAccess("Banks", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.banks.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
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

	protected function renderBankBacklogs($id)
	{
		$bank = Bank::find($id);
		$backlogs = $bank->backlogs;
		$symbol = $bank->currency_type == Bank::CURRENCY_NDT ? ' NDT' : ' đ';

		return \View::make('la.banks.bank-backlog', compact('backlogs', 'symbol'))->render();
	}
}
