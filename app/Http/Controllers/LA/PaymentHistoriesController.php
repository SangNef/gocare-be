<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
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

use App\Models\PaymentHistory;

class PaymentHistoriesController extends Controller
{
    public $show_action = false;
    public $view_col = 'provider';
    public $listing_cols = ['id', 'provider', 'response', 'message', 'order_id'];

    public function __construct()
    {
        // Field Access of Listing Columns
        if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
            $this->middleware(function ($request, $next) {
                $this->listing_cols = ModuleFields::listingColumnAccessScan('PaymentHistories', $this->listing_cols);
                return $next($request);
            });
        } else {
            $this->listing_cols = ModuleFields::listingColumnAccessScan('PaymentHistories', $this->listing_cols);
        }
    }

    /**
     * Display a listing of the PaymentHistories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $module = Module::get('PaymentHistories');
        if (Module::hasAccess($module->id)) {
            return View('la.paymenthistories.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => $this->listing_cols,
                'module' => $module
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Show the form for creating a new paymenthistory.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created paymenthistory in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified paymenthistory.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified paymenthistory.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified paymenthistory in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified paymenthistory from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Datatable Ajax fetch
     *
     * @return
     */
    public function dtajax()
    {
        $values = DB::table('paymenthistories')
            ->select('id', 'order_id', 'request', 'created_at', 'status', 'provider')
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc');

        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $total_amount = 0;

        for ($i = 0; $i < count($data->data); $i++) {
            if ($data->data[$i][1]) {
                $data->data[$i][1] = '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $data->data[$i][1]) . '">' . $data->data[$i][1] . '</a>';
            }

            if ($data->data[$i][2] != '') {
                $total = json_decode($data->data[$i][2], true);
                $seriNumbers = explode(',', $total['seri_numbers'] ?? '');
                $activationCodes = [];

                foreach ($seriNumbers as $seriNumber) {
                    $activationCode = DB::table('product_series')
                        ->where('seri_number', trim($seriNumber))
                        ->value('activation_code');
                    $activationCodes[] = $activationCode ? $seriNumber . '-' . $activationCode : $seriNumber;
                }

                $total_amount += $total['total'] ?? 0;
                $data->data[$i][2] = $total['total'] ?? 0;
                $data->data[$i][6] = implode(', ', array_filter($activationCodes));
            }

            if (!empty($data->data[$i][3])) {
                $data->data[$i][3] = Carbon::parse($data->data[$i][3])->format('d/m/Y H:i');
            }

            if ($data->data[$i][4]) {
                $data->data[$i][4] = ($data->data[$i][4] === 3) ? '<span class="label label-success">Thành công</span>' : (($data->data[$i][4] === 1) ? '<span class="label label-info">Đang xử lý</span>' : '<span class="label label-danger">Thất bại</span>');
            }
        }

        $total = [
            'total_amount' => number_format($total_amount),
        ];
        $data->total = $total;

        $out->setData($data);
        return $out;
    }
}
