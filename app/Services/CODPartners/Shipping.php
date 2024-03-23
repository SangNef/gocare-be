<?php

namespace App\Services\CODPartners;

use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\Order;
use App\Models\WarrantyOrder;
use App\Models\StoreShipping;
use GuzzleHttp\Client;
use App\Helper\CustomLAHelper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\CustomerShippingSetup;

abstract class Shipping
{
    protected $apiConnection = [];

    public abstract function baseUri();
    public abstract function getAddress();
    protected abstract function getDefaultInventoryName();

    const NAME = '';

    public function apiConnection()
    {
        return [
            'discount_type',
            'discount',
            $this->getDefaultInventoryName()
        ];
    }

    public function getApiConnection($key)
    {
        return @$this->apiConnection[$key];
    }

    public function loadConnection(Customer $customer, $loadOnlyStoreConnection = false)
    {
        $providerName = static::NAME;
        $shipping = StoreShipping::where('store_id', $customer->store_id)
            ->where('status', true)
            ->where('provider', $providerName)
            ->first();
        $connection = $shipping ? json_decode($shipping->api_connection, true) : [];
        $connection['loadedBy'] = 'store';
        if (!$loadOnlyStoreConnection) {
            $shipping = $customer->getShippingSetupByPartner($providerName);
            if ($shipping) {
                $customerConnection = $shipping->connection;
                $customerConnection[$this->getDefaultInventoryName()] = $shipping->inventory;
                $connection = array_merge($connection, $customerConnection);
                $connection['loadedBy'] = 'customer';
            }
        }

        $this->loadByApiConnection($connection);
        $this->apiConnection = $connection;

        return $this;
    }

    protected abstract function loadByApiConnection($apiConnection);

    protected function client($headers = [])
    {
        return new Client([
            'base_uri' => $this->baseUri(),
            'headers' => array_merge([
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept'       => 'application/json',
            ], $headers)
        ]);
    }

    public function prepareProductForBillOfLading(Order $order)
    {
        return $order->orderProducts->map(function ($op) use ($order) {
            $result = [];
            $product = $op->product;
            $price = intval($order->isFromAdmin() ? $op->price : $product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true));
            $quantity = $order->isNewProduct() ? $op->quantity : $op->w_quantity;
            $result['product_name'] = $product->name . ($op->attr_texts ? '-' . $op->attr_texts : '');
            $result['price'] = $price;
            $result['sku'] = $product->sku;
            $result['quantity'] = $quantity;
            $result['weight'] = @$op->dimension['weight'] ?? $product->weight;
            $result['height'] = @$op->dimension['height'] ?? $product->height;
            $result['width'] = @$op->dimension['width'] ?? $product->width;
            $result['length'] = @$op->dimension['length'] ?? $product->length;
            $result['note'] = implode(' - ', array_filter([$product->name, $op->note]));
            $result['total'] = $price * $quantity;
            $result['size'] = $result['length'] * $result['height'] * $result['width'];
            return $result;
        });
    }

    public function prepareWarrantyOrderProductForBillLading(WarrantyOrder $order)
    {
        $results = [];
        $warrantyOrderProductSeries = $order->warrantyOrderProductSeries;
        foreach ($warrantyOrderProductSeries as $wops) {
            $product = $wops->warrantyOrderProduct->product;
            $key = $product->sku;
            if (isset($results[$key])) {
                $results[$key]['quantity']++;
            } else {
                $results[$key] = [
                    'id' => $wops->id,
                    'product_name' => $product->name,
                    'price' => 0,
                    'sku' => $key,
                    'quantity' => 1,
                    'weight' => $product->weight,
                    'height' => $product->height,
                    'length' => $product->length,
                    'width' => $product->width,
                    'note' => implode(' - ', array_filter([$product->name, $wops->note])),
                    'total' => 0
                ];
            }
        }
        return collect(array_values($results));
    }

    public function prepareWarrantyOrderAllProductsForBillLading(WarrantyOrder $order)
    {
        return $order->warrantyOrderProducts->map(function ($item) {
            $product = $item->product;
            $notes = $item->warrantyOrderProductSeries->pluck('note')->toArray();

            return [
                'product_name' => $product->name,
                'price' => 0,
                'sku' => $product->sku,
                'quantity' => $item->quantity,
                'weight' => $product->weight,
                'height' => $product->height,
                'length' => $product->length,
                'width' => $product->width,
                'note' => implode(' - ', array_filter($notes)),
                'total' => 0
            ];
        });
    }

    public function getCustomerAddress(Customer $customer, $addressId)
    {
        $address = Address::find($addressId);
        if (!$address) {
            $address = new \stdClass();
            $address->name = $customer->name;
            $address->phone = $customer->phone;
            $address->address = $customer->address;
            $address->province = $customer->getProvinceId();
            $address->district = $customer->getDistrictId();
            $address->ward = $customer->getWardId();
        }
        if (
            $address
            && ($this instanceof \App\Services\CODPartners\GHNService
                || $this instanceof \App\Services\CODPartners\VTPService
                || $this instanceof \App\Services\CODPartners\VNPostService
            )
        ) {
            $partnerAddress = $this->convertAddressIdToPartnerId($address->province, $address->district, $address->ward);
            $address->province = @$partnerAddress['province'];
            $districtName = @$partnerAddress['district'] ? District::find($address->district)->name : '';
            $address->district = @$partnerAddress['district'];
            $address->district_name = $districtName;
            $wardName = @$partnerAddress['ward'] ? Ward::find($address->ward)->name : '';
            $address->ward = @$partnerAddress['ward'];
            $address->ward_name = $wardName;
        }
        if ($address && $this instanceof \App\Services\CODPartners\GHTKService) {
            $address->district_name = $address->district ? District::find($address->district)->name : '';
            $address->ward_name = $address->ward ? Ward::find($address->ward)->name : '';
        }
        return $address;
    }


    public function getPartnerAddressIdFromAddressName(string $text, $type = 'province', $typeId = 0)
    {
        $province = $this->stripAccents($text);
        if ($type === 'province') {
            $province = preg_replace("/(thi xa |xa |huyen |thanh pho |tinh |thi tran |phuong |quan )/", "", $province);
        }
        $partnerProvinces = collect($this->getAddress($type, $typeId));
        return $partnerProvinces->search(function ($item) use ($province) {
            $item = $this->stripAccents($item);
            return preg_match("~^$province$~i", $item) === 1;
        });
    }

    protected function stripAccents($str)
    {
        $str = CustomLAHelper::removeAccents($str);
        $str = str_replace("'", " ", strtolower($str));
        $str = str_replace("- ", "", $str);
        $str = str_replace("so", "phuong", $str);

        return $str;
    }

    public function applyDiscount($amount)
    {
        $apiConnection = $this->apiConnection;
        switch (@$apiConnection['discount_type']) {
            case 1:
                $amount += (int) @$apiConnection['discount'];
                break;
            case 2:
                $amount += (int) ((float) @$apiConnection['discount'] * $amount / 100);
                break;
            case 3:
                $amount -= (int) @$apiConnection['discount'];
                break;
            case 4:
                $amount -= (int) ((float) @$apiConnection['discount'] * $amount / 100);
                break;
            default:
                break;
        }

        return $amount >= 0 ? $amount : 0;
    }

    public function convertAddressIdToPartnerId($province = 0, $district = 0, $ward = 0)
    {
        $results = [];
        if ($province = Province::find($province)) {
            $partnerProvince = $this->getPartnerAddressIdFromAddressName($province->name);
            if ($partnerProvince) {
                $results['province'] = $partnerProvince ?: null;
                if ($district = District::find($district)) {
                    $partnerDistrict = $this->getPartnerAddressIdFromAddressName($district->name, 'district', $partnerProvince);
                    $results['district'] = $partnerDistrict ?: null;
                    if ($ward = Ward::find($ward)) {
                        $partnerWard = $this->getPartnerAddressIdFromAddressName($ward->name, 'ward', $partnerDistrict);
                        $results['ward'] = $partnerWard ?: null;
                    }
                }
            }
        }
        return $results;
    }
}
