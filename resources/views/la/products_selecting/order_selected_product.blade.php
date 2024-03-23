@foreach($products as $key => $product)
@php
    $currency = isset($currencyType) ? $currencyType : request('currency_type', 1);
    $price = $currency == \App\Models\Bank::CURRENCY_VND ? $product->retail_price : $product->price_in_ndt;
    $customerId = isset($order) ? $order->customer_id : request('customer_id', 0);
    $saveForNextOrder = (bool) app(\App\Services\Discount::class)->getOnlyDiscountForCustomer($customerId, $product->id);
    if ($discount = $product->getLastestPriceForCustomer($customerId)) {
        $price = $discount;
    }
    if (isset($lastestPrice)) {
        $price = $lastestPrice;
    }
    $index = @$existedIndex[$key] ?: uniqid();
    $product->has_combo = isset($has_combo) ? $has_combo : $product->has_combo;
    $product->parent_id = isset($parent_id) ? $parent_id : $product->parent_id;
    $product->combo = isset($combo) ? $combo : $product->combo;
    $product->discount_percent = isset($discount_percent) ? $discount_percent : $product->discount_percent;
    $price = @$product->has_combo ? $price - $product->combo->discount : $price;
    $total = $price * (isset($quantity) ? $quantity : 1);
    if (isset($product->discount_percent)) {
        $total *= ((100 - $product->discount_percent) / 100);
    }
@endphp
<div class="selected-product col-sm-12 order-selected-product {{ @$product->has_combo ? 'has-combo' : '' }} {{ @$product->parent_id ? 'in-combo' : '' }}" data-id="{{ $product->id }}" data-index="{{ $index }}">
    <div class="card mb-3">
        <div class="row no-gutters">
            <div class="col-md-6 col-sm-12 product_name" style="padding-top: 10px; @if ($product->parent_id) text-indent: 10px; @endif" >
                <strong class="index" style="color: red"></strong>
                <strong>{{ $product->name }}</strong><br />
            </div>

            <div class="col-md-3 col-sm-12" >
                <textarea class="order-product-note" name="products[{{ $index }}][note]" cols="30" rows="1" placeholder="Ghi chú">{{ isset($note) && $note ? $note : '' }}</textarea>
            </div>
            <div class="col-md-3 col-sm-12 order-product-input text-right p0">
                <span style="margin-right: 15px;display: none">{{ $product->warranty_period }} tháng</span>
                <span style="display: none">{{ ucfirst($product->unit) }}</span>
                <input type="hidden" class="item-key" value="{{ $index }}" />
                <input type="hidden" class="combo_discount" value="{{ @$product->combo ? $product->combo->discount : 0 }}" />
                @if (@$product->has_combo || @$product->parent_id)
                    <input type="hidden" name="products[{{ $index }}][combo_id]" value="{{ $product->combo->id }}" />
                    @if(@$product->parent_id)
                        <input type="hidden" name="products[{{ $index }}][parent_id]" value="{{ $product->parent_id }}" />
                    @endif
                @endif
                <input type="hidden" class="item-product-id" name="products[{{ $index }}][product_id]" value="{{ $product->id }}" min="0">
                <input type="hidden" class="item-cate-id" name="products[{{ $index }}][cate_id]" value="{{ $product->getFirstCategoryId()}}">
                <input type="hidden" name="products[{{ $index }}][weight]" value="{{ @$dimension['weight'] ?? $product->weight }}" min="0">
                <input type="hidden" name="products[{{ $index }}][length]" value="{{ @$dimension['length'] ?? $product->length }}" min="0">
                <input type="hidden" name="products[{{ $index }}][width]" value="{{ @$dimension['width'] ?? $product->width }}" min="0">
                <input type="hidden" name="products[{{ $index }}][height]" value="{{ @$dimension['height'] ?? $product->height }}" min="0">
                @if (isset($sub_type) && $sub_type == \App\Models\Product::NEW_PRODUCT)
                    <input type="number" class="form-control quantity n_quantity" name="products[{{ $index }}][n_quantity]" value="{{ isset($n_quantity) ? $n_quantity : 1 }}" min="0"  @if (@$product->parent_id) readonly @endif old="{{ isset($n_quantity) ? $n_quantity : 1 }}">
                @elseif (isset($sub_type) && $sub_type == \App\Models\Product::WARRANTY_PRODUCT)
                    <input type="number" class="form-control quantity w_quantity" name="products[{{ $index }}][w_quantity]" value="{{ isset($w_quantity) ? $w_quantity : 0 }}" min="0">
                    <input type="number" class="form-control quantity r_quantity" name="products[{{ $index }}][r_quantity]" value="{{ isset($r_quantity) ? $r_quantity : 0 }}" min="0">
                @else
                    <input type="number" class="form-control quantity n_quantity" name="products[{{ $index }}][n_quantity]" value="{{ isset($n_quantity) ? $n_quantity : 1 }}" min="0" @if (@$product->parent_id) readonly @endif old="{{ isset($n_quantity) ? $n_quantity : 1 }}" >
                    <input type="number" class="form-control quantity w_quantity" style="display: none;" name="products[{{ $index }}][w_quantity]" value="{{ isset($w_quantity) ? $w_quantity : 0 }}" min="0">
                    <input type="number" class="form-control quantity r_quantity" style="display: none;" name="products[{{ $index }}][r_quantity]" value="{{ isset($r_quantity) ? $r_quantity : 0 }}" min="0">
                @endif
                <input class="form-control price lastest_price currency" name="products[{{ $index }}][price]" value="{{ $price }}"/>
                <label class="small price-saving" style="display: none">
                    <input type="checkbox" name="products[{{ $index }}][save]" value="1" @if($saveForNextOrder) checked data-default-checked="true" @else data-default-checked="false" @endif/>
                    {{ trans('messages.save_for_next_time') }}
                </label>
                <div class="discount-input" style="display: none">
                    <input type="number" max="100" min="0" class="form-control discount_percent" style="width: 40px" name="products[{{ $index }}][discount_percent]" value="{{ @$product->discount_percent ?: 0 }}"/>
                </div>
                <input type="text" class="form-control price order-product-total" value="{{ number_format($total) }} đ" disabled>
                <button @if (@$product->parent_id) style="visibility: hidden;" @endif type="button" class="remove-selected-product btn btn-light text-red" data-id="{{ $product->id }}" data-index="{{ $index }}"><span aria-hidden="true">X</span></button>
            </div>
            @if ($product->attrs->count() > 0)
                <div class="col-md-12 col-sm-12 product_attrs" style="padding-top: 5px">
                    <div style="display: none" class="product_attrs_value">
                        @foreach($product->attrs as $attr)
                            <div class="form-group">
                                <label>{{ $attr->attr->name }}:</label>
                                <select class="form-control select2 product_attribute" name="products[{{ $index }}][attr_ids][]" attr_name="{{ $attr->attr->name }}">
                                    @foreach($attr->getValues() as $v)
                                        <option value="{{ $v->id }}" @if (isset($selectedAttrs) && isset($selectedAttrs[$key]) && in_array($v->id, $selectedAttrs[$key])) selected @endif>{{ $v->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <span>Phân loại sản phẩm: <span class="product_attrs_text" style="font-size: 0.9em"></span><span style="margin-left: 5px;cursor: pointer" class="fa fa-edit edit-product-attrs" data-index="{{ $index }}"></span>
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
@endforeach
