<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferMoneyRequest;
use App\Models\Bank;
use App\Models\CustomerBacklog;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Transaction;
use App\Models\Customer;

class TransactionsController extends Controller
{
	public $show_action = true;
	public $view_col = 'id';
	public $listing_cols = ['id', 'store_id', 'created_at', 'user_id', 'customer_id', 'desc', 'trans_id', 'order_id', 'bank_id', 'type', 'received_amount', 'transfered_amount', 'fee', 'bank_history', 'note', 'status'];

	public function __construct()
	{
		// Field Access of Listing Columns
		if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Transactions', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Transactions', $this->listing_cols);
		}
	}

	/**
	 * Display a listing of the Transactions.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Transactions');

		if (Module::hasAccess($module->id)) {
			$banks = \App\Models\Bank::whereNull('deleted_at')->get();
			$customers = \App\Models\Customer::whereNull('deleted_at')->pluck('name', 'id');
			return View('la.transactions.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module,
				'banks' => $banks,
				'customers' => $customers,
			]);
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for creating a new transaction.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created transaction in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if (Module::hasAccess("Transactions", "create")) {

			$rules = Module::validateRules("Transactions", $request);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			switch ($request->type) {
				case 1:
					$request->merge(['received_amount' => $request->amount, 'transfered_amount' => 0]);
					break;
				case 2:
					$request->merge(['transfered_amount' => $request->amount, 'received_amount' => 0]);
					break;
			}

			$request->merge(['created_at' => strpos($request->trans_time, '-') === false ? \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $request->trans_time) : $request->trans_time]);
			$insert_id = Module::insert("Transactions", $request);

			return redirect()->route(config('laraadmin.adminRoute') . '.transactions.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Display the specified transaction.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		if (Module::hasAccess("Transactions", "view")) {

			$transaction = Transaction::find($id);
			if (isset($transaction->id)) {
				$module = Module::get('Transactions');
				$module->row = $transaction;

				return view('la.transactions.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding"
				])->with('transaction', $transaction);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("transaction"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Show the form for editing the specified transaction.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if (Module::hasAccess("Transactions", "edit")) {
			$transaction = Transaction::find($id);
			if (isset($transaction->id)) {
				$module = Module::get('Transactions');
				$module->row = $transaction;
				$banks = \App\Models\Bank::whereNull('deleted_at')->get();
				$customers = \App\Models\Customer::whereNull('deleted_at')
					->where('id', '<>', $transaction->customer)
					->pluck('name', 'id');
				return view('la.transactions.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
					'customers' => $customers,
					'transaction' => $transaction,
					'banks' => $banks
				]);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("transaction"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Update the specified transaction in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if (Module::hasAccess("Transactions", "edit")) {

			$rules = Module::validateRules("Transactions", $request, true);

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}

			switch ($request->type) {
				case 1:
					$request->merge(['received_amount' => $request->amount, 'transfered_amount' => 0]);
					break;
				case 2:
					$request->merge(['transfered_amount' => $request->amount, 'received_amount' => 0]);
					break;
			}
			$request->merge(['created_at' => $request->trans_time]);
			$insert_id = Module::updateRow("Transactions", $request, $id);

			return redirect()->route(config('laraadmin.adminRoute') . '.transactions.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Remove the specified transaction from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if (Module::hasAccess("Transactions", "delete")) {
			Transaction::find($id)->delete();

			// Redirecting to index() method
			return redirect()->route(config('laraadmin.adminRoute') . '.transactions.index');
		} else {
			return redirect(config('laraadmin.adminRoute') . "/");
		}
	}

	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax(Request $request, Transaction $transaction)
	{
		$values = $transaction
			->select($this->listing_cols)
			->search($request->all())
			->whereNull('deleted_at')
			->orderBy('id', 'desc');

		if ($request->has('null_customer')) {
			$values->where('customer_id', 0);
		}

		$datatable = Datatables::of($values)
			->filterColumn('customer_id', function ($query, $keyword) {
				$query->where('customer_id', $keyword);
			})
			->filterColumn('bank_id', function ($query, $keyword) {
				$query->where('bank_id', $keyword);
			});
		$out = $datatable->make();
		$data = $out->getData();

		$total = [
			'total_receive' => number_format($values->sum('received_amount')),
			'total_transfer' => number_format($values->sum('transfered_amount')),
			'total_amount' => number_format($values->sum(DB::raw('received_amount + transfered_amount')))
		];
		$fields_popup = ModuleFields::getModuleFields('Transactions');
		for ($i = 0; $i < count($data->data); $i++) {
			$id = $data->data[$i][0];
			$transaction = Transaction::find($id);
			for ($j = 0; $j < count($this->listing_cols); $j++) {
				$col = $this->listing_cols[$j];

				if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if ($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/transactions/' . $data->data[$i][0]) . '">' . $data->data[$i][$j] . '</a>';
				}
				if ($col === 'id') {
					$data->data[$i][0] = '<input type="checkbox" class="row" value="' . $id . '"/>' . $id;
				}
				if ($col === 'type') {
					switch ($data->data[$i][$j]) {
						case 1:
							$data->data[$i][$j] = '<span class="label label-success">Nhận</span>';
							break;
						case 2:
							$data->data[$i][$j] = '<span class="label label-primary">Chuyển</span>';
							break;
					}
				}
				if ($col === 'status') {
					if ($transaction->isApprovable()) {
						$data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/approve-transaction?ids=' . $id) . '" class="trans-approve label label-warning">Mới</a>';
					} else {
						$data->data[$i][$j] = '<span class="label label-success">Duyệt</span>';
					}
				}
				if ($col === 'order_id') {
					$orderCode = $transaction->order ? $transaction->order->code : $data->data[$i][$j];
					$data->data[$i][$j] = $orderCode;
				}
				if (in_array($col, ['received_amount', 'transfered_amount', 'fee', 'bank_history'])) {
					$symbol = $transaction->bank && $transaction->bank->currency_type == Bank::CURRENCY_NDT ? ' NDT' : ' đ';
					$data->data[$i][$j] = number_format($data->data[$i][$j]) . $symbol;
				}
				if ($col === 'customer_id') {
					$customer = Customer::find($data->data[$i][$j]);
					$data->data[$i][$j] = $customer ? $customer->username : '';
				}
			}

			if ($this->show_action) {
				$output = '';
				$output .= '<a href="' . url(config('laraadmin.adminRoute') . '/transactions/' . $id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				$data->data[$i][] = (string)$output;
			}
		}
		$data->total = $total;
		$out->setData($data);
		return $out;
	}

	public function moneyTransfer(TransferMoneyRequest $request)
	{
		try {
			DB::beginTransaction();

			$transferBank = Bank::findOrFail($request->transfer_bank);
			$receiveBank = Bank::findOrFail($request->receive_bank);
			$internalCustomer = Customer::getInternalCustomer();

			if ($transferBank->last_balance < $request->amount) {
				return redirect()
					->route(config('laraadmin.adminRoute') . '.transactions.index')
					->withErrors(['error' => 'Số dư của ngân hàng gửi không đủ']);
			}

			$transferAmount = $transferBank->currency_type == Bank::CURRENCY_NDT
				? $request->amount_ndt
				: $request->amount;
			$receivedAmount = $receiveBank->currency_type == Bank::CURRENCY_NDT
				? $request->amount_ndt + $request->receive_fee
				: $request->amount + $request->receive_fee;

			$request->trans_time = strpos($request->trans_time, '-') === false ? \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $request->trans_time) : $request->trans_time;
			Transaction::create([
				'customer_id' => $internalCustomer ? $internalCustomer->id : 0,
				'user_id' => auth()->user()->id,
				'desc' => $request->desc,
				'trans_id' => 'Chuyển Nội Bộ',
				'bank_id' => $request->transfer_bank,
				'type' => Transaction::TRANSFERED_TYPE,
				'transfered_amount' => $transferAmount,
				'received_amount' => 0,
				'fee' => $request->transfer_fee,
				'created_at' => $request->trans_time
			]);
			Transaction::create([
				'customer_id' => $internalCustomer ? $internalCustomer->id : 0,
				'user_id' => auth()->user()->id,
				'desc' => $request->desc,
				'trans_id' => 'Nhận Nội Bộ',
				'bank_id' => $request->receive_bank,
				'type' => Transaction::RECEIVED_TYPE,
				'transfered_amount' => 0,
				'received_amount' => $receivedAmount,
				'fee' => 0,
				'created_at' => $request->trans_time,
			]);

			DB::commit();
			return redirect()->route(config('laraadmin.adminRoute') . '.transactions.index');
		} catch (\Exception $exception) {
			DB::rollback();
			\Log::error($exception->getMessage());
			\Log::error($exception->getTraceAsString());
			return redirect()->back()->withErrors($exception->getMessage());
		}
	}

	public function approve(Request $request)
	{
		$ids = explode(',', $request->ids);
		foreach ($ids as $id) {
			$transaction = Transaction::find($id);
			if ($transaction && $this->authorize('approve', $transaction)) {
				$transaction->update(['status' => 2]);
			}
		}

		return redirect()->route(config('laraadmin.adminRoute') . '.transactions.index');
	}
}
