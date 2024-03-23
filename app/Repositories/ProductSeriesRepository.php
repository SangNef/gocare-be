<?php

namespace App\Repositories;

use App\Events\WarrantyActivated;
use App\Exceptions\StoreProductException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSeri;
use App\Models\Province;
use App\Models\StoreProductGroupAttributeExtra;
use App\Services\Generator;
use Carbon\Carbon;

class ProductSeriesRepository
{
    protected $generatorSv;

    public function __construct(Generator $generatorSv)
    {
        $this->generatorSv = $generatorSv;
    }

    public function generateSeries($productId, $quantity)
    {
        $results = [];
        $prefix = \Carbon\Carbon::now()->format('mY') . $productId;
        for ($i = 0; $i < $quantity; ++$i) {
            $results[] = $this->generatorSv->generateProductSeries($prefix);
        }

        $existSeries = ProductSeri::where('product_id', $productId)
            ->whereIn('seri_number', $results)
            ->pluck('seri_number');
        if ($existSeries->count() > 0) {
            foreach ($existSeries as $seri) {
                $results = array_filter($results, function ($item) use ($seri) {
                    return $item !== $seri;
                });
            }
        }
        $results = array_unique($results);
        while (count($results) < $quantity) {
            $replacementSeries = $this->generateSeries($productId, $quantity - count($results));
            $results = array_unique(array_merge($results, $replacementSeries));
        }

        return $results;
    }

    public function createSeries($productId, $quantity, $options = [])
    {
        $results = [];
        $generatedSeries = $this->generateSeries($productId, $quantity);

        foreach ($generatedSeries as $seri) {
            $data = [
                'seri_number' => $seri,
                'product_id' => $productId
            ];
            $data = array_merge($data, $options);
            $newSeri = ProductSeri::create($data);
            $results[] = $newSeri->id;
        }

        return $results;
    }

    public function requestedSeries($products = [])
    {
        $products = array_filter($products, function ($product) {
            return @$product['series'] && $product['has_series'] == 1;
        });
        return !empty($products) ? array_merge(...array_column($products, 'series')) : [];
    }

    public function orderCreateNewSeries(Order $order, $products = [], $isUpdate = false)
    {
        if ($order->isImport()) {
            foreach ($products as $id => $product) {
                $op = $order->orderProducts->where('product_id', $id)->first();
                $quantity = $order->sub_type == 1 ? $product['n_quantity'] : $product['w_quantity'];
                if ($isUpdate) {
                    $quantity = $order->sub_type == Order::SUB_TYPE_NEW
                        ? $product['n_quantity'] - $op->quantity
                        : $product['w_quantity'] - $op->w_quantity;
                }
                if ($quantity > 0) {
                    $this->createSeries($id, $quantity, ['order_id' => $order->id]);
                }
                if ($quantity < 0) {
                    ProductSeri::where('product_id', $id)
                        ->where('order_id', $order->id)
                        ->latest('seri_number')
                        ->take(abs($quantity))
                        ->delete();
                }
            }
        }
    }

    public function orderUseAvailableSeries(Order $order, $products = [])
    {
        $selectedSeries = $this->requestedSeries($products);
        $productSeries = ProductSeri::whereIn('product_id', collect($products)->pluck('product_id'))->whereIn('id', $selectedSeries);
        
        $productSeries->update([
            'ordered_at' => $order->created_at
        ]);
    
        if ($order->customer->ownedStore && !$order->customer->store_id) {
            if ($order->isExport()) {
                $productSeries->update([
                    'store_id' => $order->customer->ownedStore->id
                ]);
            } else {
                $productSeries->update([
                    'store_id' => auth()->user()->store_id
                ]);
            }
        } else {
            if ($order->isExport()) {
                if ($productSeries->count() > 0 && in_array($order->status, [1, 2])) {
                    return $productSeries->update([
                        'qr_code_status' => 1,
                        'stock_status' => $order->status,
                        'order_id' => $order->id,
                        'pasted_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                }
            } else {
                return $productSeries->update([
                    'order_id' => $order->id,
                    'qr_code_status' => 1,
                    'stock_status' => ProductSeri::STOCK_NOT_SOLD,
                    'pasted_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function processForNewOrder(Order $order, $products = [])
    {
        $order = $order->fresh();
        switch ($order->order_series_type) {
            case 1:
                $selectedSeries = $this->requestedSeries($products);
                if (empty($selectedSeries)) {
                    foreach ($products as $index => $product) {
                        if ($product['has_series'] == 1) {
                            $pSelectedSeri = ProductSeri::where('product_id', $product['product_id'])
                                ->whereNull("order_id")
                                ->where('status', 0)
                                ->where('stock_status', 0)
                                ->limit($product['n_quantity']);
                            if (@$product['attr_ids']) {
                                $groupAtrtibuteId = StoreProductGroupAttributeExtra::where('product_id', $product['product_id'])
                                    ->where('attribute_value_ids', implode(',', $product['attr_ids']))
                                    ->first();
                                $pSelectedSeri->where('group_attribute_id', $groupAtrtibuteId->id);
                            }
                            $isDevice = Product::find($product['product_id'])->categories->first()->is_devices;
                            if (!$isDevice) {
                                $pSelectedSeri->where('qr_code_status', 0);
                            }
                            $pSelectedSeri = $pSelectedSeri
                                ->pluck('id')
                                ->toArray();

                            if (count($pSelectedSeri) != $product['n_quantity']) {
                                throw new StoreProductException('Sản phẩm ' . $product['name'] . ' số lượng trong kho không đủ');
                            }
                            $products[$index]['series'] = $pSelectedSeri;
                        }
                    }
                }
                return $this->orderUseAvailableSeries($order, $products);
                break;
            case 2:
                return $this->orderCreateNewSeries($order, $products);
                break;
            default:
                return;
        }
    }

    public function processForUpdateOrder(Order $order, $products = [])
    {
        $order = $order->fresh();
        $this->processForDeleteOrder($order, $this->requestedSeries($products));
        switch ($order->order_series_type) {
            case 1:
            case 3:
                return $this->orderUseAvailableSeries($order, $products);
                break;
            default:
                return $this->orderCreateNewSeries($order, $products, true);
        }
    }

    public function processForDeleteOrder(Order $order, $excludeSeries = [])
    {
        $opSeries = ProductSeri::where('order_id', $order->id)
            ->whereNotIn('id', $excludeSeries);
        if ($order->isFromFE()) {
            $opSeries->update([
                'ordered_at' => NULL
            ]);
        }
        if (($order->isExport()) || ($order->isImport() && $order->sub_type == 2)) {
            $opSeries->update([
                'order_id' => NULL,
                'stock_status' => ProductSeri::STOCK_NOT_SOLD,
                'pasted_at' => NULL,
                'qr_code_status' => 0
            ]);
            
        } else {
            $opSeries->delete();
        }
    }

    public function getBySeri($seri)
    {
        return ProductSeri::where('seri_number', $seri)->first();
    }

    public function activeWarrantyBySeri($seri, $customerInfo)
    {
        $pSeri = $this->getBySeri($seri);
        if ($pSeri && !$pSeri->activated_at && $this->validActivationCode($pSeri, @$customerInfo['activation_code'])) {
            $pSeri->activated_at = Carbon::now();
            $pSeri->expired_at = Carbon::now()->addMonths($pSeri->product->warranty_period);
            $pSeri->name = $customerInfo['name'];
            $pSeri->phone = $customerInfo['phone'];
            $pSeri->email = $customerInfo['email'];
            $pSeri->province = $customerInfo['province'];
            $pSeri->district = $customerInfo['district'];
            $pSeri->ward = $customerInfo['ward'];
            $pSeri->address = $customerInfo['address'];
            $pSeri->phone_info = implode(',', @$customerInfo['phone_info[provider]'] ? [@$customerInfo['phone_info[provider]'],@$customerInfo['phone_info[phone_type]']] : []);
            if ($customerInfo['customer_code']) {
                $customer = Customer::where('code', $customerInfo['customer_code'])->first();
                $pSeri->activation_customer_id = $customer->id;
            }
            $pSeri->save();

            event(new WarrantyActivated($pSeri));

            return $pSeri;
        }

        return false;
    }

    protected function validActivationCode(ProductSeri $pSeri, $code)
    {
        return !$this->isRequiredActivationCode($pSeri) || $pSeri->activation_code == $code;
    }

    protected function isRequiredActivationCode(ProductSeri $pSeri)
    {
        return $pSeri->order && $pSeri->order->customer->can_create_sub;
    }

    public function getCustomerProvince($id)
    {
        $province = Province::find($id);
        return $province ? $province->name : '';
    }

    public function getCustomerPhone($phone)
    {
        return str_pad(substr($phone, 0, -3), strlen($phone), 'x', STR_PAD_RIGHT);
    }
}
