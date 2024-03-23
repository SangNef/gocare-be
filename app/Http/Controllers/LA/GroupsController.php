<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\GroupCateDiscount;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Discount;
use Illuminate\Http\Request;
use DB;
use Validator;
use Datatables;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;

use App\Models\Group;

class GroupsController extends Controller
{
	public $show_action = true;
	public $view_col = 'name';
	public $listing_cols = ['id', 'name', 'display_name'];
	
	public function __construct() {
		// Field Access of Listing Columns
		if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$this->middleware(function ($request, $next) {
				$this->listing_cols = ModuleFields::listingColumnAccessScan('Groups', $this->listing_cols);
				return $next($request);
			});
		} else {
			$this->listing_cols = ModuleFields::listingColumnAccessScan('Groups', $this->listing_cols);
		}
	}
	
	/**
	 * Display a listing of the Groups.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('Groups');
		
		if(Module::hasAccess($module->id)) {
			return View('la.groups.index', [
				'show_actions' => $this->show_action,
				'listing_cols' => $this->listing_cols,
				'module' => $module
			]);
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new group.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created group in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Module::hasAccess("Groups", "create")) {
		
			$rules = Module::validateRules("Groups", $request);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$insert_id = Module::insert("Groups", $request);
				$group = Group::find($insert_id);
                $group->require_payment = (int) $request->require_payment ? 0 : 1;
				$group->save();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.groups.index');
			} catch (\Exception $exception) {
				\Illuminate\Support\Facades\DB::rollback();
				\Log::error($exception->getMessage());
				\Log::error($exception->getTraceAsString());
				return redirect()->back()->withErrors($exception->getMessage());
			}
			
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Display the specified group.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id, Discount $discount)
	{
		if(Module::hasAccess("Groups", "view")) {
			
			$group = Group::find($id);
			if(isset($group->id)) {
				$module = Module::get('Groups');
				$module->row = $group;
				$categories = ProductCategory::all();
				$discounts = GroupCateDiscount::where('group_id', $group->id)
                    ->orderBy('quantity', 'desc')
                    ->get();
				$withProductQuantities = array_unique($discounts->filter(function ($cate) {
				    return $cate->type == 1;
                })->map(function ($cate) {
                    return $cate->quantity;
                })->toArray());
                $withoutProductQuantities = array_unique($discounts->filter(function ($cate) {
                    return $cate->type == 2;
                })->map(function ($cate) {
                    return $cate->quantity;
                })->toArray());
                $discounts = $discounts->map(function ($cate) {
                    $cate->cate_type_id = $cate->cate_id . '_' . $cate->type . '_' . $cate->quantity;
                    $cate->discount_text = implode('+', array_filter([
                        number_format($cate->discount),
                        $cate->discount_1 > 0 ? (int) $cate->discount_1 . '%' : ''
                    ]));

                    return $cate;
                })->keyBy('cate_type_id')
                ->toArray();

				return view('la.groups.show', [
					'module' => $module,
					'view_col' => $this->view_col,
					'no_header' => true,
					'no_padding' => "no-padding",
                    'discount' => $discounts,
                    'categories' => $categories,
                    'withProductQuantities' => $withProductQuantities,
                    'withoutProductQuantities' => $withoutProductQuantities
				])->with('group', $group);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("group"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Show the form for editing the specified group.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Module::hasAccess("Groups", "edit")) {			
			$group = Group::find($id);
			if(isset($group->id)) {	
				$module = Module::get('Groups');
				
				$module->row = $group;
				
				return view('la.groups.edit', [
					'module' => $module,
					'view_col' => $this->view_col,
				])->with('group', $group);
			} else {
				return view('errors.404', [
					'record_id' => $id,
					'record_name' => ucfirst("group"),
				]);
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Update the specified group in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Module::hasAccess("Groups", "edit")) {
			
			$rules = Module::validateRules("Groups", $request, true);
			
			$validator = Validator::make($request->all(), $rules);
			
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();;
			}

			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$insert_id = Module::updateRow("Groups", $request, $id);
				$group = Group::find($insert_id);
                $group->require_payment = (int) $request->require_payment ? 0 : 1;
                $group->save();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.groups.index');
			} catch (\Exception $exception) {
				\Illuminate\Support\Facades\DB::rollback();
				\Log::error($exception->getMessage());
				\Log::error($exception->getTraceAsString());
				return redirect()->back()->withErrors($exception->getMessage());
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}

	/**
	 * Remove the specified group from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		if(Module::hasAccess("Groups", "delete")) {
			try {
				\Illuminate\Support\Facades\DB::beginTransaction();
				$group = Group::findOrFail($id);
				$group->delete();
				\Illuminate\Support\Facades\DB::commit();

				return redirect()->route(config('laraadmin.adminRoute') . '.groups.index');
			} catch (\Exception $exception) {
				\Illuminate\Support\Facades\DB::rollback();
				\Log::error($exception->getMessage());
				\Log::error($exception->getTraceAsString());
				return redirect()->back()->withErrors($exception->getMessage());
			}
		} else {
			return redirect(config('laraadmin.adminRoute')."/");
		}
	}
	
	/**
	 * Datatable Ajax fetch
	 *
	 * @return
	 */
	public function dtajax()
	{
		$values = Group::select($this->listing_cols)->whereNull('deleted_at');
		$out = Datatables::of($values)->make();
		$data = $out->getData();

		$fields_popup = ModuleFields::getModuleFields('Groups');
		
		for($i=0; $i < count($data->data); $i++) {
			for ($j=0; $j < count($this->listing_cols); $j++) { 
				$col = $this->listing_cols[$j];
				if($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
					$data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
				}
				if($col == $this->view_col) {
					$data->data[$i][$j] = '<a href="'.url(config('laraadmin.adminRoute') . '/groups/'.$data->data[$i][0]).'">'.$data->data[$i][$j].'</a>';
				}
				// else if($col == "author") {
				//    $data->data[$i][$j];
				// }
			}
			
			if($this->show_action) {
				$output = '';
				if(Module::hasAccess("Groups", "edit")) {
					$output .= '<a href="'.url(config('laraadmin.adminRoute') . '/groups/'.$data->data[$i][0].'/edit').'" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
				}
				
				if(Module::hasAccess("Groups", "delete")) {
					$output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.groups.destroy', $data->data[$i][0]], 'method' => 'delete', 'style'=>'display:inline']);
					$output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
					$output .= Form::close();
				}
				$data->data[$i][] = (string)$output;
			}
		}
		$out->setData($data);
		return $out;
	}

	public function updateDiscountQuantity(Request $request) {
	    $this->validate($request, [
	        'group_id' => 'required|exists:groups,id',
            'cate_id' => 'required|exists:productcategories,id',
            'quantity' => 'required|integer|min:1',
            'discount' => 'sometimes|integer|min:0',
            'discount_1' => 'sometimes|numeric|between:0,99.99',
            'type' => 'required|in:1,2'
        ]);

	    GroupCateDiscount::updateOrCreate(array_only($request->all(), [
            'group_id',
            'cate_id',
            'quantity',
            'type'
        ]),
            array_only($request->all(), [
            'discount',
            'discount_1',
        ]));

        return redirect(config('laraadmin.adminRoute')."/groups/".$request->group_id.'#tab-discount');
    }

    public function deleteDiscount(Request  $request)
    {
        $id = explode('_', $request->get('id', ''));
        $cateId = @$id[0];
        $type = @$id[1];
        $quantity = @$id[2];
        $groupId = $request->group_id;

        GroupCateDiscount::where('group_id', $groupId)
            ->where('cate_id', $cateId)
            ->where('type', $type)
            ->where('quantity', $quantity)
            ->delete();

        return redirect(config('laraadmin.adminRoute')."/groups/".$groupId.'#tab-discount');
    }
}
