<?php

namespace App\Http\Controllers\ApiV2;

use App\Http\Requests\ActivateProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\CODOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\ProductAttribute;
use App\Models\ProductCombo;
use App\Models\ProductGroupAttributeMedia;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\StoreProduct;
use App\Repositories\ProductSeriesRepository;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestWarranty;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use App\Models\RequestWarranty as requestWarrantyModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $guard = 'customer';
    protected $productSeriesRp;
    protected $productRp;

    public function __construct(
        ProductSeriesRepository $productSeriesRp,
        ProductRepository $productRp
    ) {
        $this->middleware('api-v2', ['except' => [
            'getProductBySeri'
        ]]);
        $this->productSeriesRp = $productSeriesRp;
        $this->productRp = $productRp;
    }

    public function getProductBySku($sku, Request $request)
    {
        $product = Product::where('sku', $sku)->first();
        if (!$product) {
            return response()->json([], 404);
        }
        $stores = $product->stores()->where('sharing', 1)->get();
        $quantity = $stores ? $stores->sum('pivot.n_quantity') : 0;
        $gallery = json_decode($product->product_gallery, true);
        $customer = Customer::find($request->has('user_id'));
        $storeQuantity = array_values(array_filter(array_values($stores->map(function ($store) {
            return [
                'store_id' => $store->id,
                'name' => $store->name,
                'quantity' => $store->pivot->n_quantity,
            ];
        })->toArray()), function ($item) {
            return $item['quantity'] > 0;
        }));
        if (!empty($storeQuantity)) {
            $quantity = array_sum(array_map(function ($item) {return $item['quantity'];}, $storeQuantity));
        }
        $firstCategory = $product->categories->first();

        $productData = $product ? [
            'p_id' => $product->id,
            'name' => $product->name,
            'status_text' => $product->status_text,
            'sku' => $product->sku,
            'price' => $request->has('user_id') ? (int) $product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true) : $product->retail_price,
            'price_for_ctv' => $request->has('user_id') ? (int) ($product->getLastestPriceForCustomer($request->user_id, true) ?? $product->price) : $product->retail_price,
            'quantity' => $quantity,
            'store_quantity' => $storeQuantity,
            'stock_status' => $quantity > 0 ? true : false,
            'desc' => html_entity_decode($product->desc),
            'short_desc' => html_entity_decode($product->short_desc),
            'seri' => [],
            'featured_image' => $product->getFeaturedImagePath(),
            'product_gallery' => $product->getProductGallery(),
            'cate_id' => $firstCategory ? $firstCategory->id : '',
            'cate_name' => $firstCategory ? $firstCategory->name : '',
            'height' => $product->height,
            'weight' => $product->weight,
            'width' => $product->width,
            'length' => $product->length,
            'attributes' => $product->attrs
                ->map(function (ProductAttribute $attribute) {
                    $values = AttributeValue::where('attribute_id', $attribute->attribute_id)
                        ->whereIn('id', explode(',', $attribute->attribute_value_id))
                        ->get()
                        ->map(function ($v) {
                            return [
                                'id' => $v->id,
                                'value' => $v->value
                            ];
                        });
                    return [
                        'id' => $attribute->attribute_id,
                        'text' => $attribute->attr->name,
                        'values' => $values
                    ];
                }),
            'group_attribute_media' => ProductGroupAttributeMedia::where('product_id', $product->id)
                ->get()
                ->map(function ($media) use ($gallery) {
                    $images = explode(',', $media->media_ids);
                    $images = array_map(function ($uploadId) use ($gallery) {
                        return array_search($uploadId, $gallery);
                    }, $images);
                    return [
                        'attr_value_ids' => $media->attribute_value_ids,
                        'media' => $images
                    ];
                }),
            'group_attribute_extra' => StoreProductGroupAttributeExtra::where('product_id', $product->id)
                ->where('store_id', $customer ? $customer->store_id : Store::first()->id)
                ->get()
                ->map(function ($extra) {
                    return [
                        'attr_value_ids' => $extra->attribute_value_ids,
                        'quantity' => (int) $extra->n_quantity,
                    ];
                }),
            'combos' => $product->combos
                ->map(function (ProductCombo $combo) use ($customer) {
                    $related = json_decode($combo->related, true);
                    $relatedProducts = [];
                    foreach ($related as $product) {
                        $quantity = $product[1];
                        $result = Product::find($product[0]);
                        if ($result) {
                            $result->p_id = $result->id;
                            $result->price = request('user_id') ? (int) $result->getPriceForCustomerGroup('khách_hàng_Điện_tử', true) : $result->retail_price;
                            $result->price_for_ctv = request('user_id') ? (int) $result->getPriceForCustomerGroup('khách_hàng_Điện_tử', true) : $result->retail_price;
                            $result->required_quantity = $quantity;
                            $relatedProducts[] = $result;
                        }
                    }

                    $combo->related = $relatedProducts;
                    $discount = $customer ? DB::table('product_combo_groups')
                        ->where('combo_id', $combo->id)
                        ->where('group_id', $customer->group_id)
                        ->first() : null;

                    $combo->discount = $discount ? $discount->discount : 0;

                    return $combo;
                })->filter(function (ProductCombo $combo) use ($customer) {
                    return count($combo->related) > 0;
                })->values(),
        ] : [];

        return response()->json($productData);
    }

    public function getProductBySeri($id, $seri, \App\Services\Upload $uploadSv)
    {
        $pSeri = $this->productSeriesRp->getBySeri($seri);
        $activateToEarn = false;
        $orderedAt = $pSeri->ordered_at ? Carbon::createFromFormat('Y-m-d H:i:s', $pSeri->ordered_at) : null;
        $start = Carbon::createFromFormat('Y/m/d', config('app.revenue_statistic_for_npp.start'));
        $end = Carbon::createFromFormat('Y/m/d', config('app.revenue_statistic_for_npp.end'));
        if ($orderedAt && $pSeri->order && $pSeri->order->customer->can_create_sub && $pSeri->ordered_at && $orderedAt->between($start, $end)
        ) {
            $activateToEarn = true;
        }
        return $pSeri && $pSeri->product
            ? response()->json([
                'p_id' => $pSeri->product->id,
                'name' => $pSeri->product->name,
                'sku' => $pSeri->product->sku,
                'desc' => $pSeri->product->desc,
                'status_text' => $pSeri->product->status_text,
                'short_desc' => $pSeri->product->short_desc,
                'featured_image' => $pSeri->product->getFeaturedImagePath($pSeri->group_attribute_id),
                'seri' => [[
                    'activated_at' => $pSeri->activated_at ? Carbon::parse($pSeri->activated_at)->format('d/m/Y') : '',
                    'expired_at' => $pSeri->expired_at ? Carbon::parse($pSeri->expired_at)->format('d/m/Y') : '',
                    'seri_number' => $pSeri->seri_number,
                    'customer_name' => $pSeri->name,
                    'customer_phone' => $this->productSeriesRp->getCustomerPhone($pSeri->phone),
                    'customer_province' => $this->productSeriesRp->getCustomerProvince($pSeri->province),
                    'activate_to_earn' => $activateToEarn,
                ]]
            ])
            : response()->json([], 422);
    }

    public function activateWarrantyForSeri($seri, ActivateProductRequest $request)
    {
        $pSeri = $this->productSeriesRp->activeWarrantyBySeri($seri, $request->all());
        return $pSeri
            ? response()->json([
                'activated_at' => $pSeri->activated_at->format('d/m/Y'),
                'expired_at' => $pSeri->expired_at->format('d/m/Y'),
                'customer_name' => $pSeri->name,
                'customer_phone' => $this->productSeriesRp->getCustomerPhone($pSeri->phone),
                'customer_province' => $this->productSeriesRp->getCustomerProvince($pSeri->province)
            ])
            : response()->json([
                'Mã seri hoặc mã kích hoạt không hợp lệ'
            ], 422);
    }

    public function index(Request $request, \App\Services\Upload $uploadSv)
    {
        $results = $this->productRp->getProducts($request->all(), $request->get('order_by', []), $request->get('page', 1), $request->get('perpage', 8));

        if ($results['items']) {
            $results['items'] = $results['items']->map(function ($product) use ($uploadSv, $request) {
                return [
                    'p_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'status_text' => $product->status_text,
                    'price' => $request->has('user_id') ? $product->getPriceForCustomerGroup('khách_hàng_Điện_tử', true) : $product->retail_price,
                    'desc' => $product->desc,
                    'short_desc' => $product->short_desc,
                    'seri' => [],
                    'featured_image' => $product->featured_image ? $uploadSv->getImagePath($product->featured_image) . '?s=600' : '',
                    'attributes' => $product->attrs
                        ->map(function (ProductAttribute $attribute) {
                            $values = AttributeValue::where('attribute_id', $attribute->attribute_id)
                                ->whereIn('id', explode(',', $attribute->attribute_value_id))
                                ->get()
                                ->map(function ($v) {
                                    return [
                                        'id' => $v->id,
                                        'value' => $v->value
                                    ];
                                });
                            return [
                                'id' => $attribute->attribute_id,
                                'text' => $attribute->attr->name,
                                'values' => $values
                            ];
                        }),
                ];
            });
        }

        return response()->json($results);
    }

    public function quickSearch(Request $request, \App\Services\Upload $uploadSv)
    {
        $results = $this->productRp->getProducts($request->all(), $request->get('order_by', []), $request->get('page', 1));

        if ($results['items']) {
            $results['items'] = $results['items']->map(function ($product) use ($uploadSv, $request) {
                return [
                    'p_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'seri' => [],
                    'featured_image' => $product->featured_image ? $uploadSv->getImagePath($product->featured_image) . '?s=600' : ''
                ];
            });
        }

        return response()->json($results);
    }

    public function getAvailableStores(Request $request)
    {
        $customer = Auth::guard($this->guard)->user();
        if ($customer && $customer->hasOwnedTransportation()) {
            $stores = [
                [
                    'id' => 0,
                    'name' => 'Kho của tôi',
                    'cods' => $customer->getAvailableOwnedTransportation(),
                    'available' => 1
                ]
            ];
            $stores = array_map(function ($store) use($request) {
                if (is_array($request->p_ids)) {
                    $totalWeight = 0;
                    foreach($request->p_ids as $pid ) {
                        $quantity = @$pid['attr_ids']
                            ? StoreProductGroupAttributeExtra::where('product_id', $pid['id'])
                                ->where('attribute_value_ids', $pid['attr_ids'])
                                ->where('store_id', $store['id'])
                                ->first()
                            : StoreProduct::where('product_id', $pid['id'])
                                ->where('store_id', $store['id'])
                                ->first();
//                        if (!$quantity || $quantity->n_quantity < $pid['quantity']) {
//                            $store['available'] = false;
//                        }
                        $product = Product::find($pid['id']);
                        $totalWeight += $product->weight;
                    }
                    if ($totalWeight >= 5000) {
                        $store['cods'] = array_filter($store['cods'], function ($item) {
                            return  $item['provider'] != CODOrder::PARTNER_GHN_5;
                        });
                    } else {
                        $store['cods'] = array_filter($store['cods'], function ($item) {
                            return  $item['provider'] != CODOrder::PARTNER_GHN;
                        });
                    }
                }
                return $store;
            }, $stores);
        } else {
            if ($customer) {
                $stores[] = [
                    'id' => $customer->store_id,
                    'name' => $customer->store->name,
                    'cods' => $customer->store->getAvailableTransportation()
                ];
                $sharedStore = Store::where('sharing', 1)
                    ->where('id', '<>', $customer->store_id)
                    ->get()
                    ->map(function ($item) use ($customer) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'cods' => $item->getAvailableSharingTransportation($customer->store_id)
                        ];
                    })
                    ->filter(function ($item) {
                        return count($item['cods']) > 0;
                    })
                    ->toArray();
                $stores = $sharedStore ? array_merge($stores, $sharedStore) : $stores;
            } else {
                $store = Store::first();
                $stores[] = [
                    'id' => $store->id,
                    'name' => $store->name,
                    'cods' => collect([])
                ];
            }
            $stores = array_map(function ($store) use($request) {
                $store['available'] = 1;
                if (is_array($request->p_ids)) {
                    $totalWeight = 0;
                    foreach($request->p_ids as $pid ) {
                        $quantity = @$pid['attr_ids']
                            ? StoreProductGroupAttributeExtra::where('product_id', $pid['id'])
                                ->where('attribute_value_ids', $pid['attr_ids'])
                                ->where('store_id', $store['id'])
                                ->first()
                            : StoreProduct::where('product_id', $pid['id'])
                                ->where('store_id', $store['id'])
                                ->first();
                        if (!$quantity || $quantity->n_quantity < $pid['quantity']) {
                            $store['available'] = false;
                        }
                        $product = Product::find($pid['id']);
                        $totalWeight += $product->weight;
                    }
                    if ($totalWeight >= 5000) {
                        $store['cods'] = $store['cods']->filter(function ($item) {
                            return  $item['provider'] != CODOrder::PARTNER_GHN_5;
                        });
                    } else {
                        $store['cods'] = $store['cods']->filter(function ($item) {
                            return  $item['provider'] != CODOrder::PARTNER_GHN;
                        });
                    }
                }
                return $store;
            }, $stores);
        }

        return response()->json($stores);
    }
}
