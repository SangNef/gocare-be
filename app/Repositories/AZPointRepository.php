<?php
namespace App\Repositories;
use App\Models\ProductSeri;
use App\Models\AZPoint;
use App\Models\Customer;
use App\Models\ProductSeriHistory;

class AZPointRepository {
    public function processForActivatingPSeri(ProductSeri $pseri)
    {
        $customer = $price = '';
        if ($pseri->order) {
            $customer = $pseri->order->customer;
            $price = $pseri->order->orderProducts()->where('product_id', $pseri->product_id)->first()->retail_price;
        }
        if ($pseri->activation_customer_id) {
            $customer = Customer::find($pseri->activation_customer_id);
            $price = $pseri->product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true);
        }
        if ($customer && $price) {
            $price = $pseri->order->orderProducts()->where('product_id', $pseri->product_id)->first()->retail_price;
            $point = ceil($price / config('app.azpoint_conversion'));
            $receiver = [];
            if ($customer->can_create_sub) {
                $subCustomers = ProductSeriHistory::where('product_seri_id', $pseri->id)
                    ->get()
                    ->pluck('customer_id', 'creator_id')
                    ->toArray();
                if (empty($subCustomers)) {
                    $subCustomers = $customer->getParrentIds();
                }
                $percent = 100;
                $customerID = $customer->id;
                while ($percent > 0 && $customerID) {
                    $percent -= 20;
                    $receiver[] = $customerID;
                    $customerID = @$subCustomers[$customerID];
                }
            } else {
                $receiver[] = $customer->id;
            }

            $receiver = array_slice($receiver, 0, 5);
            $left = 100;
            for ($i = 0; $i <= count($receiver) - 1; $i++) {
                $last = AZPoint::where('customer_id', $receiver[$i])->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $amount = $i == count($receiver) - 1 ? ceil($point * $left / 100) : ceil($point / 5);
                AZPoint::create([
                    'customer_id' => $receiver[$i],
                    'description' => 'Thưởng AZ Point cho sự kiện kích hoạt seri #' . $pseri->seri_number . ' thành công',
                    'balance' => ($last ? $last->balance : 0) + $amount,
                    'pseri_id' => $pseri->id,
                    'amount' => $amount,
                ]);
                $left -= 20;
            }
        }
    }

}
