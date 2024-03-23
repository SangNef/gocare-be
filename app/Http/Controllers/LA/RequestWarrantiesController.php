<?php

namespace App\Http\Controllers\LA;

use Illuminate\Http\Request;

use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\RequestWarranty;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RequestWarrantiesController extends Controller
{
    public $show_action = true;
    public $view_col = 'seri_number';
    public $listing_cols = ['id', 'created_at', 'seri_number', 'product_name', 'phone', 'name', 'content', 'user_id', 'group_id', 'status', 'from'];

    public function __construct()
    {
        // Field Access of Listing Columns
        if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
            $this->middleware(function ($request, $next) {
                $this->listing_cols = ModuleFields::listingColumnAccessScan('RequestWarranties', $this->listing_cols);
                return $next($request);
            });
        } else {
            $this->listing_cols = ModuleFields::listingColumnAccessScan('RequestWarranties', $this->listing_cols);
        }
    }

    public function index()
    {
        $module = Module::get('RequestWarranties');

        if (Module::hasAccess($module->id)) {
            $statusList = RequestWarranty::getListStatus();
            $groups = Group::getWarrantyUnitsGroup();
            $provinces = \App\Models\Province::get(['name', 'id']);

            return View('la.request_warranties.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => $this->listing_cols,
                'module' => $module,
                'statusList' => $statusList,
                'groups' => $groups,
                'provinces' => $provinces
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if (Module::hasAccess("RequestWarranties", "create")) {

            $rules = Module::validateRules("RequestWarranties", $request);
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            try {
                DB::beginTransaction();
                $insert_id = Module::insert("RequestWarranties", $request);
                $requestWarranty = RequestWarranty::find($insert_id);
                $requestWarranty->from = RequestWarranty::FROM_ADMIN;
                $requestWarranty->save();
                DB::commit();

                return redirect()->back();
            } catch (\Exception $exception) {
                DB::rollback();
                \Log::error($exception->getMessage());
                \Log::error($exception->getTraceAsString());

                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if (Module::hasAccess("RequestWarranties", "edit")) {
            $requestWarranty = RequestWarranty::find($id);
            if (isset($requestWarranty->id)) {
                $module = Module::get('RequestWarranties');

                $module->row = $requestWarranty;
                $images = $requestWarranty->getAttachmentsPath();
                $histories = $requestWarranty->histories;

                return view('la.request_warranties.edit', [
                    'module' => $module,
                    'view_col' => $this->view_col,
                    'images' => $images,
                    'histories' => $histories
                ])->with('requestWarranty', $requestWarranty);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("requestWarranty"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    public function update(Request $request, $id)
    {
        if (Module::hasAccess("RequestWarranties", "edit")) {

            $rules = Module::validateRules("RequestWarranties", $request, true);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();;
            }
            try {
                DB::beginTransaction();
                $insert_id = Module::updateRow("RequestWarranties", $request, $id);
                $requestWarranty = RequestWarranty::find($insert_id);
                if ($request->has('histories')) {
                    $histories = array_reduce($request->histories, function ($totalHistory, $history) use ($id) {
                        if (@$history['detail']) {
                            $history['request_warranty_id'] = $id;
                            array_push($totalHistory, $history);
                        }
                        return $totalHistory;
                    }, []);
                    $requestWarranty->histories()->insert($histories);
                }
                DB::commit();

                return redirect()->back();
            } catch (\Exception $exception) {
                DB::rollback();
                \Log::error($exception->getMessage());
                \Log::error($exception->getTraceAsString());

                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    public function destroy($id)
    {
        if (Module::hasAccess("RequestWarranties", "delete")) {
            $requestWarranty = RequestWarranty::find($id);
            $requestWarranty->delete();

            // Redirecting to index() method
            return redirect()->route(config('laraadmin.adminRoute') . '.request-warranties.index');
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    public function dtajax(Request $request)
    {
        $values = RequestWarranty::select($this->listing_cols)
            ->orderBy('id', 'desc')
            ->search($request->all());
        $out = Datatables::of($values)->make();
        $data = $out->getData();

        $fields_popup = ModuleFields::getModuleFields('RequestWarranties');

        for ($i = 0; $i < count($data->data); $i++) {
            $id = $data->data[$i][0];
            $requestWarranty = RequestWarranty::find($id);
            for ($j = 0; $j < count($this->listing_cols); $j++) {
                $col = $this->listing_cols[$j];
                if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
                    $data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
                }
                if ($col == $this->view_col) {
                    $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/request-warranties/' . $id) . '/edit">' . $data->data[$i][$j] . '</a>';
                }
                if ($col == 'user_id') {
                    $user = $requestWarranty->user;
                    $data->data[$i][$j] = $user ? $user->name : '';
                }
                if ($col == 'group_id') {
                    $groups = Group::pluck('display_name', 'id');
                    $data->data[$i][$j] = $data->data[$i][$j] && !$groups->isEmpty() ? $groups[$data->data[$i][$j]] : '';
                }
                if ($col == 'status') {
                    $data->data[$i][$j] = $requestWarranty->getStatusHtmlFormat();
                }
                if ($col == 'from') {
                    $data->data[$i][$j] = $requestWarranty->getFromHtmlFormat();
                }
            }

            if ($this->show_action) {
                $output = '';
                if (Module::hasAccess("RequestWarranties", "edit")) {
                    $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/request-warranties/' . $data->data[$i][0] . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                }

                if (Module::hasAccess("RequestWarranties", "delete")) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.request-warranties.destroy', $data->data[$i][0]], 'method' => 'delete', 'style' => 'display:inline']);
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
