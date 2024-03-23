<?php

namespace App\Services\CODPartners;

use App\Exceptions\CODException;
use App\Models\Address;
use GuzzleHttp\Exception\ClientException;
use App\Models\Config;
use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use App\Models\CODOrder;
use App\Models\Order;
use App\Models\WarrantyOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Helper\StringHelper;
use GuzzleHttp\Client;

class GHTKService extends Shipping
{
    protected $token;
    const NAME = CODOrder::PARTNER_GHTK;

    public function __construct($token = null)
    {
        $this->loadByApiConnection([
            'token' => $token
        ]);
    }

    public function baseUri()
    {
        return "https://services.giaohangtietkiem.vn/services/shipment/";
    }

    public function apiConnection()
    {
        return array_merge(parent::apiConnection(), [
            'token',
            'storeIdForSharing',
        ]);
    }

    protected function getDefaultInventoryName()
    {
        return 'ghtkDefaultStoreId';
    }

    protected function loadByApiConnection($apiConnection)
    {
        $this->token = @$apiConnection['token'];

        return $this;
    }

    public function getAddress($provinceId = 0, $districtId = 0, $wardId = 0)
    {
        $results = [
            'province' => '',
            'district' => '',
            'ward' => ''
        ];
        if ($provinceId && $province = Province::find($provinceId)) {
            $results['province'] = $province->name;
        }
        if ($districtId && $district = District::find($districtId)) {
            $results['district'] = $district->name;
        }
        if ($wardId && $ward = Ward::find($wardId)) {
            $results['ward'] = $ward->name;
        }
        return $results;
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
        $path = "list_pick_add";
        try {
            $response = $this->getClient()->get($path)->getBody()->getContents();
            $response = json_decode($response, true);
            return $response['data'];
        } catch (ClientException $exception) {
            \Log::error($exception->getMessage());
            $response = $exception->getResponse()->getBody()->getContents();
            $response = json_decode($response, true);
            throw new CODException("(GHTK) " . $response['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getDefaultStore($storeId = null)
    {
        $stores = collect($this->getStores());
        if ($storeId) {
            $stores = $stores->where('pick_address_id', $storeId);
        }
        $stores = $stores->first();
        return $stores;
    }

    public function getServicePrice($params = [])
    {
        $weight = $params['weight'];
        $isUseBBs = $weight > 20000;
        $pickUpAddress = $this->getPickAddress($params['pick_address_id']);
        $data = [
            'pick_address_id' => $pickUpAddress['pick_address_id'],
            'pick_province' => $pickUpAddress['pick_province'],
            'pick_district' => $pickUpAddress['pick_district'],
        ];

        if ($isUseBBs) {
            $data['customer_province'] = $params['province'];
            $data['customer_district'] = $params['district'];
            // $data['customer_address'] = $params['address'];
            $data['products'] = array_map(function ($item) {
                $item['weight'] = (float) $item['weight'];
                $item['height'] = (int) $item['height'];
                $item['width'] = (int) $item['width'];
                $item['length'] = (int) $item['length'];
                $item['quantity'] = (int) $item['quantity'];
                return $item;
            }, $params['products']);
            if (@$params['tags']) {
                $data['tags'] = $params['tags'];
            }
        } else {
            $data += collect($params)->only(['value', 'province', 'district', 'weight', 'address', 'transport', 'tags'])->toArray();
            $data['deliver_option'] = 'none';
        }

        try {
            $response = $isUseBBs
                ? $this->getClient()->post('3pl/fee', [
                    'json' => $data
                ])
                : $this->getClient()->get('fee?' . http_build_query($data));
            $response = json_decode($response->getBody()->getContents(), true);

            if (!$response['success']) {
                throw new CODException($response['message']);
            }
            if (!$isUseBBs && !$response['fee']['delivery']) {
                throw new CODException('GTHK chưa hỗ trợ giao đến khu vực này');
            }

            return $isUseBBs
                ? [
                    'fee' => $response['data']['total_value'],
                    'name' => $response['data']['region']
                ]
                : $response['fee'];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function createBill($attributes)
    {
        $attributes['items'] = @$attributes['items'] ?: @$attributes['products'];
        $path = "order?ver=1.5";
        try {
            $order = [
                'id' => $attributes['id'],
                'pick_address_id' => $attributes['pick_address_id'],
                'pick_name' => $attributes['pick_name'],
                'pick_tel' => $attributes['pick_tel'],
                'pick_address' => $attributes['pick_address'],
                'pick_province' => $attributes['pick_province'],
                'pick_district' => $attributes['pick_district'],
                'tel' => $attributes['phone'],
                'name' => $attributes['name'],
                'address' => $attributes['address'],
                'province' => $attributes['province'],
                'district' => $attributes['district'],
                'ward' => $attributes['ward'],
                'hamlet' => 'Khác',
                'is_freeship' => (int) $attributes['count_fee'],
                'transport' => $attributes['transport'],
                'pick_money' => (int) $attributes['cod_amount'],
                'note' => @$attributes['note'] ?? '',
                'value' => (int) $attributes['total'],
                '3pl' => array_sum(array_column($attributes['items'], 'weight')) > 20 ? 1 : 0,
                'hamlet' => $this->getAddress4($attributes['province'], $attributes['district'], $attributes['ward'], $attributes['address']),
            ];
            if (@$attributes['tags']) {
                $order['tags'] = $attributes['tags'];
            }
            $response = $this->getClient()->post($path, [
                'json' => [
                    'order' => $order,
                    'products' => array_map(function ($item) {
                        return [
                            'name' => $item['name'],
                            'weight' => (float) $item['weight'],
                            'height' => (int) $item['height'],
                            'width' => (int) $item['width'],
                            'length' => (int) $item['length'],
                            'quantity' => (int) $item['quantity'],
                        ];
                    }, @$attributes['items'])
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            if ($response['success']) {
                return [
                    'store_id' => $attributes['pick_address_id'],
                    'order_code' => $response['order']['label'],
                    'partner' => 'ghtk',
                    'quantity' => array_sum(array_column($attributes['items'], 'quantity')),
                    'cod_amount' => $attributes['cod_amount'],
                    'fee_amount' => $response['order']['fee'],
                    'package_price' => $attributes['total'],
                    'charge_fee' => $attributes['count_fee'] == 0 ? 1 : 0
                ];
            }
            throw new CODException($response['message']);
        } catch (ClientException $exception) {
            \Log::error($exception->getMessage());
            $response = $exception->getResponse()->getBody()->getContents();
            $response = json_decode($response, true);
            throw new CODException($response['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function statusList()
    {
        return [
            "-1" => "Hủy đơn hàng",
            "1" => "Chưa tiếp nhận",
            "2" => "Đã tiếp nhận",
            "3" => "Đã lấy hàng/Đã nhập kho",
            "4" => "Đã điều phối giao hàng/Đang giao hàng",
            "5" => "Giao hàng thành công",
            "6" => "Đã đối soát",
            "7" => "Không lấy được hàng",
            "8" => "Hoãn lấy hàng",
            "9" => "Không giao được hàng",
            "10" => "Delay giao hàng",
            "11" => "Đã đối soát công nợ trả hàng",
            "12" => "Đã điều phối lấy hàng/Đang lấy hàng",
            "13" => "Đơn hàng bồi hoàn",
            "20" => "Đang trả hàng (COD cầm hàng đi trả)",
            "21" => "Đã trả hàng (COD đã trả xong hàng)",
            "123" => "Shipper báo đã lấy hàng",
            "127" => "Shipper (nhân viên lấy/giao hàng) báo không lấy được hàng",
            "128" => "Shipper báo delay lấy hàng",
            "45" => "Shipper báo đã giao hàng",
            "49" => "Shipper báo không giao được hàng",
            "410" => "Shipper báo delay giao hàng"
        ];
    }

    public function renderBillOfLadding(Order $order)
    {
        $order = $order->fresh();
        $customer = $order->customer;
        $products = $this->prepareProductForBillOfLading($order)->map(function ($product) {
            $weight = (int) $product['weight'] * (int)$product['quantity'] * 0.001;
            $product['weight'] = $weight;
            return $product;
        });
        $stores = collect($this->getStores())->pluck('pick_name', 'pick_address_id');
        $provinces = Province::pluck('name', 'id');
        $address = $this->getCustomerAddress($customer, $order->address_id);
        $codAmount = $order->isFromFE() ? $products->sum('total') - $order->discount : $order->total;

        return View::make(
            'la.cod_orders.bill_ladings.ghtk',
            compact('order', 'customer', 'codAmount', 'products', 'stores', 'provinces', 'address')
        )->render();
    }


    public function renderBillOfLaddingForWarrantyOrder(WarrantyOrder $order, $type)
    {
        $customer = $order->customer;
        $codAmount = 0;
        $stores = collect($this->getStores())->pluck('pick_name', 'pick_address_id');
        $provinces = Province::pluck('name', 'id');
        $products = $type === "all"
            ? $this->prepareWarrantyOrderAllProductsForBillLading($order)
            : $this->prepareWarrantyOrderProductForBillLading($order);
        $products = $products->map(function ($product) {
            $weight = $product['weight'] * $product['quantity'] * 0.001;
            $product['weight'] = $weight;
            return $product;
        });
        $address = $this->getCustomerAddress($customer, null);

        return View::make(
            'la.cod_orders.bill_ladings.ghtk',
            compact('order', 'customer', 'codAmount', 'products', 'stores', 'provinces', 'address')
        )->render();
    }

    public function updateStatus($code)
    {
        $path = "cancel/" . $code;
        $response = $this->getClient()->post($path)->getBody()->getContents();
        $response = json_decode($response, true);
        if ($response['success']) {
            return true;
        }
        throw new CODException($response['message']);
    }

    public function getPickAddress($storeId = null)
    {
        $store = $this->getDefaultStore($storeId);
        if (!$store) {
            throw new CODException('Không tìm thấy kho.');
        }
        $address = explode(', ', $store['address']);
        $districtProvince = array_splice($address, -2, 2);

        return [
            'pick_name' => $store['pick_name'],
            'pick_tel' => $store['pick_tel'],
            'pick_address_id' => $store['pick_address_id'],
            'pick_address' => count($address) > 1 ? implode(', ', $address) : $address[0],
            'pick_district' => $districtProvince[0],
            'pick_province' => $districtProvince[1]
        ];
    }

    public function getAddress4($province, $district, $ward, $address)
    {
        $client = new Client([
            'base_uri' => 'https://services.giaohangtietkiem.vn/services/address/getAddressLevel4/',
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json',
                'Token' => $this->token
            ]
        ]);
        $url = '?' . http_build_query([
            'province' => $province,
            'district' => $district,
            'ward_street' => $ward
        ]);
        $response = $client->post($url)->getBody()->getContents();
        $response = json_decode($response, true);
        if ($response['success']) {
            $result = [];
            if (!empty($response['data'])) {
                foreach ($response['data'] as $key => $address4){
                    $result[$key] = $this->compare($address,$address4);
                }
                arsort($result);

                return $response['data'][array_keys($result)[0]];
            }

            return 'Khác';
        }
        throw new CODException($response['message']);
    }

    protected function compare($string1, $string2)
    {
        $string1 = explode(' ', strtolower(StringHelper::convertUTF8ToASCII($string1)));
        $string2 = explode(' ', strtolower(StringHelper::convertUTF8ToASCII($string2)));
        $count = 0;
        foreach ($string1 as $v) {
            $count += in_array($v, $string2) ? 1 : 0;
        }
        
        return count($string1) > 0 ? ceil($count / count($string1) * 100) : 0;
    }
}
