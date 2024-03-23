<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\Bank;
use App\Models\LockCommission;
use App\Models\Commission;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StoreProduct;
use App\Models\Revenue;

class RevenueStatisticRepository
{
	public function statistic(Store $store = null)
	{
        $stores = $store ? collect()->push($store) : Store::all();
        $stores->map(function (Store $tmpStore) {
            $bankAmount = (int) $this->getBanksAmount($tmpStore->id);
            $customerAmount = $this->getCustomerAmount($tmpStore);
            $productAmount = $this->getProductAmount($tmpStore);
            $last = Revenue::where('store_id', $tmpStore->id)
                ->orderBy('reported_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $total = $bankAmount + $customerAmount['total'] + $productAmount['total'];
            
            Revenue::create([
                'store_id' => $tmpStore->id,
                'bank_amount' => $bankAmount,
                'customer_amount' => $customerAmount['total'],
                'product_amount' => $productAmount['total'],
                'total' => $total,
                'reported_at' => \Carbon\Carbon::now(),
                'old' => $last ? $last->id : 0,
                'detail' => [
                    'customers' => $customerAmount,
                    'products' => $productAmount,
                ]
            ]);
        });
	}

    protected function getBanksAmount($storeId)
    {
        return Bank::where('store_id', $storeId)
            ->get()
            ->sum('last_balance');
    }

    protected function getCustomerAmount(Store $store)
    {
        $result = [
            'total' => 0,
            'customer_debt' => 0,
            'ctv_balance' => 0,
            'ctv_lock_balance' => 0,
        ];
        $ctvs = $store->setting['commission_groups'];
        $excludedGroups = [6,7,8,9,10,11,12,13,14,15];
        $excludedIds = [6387];
        $result['customer_debt'] = $result['total'] = (int) Customer::whereNotIn('group_id', $ctvs)
            ->whereNotIn('id', $excludedIds)
            ->whereNotIn('group_id', $excludedGroups)
            ->sum('debt_total');
        if (!empty($ctvs)) {
            $customers = Customer::whereIn('group_id', $ctvs)
                ->get()
                ->map(function(Customer $customer) {

                    $ctvAmountL = LockCommission::where('customer_id', $customer->id)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    $ctvAmount = Commission::where('customer_id', $customer->id)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    $customer->ctv_balance = $ctvAmount ? $ctvAmount->balance : 0;
                    $customer->ctv_lock_balance = $ctvAmountL ? $ctvAmountL->balance : 0;

                    return $customer;
                });
            
            $result['ctv_balance'] = $customers->sum('ctv_balance');
            $result['ctv_lock_balance'] = $customers->sum('ctv_lock_balance');
            $result['total'] -= ($customers->sum('ctv_balance') + $customers->sum('ctv_lock_balance'));
        }

        return $result;
    }

    protected function getProductAmount(Store $store)
    {
        $categories = [];
        $excludeProductIds = array_unique(\DB::table('products_product_category')
            ->whereIn('product_category_id', [3, 28])
            ->pluck('product_id'));
        $products = Product::whereNotIn('id', $excludeProductIds)
        ->get()
        ->map(function (Product $product) use($store, &$categories) {
            $price = $product->getPriceForCustomerGroup('Nhà Cung Cấp Điện Tử');
            $quantity = StoreProductGroupAttributeExtra::where('product_id', $product->id)
                ->where('store_id', $store->id)
                ->get();
            if (!$quantity->count()) {
                $quantity = StoreProduct::where('product_id', $product->id)
                ->where('store_id', $store->id)
                ->get();
            }

            $product->amount = $quantity->sum('n_quantity') * $price;
            if ($product->categories->count() > 0) {
                foreach ($product->categories as $category) {
                    if (!@$categories[$category->id]) {
                        $categories[$category->id] = 0;
                    }
                    $categories[$category->id] += $product->amount;
                }
            }
            return $product;
            
        })->sum('amount');

        return  [
            'total' => $products,
            'categories' => $categories
        ];
    }
}