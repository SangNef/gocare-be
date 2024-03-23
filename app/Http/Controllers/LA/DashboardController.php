<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Carbon\Carbon;
use Dwij\Laraadmin\Models\Module;
use Illuminate\Http\Request;
use App\Models\CustomerStatistic;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index(ProductRepository $productRepository)
    {
        $data = [];
        $order = Order::where('created_at', '>=', Carbon::today())->count();
        $product = Product::where('status', 1)->count();
        $post = Post::count();
        $custommer = Customer::select('group_id', DB::raw('count(*) as total'))->GroupBy('group_id')->lists('total', 'group_id');;
        $data['order'] = $order;
        $data['product'] = $product;
        $data['post'] = $post;
        $data['daily'] = $custommer[6] ?? 0;
        $data['khachhang'] = $custommer[51] ?? 0;
        $data['ctv'] = $custommer[52] ?? 0;
        $products = $productRepository->getProductsOutOfStock();
        $order = Order::where('status',1)->with('customer')->orderBy('id','DESC')->limit(8);
        $data['order_list'] = $order->get();
        $data['order_count'] = $order->count();
        return view('la.dashboard', ['data' => $data,'product'=>$products]);
    }

    protected function getCustomers($filter)
    {
        $result = \DB::table(\DB::raw('(SELECT * FROM customer_activity_statistics ORDER BY created_at DESC LIMIT 10000) as customer_activity_statistics'))
            ->select(\DB::raw('customer_activity_statistics.*, customers.username, users.name as admin_name, customers.debt_total, IF(customer_activity_statistics.o_amount > 0, customers.debt_total/customer_activity_statistics.o_amount, 0) as r_percent'))
            ->join('customers', 'customers.id', '=', 'customer_activity_statistics.customer_id')
            ->join('users', 'users.id', '=', 'customers.parent_id')
            ->where('customer_activity_statistics.created_at', '>=', \Carbon\Carbon::now()->subDays(30))
            ->groupBy('customer_id');
        if (@$filter['parent_id']) {
            $result->where('customers.parent_id', $filter['parent_id']);
        }
        if (@$filter['username']) {
            $result->where('customers.username', 'like', '%' . $filter['username'] . '%');
        }
        if (@$filter['order']) {
            $result->orderBy($filter['order'], 'desc');
        } else {
            $result->orderBy('customers.debt_total', 'desc');
        }

        return $result;
    }

    public function report(Request $request)
    {

        $profit = Order::whereIn('status', [1, 2]);
        if ($request->to && $request->from) {
            $profit = $profit->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
        }
        $data = [];
        $data['profit'] = ['total' => $profit->sum('total'), 'count' => $profit->count(), 'paid' => $profit->sum('paid'), 'unpaid' => $profit->sum('unpaid')];
        $unpaid = $profit;
        $data['unpaid'] = $unpaid->where('unpaid', '>', 0)->count();
        return view('la.dashboard.report', [
            'data' => $data,
        ])->render();
    }

    public function customers(Request $request)
    {
        \Cache::put('dashboard.customers.filter.' . auth()->user()->id, json_encode($request->all()), 600);
        $customers = $this->getCustomers($request->all())->paginate(30);

        return view('la.dashboard.customers', [
            'customers' => $customers
        ])->render();
    }

    public function exportCustomers()
    {
        $filter = json_decode(\Cache::get('dashboard.customers.filter.' . auth()->user()->id, ''), true);
        $customers = collect($this->getCustomers($filter)
            ->get())
            ->map(function ($customer) {
                return [
                    'Người quản lý' => $customer->admin_name,
                    'Tài khoản' => $customer->username,
                    'Tổng nạp' => $customer->amount,
                    'Tổng sản lượng' => $customer->o_amount,
                    'Tổng nợ' => $customer->debt,
                    '%' => $customer->percent,
                    'Ngày nạp' => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customer->created_at)->format('d/m/Y H:i'),
                ];
            });

        return $this->download($customers);
    }

    public function download($data, $extension = 'xlsx')
    {
        Excel::create('cong_no', function ($excel) use ($data) {

            $excel->sheet('cong_no', function ($sheet) use ($data) {

                $sheet->setOrientation('landscape');
                $sheet->fromArray($data);

            });

        })->download($extension);;
    }
}