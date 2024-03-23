<?php


namespace App\Http\Controllers\ApiV2;

use App\Models\Address;
use App\Models\Audit;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\District;
use App\Models\LockCommission;
use App\Models\Order;
use App\Models\Product;
use App\Models\Province;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\Ward;
use App\Models\DOrder;
use App\Models\AZPoint;


use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddressRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditController extends Controller
{
    protected $guard = 'customer';

    public function index(Request $request)
    {
        $customer = Auth::guard($this->guard)->user();
        $store = $customer->store;
        if ($store->neededToPayCommission($customer->group_id)) {
            return $this->getCommissions($request, $customer);
        }
        $audits = Audit::where('customer_id', $customer->id)
            ->orderBy('id', 'desc');

        if ($request->order_code) {
            $orders = Order::where('code', $request->order_code)->pluck('id')->toArray();
            $audits->whereIn('order_id', $orders);
        }

        $audits = $audits->paginate();

        $items = $audits->getCollection();
        $items->map(function ($item) use ($customer) {
            $order = Order::find($item->order_id);
            $item->order_code = $order ? $order->code : '';
            $transaction = Transaction::find($item->trans_id);
            $item->trans_note = $transaction ? $transaction->id . ' - ' . $transaction->note : '';
            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'order_code' => $order ? $order->code : '',
                'trans_id' => $item->trans_id,
                'trans_note' => $transaction ? $transaction->id . ' - ' . $transaction->note : '',
                'amount' => $customer->hasOwnedShippingService() ? -$item->amount : $item->amount,
                'balance' => $customer->hasOwnedShippingService() ? -$item->balance : $item->balance,
                'created_at' => $item->created_at->format('d/m/Y H:i')
            ];
        });


        return response()->json([
            'last_page' => $audits->lastPage(),
            'total' => $audits->total(),
            'has_more' => $audits->hasMorePages(),
            'items' => $items
        ]);
    }

    protected function getCommissions(Request $request,Customer $customer)
    {
        $customer = Auth::guard($this->guard)->user();
        $source = $request->lock_commission ? '\App\Models\LockCommission' : '\App\Models\Commission';
        $audits = $source::search($request->all())
            ->orderBy('created_at', 'desc')
            ->where('customer_id', $customer->id)
            ->where(function ($q) use ($request) {
                if ($request->type == 1) {
                    $q->where('order_id', '<>', 0);
                }
                if ($request->type == 2) {
                    $q->where('trans_id', '<>', 0);
                }
                if ($request->order_code) {
                    $orders = Order::where('code', $request->order_code)->pluck('id')->toArray();
                    $q->whereIn('order_id', $orders);
                }
            })
            ->paginate();

        $items = $audits->getCollection();
        $items = $items->map(function ($item) use ($customer, $source) {
            if ($item instanceof Commission) {
                $order = Order::find($item->order_id);
                $item->order_code = $order ? $order->code : '';
            }

            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'order_code' => @$item->order_code,
                'trans_id' => $item->trans_id,
                'trans_note' => $item->note,
                'amount' => $customer->hasOwnedShippingService() ? -$item->amount : $item->amount,
                'balance' => $customer->hasOwnedShippingService() ? -$item->balance : $item->balance,
                'created_at' => $item->created_at->format('d/m/Y H:i')
            ];
        });


        return response()->json([
            'last_page' => $audits->lastPage(),
            'total' => $audits->total(),
            'has_more' => $audits->hasMorePages(),
            'items' => $items
        ]);
    }

    public function analytics()
    {
        $customer = Auth::guard($this->guard)->user();

        $balance = Commission::where('customer_id', $customer->id)
            ->orderBy('id', 'desc')
            ->first();
        $success = Commission::where('customer_id', $customer->id)
            ->whereBetween('created_at', [
                Carbon::now()->firstOfMonth(),
                Carbon::now()
            ])
            ->where('order_id', '<>', 0)
            ->count();

        $processing = Order::where('approve', '<>', 1)
            ->where('customer_id', $customer->id)
            ->get();
        $pendingBalance = LockCommission::where('customer_id', $customer->id)
            ->orderBy('id', 'desc')
            ->first();
        $balance->balance = $customer->hasOwnedShippingService() ? -$balance->balance : $balance->balance;

        return response()->json([
            'az_point' => $customer->getAZPoint(),
            'balance' => $balance ? $balance->balance : 0,
            'success' => $success,
            'processing' => $processing->count(),
            'proccesing_balance' => $pendingBalance ? $pendingBalance->balance : 0
        ]);
    }
}
