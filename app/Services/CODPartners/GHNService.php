<?php

namespace App\Services\CODPartners;

use App\Exceptions\CODException;
use App\Models\Address;
use App\Models\CODOrder;
use App\Models\District;
use App\Models\Ward;
use GuzzleHttp\Exception\ClientException;
use App\Services\CODPartners\Shipping;
use App\Models\Config;
use App\Models\Order;
use App\Models\WarrantyOrder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class GHNService extends Shipping
{
    protected $token;
//    protected $storeIdForLightWeightProduct;
//    protected $storeIdForHeavyWeightProduct;
    const NAME = CODOrder::PARTNER_GHN;

    public function __construct($token = null)
    {
        $this->loadByApiConnection([
            'token' => $token
        ]);
    }

    protected function loadByApiConnection($apiConnection)
    {
        $this->token = @$apiConnection['token'];
//        $this->storeIdForLightWeightProduct = @$apiConnection['storeIdForLightWeight'];
//        $this->storeIdForHeavyWeightProduct = @$apiConnection['storeIdForHeavyWeight'];

        return $this;
    }

    public function baseUri()
    {
        return 'https://online-gateway.ghn.vn/shiip/public-api/';
    }

    protected function getDefaultInventoryName()
    {
        return 'ghnDefaultStoreId';
    }

    protected function getClient($headers = [])
    {
        $headers = array_merge([
            'Token' => $this->token
        ], $headers);
        return $this->client($headers);
    }

    public function getStores()
    {
        $path = 'v2/shop/all';
        try {
            $response = $this->getClient()->get($path)->getBody()->getContents();
            $response = json_decode($response, true);
            return $response['data']['shops'];
        } catch (ClientException $exception) {
            \Log::error($exception->getMessage());
            $response = $exception->getResponse()->getBody()->getContents();
            $response = json_decode($response, true);
            throw new CODException("(GHN) " . $response['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getAddress($type = 'province', $id = 0)
    {
        $path = 'master-data';
        switch ($type) {
            case 'district':
                $path .= '/district?province_id=' . $id;
                $value = ['DistrictID', 'DistrictName'];
                break;
            case 'ward':
                $path .= '/ward?district_id=' . $id;
                $value = ['WardCode', 'WardName'];
                break;
            default:
                $path .= '/province';
                $value = ['ProvinceID', 'ProvinceName'];
        }
        $results = [];
        try {
            $response = $this->getClient()->get($path)->getBody()->getContents();
            $response = json_decode($response, true);
            foreach ($response['data'] as $data) {
                $results[$data[$value[0]]] = $data[$value[1]];
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $results;
    }

    public function getServices($params = [])
    {
        $path = 'v2/shipping-order/available-services?' . http_build_query($params);
        $results = [];
        try {
            $response = $this->getClient()->get($path)->getBody()->getContents();
            $response = json_decode($response, true);
            foreach ($response['data'] as $service) {
                $results[$service['service_id']] = $service['short_name'];
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $results;
    }

    public function getServicePrice($shopId, $params = [])
    {
        $path = 'v2/shipping-order/fee?' . http_build_query($params);
        $price = 0;
        try {
            $response = $this->getClient([
                'ShopId' => $shopId
            ])->get($path)->getBody()->getContents();
            $response = json_decode($response, true);
            $price = $response['data']['total'];
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $price;
    }

    public function renderBillOfLadding(Order $order)
    {
        $order = $order->fresh();
        $customer = $order->customer;
        $products = $this->prepareProductForBillOfLading($order)->map(function ($product) {
            $itemWeight = ($product['length'] * $product['width'] * $product['height'] / 5000) * 1000;
            $itemWeight = $itemWeight > $product['weight'] ? intval($itemWeight) : $product['weight'];
            $product['weight'] = $itemWeight * $product['quantity'];

            return $product;
        });
        $weight = $products->sum('weight');
        $partnerProvinces = $this->getAddress();
        $stores = array_map(function ($store) use($weight) {
            $result['id'] = $store['_id'];
            $result['name'] = $store['_id'] . ' - ' . $store['name'];
            $result['district_id'] = $store['district_id'];
//            if ($weight > 5000 && $store['_id'] == $this->getStoreIdForHeavyWeightProducts()) {
//                $result['selected'] = true;
//            } else if ($weight < 5000  && $store['_id'] == $this->getStoreIdForLightWeightProducts()) {
//                $result['selected'] = true;
//            }
            return $result;
        }, $this->getStores());
        $address = $this->getCustomerAddress($customer, $order->address_id);
        $codAmount = $order->isFromFE() ? $products->sum('total') - $order->discount : $order->total;
        $partner = static::NAME;
        $view  = $partner == 'ghn' ? 'la.cod_orders.bill_ladings.ghn' : 'la.cod_orders.bill_ladings.ghn5';
        return View::make(
            $view,
            compact('order', 'customer', 'codAmount', 'products', 'partnerProvinces', 'stores', 'address', 'partner')
        )->render();
    }

    public function getTotalWeightForOrder(Order $order)
    {
        $total = 0;
        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            $dimension = $orderProduct->dimension ?: [
                'weight' => $product->weight,
                'width' => $product->width,
                'height' => $product->height,
                'length' => $product->length,
            ];
            $itemWeight = ($dimension['length'] * $dimension['width'] * $dimension['height'] / 5000) * 1000;
            $itemWeight = $itemWeight > $dimension['weight'] ? intval($itemWeight) : $dimension['weight'];
            $total += $itemWeight * $orderProduct->quantity;
        }

        return $total;
    }

    public function getTotalWeightForWOrder(WarrantyOrder $order)
    {
        $total = 0;
        foreach ($order->warrantyOrderProducts as $orderProduct) {
            $product = $orderProduct->product;
            $dimension = $orderProduct->dimension ?: [
                'weight' => $product->weight,
                'width' => $product->width,
                'height' => $product->height,
                'length' => $product->length,
            ];
            $itemWeight = ($dimension['length'] * $dimension['width'] * $dimension['height'] / 5000) * 1000;
            $itemWeight = $itemWeight > $dimension['weight'] ? intval($itemWeight) : $dimension['weight'];
            $total += $itemWeight * $orderProduct->quantity;
        }

        return $total;
    }

    public function renderBillOfLaddingForWarrantyOrder(WarrantyOrder $order, $type)
    {
        $customer = $order->customer;
        $codAmount = 0;
        $partnerProvinces = $this->getAddress();
        $stores = array_map(function ($store) {
            $result['id'] = $store['_id'];
            $result['name'] = $store['_id'] . ' - ' . $store['name'];
            $result['district_id'] = $store['district_id'];
            return $result;
        }, $this->getStores());
        $products = $type === "all"
            ? $this->prepareWarrantyOrderAllProductsForBillLading($order)
            : $this->prepareWarrantyOrderProductForBillLading($order);
        $products = $products->map(function ($product) {
            $itemWeight = ($product['length'] * $product['width'] * $product['height'] / 5000) * 1000;
            $itemWeight = $itemWeight > $product['weight'] ? intval($itemWeight) : $product['weight'];
            $product['weight'] = $itemWeight * $product['quantity'];

            return $product;
        });
        $address = $this->getCustomerAddress($customer, null);
        $partner = static::NAME;
        $view  = $partner == 'ghn' ? 'la.cod_orders.bill_ladings.ghn' : 'la.cod_orders.bill_ladings.ghn5';
        return View::make(
            $view,
            compact('order', 'customer', 'codAmount', 'products', 'partnerProvinces', 'stores', 'address', 'partner')
        )->render();
    }

    public function createBill($attributes)
    {
        $path = 'v2/shipping-order/create';
        try {
            $response = $this->getClient([
                'ShopId' => $attributes['inventory']
            ])->post($path, [
                'json' => [
                    'payment_type_id' => (int) @$attributes['payment_type_id'],
                    'note' => @$attributes['note'] ?? '',
                    'required_note' => $attributes['required_note'],
                    'to_name' => $attributes['to_name'],
                    'to_phone' => $attributes['to_phone'],
                    'to_address' => $attributes['to_address'],
                    'to_ward_code' => (string) $attributes['to_ward_code'],
                    'to_district_id' => (int) $attributes['to_district_id'],
                    'cod_amount' => (int) $attributes['cod_amount'],
                    'content' => $attributes['content'],
                    'weight' => (int) $attributes['weight'],
                    'length' => (int) $attributes['length'],
                    'width' => (int) $attributes['width'],
                    'height' => (int) $attributes['height'],
                    'insurance_value' => (int) $attributes['insurance_value'],
                    'coupon' => isset($attributes['coupon']) ? $attributes['coupon'] : '',
                    'service_id' => (int) $attributes['service_id'],
                    'items' => array_map(function ($item) {
                        return [
                            'name' => $item['name'],
                            'code' => $item['code'],
                            'quantity' => intval($item['quantity']),
                            'weight' => intval($item['weight'])
                        ];
                    }, $attributes['items'])
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            return [
                'store_id' => $attributes['inventory'],
                'order_code' => $response['data']['order_code'],
                'partner' => static::NAME,
                'quantity' => 1,
                'cod_amount' => $attributes['cod_amount'],
                'fee_amount' => $response['data']['total_fee'],
                'package_price' => $attributes['insurance_value'],
                'charge_fee' => @$attributes['payment_type_id'] == 2 ? 1 : 0
            ];
        } catch (ClientException $exception) {
            \Log::error($exception->getMessage());
            $response = $exception->getResponse()->getBody()->getContents();
            $response = json_decode($response, true);
            throw new CODException($response['code_message_value']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getStoreById($id)
    {
        $stores = collect($this->getStores());
        return $stores->where('_id', (int) $id)->first();
    }

    public function apiConnection()
    {
        return array_merge(parent::apiConnection(), [
            'token',
            'storeIdForSharing'
        ]);
    }

    public function updateStatus($code, $status, $shopId)
    {
        $path = 'v2/switch-status/' . $status . '?order_codes=' . $code;
        $response = $this->getClient([
            'ShopId' => $shopId
        ])->get($path)->getBody()->getContents();
        $response = json_decode($response, true);

        if ($response['code'] == 200) {
            if ($response['data'][0]['result']) {
                return true;
            }
            $message = $response['data'][0]['message'];
        } else {
            $message = 'Lỗi gọi API';
        }
        throw new CODException($message);
    }

//    public function getStoreIdForLightWeightProducts()
//    {
//        return $this->storeIdForLightWeightProduct;
//    }
//
//    public function getStoreIdForHeavyWeightProducts()
//    {
//        return $this->storeIdForHeavyWeightProduct;
//    }
}
