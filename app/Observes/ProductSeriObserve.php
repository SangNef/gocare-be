<?php
namespace App\Observes;

use App\Models\Product;
use App\Models\ProductSeri;
use App\Models\ActivateToEarn;
use App\Repositories\AZPointRepository;

class ProductSeriObserve
{
    public function saving(ProductSeri $productSeri)
    {
        if ($productSeri->isDirty('order_id')) {
            $order = $productSeri->order;
            if ($order) {
                $productSeri->ordered_at = $order->created_at;
            }

            if (!$order) {
                $originalOrder = Order::find($productSeri->getOriginal('order_id'));
                if ($originalOrder) {
                    $productSeri->ordered_at = null;
                }
            }
        }

        if ($productSeri->isDirty('activated_at')) {
            $order = $productSeri->order;
            if ($order && $order->customer->can_create_sub) {
                if (ActivateToEarn::where('phone', $productSeri->phone)->exists()) {
                    ActivateToEarn::create([
                        'order_id' => $order->id,
                        'product_seri_id' => $productSeri->id,
                        'name' => $productSeri->name,
                        'phone' => $productSeri->phone,
                        'activated_at' => $productSeri->activated_at,
                        'status' => 3,
                        'amount' => config('app.reward_for_activated'),
                        'phone_info' => $productSeri->phone_info,
                        'result' => 'Số điện thoại đã được sử dụng'
                    ]);
                } else {
                    ActivateToEarn::create([
                        'order_id' => $order->id,
                        'product_seri_id' => $productSeri->id,
                        'name' => $productSeri->name,
                        'phone' => $productSeri->phone,
                        'activated_at' => $productSeri->activated_at,
                        'status' => 0,
                        'amount' => config('app.reward_for_activated'),
                        'phone_info' => $productSeri->phone_info,
                    ]);
                }
            } 
            app(AZPointRepository::class)->processForActivatingPSeri($productSeri);
        }
    }
}
