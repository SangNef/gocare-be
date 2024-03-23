<?php

namespace App\Services\CODPartners;

use App\Exceptions\CODException;
use App\Models\CODOrder;
use App\Models\Config;
use App\Models\Order;
use App\Models\WarrantyOrder;
use App\Services\CODPartners\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Auth;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;

class VTPService extends Shipping
{
    const NAME = CODOrder::PARTNER_VTP;

    protected $username;
    protected $password;
    protected $token;

    public function __construct($username = null, $password = null)
    {
        $this->loadByApiConnection([
            'username' => $username,
            'password' => $password
        ]);
    }

    protected function loadByApiConnection($apiConnection)
    {
        $this->username = @$apiConnection['username'];
        $this->password = @$apiConnection['password'];
        $this->token = '';
        if (Cache::has(md5('vtp_token_' . $this->username))) {
            $this->token = Cache::get(md5('vtp_token_' . $this->username));
        }

        return $this;
    }

    public function baseUri()
    {
        return 'https://partner.viettelpost.vn/v2/';
    }

    public function login()
    {
        $path = 'user/login';
        try {
            $response = $this->client()->post($path, [
                'json' => [
                    'USERNAME' => $this->username,
                    'PASSWORD' => $this->password
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            if ($response['status'] == 200) {
                return $response['data']['token'];
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $this->token;
    }

    public function ownerToken()
    {
        $path = 'user/ownerconnect';
        try {
            $loginToken = $this->login();
            $response = $this->client([
                'Token' => $loginToken
            ])->post($path, [
                'json' => [
                    'USERNAME' => $this->username,
                    'PASSWORD' => $this->password
                ]
            ])->getBody()->getContents();

            $response = json_decode($response, true);
            $this->token = $response['data']['token'];
            Cache::put(md5('vtp_token_' . $this->username), $this->token, 10);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $this->token;
    }

    public function getToken()
    {
        if (!$this->token) {
            return $this->ownerToken();
        }
        return $this->token;
    }

    private function ownerRequest()
    {
        return $this->client([
            'Token' => $this->getToken()
        ]);
    }

    public function getStores()
    {
        $path = 'user/listInventory';
        $results = [];
        try {
            $response = $this->ownerRequest()->get($path)->getBody()->getContents();
            $response = json_decode($response, true);

            if ($response['status'] == 200) {
                $results = $response['data'];
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $results;
    }

    public function getAddress($type = 'province', $id = 0)
    {
        $path = 'categories';
        switch ($type) {
            case 'district':
                $path .= '/listDistrict?provinceId=' . $id;
                $value = ['DISTRICT_ID', 'DISTRICT_NAME'];
                break;
            case 'ward':
                $path .= '/listWards?districtId=' . $id;
                $value = ['WARDS_ID', 'WARDS_NAME'];
                break;
            default:
                $path .= '/listProvinceById?provinceId=' . $id;
                $value = ['PROVINCE_ID', 'PROVINCE_NAME'];
        }
        $results = [];
        try {
            $response = $this->client()->get($path)->getBody()->getContents();
            $response = json_decode($response, true);
            foreach ($response['data'] as $data) {
                $results[$data[$value[0]]] = $data[$value[1]];
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $results;
    }

    public function getServices()
    {
        $path = 'categories/listService';
        $services = [];
        try {
            $response = $this->client()->post($path, [
                'json' => [
                    'TYPE' => 1
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            if ($response['status'] == 200 && $response['data']) {
                foreach ($response['data'] as $data) {
                    $services[$data['SERVICE_CODE']] = $data['SERVICE_NAME'];
                }
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return $services;
    }

    public function getServicePrice(array $params)
    {
        $path = 'order/getPrice';
        $results = [];
        try {
            $response = $this->ownerRequest()->post($path, [
                'json' => [
                    'PRODUCT_WEIGHT' => (int) $params['PRODUCT_WEIGHT'],
                    'PRODUCT_HEIGHT' => (int) $params['PRODUCT_HEIGHT'],
                    'PRODUCT_WIDTH' => (int) $params['PRODUCT_WIDTH'],
                    'PRODUCT_LENGTH' => (int) $params['PRODUCT_LENGTH'],
                    'PRODUCT_PRICE' => (int) $params['PRODUCT_PRICE'],
                    'MONEY_COLLECTION' => (int) $params['MONEY_COLLECTION'],
                    'ORDER_SERVICE' => $params['ORDER_SERVICE'],
                    'SENDER_PROVINCE' => $params['SENDER_PROVINCE'],
                    'SENDER_DISTRICT' => $params['SENDER_DISTRICT'],
                    'RECEIVER_PROVINCE' => $params['RECEIVER_PROVINCE'],
                    'RECEIVER_DISTRICT' => $params['RECEIVER_DISTRICT'],
                    'PRODUCT_TYPE' => $params['PRODUCT_TYPE'],
                    'NATIONAL_TYPE' => 1
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            if ($response['status'] == 200) {
                $results = $response['data'];
            }
        } catch (\Exception $exception) {
            \Log::info(['VTP_SERVICE_PRICE_PARAMS' => $params]);
            \Log::error($exception->getMessage());
        }
        return $results;
    }

    public function getStoreInfo($storeId)
    {
        $store = collect($this->getStores())->where('groupaddressId', (int) $storeId)->first();
        return $store;
    }

    public function renderBillOfLadding(Order $order)
    {
        $order = $order->fresh();
        $customer = $order->customer;
        $partnerProvinces = $this->getAddress();
        $stores = array_map(function ($store) {
            $result['group_id'] = $store['groupaddressId'];
            $result['name'] = $store['groupaddressId'] . ' - ' . $store['name'];
            $result['province_id'] = $store['provinceId'];
            $result['district_id'] = $store['districtId'];
            return $result;
        }, $this->getStores());
        $products = $this->prepareProductForBillOfLading($order)->map(function ($product) {
            $weight = ($product['length'] * $product['height'] * $product['width'] / 6000) * 1000;
            $totalWeight = $weight > $product['weight'] ? $weight : $product['weight'];

            $product['weight'] = intval($totalWeight) * $product['quantity'];
            return $product;
        });
        $address = $this->getCustomerAddress($customer, $order->address_id);
        $codAmount = $order->isFromFE() ? $products->sum('total') - $order->discount : $order->total;

        return View::make(
            'la.cod_orders.bill_ladings.vtp',
            compact('order', 'customer', 'codAmount', 'partnerProvinces', 'stores', 'products', 'address')
        )->render();
    }

    public function renderBillOfLaddingForWarrantyOrder(WarrantyOrder $order, $type)
    {
        $customer = $order->customer;
        $codAmount = 0;
        $partnerProvinces = $this->getAddress();
        $stores = array_map(function ($store) {
            $result['group_id'] = $store['groupaddressId'];
            $result['name'] = $store['groupaddressId'] . ' - ' . $store['name'];
            $result['province_id'] = $store['provinceId'];
            $result['district_id'] = $store['districtId'];
            return $result;
        }, $this->getStores());
        $products = $type === "all"
            ? $this->prepareWarrantyOrderAllProductsForBillLading($order)
            : $this->prepareWarrantyOrderProductForBillLading($order);
        $products = $products->map(function ($product) {
            $weight = ($product['length'] * $product['height'] * $product['width'] / 6000) * 1000;
            $totalWeight = $weight > $product['weight'] ? $weight : $product['weight'];

            $product['weight'] = intval($totalWeight) * $product['quantity'];
            return $product;
        });
        $address = $this->getCustomerAddress($customer, null);

        return View::make(
            'la.cod_orders.bill_ladings.vtp',
            compact('order', 'customer', 'codAmount', 'partnerProvinces', 'stores', 'products', 'address')
        )->render();
    }

    public function requestServicePrice(Request $request, $storeID = 0)
    {
        $services = $this->getServices();
        $results = [];
        $sender = [
            'province' => $request->has('inventory') && !$request->has('SENDER_PROVINCE')
                ? @$this->getStoreInfo($request->inventory)['provinceId']
                : $request->SENDER_PROVINCE,
            'district' => $request->has('inventory') && !$request->has('SENDER_DISTRICT')
                ? @$this->getStoreInfo($request->inventory)['districtId']
                : $request->SENDER_DISTRICT,
        ];
        foreach ($services as $code => $name) {
            $data = [
                'PRODUCT_WEIGHT' => (int) $request->PRODUCT_WEIGHT,
                'PRODUCT_WIDTH' => (int) $request->PRODUCT_WIDTH,
                'PRODUCT_HEIGHT' => (int) $request->PRODUCT_HEIGHT,
                'PRODUCT_LENGTH' => (int) $request->PRODUCT_LENGTH,
                'PRODUCT_PRICE' => (int) $request->PRODUCT_PRICE,
                'MONEY_COLLECTION' => (int) $request->MONEY_COLLECTION,
                'ORDER_SERVICE' => $code,
                'SENDER_PROVINCE' => strval($sender['province']),
                'SENDER_DISTRICT' => strval($sender['district']),
                'RECEIVER_PROVINCE' => $request->RECEIVER_PROVINCE,
                'RECEIVER_DISTRICT' => $request->RECEIVER_DISTRICT,
                'PRODUCT_TYPE' => $request->PRODUCT_TYPE
            ];
            $price = $this->getServicePrice($data);
            if (!empty($price)) {
                $amount = $price['MONEY_TOTAL'];
                $results[$code]['name'] = $name . ' - ' . number_format($amount) . ' đ';
                $results[$code]['price'] = $amount;
            }
        }
        return $results;
    }

    public function createBill($attributes)
    {
        $path = 'order/createOrder';
        try {
            $response = $this->ownerRequest()->post($path, [
                'json' => [
                    'ORDER_NUMBER' => $attributes['ORDER_NUMBER'],
                    'GROUPADDRESS_ID' => (int) $attributes['GROUPADDRESS_ID'],
                    'CUS_ID' => (int) $attributes['CUS_ID'],
                    'DELIVERY_DATE' => Carbon::createFromFormat('d/m/Y H:i:s', $attributes['DELIVERY_DATE'])->format('d/m/Y H:i:s'),
                    'SENDER_FULLNAME' => $attributes['SENDER_FULLNAME'],
                    'SENDER_ADDRESS' => $attributes['SENDER_ADDRESS'],
                    'SENDER_PHONE' => $attributes['SENDER_PHONE'],
                    'SENDER_WARD' => (int) $attributes['SENDER_WARD'],
                    'SENDER_DISTRICT' => (int) $attributes['SENDER_DISTRICT'],
                    'SENDER_PROVINCE' => (int) $attributes['SENDER_PROVINCE'],
                    'RECEIVER_FULLNAME' => $attributes['RECEIVER_FULLNAME'],
                    'RECEIVER_ADDRESS' => $attributes['RECEIVER_ADDRESS'],
                    'RECEIVER_PHONE' => $attributes['RECEIVER_PHONE'],
                    'RECEIVER_WARD' => (int) $attributes['RECEIVER_WARDS'],
                    'RECEIVER_DISTRICT' => (int) $attributes['RECEIVER_DISTRICT'],
                    'RECEIVER_PROVINCE' => (int) $attributes['RECEIVER_PROVINCE'],
                    'ORDER_PAYMENT' => (int) $attributes['ORDER_PAYMENT'],
                    'ORDER_SERVICE' => $attributes['ORDER_SERVICE'],
                    'ORDER_NOTE' => @$attributes['ORDER_NOTE'] ?? '',
                    'PRODUCT_WEIGHT' => (int) $attributes['PRODUCT_WEIGHT'],
                    'PRODUCT_HEIGHT' => (int) $attributes['PRODUCT_HEIGHT'],
                    'PRODUCT_WIDTH' => (int) $attributes['PRODUCT_WIDTH'],
                    'PRODUCT_LENGTH' => (int) $attributes['PRODUCT_LENGTH'],
                    'PRODUCT_TYPE' => $attributes['PRODUCT_TYPE'],
                    'PRODUCT_PRICE' => (int) $attributes['PRODUCT_PRICE'],
                    'PRODUCT_NAME' => $attributes['PRODUCT_NAME'],
                    'PRODUCT_QUANTITY' => (int) $attributes['PRODUCT_QUANTITY'],
                    'LIST_ITEM' => $attributes['LIST_ITEM'],
                    'MONEY_COLLECTION' => (int) $attributes['MONEY_COLLECTION']
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            if ($response['status'] == 200) {
                return [
                    'store_id' => $attributes['GROUPADDRESS_ID'],
                    'order_code' => $response['data']['ORDER_NUMBER'],
                    'partner' => 'vtp',
                    'quantity' => $attributes['PRODUCT_QUANTITY'],
                    'cod_amount' => $response['data']['MONEY_COLLECTION'],
                    'fee_amount' => $response['data']['MONEY_TOTAL'],
                    'package_price' => $attributes['PRODUCT_PRICE'],
                    'charge_fee' => in_array($attributes['ORDER_PAYMENT'], [2, 4]) ? 1 : 0
                ];
            }
            throw new CODException($response['message']);
        } catch (\Exception $exception) {
            \Log::info(['VTP_CREATE_BILL_PARAMS' => $attributes]);
            \Log::error($exception->getMessage());
            throw $exception;
        }
    }

    public function statusList()
    {
        return [
            "-100" => "Chưa duyệt",
            "100" => "Đã duyệt",
            "102" => "Đã duyệt",
            "103" => "Đã duyệt",
            "104" => "Đã duyệt",
            "-108" => "Đã duyệt",
            "-109" => "Đã gửi tại cửa hàng tiện lợi",
            "-110" => "Đã gửi tại cửa hàng tiện lợi",
            "107" => "Đã hủy",
            "105" => "Đã lấy hàng",
            "200" => "Đang vận chuyển",
            "201" => "Đã hủy",
            "202" => "Đang vận chuyển",
            "300" => "Đang vận chuyển",
            "320" => "Đang vận chuyển",
            "400" => "Đang vận chuyển",
            "500" => "Đang giao hàng",
            "506" => "Đang giao hàng",
            "570" => "Đang giao hàng",
            "508" => "Đang giao hàng",
            "509" => "Đang giao hàng",
            "550" => "Đang giao hàng",
            "505" => "Duyệt hoàn",
            "502" => "Duyệt hoàn",
            "515" => "Duyệt hoàn",
            "507" => "Giao hàng thất bại",
            "504" => "Hoàn thành công",
            "501" => "Giao hàng thành công",
            "503" => "Phát thành công tiêu hủy"
        ];
    }

    public function apiConnection()
    {
        return array_merge(parent::apiConnection(), [
            'username',
            'password',
            'vtpDefaultStoreId',
            'storeIdForSharing'
        ]);
    }

    protected function getDefaultInventoryName()
    {
        return 'vtpDefaultStoreId';
    }

    public function updateStatus($code, $statusType)
    {
        $path = 'order/UpdateOrder';
        $response = $this->ownerRequest()->post($path, [
            'json' => [
                'TYPE' => $statusType,
                'ORDER_NUMBER' => $code
            ]
        ])->getBody()->getContents();
        $response = json_decode($response, true);
        if ($response['status'] == 200) {
            return true;
        }
        throw new CODException($response['message']);
    }
}
