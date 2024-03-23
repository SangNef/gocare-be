<?php

namespace App\Repositories;

use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProductSeri;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarrantyOrderRepository
{
    public function getProcessSeries(WarrantyOrder $wOrder)
    {
        return $wOrder->warrantyOrderProductSeries()
            ->select(
                \DB::raw('
                    count(*) as total, 
                    sum(status = 1) as received, 
                    sum(status = 2) as processing, 
                    sum(status = 3) as processed, 
                    sum(status = 4) as advance_warranty, 
                    sum(status = 5) as returned
                ')
            )
            ->first();
    }

    public function getProcessSeriesPercent(WarrantyOrder $wOrder)
    {
        $processTotal = $this->getProcessSeries($wOrder)->toArray();
        $total = $processTotal['total'];
        unset($processTotal['total']);
        unset($processTotal['warranty_order_id']);

        $results = [];
        $colors = [
            'received' => 'default',
            'processing' => 'primary',
            'processed' => 'warning',
            'advance_warranty' => 'danger',
            'returned' => 'success'
        ];
        foreach ($processTotal as $key => $process) {
            $results[] = [
                'percent' => intval(($process / $total) * 100),
                'label' => trans('status.' . $key),
                'color' => $colors[$key],
                'title' => $process . '/' . $total
            ];
        }

        return $results;
    }

    public function getDataForPrinting(Request $request)
    {
        $results = [];
        $wOrders = WarrantyOrder::with(['warrantyOrderProductSeries' => function ($query) use ($request) {
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('return_at')) {
                if (is_array($request->return_at)) {
                    if (@$request->return_at['from']) {
                        $query->whereDate('return_at', '>=', date($request->return_at['from']));
                    }
                    if (@$request->return_at['to']) {
                        $query->whereDate('return_at', '<=', date($request->return_at['to']));
                    }
                } else {
                    $query->whereDate('return_at', '=', $request->return_at);
                }
            }
            if ($request->has('seri_id')) {
                $id = $request->seri_id;
                $clause = is_array($id) ? 'whereIn' : 'where';
                $query->{$clause}('warranty_order_product_series.id', $id);
            }
        }])
            ->where(function ($query) use ($request) {
                $id = $request->id;
                $clause = is_array($id) ? 'whereIn' : 'where';
                $query->{$clause}('id', $id);
            })
            ->get();
        foreach ($wOrders as $wOrder) {
            foreach ($wOrder->warrantyOrderProductSeries as $wops) {
                // if ($wOrder->codOrder()->exists() || $wops->codOrder()->exists()) {
                    $results[] = [
                        'id' => $wops->id,
                        'order_code' => $wOrder->code,
                        'product_name' => $wops->warrantyOrderProduct->product->name,
                        'seri_number' => $wops->productSeri ? $wops->productSeri->seri_number : '',
                        'error_type' => trans('warranty_order.error_type_' . $wops->error_type),
                        'status' => WarrantyOrderProductSeri::getAvailableStatus()[$wops->status],
                        'note' => $wops->note,
                        'return_at' => $wops->return_at ? $wops->return_at->format('d/m/Y') : '',
                        'created_at' => $wOrder->created_at->format('d/m/Y'),
                    ];
                // }
            }
        }
        return $results;
    }

    public function transformDataForCOD(WarrantyOrder $wOrder, $partner, $products)
    {
        $customer = $wOrder->customer;
        $customerProvinceId = $customer->getProvinceId();
        $customerDistrictId = $customer->getDistrictId();
        $customerWardId = $customer->getWardId();

        switch ($partner) {
            case 'vtp':
                $shipping = app(\App\Services\CODPartners\VTPService::class);
                $partnerAddress = $shipping->loadConnection($customer, true)->convertAddressIdToPartnerId($customerProvinceId, $customerDistrictId, $customerWardId);
                $results = [
                    'DELIVERY_DATE' => Carbon::now()->format('d/m/Y H:i:s'),
                    'RECEIVER_FULLNAME' => $customer->name,
                    'RECEIVER_PHONE' => $customer->phone,
                    'RECEIVER_ADDRESS' => $customer->address,
                    'RECEIVER_PROVINCE' => @$partnerAddress['province'] ?? 0,
                    'RECEIVER_DISTRICT' => @$partnerAddress['district'] ?? 0,
                    'RECEIVER_WARDS' => @$partnerAddress['ward'] ?? 0,
                    'PRODUCT_TYPE' => 'HH',
                    'PRODUCT_QUANTITY' => $products->sum('quantity'),
                    'ORDER_NUMBER' => $wOrder->code,
                    'MONEY_COLLECTION' => 0,
                    'PRODUCT_WEIGHT' => $products->sum('weight'),
                    'PRODUCT_HEIGHT' => $products[0]['height'],
                    'PRODUCT_LENGTH' => $products[0]['length'],
                    'PRODUCT_WIDTH' => $products[0]['width'],
                    'PRODUCT_PRICE' => 0,
                    'PRODUCT_NAME' => implode(', ', $products->pluck('product_name')->toArray()),
                    'LIST_ITEM' => $products->map(function ($item) {
                        return [
                            'PRODUCT_NAME' => $item['product_name'],
                            'PRODUCT_WEIGHT' => $item['weight'],
                            'PRODUCT_PRICE' => $item['price'],
                            'PRODUCT_QUANTITY' => $item['quantity'],
                        ];
                    })
                ];
                break;
            case 'ghn':
            case 'ghn_5':
                $shipping = $partner == 'ghn'
                    ? app(\App\Services\CODPartners\GHNService::class)
                    : app(\App\Services\CODPartners\GHN5Service::class);
                $partnerAddress = $shipping->loadConnection($customer, true)->convertAddressIdToPartnerId($customerProvinceId, $customerDistrictId, $customerWardId);
                $results = [
                    'to_name' => $customer->name,
                    'to_phone' => $customer->phone,
                    'to_address' => $customer->address,
                    'to_province' => @$partnerAddress['province'] ?? 0,
                    'to_district_id' => @$partnerAddress['district'] ?? 0,
                    'to_ward_code' => @$partnerAddress['ward'] ?? 0,
                    'weight' => $products->sum('weight'),
                    'height' => $products->sum('height'),
                    'length' => $products->sum('length'),
                    'width' => $products->sum('width'),
                    'cod_amount' => 0,
                    'insurance_value' => 0,
                    'content' => implode(', ', $products->pluck('product_name')->toArray()),
                    'note' => implode(', ', $products->pluck('note')->toArray()),
                    'items' => $products->map(function ($item) {
                        return [
                            'name' => $item['product_name'],
                            'code' => $item['sku'],
                            'quantity' => $item['quantity'],
                            'weight' => $item['weight'],
                        ];
                    })
                ];
                break;
            case 'ghtk':
                $results = [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'province' => $customerProvinceId,
                    'district' => $customerDistrictId,
                    'ward' => $customerWardId,
                    'cod_amount' => 0,
                    'total' => 0,
                    'products' => $products->map(function ($item) {
                        return [
                            'name' => $item['product_name'],
                            'weight' => $item['weight'],
                            'length' => $item['length'],
                            'width' => $item['width'],
                            'height' => $item['height'],
                            'quantity' => $item['quantity'],
                            'product_code' => ''
                        ];
                    }),
                    'note' => implode(', ', $products->pluck('note')->toArray()),
                ];
                break;
            default:
                $results = [];
                break;
        }
        return $results;
    }
}
