<?php

use App\Models\Order;
use App\Models\DOrder;
use Illuminate\Database\Seeder;

class update_cod_price_statement_col extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $oClasses = [DOrder::class, Order::class];
        foreach ($oClasses as $oClass) {
            $oClass::whereNull('deleted_at')
                ->where('cod_price_statement', 0)
                ->where('payment_method', Order::PAYMENT_METHOD_COD)
                ->get()
                ->map(function ($order) {
                    $price = $order->isFromFE()
                        ? $order->products->reduce(function ($total, $product) {
                            return $total + intval($product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true));
                        }, 0)
                        : $order->total;
                    $order->update(['cod_price_statement' => $price]);
                });
        }
    }
}
