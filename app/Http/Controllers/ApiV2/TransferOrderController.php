<?php

namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\ProductSeri;
use App\Models\Product;
use Carbon\Carbon;
use App\Http\Requests\TransferOrderRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderStatus;
use App\Models\ProductGroupAttributeMedia;
use App\Models\TransferOrder;
use App\Models\ProductSeriHistory;
use App\Models\Customer;
use App\Repositories\ProductSeriesRepository;
use Illuminate\Support\Facades\Validator;

class TransferOrderController extends Controller
{
    public function store(TransferOrderRequest $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'unique:transferorders,code',
            ],
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'code' => ['Mã đã tồn tại, vui lòng chọn mã khác']
            ], 422);
        }

        $seris = explode(',', $request->seris);
        
        $customer = Auth::guard('customer')->user();

        // $orders = ProductSeri::whereIn('seri_number', $seris)
        //     ->where()
        $validSeris = \DB::table('product_series')
            ->selectRaw('product_series.*')
            ->join('orders', 'orders.id', '=', 'product_series.order_id')
            ->where('orders.customer_id', $customer->id)
            ->where('product_series.status', 0)
            // ->where('orders.status', OrderStatus::SUCCESS)
            ->whereIn('product_series.seri_number', $seris)
//            ->whereBetween('ordered_at', [
//                Carbon::createFromFormat('Y/m/d', config('app.revenue_statistic_for_npp.start')),
//                Carbon::createFromFormat('Y/m/d', config('app.revenue_statistic_for_npp.end')),
//            ])
            ->whereNotExists(function ($q) use ($customer) {
                $q->select('productserihistories.id')
                    ->from('productserihistories')
                    ->whereRaw('productserihistories.product_seri_id = product_series.id')
                    ->where('productserihistories.creator_id', $customer->id);
            })
            ->count();
        if ($validSeris != count($seris)) {
            return response()->json([
                'seris' => ['Mã seri không hợp lệ']
            ], 422);
        }
        $subCustomer = Customer::where('customer_parent_id', $customer->id)
            ->where('id', $request->customer_id)
            ->first();
        if (!$subCustomer) {
            return response()->json([
                'customer' => ['Tài khoản cấp dưới không hợp lệ']
            ], 422);
        }


        $transferOrder = TransferOrder::create([
            'code' => $request->code,
            'customer_id' => $request->customer_id,
            'creator_id' => $customer->id,
            'number_of_seris' => count($seris),
            'amount' => 0,
        ]);
        $total = 0;
        foreach ($seris as $seri) {
            $seri = ProductSeri::where('seri_number', $seri)->first();
            $product = $seri->product;
            ProductSeriHistory::create([
                'product_seri_id' => $seri->id,
                'creator_id' => $customer->id,
                'customer_id' => $subCustomer->id,
                'transfered_at' => \Carbon\Carbon::now(),
                'transfer_order_id' => $transferOrder->id,
                'price' => $product->price,
            ]);
            $total += $product->price;
        }

        $transferOrder->amount = $total;
        $transferOrder->save();

        return response()->json('OK');
    }

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $owner = $request->get('type') == 1 ? 'creator_id' : 'customer_id';
        $transderOrders = TransferOrder::where($owner, $customer->id)
            ->where(function ($q) use($request) {
                if ($request->code) {
                    $q->where('code', 'like', '%' . $request->code . '%');
                }
                if ($request->customer_id) {
                    $q->where('customer_id', $request->customer_id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate();
        $items = $transderOrders->getCollection()->map(function ($to) {
            $result = $to->toArray();
            $result['customer_name'] = $to->customer->name;
            $result['transfered_at'] = $to->created_at->format('d/m/Y');
            
            return $result;
        });
        return response()->json([
            'last_page' => $transderOrders->lastPage(),
            'total' => $transderOrders->total(),
            'has_more' => $transderOrders->hasMorePages(),
            'items' => $items
        ]);
    }

    public function getAvailableSeris(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $productSeris = \DB::table('product_series')
            ->selectRaw('product_series.*')
            ->join('orders', 'orders.id', '=', 'product_series.order_id')
            ->where('orders.customer_id', $customer->id)
             ->where('product_series.status', 0)
//            ->whereBetween('ordered_at', [
//                Carbon::createFromFormat('Y/m/d', config('app.revenue_statistic_for_npp.start')),
//                Carbon::createFromFormat('Y/m/d', config('app.revenue_statistic_for_npp.end')),
//            ])
            ->whereNotExists(function ($q) use ($customer) {
                $q->select('productserihistories.id')
                    ->from('productserihistories')
                    ->whereRaw('productserihistories.product_seri_id = product_series.id')
                    ->where('productserihistories.creator_id', $customer->id);
            })
            ->where(function ($q) use ($request) {
                if ($request->seri) {
                    $q->where('seri_number', 'like', '%' . $request->seri . '%');
                }
                if ($request->order_id) {
                    $q->where('orders.id', $request->order_id);
                }
                if ($request->exclude_seris) {
                    $q->whereNotIn('seri_number', explode(',', $request->exclude_seris));
                }
            })
            ->paginate();

        $items = $productSeris->getCollection()->map(function ($seri) {
            $product = Product::find($seri->product_id);
            $result = [
                'name' => $product->name,
                'sku' => $product->sku,
                'seri_number' => $seri->seri_number,
                'activation_code' => $seri->activation_code,
                'price' => $product->price
            ];
            if ($seri->group_attribute_id) {
                $attribute = ProductGroupAttributeMedia::find($seri->group_attribute_id);
                if ($attribute) {
                    $request['attr_text'] = $attribute->attribute_value_texts;
                }
            }

            return $result;
        });

        return response()->json([
            'last_page' => $productSeris->lastPage(),
            'total' => $productSeris->total(),
            'has_more' => $productSeris->hasMorePages(),
            'items' => $items
        ]);
    }

    public function getProductSeriHistories(Request $request, ProductSeriesRepository $rp)
    {
        $customer = Auth::guard('customer')->user();
        $pseri = ProductSeri::where('seri_number', $request->seri)->first();
        if ($pseri && $pseri->order) {
            $history = ProductSeriHistory::where('product_seri_id', $pseri->id)
                ->where('creator_id', $customer->id)
                ->first();
            if ($history || $pseri->order->customer_id == $customer->id) {
                if ($history) {
                    $histories = ProductSeriHistory::where('product_seri_id', $pseri->id)
                        ->where('created_at', '>=', $history->created_at)
                        ->get()
                        ->map(function ($history) {
                            $transferOrder = $history->transferOrder;
                            
                            return [
                                'from' => $history->creator->name,
                                'to' => $history->customer->name,
                                'transfered_at' => $history->created_at->format('d/m/Y'),
                                'order_code' => $transferOrder->code,
                            ];
                        })
                        ->toArray();
                } else {
                    $histories = [];
                }
                $activated = [];
                if ($pseri->activated_at) {
                    $activated = [
                        'status' => 'Đã kích hoạt',
                        'activated_at' => $pseri->activated_at,
                        'info' => implode(', ', [
                            $pseri->name,
                            $rp->getCustomerPhone($pseri->phone),
                            $rp->getCustomerProvince($pseri->province),
                        ])
                    ];
                } else {
                    $activated = [
                        'status' => 'Chưa kích hoạt',
                        'activated_at' => '',
                        'info' => '',
                        'activation_code' => $pseri->getActivationCode()
                    ];
                }

                return [
                    'histories' => $histories,
                    'activated' => $activated,
                ];
                
            }
        }

        return [];
    }

    public function get($id)
    {
        $customer = Auth::guard('customer')->user();
        $transferOrder = TransferOrder::where(function ($q) use ($customer) {
                $q->where('creator_id', $customer->id)
                ->orWhere('customer_id', $customer->id);
            })
            ->where('id', $id)
            ->first();
        
        return [
            'code' => $transferOrder->code,
            'customer_id' => $transferOrder->customer_id,
            'customer_name' => $transferOrder->customer->name, 
            'parent' => $transferOrder->customer->parent ? $transferOrder->customer->customer_parent_id : '',
            'seris' =>  $transferOrder->seris->map(function ($history) {
                $pSeri = $history->productSeri;
                $product = $pSeri->product;
                return [
                    'seri_number' => $pSeri->seri_number,
                    'price' => $history->price,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'activated_status' => $pSeri->activated_at ? 1 :  0,
                    'activation_code' => $pSeri->activation_code,
                    'p_id' => $product->id,
                ];
            }),
        ];
    }
}
