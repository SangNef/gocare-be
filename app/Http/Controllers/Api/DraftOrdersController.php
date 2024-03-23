<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\DraftOrder;
use App\Models\DraftOrderProduct;
use App\Models\ProductSeri;
use App\Services\Generator;

use function PHPSTORM_META\map;

class DraftOrdersController extends Controller
{
    protected $draftOrder;

    public function __construct(DraftOrder $draftOrder)
    {
        $this->draftOrder = $draftOrder;
    }
    public function index()
    {
        $orders = $this->draftOrder->all();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $order = $this->draftOrder->create([
            'order_code' => $request->name
        ]);
        return response()->json([
            'order_id' => $order->id,
            'order_code' => $order->order_code
        ]);
    }

    public function get($id)
    {
        $order = $this->draftOrder->find($id);
        if ($order) {
            $orderProducts = $order->getOrderProductData();
            return response()->json([
                'id' => $order->id,
                'order_code' => $order->order_code,
                'number_of_products' => $order->number_of_products,
                'created_at' => $order->created_at,
                'products' => $orderProducts
            ]);
        }

        return response()->json([
            'errors' => [
                'id' => [
                    'Không tìm thấy đơn hàng'
                ]
            ]
        ], 404);
    }

    public function destroy($id)
    {
        $order = $this->draftOrder->find($id);
        if ($order) {
            $order->orderProducts()->delete();
            $order->delete();
            return response()->json('OK', 200);
        }

        return response()->json([
            'errors' => [
                'id' => [
                    'Không tìm thấy đơn hàng'
                ]
            ]
        ], 404);
    }

    public function addProductToOrder($id, $seri)
    {
        $order = $this->draftOrder->find($id);
        if ($order) {
            $prodSeri = ProductSeri::where('seri_number', $seri)->first();
            if (!$prodSeri || $prodSeri->order()->exists()) {
                return response()->json([
                    'errors' => [
                        'seri' => [
                            'Seri không tồn tại hoặc đã được sử dụng'
                        ]
                    ]
                ], 404);
            }

            $order->orderProducts()->updateOrCreate([
                'product_seri' => $prodSeri->seri_number
            ], [
                'product_id' => $prodSeri->product_id,
                'product_seri' => $prodSeri->seri_number
            ]);
            $orderProducts = $order->getOrderProductData();

            return response()->json([
                'id' => $order->id,
                'order_code' => $order->order_code,
                'number_of_products' => $order->number_of_products,
                'created_at' => $order->created_at,
                'products' => $orderProducts
            ]);
        }

        return response()->json([
            'errors' => [
                'id' => [
                    'Không tìm thấy đơn hàng'
                ]
            ]
        ], 404);
    }

    public function removeProductFromOrder($id, $seri)
    {
        $order = $this->draftOrder->find($id);
        if ($order) {
            $order->orderProducts()->where('product_seri', $seri)->delete();

            return response()->json("OK");
        }

        return response()->json([
            'errors' => [
                'id' => [
                    'Không tìm thấy đơn hàng'
                ]
            ]
        ], 404);
    }
}
