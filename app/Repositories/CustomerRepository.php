<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\CustomerRevenue;
use App\Models\GroupCateDiscount;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSeri;
use App\Models\ProductsProductCategory;
use App\User;
use App\Models\TransferOrder;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerRepository
{
    public function create($attributes = [])
    {
        $attributes = array_filter($attributes);
        $data = array_replace($attributes, [
            'phone' => isset($attributes['phone']) ? $attributes['phone'] : '',
            'address' => @$attributes['address'],
            'parent_id' => User::whereHas('roles', function ($q) {
                $q->where('name', 'SUPER_ADMIN');
            })->first()->id,
            'debt_in_advance' => 0,
            'debt_total' => 0,
            'note' => isset($attributes['note']) ? $attributes['note'] : '',
            'province' => @$attributes['province'],
            'district' => @$attributes['district'],
            'ward' => @$attributes['ward'],
            'customer_currency' => 1
        ]);
        $customer = Customer::create($data);
        return $customer;
    }

    public function getSubCustomers($parentID, $query)
    {
        $customers = Customer::where('customer_parent_id', $parentID)
            ->orderBy('created_at', 'desc');

        if (@$query['search']) {
            $customers->where(function ($q) {
                $q->where('phone', 'like', '%' . $query['search'] . '%')
                    ->orWhere('name', 'like', '%' . $query['search'] . '%')
                    ->orWhere('address', 'like', '%' . $query['search'] . '%');
            });
        }
        if (@$query['phone']) {
            $customers->where('phone', 'like', '%' . $query['phone'] . '%');
        }
        if (@$query['name']) {
            $customers->where('name', 'like', '%' . $query['name'] . '%');
        }
        if (@$query['address']) {
            $customers->where('address', 'like', '%' . $query['address'] . '%');
        }


        return $customers;
    }

    public function analysisForCustomer($customer, $from, $to)
    {
        if ($customer->customer_parent_id) {
            $orders = TransferOrder::where('customer_id', $customer->id)
                ->whereBetween('created_at', [
                    $from,
                    $to
                ]);
        } else {
            $orders = Order::where('customer_id', $customer->id)
				->whereIn('status', [1,2])
                ->where('payment_method', '<>', 'online')
				->whereBetween('created_at', [
					$from,
					$to
				]);
        }

        $affiliateOrders = Order::where('customer_id', $customer->id)
            ->whereIn('status', [1,2])
            ->where('payment_method', 'online')
            ->whereBetween('created_at', [
                $from,
                $to
            ]);


        return [
            'number_of_orders' => $orders->count(),
            'total_amount' => (int) $orders->sum('amount'),
//            'number_of_affiliate_orders'
        ];
    }

    public function report($customerId, $month = 0, $year = 0)
    {
        $result = [];
        $customer = Customer::find($customerId);
        if ($customer && !$customer->customer_parent_id) {
            if (!$year) {
                $year = Carbon::now()->format('Y');
            }
            if (!$month) {
                $month = Carbon::now()->format('m');
            }
            $month = Carbon::createFromFormat('Ym', $year . $month)->format('m-Y');
            $customerRevenue = new CustomerRevenue();
            $customerRevenue->month = $month;
            $customerRevenue->customer_id = $customerId;

            $orders = $customerRevenue->getOrders()
                ->get();
            $result['orders'] = [
                'total' => $orders->sum('total'),
                'amount' => $orders->count()
            ];
            $onlineOrders = $customerRevenue->getOnlineOrders()
                ->get();
            $result['online_orders'] = [
                'total' => $onlineOrders->sum('total'),
                'amount' => $onlineOrders->count()
            ];
            $affiliateOrders = $customerRevenue->getAffiliateOrders()
                ->get();
            $result['affiliate_orders'] = [
                'total' => $affiliateOrders->sum('total'),
                'amount' => $affiliateOrders->count()
            ];
            $activations = $customerRevenue->getActivations()
                ->get();
            $result['activation'] = [
                'total' => $activations->sum('retail_price'),
                'amount' => $activations->count()
            ];
            $affilicateActivations = $customerRevenue->getAffiliateActivations()
                ->get();
            $result['affiliate_activation'] = [
                'total' => $affilicateActivations->sum('retail_price'),
                'amount' => $affilicateActivations->count()
            ];
            $devicesGroup = ProductCategory::select(['id', 'name'])
                ->where('is_devices', 1)
                ->get()
                ->keyBy('id')
                ->toArray();
            $nonDevicesGroup = ProductCategory::select(['id', 'name'])
                ->where('is_devices', 0)
                ->get()
                ->keyBy('id')
                ->toArray();
            $productCategory = ProductsProductCategory::groupBy('product_id')
                ->pluck('product_category_id', 'product_id');
            foreach ($orders->merge($onlineOrders)->merge($affiliateOrders) as $order) {
                $orderProducts = $order->orderProducts;
                foreach ($orderProducts as $orderProduct) {
                    $productId = $orderProduct->product_id;
                    $firstCaregoryId = @$productCategory[$productId];
                    if ($firstCaregoryId && isset($devicesGroup[$firstCaregoryId])) {
                        $devicesGroup[$firstCaregoryId]['amount'] = (int) @$devicesGroup[$firstCaregoryId]['amount'] + $orderProduct->total;
                        $devicesGroup[$firstCaregoryId]['quantity'] = (int) @$devicesGroup[$firstCaregoryId]['quantity'] + $orderProduct->quantity;
                    }
                }
            }
            foreach ($activations->merge($affilicateActivations) as $activation) {
                $firstCaregoryId = @$productCategory[$activation->product_id];
                if ($firstCaregoryId && isset($nonDevicesGroup[$firstCaregoryId])) {
                    $nonDevicesGroup[$firstCaregoryId]['amount'] = (int) @$nonDevicesGroup[$firstCaregoryId]['amount'] + (int) $activation->retail_price;
                    $nonDevicesGroup[$firstCaregoryId]['quantity'] = (int) @$nonDevicesGroup[$firstCaregoryId]['quantity'] + 1;
                }
            }

            $numberOfProductByCate = array_merge($devicesGroup, $nonDevicesGroup);
            foreach ($numberOfProductByCate as $key => $cate)
            {
                $cate['total_discount'] = 0;
                $cate['quantity'] = @$cate['quantity'] ?: 0;
                $cate['amount'] = @$cate['amount'] ?: 0;
                $discounts = GroupCateDiscount::where('group_id', $customer->group_id)
                    ->where('cate_id', $cate['id'])
                    ->orderBy('quantity', 'asc')
                    ->where('type', GroupCateDiscount::TYPE_WITHOUT_PRODUCT)
                    ->get();
                foreach ($discounts as $discount) {
                    if ((int) @$cate['quantity'] >= $discount->quantity) {
                        $cate['discount_text'] = implode('+', array_filter([
                            number_format($discount->discount),
                            $discount->discount_1 > 0 ? (int) $discount->discount_1 . '%' : ''
                        ]));
                        $cate['total_discount'] = @$cate['amount'] * (int) $discount->discount_1 / 100 + (int) $discount->discount;
                    }
                }
                $numberOfProductByCate[$key] = $cate;
            }

            $result['discount_by_cate'] = $numberOfProductByCate;

            return $result;
        }

        return null;
    }
}
