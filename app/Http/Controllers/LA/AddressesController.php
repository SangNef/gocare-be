<?php

namespace App\Http\Controllers\LA;

use Illuminate\Http\Request;
use App\Datatable\Datatables;
use App\Http\Controllers\Controller;
use Validator;

class AddressesController extends Controller
{
    protected $model;
    protected $type;
    protected $name;
    public $show_action = true;
    public $view_col = 'name';
    public $listing_cols = [
        [
            'title' => 'ID',
            'field' => 'id',
        ],
        [
            'title' => 'Tên',
            'field' => 'name',
        ],
    ];

    public function __construct()
    {
        $this->type = request()->type ?: 'province';
        switch ($this->type) {
            case 'district':
                $this->model = "\App\Models\District";
                $this->name = 'Quận/Huyện';
                break;
            case 'ward':
                $this->model = "\App\Models\Ward";
                $this->name = 'Phường/Xã';
                break;
            default:
                $this->model = "\App\Models\Province";
                $this->name = 'Tỉnh/Thành phố';
        }
    }

    public function index()
    {
        return view('la.addresses.index', [
            'show_actions' => $this->show_action,
            'cols' => $this->listing_cols,
            'datatable_path' => "address_dt_ajax",
            'name' => $this->name
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $address = $this->model::findOrFail($id);
        switch ($this->type) {
            case "province":
                $childrens = [
                    'type' => 'district',
                    'data' => $address->districts
                ];
                break;
            case "district":
                $childrens = [
                    'type' => 'ward',
                    'data' => $address->wards
                ];
                break;
            default:
                $childrens = null;
        }

        return view('la.addresses.edit', [
            'address' => $address,
            'view_col' => $this->view_col,
            'name' => $this->name,
            'childrens' => $childrens,
            'type' => $this->type
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $address = $this->model::findOrFail($id);
        $address->name = $request->name;
        $address->timestamps = false;
        $address->save();

        return redirect()->back();
    }

    public function destroy($id)
    {
        //
    }

    public function dtajax()
    {
        $values = $this->model::select(array_column($this->listing_cols, 'field'))->orderBy('id');
        $datatable = Datatables::of($values);
        $out = $datatable->make();
        $data = $out->getData();

        for ($i = 0; $i < count($data->data); $i++) {
            if ($this->show_action) {
                $output = '<a href="' . url(config('laraadmin.adminRoute') . '/addresses/' . $data->data[$i][0] . '/edit?type=' . $this->type) . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                $data->data[$i][] = (string)$output;
            }
        }

        $out->setData($data);
        return $out;
    }
}
