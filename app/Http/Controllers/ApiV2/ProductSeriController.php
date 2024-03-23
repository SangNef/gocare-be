<?php

namespace App\Http\Controllers\ApiV2;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSeri;
use App\Models\ProductsProductCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductSeriController extends Controller
{
    protected $guard = 'customer';

    public function index(Request $request)
    {
        // TODO: Refactor -> Viết lại query cho tối ưu
        $customer = Auth::guard($this->guard)->user();
        if ($customer->customer_parent_id) {
            $seris = ProductSeri::whereExists(function ($q) use ($customer, $request) {
                $q->select('productserihistories.product_seri_id')
                    ->from('productserihistories')
                    ->whereRaw('productserihistories.product_seri_id = product_series.id')
                    ->where('productserihistories.customer_id', $customer->id);
                if ($request->order_code) {
                    $q->join('transferorders', 'productserihistories.transfer_order_id', '=', 'transferorders.id');
                    $q->where('transferorders.code', $request->order_code);
                }
            });
        } else {
            $seris = ProductSeri::whereExists(function ($q) use ($customer, $request) {
                $q->select('orders.id')
                    ->from('orders')
                    ->whereRaw('orders.id = product_series.order_id')
                    ->where('orders.customer_id', $customer->id);
                if ($request->order_code) {
                    $q->where('orders.code', $request->order_code);
                }
            });
        }
        if ($request->product_name) {
            $seris->whereExists(function ($q) use ($request) {
                $q->select('products.id')
                    ->from('products')
                    ->whereRaw('products.id = product_series.product_id')
                    ->where(function ($q1) use ($request) {
                        $q1->where('products.name', 'LIKE', "%{$request->product_name}%")
                            ->orWhere('products.sku', 'LIKE', "%{$request->product_name}%");
                    });
            });
        }
        if ($request->seri_numbers) {
            $seris->whereIn('seri_number', explode(',', $request->seri_numbers));
        }

        // status: all = tất cả, 0 = chưa thanh toán, 1 = đã thanh toán, 2 = đã sử dụng, 3 = bị khóa
        if ($request->has('status') && $request->get('status') !== 'all') {
            $seris->where('status', $request->get('status'));
        }

        if ($request->seri_number) {
            $seris->where('seri_number', $request->seri_number);
        }

        if ($request->activation_code) {
            $seris->where('activation_code', $request->activation_code);
        }

        if ($request->seri_activation) {
            $requestSeriActivation = data_get($request, 'seri_activation');
            $seris->where(function ($q) use ($request) {
                $q->where('seri_number', 'like', "%{$requestSeriActivation}%")
                    ->orWhere('activation_code', 'like', "%{$requestSeriActivation}%");
            });
        }
        
        if ($request->type == 1) {
            $deviceCates = ProductCategory::where('is_devices', 1)->pluck('id');
            $pIds = ProductsProductCategory::whereIn('product_category_id', $deviceCates)->pluck('product_id');
            $seris->whereIn('product_id', $pIds);
        } else if ($request->type == 2) {
            $nonDeviceCates = ProductCategory::where('is_devices', '<>', 1)->pluck('id');
            $pIds = ProductsProductCategory::whereIn('product_category_id', $nonDeviceCates)->pluck('product_id');
            $seris->whereIn('product_id', $pIds);
        }

        $seris = $seris->paginate($request->get('perpage', 15));

        $items = $seris->getCollection();
        $orders = Order::query()->findMany($items->pluck('order_id'));
        $products = Product::query()->findMany($items->pluck('product_id'));
        $items = $items->map(function ($item) use ($customer, $orders, $products) {
            return [
                'id' => $item->id,
                'order' => $orders->where('id', $item->order_id)->first(),
                'product' => $products->where('id', $item->product_id)->first(),
                'serial_number' => $item->seri_number,
                'activation_code' => $item->activation_code,
                'status' => $item->status,
                'purchased_date' => $item->purchased_date,
                'activated_date' => $item->activated_date,
            ];
        });
        
        return response()->json([
            'last_page' => $seris->lastPage(),
            'total' => $seris->total(),
            'has_more' => $seris->hasMorePages(),
            'items' => $items
        ]);
    }
}
