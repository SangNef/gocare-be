<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Models\GroupProductDiscount;
use App\Models\Product;

class Discount
{
    private $discountColsByCurrency = [
        Bank::CURRENCY_VND => 'discount',
        Bank::CURRENCY_NDT => 'discount_ndt',
    ];

    public function setDiscountForProduct(Product $product, array $discount)
    {
        foreach ($discount as $discountCol => $groups) {
            foreach ($groups as $groupId => $value) {
                GroupProductDiscount::updateOrCreate([
                    'product_id' => $product->id,
                    'group_id' => $groupId
                ], [
                    $discountCol => $value,
                    'creator_id' => auth()->user()->id
                ]);
            }
        }
    }

    public function getGroupDiscountByCurrencyForProduct($productId, $currency = Bank::CURRENCY_VND)
    {
        $discountCol = $this->discountColsByCurrency[$currency];

        return GroupProductDiscount::where('product_id', $productId)
            ->pluck($discountCol, 'group_id');
    }

    public function getGroupDiscountPercentForProduct($productId, $groupId = null)
    {
        $query = GroupProductDiscount::where('product_id', $productId);
        return $groupId
            ? $query->where('group_id', $groupId)->first()
            : $query->pluck('discount_percent', 'group_id');
    }

    public function getDiscountForGroup($groupId, $productId = '', $currency = Bank::CURRENCY_VND)
    {
        $discountCol = $this->discountColsByCurrency[$currency];
        $results = [];
        GroupProductDiscount::where('group_id', $groupId)
            ->where(function ($query) use ($productId) {
                if ($productId) {
                    $query->where('product_id', $productId);
                }
            })
            ->get()
            ->each(function ($discount) use ($discountCol, &$results) {
                $results[$discount->product_id] = [
                    'price' => $discount->$discountCol,
                    'percent' => $discount->discount_percent
                ];
            });
        return $results;
    }

    public function getDiscountForCustomer($customerId, $productId, $applyGroupDiscountPercent = false)
    {
        $discount = null;
        $customer = Customer::find($customerId);
        if ($customer) {
            $currency = $customer->customer_currency;
            $groupDiscount = $this->getDiscountForGroup($customer->group_id, $productId, $currency);
            if (isset($groupDiscount[$productId])) {
                $discount = $groupDiscount[$productId]['price'];
                if ($applyGroupDiscountPercent) {
                    $discount *= ((100 - $groupDiscount[$productId]['percent']) / 100);
                }
            }

            if ($cDiscount = $this->getOnlyDiscountForCustomer($customer->id, $productId)) {
                $discount = $cDiscount->discount;
            }
        }
        return $discount;
    }

    public function getOnlyDiscountForCustomer($customerId, $productId)
    {
        return CustomerProductDiscount::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();
    }
}
