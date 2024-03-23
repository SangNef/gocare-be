<?php

namespace App\Repositories;

use App\Exceptions\CODException;
use App\Models\Order;
use App\Models\CODOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProductSeri;
use App\Services\CODPartners\GHNService;
use App\Services\CODPartners\GHN5Service;
use App\Services\CODPartners\GHTKService;
use App\Services\CODPartners\VNPostService;
use App\Services\CODPartners\VTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class CODOrderRepository
{
    protected $ghnSv;
    protected $ghn5Sv;
    protected $vtpSv;
    protected $ghtkSv;
    protected $vnpostSv;
    protected $codOrder;
    protected $orderRp;

    public function __construct(
        GHNService $ghnSv,
        GHN5Service $ghn5Sv,
        VTPService $vtpSv,
        GHTKService $ghtkSv,
        VNPostService $vnpostSv,
        CODOrder $codOrder,
        OrderRepository $orderRp
    ) {
        $this->ghnSv = $ghnSv;
        $this->ghn5Sv = $ghn5Sv;
        $this->vtpSv = $vtpSv;
        $this->ghtkSv = $ghtkSv;
        $this->vnpostSv = $vnpostSv;
        $this->codOrder = $codOrder;
        $this->orderRp = $orderRp;
    }

    public function loadCodServiceByStore(Customer $customer, $loadOnlyStoreConnection = false)
    {
        $this->vtpSv->loadConnection($customer, $loadOnlyStoreConnection);
        $this->ghnSv->loadConnection($customer, $loadOnlyStoreConnection);
        $this->ghn5Sv->loadConnection($customer, $loadOnlyStoreConnection);
        $this->ghtkSv->loadConnection($customer, $loadOnlyStoreConnection);
        $this->vnpostSv->loadConnection($customer, $loadOnlyStoreConnection);
    }

    public function getAvailableProvider()
    {
        return [
            CODOrder::PARTNER_VTP => 'ViettelPost',
            CODOrder::PARTNER_GHN => 'Giaohangnhanh',
            CODOrder::PARTNER_GHN_5 => 'Giaohangnhanh < 5kg',
            CODOrder::PARTNER_GHTK => 'GHTK',
            CODOrder::PARTNER_VNPOST => 'VNPost'
        ];
    }

    public function getPartnerStores($partner, Order $order = null)
    {
        if ($order) {
            $this->loadCodServiceByStore($order->customer, $order->isFromAdmin());
        }
        switch ($partner) {
            case 'vtp':
                $results = collect($this->vtpSv->getStores())->pluck('name', 'groupaddressId');
                break;
            case 'ghn_5':
                $results = collect($this->ghn5Sv->getStores())->pluck('name', '_id');
                break;
            case 'ghn':
                $results = collect($this->ghnSv->getStores())->pluck('name', '_id');
                break;
            case 'ghtk':
                $results = collect($this->ghtkSv->getStores())->pluck('pick_name', 'pick_address_id');
                break;
            default:
                $results = collect();
                break;
        }
        return $results;
    }


    public function getBillLadingHTML(Order $order)
    {
        $this->loadCodServiceByStore($order->customer, $order->isFromAdmin());
        $order = $order->fresh();
        switch ($order->cod_partner) {
            case CODOrder::PARTNER_GHN:
                $results = $this->ghnSv->renderBillOfLadding($order);
                break;
            case CODOrder::PARTNER_GHN_5:
                $results = $this->ghn5Sv->renderBillOfLadding($order);
                break;
            case CODOrder::PARTNER_VTP:
                $results = $this->vtpSv->renderBillOfLadding($order);
                break;
            case CODOrder::PARTNER_GHTK:
                $results = $this->ghtkSv->renderBillOfLadding($order);
                break;
            case CODOrder::PARTNER_VNPOST:
                $results = $this->vnpostSv->renderBillOfLadding($order);
                break;
            case CODOrder::PARTNER_OTHER:
                $results = '';
                break;
            default:
                $results = '<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
    <li class="active"><a role="tab" data-toggle="tab" class="active" data-target="#vtp">ViettelPost</a></li>
    <li><a role="tab" data-toggle="tab" data-target="#ghn">GiaoHangNhanh</a></li>
    <li><a role="tab" data-toggle="tab" data-target="#ghn_5">GiaoHangNhanh < 5kg</a></li>
    <li><a role="tab" data-toggle="tab" data-target="#ghtk">GHTK</a></li>
    <li><a role="tab" data-toggle="tab" data-target="#vnpost">VNPost</a></li>
</ul>';
                $results .= '<div class="tab-content">';
                $results .= '<div role="tabpanel" class="tab-pane active fade in" id="vtp">' . $this->vtpSv->renderBillOfLadding($order) . '</div>';
                $results .= '<div role="tabpanel" class="tab-pane fade in" id="ghn">' . $this->ghnSv->renderBillOfLadding($order) . '</div>';
                $results .= '<div role="tabpanel" class="tab-pane fade in" id="ghn_5">' . $this->ghn5Sv->renderBillOfLadding($order) . '</div>';
                $results .= '<div role="tabpanel" class="tab-pane fade in" id="ghtk">' . $this->ghtkSv->renderBillOfLadding($order) . '</div>';
                $results .= '<div role="tabpanel" class="tab-pane fade in" id="vnpost">' . $this->vnpostSv->renderBillOfLadding($order) . '</div>';
                $results .= '</div>';
        }
        return $results;
    }

    public function getWarrantyOrderBillLadingHTML(WarrantyOrder $order, $type)
    {
        $this->loadCodServiceByStore($order->customer, true);
        $results = '<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
<li class="active"><a role="tab" data-toggle="tab" class="active" data-target="#vtp">ViettelPost</a></li>
<li><a role="tab" data-toggle="tab" data-target="#ghn">GiaoHangNhanh</a></li>
<li><a role="tab" data-toggle="tab" data-target="#ghn_5">GiaoHangNhanh < 5kg</a></li>
<li><a role="tab" data-toggle="tab" data-target="#ghtk">GHTK</a></li>
</ul>';
        $results .= '<div class="tab-content">';
        $results .= '<div role="tabpanel" class="tab-pane active fade in" id="vtp">' . $this->vtpSv->renderBillOfLaddingForWarrantyOrder($order, $type) . '</div>';
        $results .= '<div role="tabpanel" class="tab-pane fade in" id="ghn">' . $this->ghnSv->renderBillOfLaddingForWarrantyOrder($order, $type) . '</div>';
        $results .= '<div role="tabpanel" class="tab-pane fade in" id="ghn_5">' . $this->ghn5Sv->renderBillOfLaddingForWarrantyOrder($order, $type) . '</div>';
        $results .= '<div role="tabpanel" class="tab-pane fade in" id="ghtk">' . $this->ghtkSv->renderBillOfLaddingForWarrantyOrder($order, $type) . '</div>';
        $results .= '</div>';
        return $results;
    }

    protected function createPartnerOrder($partner, $order, $attributes = [])
    {
        $shipping = null;
        switch ($partner) {
            case CODOrder::PARTNER_GHN:
                $results = $this->ghnSv->createBill($attributes);
                $shipping = $this->ghnSv;
                break;
            case CODOrder::PARTNER_GHN_5:
                $results = $this->ghn5Sv->createBill($attributes);
                $shipping = $this->ghn5Sv;
                break;
            case CODOrder::PARTNER_VTP:
                $defaultStore = $this->vtpSv->getStoreInfo($attributes['inventory']);
                if (!$defaultStore) {
                    throw new CODException('Không tìm thấy kho.');
                }
                $attributes = array_merge($attributes, [
                    'ORDER_NUMBER' => @$order->code ?? "",
                    'GROUPADDRESS_ID' => $defaultStore['groupaddressId'],
                    'CUS_ID' => $defaultStore['cusId'],
                    'SENDER_FULLNAME' => $defaultStore['name'],
                    'SENDER_ADDRESS' => $defaultStore['address'],
                    'SENDER_PHONE' => $defaultStore['phone'],
                    'SENDER_WARD' => $defaultStore['wardsId'],
                    'SENDER_DISTRICT' => $defaultStore['districtId'],
                    'SENDER_PROVINCE' => $defaultStore['provinceId']
                ]);
                $results = $this->vtpSv->createBill($attributes);
                $shipping = $this->vtpSv;
                break;
            case CODOrder::PARTNER_GHTK:
                $customerAddress = $this->ghtkSv->getAddress($attributes['province'], $attributes['district'], $attributes['ward']);
                $store = $this->ghtkSv->getPickAddress($attributes['inventory']);
                $attributes = array_merge($attributes, [
                    'id' => $order->code,
                    'pick_address_id' => $store['pick_address_id'],
                    'pick_name' => $store['pick_name'],
                    'pick_tel' => $store['pick_tel'],
                    'pick_address' => $store['pick_address'],
                    'pick_district' => $store['pick_district'],
                    'pick_province' => $store['pick_province'],
                    'province' => $customerAddress['province'],
                    'district' => $customerAddress['district'],
                    'ward' => $customerAddress['ward'],
                ]);
                if (!empty($attributes['items'])) {
                    $attributes['items'] = array_map(function ($item) {
                        $item['weight'] = $item['weight'] * $item['quantity'] / 1000;
                        return $item;
                    }, $attributes['items']);
                }
                $results = $this->ghtkSv->createBill($attributes);
                $shipping = $this->ghtkSv;
                break;
            case CODOrder::PARTNER_VNPOST:
                $results = $this->vnpostSv->createBill($attributes);
                $shipping = $this->vnpostSv;
                break;
            default:
                $results = [];
        }
        if (!empty($results)) {
            if ($results['charge_fee']) {
                $results['fee_amount'] = 0;
            }
            $results['real_amount'] = $results['fee_amount'];
            $results['charge_method'] = $attributes['charge_method'];
        }
        return [
            'results' => $results,
            'shipping' => $shipping
        ];
    }

    public function createBillLading($partner, Order $order, $attributes = [])
    {
        $this->loadCodServiceByStore($order->customer, $order->isFromAdmin());
        $shippingResult = $this->createPartnerOrder($partner, $order, $attributes);
        $results = $shippingResult['results'];
        $shipping = $shippingResult['shipping'];
        if (!empty($results)) {
            if ($order->isFromFE() && $shipping && !$results['charge_fee']) {
                $results['fee_amount'] = $shipping->applyDiscount($results['fee_amount']);
            }
            $results['customer_id'] = $order->customer_id;
            $order->codOrder()->updateOrCreate([
                'order_id' => $order->id
            ], $results);
        }
    }

    public function createBillLadingForWarrantyOrder($partner, WarrantyOrder $order, $attributes = [])
    {
        $this->loadCodServiceByStore($order->customer, true);
        $shippingResult = $this->createPartnerOrder($partner, $order, $attributes);
        $shipping = $shippingResult['shipping'];
        $results = $shippingResult['results'];
        $results['customer_id'] = $order->customer_id;
        if (!empty($results)) {
            if ($shipping && !$results['charge_fee']) {
                $results['fee_amount'] = $shipping->applyDiscount($results['fee_amount']);
            }
            $codOrder = CODOrder::create(array_merge([
                'order_id' => $attributes['type'] === 'all' ? $order->id : 0,
                'order_type' => $attributes['type'] === 'all' ? 'WarrantyOrder' : 'WarrantyOrderProductSeri'
            ], $results));

            $updatingWops = [
                'status' => WarrantyOrderProductSeri::STATUS_RETURNED,
                'warranty_order_product_series.return_at' => Carbon::now(),
            ];
            $wopsIds = [];
            if (@$attributes['wops_ids'] && $attributes['type'] === 'some') {
                $updatingWops['cod_order_id'] = $codOrder->id;
                $wopsIds = $attributes['wops_ids'];
            }
            $order->warrantyOrderProductSeries()
                ->where(function ($query) use ($wopsIds) {
                    $query->whereIn('warranty_order_product_series.id', $wopsIds);
                })
                ->toBase()
                ->update($updatingWops);
        }
    }

    public function GHNShippingOptionsForFE(Request $request)
    {
        $results = [];
        $products = $this->getProductForShippingOptionsFE($request->products, function ($product) {
            $itemWeight = ($product->length * $product->width * $product->height / 6000) * 1000;
            $itemWeight = $itemWeight > $product->weight ? intval($itemWeight) : $product->weight;
            $product->weight = $itemWeight * $product->input_quantity;
            return $product;
        });
        $totalWeight = $products->sum('weight');
        $totalHeight = $products[0]['height'];
        $totalLength = $products[0]['length'];
        $totalWidth = $products[0]['width'];
        $totalPrice =  $request->total;

        $loadedBy = $this->ghnSv->getApiConnection('loadedBy');
        if ($request->partner_store) {
            $storeId = $request->partner_store;
        } else {
            $storeId = $this->ghnSv->getApiConnection('ghnDefaultStoreId');
            if (!$storeId || $loadedBy === 'customer') {
                $storeId = $this->ghnSv->getApiConnection('ghnDefaultStoreId');
            }
        }
        $store = $this->ghnSv->getStoreById($storeId);

        $partnerAddress = $this->ghnSv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        $services = $this->ghnSv->getServices([
            'shop_id' => @$store['_id'],
            'from_district' => @$store['district_id'],
            'to_district' => @$partnerAddress['district']
        ]);
        foreach ($services as $serviceId => $name) {
            $data = [
                'from_district_id' => $store['district_id'],
                'service_id' => $serviceId,
                'to_district_id' => @$partnerAddress['district'],
                'to_ward_code' => @$partnerAddress['ward'],
                'height' => $totalHeight > 200 ? 200 : $totalHeight,
                'length' => $totalLength > 200 ? 200 : $totalLength,
                'weight' => $totalWeight,
                'width' => $totalWidth > 200 ? 200 : $totalWidth,
                'insurance_value' => $totalPrice > 5000000 ? 5000000 : $totalPrice,
            ];
            $price = $this->ghnSv->getServicePrice($store['_id'], $data);
            $amount = $this->ghnSv->applyDiscount($price);
            $results[$serviceId]['name'] = $name . ' - ' . number_format($amount) . ' đ';
            $results[$serviceId]['price'] = $this->ghnSv->applyDiscount($amount);
        }
        return $results;
    }

    public function GHN5ShippingOptionsForFE(Request $request)
    {
        $results = [];
        $products = $this->getProductForShippingOptionsFE($request->products, function ($product) {
            $itemWeight = ($product->length * $product->width * $product->height / 6000) * 1000;
            $itemWeight = $itemWeight > $product->weight ? intval($itemWeight) : $product->weight;
            $product->weight = $itemWeight * $product->input_quantity;
            return $product;
        });
        $totalWeight = $products->sum('weight');
        $totalHeight = $products[0]['height'];
        $totalLength = $products[0]['length'];
        $totalWidth = $products[0]['width'];
        $totalPrice =  $request->total;

        $loadedBy = $this->ghn5Sv->getApiConnection('loadedBy');
        if ($request->partner_store) {
            $storeId = $request->partner_store;
        } else {
            $storeId = $this->ghn5Sv->getApiConnection('ghnDefaultStoreId');
            if (!$storeId || $loadedBy === 'customer') {
                $storeId = $this->ghn5Sv->getApiConnection('ghnDefaultStoreId');
            }
        }
        $store = $this->ghn5Sv->getStoreById($storeId);

        $partnerAddress = $this->ghn5Sv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        $services = $this->ghn5Sv->getServices([
            'shop_id' => @$store['_id'],
            'from_district' => @$store['district_id'],
            'to_district' => @$partnerAddress['district']
        ]);
        foreach ($services as $serviceId => $name) {
            $data = [
                'from_district_id' => $store['district_id'],
                'service_id' => $serviceId,
                'to_district_id' => @$partnerAddress['district'],
                'to_ward_code' => @$partnerAddress['ward'],
                'height' => $totalHeight > 200 ? 200 : $totalHeight,
                'length' => $totalLength > 200 ? 200 : $totalLength,
                'weight' => $totalWeight,
                'width' => $totalWidth > 200 ? 200 : $totalWidth,
                'insurance_value' => $totalPrice > 5000000 ? 5000000 : $totalPrice,
            ];
            $price = $this->ghn5Sv->getServicePrice($store['_id'], $data);
            $amount = $this->ghn5Sv->applyDiscount($price);
            $results[$serviceId]['name'] = $name . ' - ' . number_format($amount) . ' đ';
            $results[$serviceId]['price'] = $this->ghn5Sv->applyDiscount($amount);
        }
        return $results;
    }

    public function VTPShippingOptionsForFE(Request $request)
    {
        $storeId = $request->partner_store ?: $this->vtpSv->getApiConnection('vtpDefaultStoreId');
        $products = $this->getProductForShippingOptionsFE($request->products, function ($product) {
            $itemWeight = ($product->length * $product->width * $product->height / 6000) * 1000;
            $itemWeight = $itemWeight > $product->weight ? intval($itemWeight) : $product->weight;
            $product->weight = $itemWeight * $product->input_quantity;
            return $product;
        });
        $totalPrice =  $request->total;
        $defaultStore = $this->vtpSv->getStoreInfo($storeId);
        $partnerAddress = $this->vtpSv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);

        $data = [
            'PRODUCT_WEIGHT' => $products->sum('weight'),
            'PRODUCT_LENGTH' => $products[0]['length'],
            'PRODUCT_HEIGHT' => $products[0]['height'],
            'PRODUCT_WIDTH' => $products[0]['width'],
            'PRODUCT_PRICE' => $totalPrice,
            'MONEY_COLLECTION' => $request->payment_method == "cod" ? $totalPrice : 0,
            'SENDER_PROVINCE' => $defaultStore['provinceId'],
            'SENDER_DISTRICT' => $defaultStore['districtId'],
            'RECEIVER_PROVINCE' => @$partnerAddress['province'],
            'RECEIVER_DISTRICT' => @$partnerAddress['district'],
            'PRODUCT_TYPE' => "HH"
        ];
        $request = new Request($data);
        $result = $this->vtpSv->requestServicePrice($request, $storeId);
        foreach ($result as $key => $value) {
            $amount = $this->vtpSv->applyDiscount($result[$key]['price']);
            $result[$key]['name'] = substr($value['name'], 0, strpos($value['name'], '-')) . ' - ' . number_format($amount) . ' đ';
            $result[$key]['price'] = $amount;
        }

        return $result;
    }

    public function GHTKShippingOptionsForFE(Request $request)
    {
        $products = $this->getProductForShippingOptionsFE($request->products, function ($product) {
            return [
                'name' => $product->name,
                'weight' => $product->weight * $product->input_quantity * 0.001,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
                'quantity' => $product->input_quantity,
                'product_code' => ''
            ];
        });
        $customerAddress = $this->ghtkSv->getAddress($request->province, $request->district, $request->ward);
        $data = [
            'pick_address_id' => $request->partner_store ?: $this->ghtkSv->getApiConnection('ghtkDefaultStoreId'),
            'province' => $customerAddress['province'],
            'district' => $customerAddress['district'],
            'weight' => $products->sum('weight') * 1000,
            'value' => $request->total,
            'transport' => 'road',
            'products' => $products->toArray(),
        ];
        if ($request->tag) {
            $data['tags'] = [1];
        }
        $service = $this->ghtkSv->getServicePrice($data);
        $amount = $this->ghtkSv->applyDiscount($service['fee']);
        return [
            $service['name'] => [
                'name' => $service['name']  . ' - ' . number_format($amount) . ' đ',
                'price' => $amount
            ]
        ];
    }

    public function VNPOSTShippingOptionsForFE(Request $request)
    {
        $products = $this->getProductForShippingOptionsFE($request->products);
        $senderData = $this->vnpostSv->getSenderData();
        $partnerAddress = $this->vnpostSv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        $totalPrice = $request->total;
        $data = [
            'SenderDistrictId' => (string) @$senderData['SenderDistrictId'],
            'SenderProvinceId' => (string) @$senderData['SenderProvinceId'],
            'ReceiverDistrictId' => (string) @$partnerAddress['district'],
            'ReceiverProvinceId' => (string) @$partnerAddress['province'],
            'Weight' => (float) $products->sum('weight'),
            'Width' => (int) $products[0]['width'],
            'Length' => (int) $products[0]['length'],
            'Height' => (int) $products[0]['height'],
            'CodAmount' => (int) $totalPrice,
            'IsReceiverPayFreight' => (bool) $request->countFeeCustomer,
            'OrderAmount' => (int) $totalPrice,
        ];
        return $this->vnpostSv->getPriceForAllServices($data, true);
    }

    protected function prepareDataForGHNFE(Request $request, Order $order)
    {
        $products = $this->ghnSv->prepareProductForBillOfLading($order)
            ->map(function ($item) {
                $itemWeight = ($item['length'] * $item['width'] * $item['height'] / 6000) * 1000;
                $itemWeight = $itemWeight > $item['weight'] ? intval($itemWeight) : $item['weight'];
                $itemWeight *= $item['quantity'];

                return [
                    'name' => $item['product_name'],
                    'code' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'weight' => $itemWeight,
                    'height' => $item['height'],
                    'length' => $item['length'],
                    'width' => $item['width'],
                    'total' => $item['total'],
                    'price' => $item['price']
                ];
            });
        $totalWeight = $products->sum('weight');
        $totalHeight = $products[0]['height'];
        $totalLength = $products[0]['length'];
        $totalWidth = $products[0]['width'];
        $priceStatement = $order->cod_price_statement;
        $codAmount = $products->sum('total');
        $codAmount = $codAmount > 10000000 ? 10000000 : $codAmount;

        $loadedBy = $this->ghnSv->getApiConnection('loadedBy');
        $storeId = $this->ghnSv->getApiConnection('ghnDefaultStoreId');

        if (!$storeId || $loadedBy === 'customer') {
            $storeId = $this->ghnSv->getApiConnection('ghnDefaultStoreId');
        }

        $discount = $request->get('discount', 0);
        $partnerAddress = $this->ghnSv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        return [
            'inventory' => $storeId,
            'to_name' => $request->name,
            'to_phone' => $request->phone,
            'to_address' => $request->address,
            'to_province' => @$partnerAddress['province'],
            'to_district_id' => @$partnerAddress['district'],
            'to_ward_code' => @$partnerAddress['ward'],
            'required_note' => "CHOTHUHANG",
            'payment_type_id' => $request->countFeeCustomer ? 2 : 1,
            'to_ward_code' => @$partnerAddress['ward'],
            'height' => $totalHeight > 200 ? 200 : $totalHeight,
            'length' => $totalLength > 200 ? 200 : $totalLength,
            'weight' => min($totalWeight, 30000),
            'width' => $totalWidth > 200 ? 200 : $totalWidth,
            'cod_amount' => $request->payment_method == "cod" || $request->payment_method == Order::PAYMENT_METHOD_COD ? $codAmount - $discount : 0,
            'insurance_value' => $priceStatement > 5000000 ? 5000000 : $priceStatement,
            'service_id' => $request->service_id,
            'content' => $products->implode('note', ', '),
            'note' => $request->note,
            'items' => $products->toArray()
        ];
    }

    protected function prepareDataForGHN5FE(Request $request, Order $order)
    {
        $products = $this->ghn5Sv->prepareProductForBillOfLading($order)
            ->map(function ($item) {
                $itemWeight = ($item['length'] * $item['width'] * $item['height'] / 6000) * 1000;
                $itemWeight = $itemWeight > $item['weight'] ? intval($itemWeight) : $item['weight'];
                $itemWeight *= $item['quantity'];

                return [
                    'name' => $item['product_name'],
                    'code' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'weight' => $itemWeight,
                    'height' => $item['height'],
                    'length' => $item['length'],
                    'width' => $item['width'],
                    'total' => $item['total'],
                    'price' => $item['price']
                ];
            });
        $totalWeight = $products->sum('weight');
        $totalHeight = $products[0]['height'];
        $totalLength = $products[0]['length'];
        $totalWidth = $products[0]['width'];
        $priceStatement = $order->cod_price_statement;
        $codAmount = $products->sum('total');
        $codAmount = $codAmount > 10000000 ? 10000000 : $codAmount;

        $loadedBy = $this->ghn5Sv->getApiConnection('loadedBy');
        $storeId = $this->ghn5Sv->getApiConnection('ghnDefaultStoreId');

        if (!$storeId || $loadedBy === 'customer') {
            $storeId = $this->ghn5Sv->getApiConnection('ghnDefaultStoreId');
        }

        $discount = $request->get('discount', 0);
        $partnerAddress = $this->ghn5Sv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        return [
            'inventory' => $storeId,
            'to_name' => $request->name,
            'to_phone' => $request->phone,
            'to_address' => $request->address,
            'to_province' => @$partnerAddress['province'],
            'to_district_id' => @$partnerAddress['district'],
            'to_ward_code' => @$partnerAddress['ward'],
            'required_note' => "CHOTHUHANG",
            'payment_type_id' => $request->countFeeCustomer ? 2 : 1,
            'to_ward_code' => @$partnerAddress['ward'],
            'height' => $totalHeight > 200 ? 200 : $totalHeight,
            'length' => $totalLength > 200 ? 200 : $totalLength,
            'weight' => min($totalWeight, 30000),
            'width' => $totalWidth > 200 ? 200 : $totalWidth,
            'cod_amount' => $request->payment_method == "cod" || $request->payment_method == Order::PAYMENT_METHOD_COD ? $codAmount - $discount : 0,
            'insurance_value' => $priceStatement > 5000000 ? 5000000 : $priceStatement,
            'service_id' => $request->service_id,
            'content' => $products->implode('note', ', '),
            'note' => $request->note,
            'items' => $products->toArray()
        ];
    }

    protected function prepareDataForVtpFE(Request $request, Order $order)
    {
        $partnerAddress = $this->vtpSv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        $paymentMethod = $request->payment_method;
        $countFee = $request->countFeeCustomer;
        $discount = $request->get('discount', 0);
        switch (true) {
            case $paymentMethod == "cod" && !$countFee:
                $orderPayment = 3;
                break;
            case $paymentMethod == "bank" && !$countFee:
                $orderPayment = 1;
                break;
            case $paymentMethod == "bank" && $countFee:
                $orderPayment = 4;
                break;
            default:
                $orderPayment = 2;
        }
        $products = $this->vtpSv->prepareProductForBillOfLading($order)
            ->map(function ($item) {
                $itemWeight = ($item['length'] * $item['width'] * $item['height'] / 6000) * 1000;
                $itemWeight = $itemWeight > $item['weight'] ? intval($itemWeight) : $item['weight'];
                $itemWeight *= $item['quantity'];

                return [
                    'PRODUCT_NAME' => $item['product_name'],
                    'PRODUCT_WEIGHT' => $itemWeight,
                    'PRODUCT_HEIGHT' => $item['height'],
                    'PRODUCT_LENGTH' => $item['length'],
                    'PRODUCT_WIDTH' => $item['width'],
                    'PRODUCT_PRICE' => $item['price'],
                    'PRODUCT_QUANTITY' => $item['quantity'],
                    'TOTAL' => $item['total'],
                ];
            });
        $codAmount = $products->sum('TOTAL');
        $priceStatement = $order->cod_price_statement;

        return [
            'inventory' => $this->vtpSv->getApiConnection('vtpDefaultStoreId'),
            'DELIVERY_DATE' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
            'RECEIVER_FULLNAME' => $request->name,
            'RECEIVER_ADDRESS' => $request->address,
            'RECEIVER_PHONE' => $request->phone,
            'RECEIVER_WARDS' => @$partnerAddress['ward'],
            'RECEIVER_DISTRICT' => @$partnerAddress['district'],
            'RECEIVER_PROVINCE' => @$partnerAddress['province'],
            'ORDER_PAYMENT' => $orderPayment,
            'ORDER_SERVICE' => $request->service_id,
            'ORDER_NOTE' => $request->note,
            'PRODUCT_WEIGHT' => $products->sum('PRODUCT_WEIGHT'),
            'PRODUCT_WIDTH' => $products[0]['PRODUCT_WIDTH'],
            'PRODUCT_HEIGHT' => $products[0]['PRODUCT_HEIGHT'],
            'PRODUCT_LENGTH' => $products[0]['PRODUCT_LENGTH'],
            'PRODUCT_TYPE' => "HH",
            'PRODUCT_PRICE' => $priceStatement,
            'MONEY_COLLECTION' => $paymentMethod == "cod" || $paymentMethod == Order::PAYMENT_METHOD_COD ? $codAmount - $discount : 0,
            'PRODUCT_NAME' => $products->implode('PRODUCT_NAME', ', '),
            'PRODUCT_QUANTITY' => $products->sum('PRODUCT_QUANTITY'),
            'LIST_ITEM' => $products->toArray(),
        ];
    }

    protected function prepareDataForGhtkFE(Request $request, Order $order)
    {
        $products = $this->ghtkSv->prepareProductForBillOfLading($order)
            ->map(function ($item) {
                return [
                    'name' => $item['product_name'],
                    'weight' => $item['weight'] * $item['quantity'] / 1000,
                    'length' => $item['length'],
                    'width' => $item['width'],
                    'height' => $item['height'],
                    'quantity' => $item['quantity'],
                    'product_code' => '',
                    'total' => $item['total']
                ];
            });
        $codAmount = $products->sum('total');
        $priceStatement = $order->cod_price_statement;
        $discount = $request->get('discount', 0);

        $result = [
            'inventory' => $this->ghtkSv->getApiConnection('ghtkDefaultStoreId'),
            'phone' => $request->phone,
            'name' => $request->name,
            'address' => $request->address,
            'province' => $request->province,
            'district' => $request->district,
            'ward' => $request->ward,
            'count_fee' => (int) !$request->countFeeCustomer,
            'cod_amount' => $request->payment_method == "cod" || $request->payment_method == Order::PAYMENT_METHOD_COD ? $codAmount - $discount : 0,
            'note' => $request->note,
            'total' => $priceStatement,
            'products' => $products->toArray(),
            'transport' => 'road',
        ];

        if ($request->cod_tag) {
            $result['tags'] = [1];
        }

        return $result;
    }

    protected function prepareDataForVNPostFE(Request $request, Order $order)
    {
        $products = $this->vnpostSv->prepareProductForBillOfLading($order);
        $partnerAddress = $this->vnpostSv->convertAddressIdToPartnerId($request->province, $request->district, $request->ward);
        $senderData = $this->vnpostSv->getSenderData();
        $discount = $request->get('discount', 0);
        $total = $products->sum('total');
        $priceStatement = $order->cod_price_statement;

        return [
            'SenderTel' => $senderData['SenderTel'],
            'SenderFullname' => $senderData['SenderFullname'],
            'SenderAddress' => $senderData['SenderAddress'],
            'SenderWardId' => $senderData['SenderWardId'],
            'SenderDistrictId' => $senderData['SenderDistrictId'],
            'SenderProvinceId' => $senderData['SenderProvinceId'],
            'ReceiverTel' => $request->phone,
            'ReceiverFullname' => $request->name,
            'ReceiverAddress' => $request->address,
            'ReceiverWardId' => $partnerAddress['ward'],
            'ReceiverDistrictId' => $partnerAddress['district'],
            'ReceiverProvinceId' => $partnerAddress['province'],
            'ServiceName' => $request->service_id,
            'OrderCode' => $order->code,
            'PackageContent' => $products->map(function ($product) {
                return $product['product_name'] . ' x ' . $product['quantity'];
            })->implode(PHP_EOL),
            'WeightEvaluation' => $products->sum('weight'),
            'WidthEvaluation' => $products[0]['width'],
            'LengthEvaluation' => $products[0]['length'],
            'HeightEvaluation' => $products[0]['height'],
            'IsPackageViewable' => true,
            'CustomerNote' => $request->note,
            'PickupType' => 1,
            'CodAmountEvaluation' => $request->payment_method == "cod" || $request->payment_method == Order::PAYMENT_METHOD_COD ? $total - $discount : 0,
            'IsReceiverPayFreight' => $request->countFeeCustomer,
            'OrderAmountEvaluation' => $priceStatement,
            'quantity' => $products->sum('quantity')
        ];
    }

    public function createBillLadingForFE(Order $order, Request $request)
    {
        $this->loadCodServiceByStore($order->customer);
        $order = $order->fresh();
        if ($order->isCODOrder()) {
            $partner = $order->cod_partner;
            $data = [
                'charge_method' => CODOrder::CHARGE_METHOD_DEBT,
                'customer_id' => $order->customer_id,
                'inventory' => $request->cod_partner_store_id ?: @$shippingSetup->inventory
            ];
            switch ($partner) {
                case "ghn_5":
                    $partnerData = $this->prepareDataForGHN5FE($request, $order);
                    break;
                case "ghn":
                    $partnerData = $this->prepareDataForGHNFE($request, $order);
                    break;
                case "vtp":
                    $partnerData = $this->prepareDataForVtpFE($request, $order);
                    break;
                case "ghtk":
                    $partnerData = $this->prepareDataForGhtkFE($request, $order);
                    break;
                case "vnpost":
                    $partnerData = $this->prepareDataForVNPostFE($request, $order);
                    break;
                default:
                    $partnerData = [];
            }
            $data = array_merge($data, $partnerData);
            if ($request->cod_partner_store_id) {
                $data['inventory'] = $request->cod_partner_store_id;
            }
            try {
                $this->createBillLading($order->cod_partner, $order, $data);
            } catch (\Exception $exception) {
                throw new CODException($exception->getMessage());
            }
        }
    }

    public function renderUpdateStatusForm(CODOrder $codOrder)
    {
        switch ($codOrder->partner) {
            case CODOrder::PARTNER_GHN_5:
            case CODOrder::PARTNER_GHN:
                $status = [
                    'return' => 'Yêu cầu trả hàng',
                    'cancel' => 'Hủy đơn hàng',
                    'storing' => 'Yêu cầu giao lại'
                ];
                break;
            case CODOrder::PARTNER_VTP:
                $status = [
                    0 => 'Chưa duyệt',
                    1 => 'Duyệt đơn hàng',
                    2 => 'Duyệt chuyển hoàn',
                    3 => 'Phát tiếp',
                    4 => 'Hủy đơn hàng',
                    5 => 'Lấy lại đơn hàng (Gửi lại)',
                    11 => 'Xóa đơn hàng đã hủy'
                ];
                break;
            case CODOrder::PARTNER_GHTK:
                $status = [
                    'cancel' => 'Hủy đơn hàng'
                ];
                break;
            case CODOrder::PARTNER_VNPOST:
                $status = [
                    'cancel' => 'Huỷ đơn hàng'
                ];
                break;
            default:
                $status = [];
                break;
        }
        $status = array_merge($status, ['delete_on_system' => '[AzPro] Xoá vận đơn trên hệ thống']);
        return View::make('la.cod_orders.update_status_modal', compact('status', 'codOrder'))->render();
    }

    protected function getProductForShippingOptionsFE($inputs = [], $callback = null)
    {
        $inputs = array_filter($inputs, function ($input) {
            return @$input['p_id'];
        });
        $inputs = collect($inputs);
        $pIds = $inputs->pluck('p_id');
        return Product::whereIn('id', $pIds)
            ->get()
            ->map(function ($product) use ($inputs, $callback) {
                $input = $inputs->where('p_id', $product->id)->first();
                $product->weight = @$input['weight'] ?? $product->weight;
                $product->length = @$input['length'] ?? $product->length;
                $product->width = @$input['width'] ?? $product->width;
                $product->height = @$input['height'] ?? $product->height;
                $product->input_quantity = @$input['quantity'] ?? 0;

                return $callback ? $callback($product) : $product;
            });
    }

    public function cancelOrder($codOrder)
    {
        $orderCode = $codOrder->order_code;
        $order = $codOrder->order;
        if ($order) {
            try {
                switch ($codOrder->partner) {
                    case CODOrder::PARTNER_GHN:
                        $this->ghnSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode, 'cancel', $codOrder->store_id);
                        $cancelStatus = 'cancel';
                        break;
                    case CODOrder::PARTNER_GHN_5:
                        $this->ghn5Sv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode, 'cancel', $codOrder->store_id);
                        $cancelStatus = 'cancel';
                        break;
                    case CODOrder::PARTNER_GHTK:
                        // GHTK order can only be canceled
                        $this->ghtkSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode);
                        $cancelStatus = '-1';
                        break;
                    case CODOrder::PARTNER_VTP:
                        // VTP's order cancellation status is 4
                        $this->vtpSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($orderCode, 4);
                        $cancelStatus = '201';
                        break;
                    case CODOrder::PARTNER_VNPOST:
                        // VNPost order can only be canceled
                        $vnPostOrderId = @$codOrder->additional_data['id'];
                        if (!$vnPostOrderId) {
                            return redirect()->back()->withErrors(['id' => 'VNPost order id không tồn tại']);
                        }
                        $this->vnpostSv->loadConnection($order->customer, $order->isFromAdmin())->updateStatus($vnPostOrderId);
                        $cancelStatus = '60';
                    default:
                        $cancelStatus = null;
                        break;
                }
                if ($cancelStatus) {
                    $codOrder->status = $cancelStatus;
                    $codOrder->save();
                }
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());
                $codOrder->status = '-2';
                $codOrder->additional_data = array_merge(is_array($codOrder->additional_data) ? $codOrder->additional_data : [], [
                    'mess' => $e->getMessage(),
                ]);
                $codOrder->save();
            }
        }
    }
}
