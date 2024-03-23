<?php

namespace App\Services\CODPartners;

use App\Exceptions\CODException;
use App\Models\CODOrder;
use App\Models\Order;
use App\Models\StoreShipping;
use Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class VNPostService extends Shipping
{
    const NAME = CODOrder::PARTNER_VNPOST;

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

    public function apiConnection()
    {
        return array_merge(parent::apiConnection(), [
            'username',
            'password',
            'sender_list'
        ]);
    }

    protected function getDefaultInventoryName()
    {
        return 'vnpostDefaultStoreId';
    }

    protected function loadByApiConnection($apiConnection)
    {
        $this->username = @$apiConnection['username'];
        $this->password = @$apiConnection['password'];
        if (Cache::has(md5('vnpost_auth_' . $this->username))) {
            $cache = Cache::get(md5('vnpost_auth_' . $this->username));
            $this->token = @$cache['token'];
        }

        return $this;
    }

    public function baseUri()
    {
        return 'https://donhang.vnpost.vn/api/api/';
    }

    public function login()
    {
        try {
            $response = $this->client()->post('MobileAuthentication/GetAccessToken', [
                'json' => [
                    'TenDangNhap' => $this->username,
                    'MatKhau' => $this->password
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            if (!$response['IsSuccess']) {
                throw new CODException($response['ErrorMessage']);
            }

            $this->token = $response['Token'];

            Cache::put(md5('vnpost_auth_' . $this->username), [
                'token' => $this->token,
            ], 60);

            return $this;
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            throw $exception;
        }
    }

    private function getToken()
    {
        if (!$this->token) {
            $this->login();
        }
        return $this->token;
    }

    public function getAddress($type = 'province', $id = null)
    {
        switch ($type) {
            case 'district':
                $path = 'QuanHuyen/GetAll';
                $value = ['MaQuanHuyen', 'TenQuanHuyen'];
                $filterBy = 'MaTinhThanh';
                break;
            case 'ward':
                $path = 'PhuongXa/GetAll';
                $value = ['MaPhuongXa', 'TenPhuongXa'];
                $filterBy = 'MaQuanHuyen';
                break;
            default:
                $path = 'TinhThanh/GetAll';
                $value = ['MaTinhThanh', 'TenTinhThanh'];
                $filterBy = 'MaTinhThanh';
        }
        try {
            $response = $this->client()->get($path)->getBody()->getContents();
            $results = collect(json_decode($response, true));
            if ($id) {
                $results = $results->where($filterBy, strval($id));
            }
            return $results->pluck($value[1], $value[0])->toArray();
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }
        return [];
    }

    public function getPriceForAllServices($attributes, $applyDiscount = false)
    {
        $token = $this->getToken();
        try {
            $response = $this->client([
                'h-token' => $token
            ])->post('CustomerConnect/TinhCuocTatCaDichVu', [
                'json' => [
                    'SenderDistrictId' => (string) @$attributes['SenderDistrictId'],
                    'SenderProvinceId' => (string) @$attributes['SenderProvinceId'],
                    'ReceiverDistrictId' => (string) @$attributes['ReceiverDistrictId'],
                    'ReceiverProvinceId' => (string) @$attributes['ReceiverProvinceId'],
                    'Weight' => (float) $attributes['Weight'],
                    // 'Width' => (int) $attributes['Width'],
                    // 'Length' => (int) $attributes['Length'],
                    // 'Height' => (int) $attributes['Height'],
                    'Width' => 10,
                    'Length' => 10,
                    'Height' => 10,
                    'CodAmount' => (int) $attributes['CodAmount'],
                    'IsReceiverPayFreight' => (bool) $attributes['IsReceiverPayFreight'],
                    'OrderAmount' => (int) $attributes['OrderAmount'],
                    'UseBaoPhat' => (bool) @$attributes['UseBaoPhat'],
                    'UseHoaDon' => (bool) @$attributes['UseHoaDon'],
                    'UseNhanTinSmsNguoiNhanTruocPhat' => (bool) @$attributes['UseNhanTinSmsNguoiNhanTruocPhat'],
                    'UseNhanTinSmsNguoiNhanSauPhat' => (bool) @$attributes['UseNhanTinSmsNguoiNhanSauPhat'],
                    'CustomerCode' => ''
                ]
            ])->getBody()->getContents();

            $results = collect(json_decode($response, true));

            return $results->reduce(function ($allServices, $service) use ($applyDiscount) {
                if ($service['Success']) {
                    $price = $service['TongCuocBaoGomDVCT'];
                    if ($applyDiscount) {
                        $price = $this->applyDiscount($price);
                    }
                    $allServices[$service['MaDichVu']] = [
                        'name' => $service['MaDichVu'] . ' - ' . number_format($price) . 'đ',
                        'price' => $price
                    ];
                }

                return $allServices;
            }, []);
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            throw $exception;
        }
    }

    public function renderBillOfLadding(Order $order)
    {
        $order = $order->fresh();
        $storeShipping = StoreShipping::where('store_id', $order->store_id)
            ->where('provider', static::NAME)
            ->first();
        $apiConnection = $storeShipping ? json_decode($storeShipping->api_connection, true) : [];
        $senderList = @$apiConnection['sender_list'] ?? [];
        $customer = $order->customer;
        $products = $this->prepareProductForBillOfLading($order);
        $packageContent = $products->map(function ($product) {
            return $product['product_name'] . ' x ' . $product['quantity'];
        })->implode(PHP_EOL);
        $address = $this->getCustomerAddress($customer, $order->address_id);
        $partnerProvinces = $this->getAddress();
        $shippingSetupInventory = @$customer->getShippingSetupByPartner(static::NAME)->inventory;
        $codAmount = $order->isFromFE() ? $products->sum('total') - $order->discount : $order->total;

        return View::make(
            'la.cod_orders.bill_ladings.vnpost',
            compact('order', 'codAmount', 'customer', 'products', 'address', 'partnerProvinces', 'packageContent', 'senderList', 'shippingSetupInventory')
        )->render();
    }

    public function createBill($attributes)
    {
        $token = $this->getToken();
        try {
            $response = $this->client([
                'h-token' => $token
            ])->post('CustomerConnect/CreateOrder', [
                'json' => [
                    'SenderTel' => (string) $attributes['SenderTel'],
                    'SenderFullname' => (string) $attributes['SenderFullname'],
                    'SenderAddress' => (string) $attributes['SenderAddress'],
                    'SenderWardId' => (string) $attributes['SenderWardId'],
                    'SenderDistrictId' => (string) $attributes['SenderDistrictId'],
                    'SenderProvinceId' => (string) $attributes['SenderProvinceId'],
                    'ReceiverTel' => (string) $attributes['ReceiverTel'],
                    'ReceiverFullname' => (string) $attributes['ReceiverFullname'],
                    'ReceiverAddress' => (string) $attributes['ReceiverAddress'],
                    'ReceiverWardId' => (string) $attributes['ReceiverWardId'],
                    'ReceiverDistrictId' => (string) $attributes['ReceiverDistrictId'],
                    'ReceiverProvinceId' => (string) $attributes['ReceiverProvinceId'],
                    'ServiceName' => (string) $attributes['ServiceName'],
                    'OrderCode' => (string) $attributes['OrderCode'],
                    'PackageContent' => (string) $attributes['PackageContent'],
                    'WeightEvaluation' => (float) $attributes['WeightEvaluation'],
                    // 'WidthEvaluation' => (int) $attributes['WidthEvaluation'],
                    // 'LengthEvaluation' => (int) $attributes['LengthEvaluation'],
                    // 'HeightEvaluation' => (int) $attributes['HeightEvaluation'],
                    'WidthEvaluation' => 10,
                    'LengthEvaluation' => 10,
                    'HeightEvaluation' => 10,
                    'IsPackageViewable' => (bool) $attributes['IsPackageViewable'],
                    'CustomerNote' => @$attributes['CustomerNote'] ?? '',
                    'PickupType' => (int) $attributes['PickupType'],
                    'CodAmountEvaluation' => (int) $attributes['CodAmountEvaluation'],
                    'IsReceiverPayFreight' => (bool) $attributes['IsReceiverPayFreight'],
                    'OrderAmountEvaluation' => (int) $attributes['OrderAmountEvaluation'],
                    'UseBaoPhat' => (bool) @$attributes['UseBaoPhat'],
                    'UseHoaDon' => (bool) @$attributes['UseHoaDon'],
                    'UseNhanTinSmsNguoiNhanTruocPhat' => (bool) @$attributes['UseNhanTinSmsNguoiNhanTruocPhat'],
                    'UseNhanTinSmsNguoiNhanSauPhat' => (bool) @$attributes['UseNhanTinSmsNguoiNhanSauPhat'],
                ]
            ])->getBody()->getContents();
            $response = json_decode($response, true);
            return [
                'store_id' => 0,
                'order_code' => $response['ItemCode'],
                'partner' => 'vnpost',
                'quantity' => $attributes['quantity'],
                'cod_amount' => (int) $response['OriginalCodAmountEvaluation'],
                'fee_amount' => (int) $response['TotalFreightIncludeVatEvaluation'],
                'package_price' => (int) $attributes['OrderAmountEvaluation'],
                'charge_fee' => (bool) $attributes['IsReceiverPayFreight'],
                'additional_data' => [
                    'id' => $response['Id']
                ]
            ];
        } catch (\Exception $exception) {
            \Log::info(['VNPOST_CREATE_BILL_PARAMS' => $attributes]);
            \Log::error($exception->getMessage());
            throw $exception;
        }
    }

    public function updateStatus($vnPostOrderId)
    {
        $token = $this->getToken();
        try {
            $this->client([
                'h-token' => $token
            ])->post('CustomerConnect/CancelOrder', [
                'json' => [
                    'OrderId' => $vnPostOrderId
                ]
            ]);

            return true;
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            throw $exception;
        }
    }

    public function getSenderData()
    {
        $senderList = $this->getApiConnection('sender_list');
        $senderId = $this->getApiConnection('vnpostDefaultStoreId');
        if (!$senderList) {
            throw new CODException('Kho chưa tạo địa chỉ gửi hàng.');
        }
        if (!$senderId) {
            throw new CODException('Khách hàng hoặc kho chưa thiết lập địa chỉ gửi hàng.');
        }
        $sender = collect($senderList)
            ->where('SenderId', $this->getApiConnection('vnpostDefaultStoreId'))
            ->first();
        if (!$sender) {
            throw new CODException('Địa chỉ gửi hàng không tồn tại.');
        }
        return collect($sender)->only([
            'SenderFullname', 'SenderTel', 'SenderAddress', 'SenderProvinceId', 'SenderDistrictId', 'SenderWardId'
        ]);
    }
}
