<?php

namespace App\Http\Controllers\LA;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ProductSeri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Datatables;
use Carbon\Carbon;

class ActivatedWarrantiesController extends Controller
{
    protected $isAdmin = false;
    public $show_action = true;
    public $listing_cols = [
        'id' => 'ID',
        'seri_number' => 'Seri',
        'product_id' => 'Sản phẩm',
        'name' => 'Họ tên',
        'address' => 'Địa chỉ',
        'phone' => 'SĐT',
        'activated_at' => 'Ngày kích hoạt'
    ];

    public function __construct()
    {
        $this->isAdmin = Auth::user()->isSupperAdminRole();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('la.activated_warranties.index', [
            'show_actions' => $this->show_action,
            'listing_cols' => $this->listing_cols,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($this->isAdmin) {
            $seri = ProductSeri::where('id', $id)
                ->whereNotNull('activated_at')
                ->first();
            if ($seri) {
                return view('la.activated_warranties.edit', [
                    'seri' => $seri,
                    'isAdmin' => $this->isAdmin
                ]);
            }
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("seri"),
            ]);
        }
        return redirect(config('laraadmin.adminRoute') . "/");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($this->isAdmin) {
            $seri = ProductSeri::where('id', $id)
                ->whereNotNull('activated_at')
                ->first();
            if ($seri) {
                $seri->activated_at = $request->activated_at;
                $seri->expired_at = Carbon::parse($request->activated_at)->addMonths($seri->product->warranty_period);
                $seri->save();
                return redirect()->back();
            }
            return view('errors.404', [
                'record_id' => $id,
                'record_name' => ucfirst("seri"),
            ]);
        }
        return redirect(config('laraadmin.adminRoute') . "/");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Datatable Ajax fetch
     *
     * @return
     */
    public function dtajax(Request $request)
    {
        $cols = array_keys($this->listing_cols);
        $values = ProductSeri::select($cols)
            ->orderBy('activated_at', 'desc')
            ->whereNotNull('activated_at')
            ->search($request->all());
        if ($request->filter_week) {
            $values->whereDate('activated_at', '>', $request->filter_week);
        }

        $out = Datatables::of($values)->make();
        $data = $out->getData();

        for ($i = 0; $i < count($data->data); $i++) {
            $seri = ProductSeri::find($data->data[$i][0]);
            for ($j = 0; $j < count($cols); $j++) {
                $col = $cols[$j];
                if ($col == 'product_id') {
                    $data->data[$i][$j] = $seri->product->name;
                }
                if ($col == 'address') {
                    $data->data[$i][$j] = $seri->warranty_full_address;
                }
            }

            if ($this->show_action && $this->isAdmin) {
                $output = '<a href="' . url(config('laraadmin.adminRoute') . '/activated-warranties/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                $data->data[$i][] = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }
}
