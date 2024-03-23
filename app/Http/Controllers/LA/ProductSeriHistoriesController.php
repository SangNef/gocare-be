<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
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

class ProductSeriHistoriesController extends Controller
{
	public $show_action = false;

	/**
	 * Display a listing of the ProductSeriHistories.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$module = Module::get('ProductSeriHistories');

		if(Module::hasAccess($module->id)) {
			return View('la.productserihistories.index');
		} else {
            return redirect(config('laraadmin.adminRoute')."/");
        }
	}

	/**
	 * Show the form for creating a new productserihistory.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created productserihistory in database.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
	}

	/**
	 * Display the specified productserihistory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
	}

	/**
	 * Show the form for editing the specified productserihistory.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{

	}

	/**
	 * Update the specified productserihistory in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
	}

	/**
	 * Remove the specified productserihistory from storage.
	 *
	 * @param  int  $id
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
		$values = DB::table('product_series')->select('seri_number','activation_code','product_id','qr_code_status','activation_customer_id','order_id','purchased_date','status','activated_date');
        $out = Datatables::of($values)->make();
        $data = $out->getData();
        for ($i = 0; $i < count($data->data); $i++) {
            for ($j = 0; $j < 10; $j++) {
                if ($j ==3) {
                    $data->data[$i][$j] = ($data->data[$i][$j] == 0) ? '<span class="label label-success">Mã mềm</span>' : '<span class="label label-danger">Mã cứng</span>';
                }
                if ($j ==5) {
                    $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/orders/' . $data->data[$i][$j]) . '">' . $data->data[$i][$j] . '</a>';
                }
                if ($j == 6 && !empty($data->data[$i][$j])) {
                    $data->data[$i][$j] = Carbon::parse($data->data[$i][$j])->format('d/m/Y H:i');
                }
                if ($j == 7) {
                    $data->data[$i][7] = ($data->data[$i][7] == 0) ? '<span class="label label-danger">Chưa thanh toán</span>' : '<span class="label label-success"> Đã thanh toán</span>';
                }
                if ($j == 8) {
                    $data->data[$i][$j] = Carbon::parse($data->data[$i][$j])->format('d/m/Y H:i');
                }
                if ($j == 2) {
                    $product = (!empty($data->data[$i][$j]))?Product::find($data->data[$i][$j]):'';
                    if($product)$data->data[$i][$j] = $product->first()->name;
                    else
                    $data->data[$i][$j] = '';
                }
                if ($j == 4 && $data->data[$i][5]) {
                    $order = Order::where('id',$data->data[$i][5]);
                    $data->data[$i][4] = ($order->count() >0)? '<a href="' . url(config('laraadmin.adminRoute') . '/customers/' . $order->with('customer')->first()->customer->id) . '">' . $order->with('customer')->first()->customer->email . '</a>':'';

                }
                if ($j == 9) {
                    if( $data->data[$i][7] ==2){
                        $data->data[$i][9]  = '<span class="label label-success">Đã sử dụng</span>';
                    } else if( $data->data[$i][7] ==3){
                        $data->data[$i][$j]  = '<span class="label label-success">Đã bị khoá</span>';
                    }else
                        $data->data[$i][$j]  ='';
                }


            }
        }
        $out->setData($data);
        return $out;
	}
}
