<form action="{{ route('co.create-bill', ['id' => $order->id, 'partner' => 'ghn_5']) }}" method="POST" id="ghn-5-form" class="cod-form">
    {{ csrf_field() }}
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Đẩy đơn hàng lên GHN cho đơn < 5kg</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="sender-info" style="padding-bottom: 10px">
                    <h4>Thông tin gửi hàng</h4>
                    <div class="form-group">
                        <label for="status">Chọn kho hàng * : </label>
                        <select name="inventory" class="inventory form-control getprice-required submit-required">
                            @foreach($stores as $store)
                            <option value="{{ $store['id'] }}" data-district="{{ $store['district_id'] }}" @if(@$store['selected']) selected="selected" @endif>{{ $store['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="margin-right: 10px">Hình thức thu tiền :</label>
                        <label style="margin-right: 10px"><input type="radio" value="1" name="charge_method" checked>Thu COD</label>
                        <label><input type="radio" value="2" name="charge_method">Tính vào công nợ</label>
                    </div>
                </div>
                <div class="receiver-info">
                    <h4>Thông tin khách hàng</h4>
                    <div class="form-group">
                        <input type="checkbox" name="update_customer_info" id="update-customer-info">
                        <label for="update-customer-info">Cập nhật lại thông tin của khách hàng</label>
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="hidden" name="update_customer_info_detail" value="" id="update_customer_info_detail" />
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Tên khách hàng * :</label>
                                <input type="text" name="to_name" class="form-control submit-required" colname="name" placeholder="Nhập tên khách hàng" value="{{ $address->name }}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">SĐT * :</label>
                                <input type="text" name="to_phone" class="form-control submit-required" colname="phone" placeholder="Nhập số điện thoại" value="{{ $address->phone }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Địa chỉ * :</label>
                                <input type="text" name="to_address" class="form-control submit-required" colname="address" placeholder="Nhập địa chỉ" value="{{ $address->address }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="partner-province">Tỉnh/Thành phố * :</label>
                                <select colname="province" class="partner-province form-control select2 getprice-required submit-required" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="to_province" tabindex="-1" aria-hidden="true">
                                    <option value="">Chọn Tỉnh/Thành phố</option>
                                    @foreach($partnerProvinces as $key => $value)
                                    <option value="{{ $key }}" data-id="{{ $key }}" @if($address->province == $key) selected="selected" @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district">Quận/Huyện * :</label>
                                <select colname="district" class="partner-district form-control  select2 getprice-required submit-required" data-placeholder="Enter Quận/Huyện" rel="select2" name="to_district_id" tabindex="-1" aria-hidden="true">
                                    @if ($address->district)
                                        <option value="{{ $address->district }}">{{ $address->district_name }}</option>
                                    @else
                                        <option class="selected" value="">Chọn Quận/Huyện</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Xã/Phường * :</label>
                                <select colname="ward" class="partner-ward form-control select2 getprice-required submit-required" data-placeholder="Enter Xã/Phường" rel="select2" name="to_ward_code" tabindex="-1" aria-hidden="true">
                                    @if ($address->ward)
                                        <option value="{{ $address->ward }}">{{ $address->ward_name }}</option>
                                    @else
                                        <option class="selected" value="">Chọn Xã/Phường</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="transfer-info" style="padding-bottom: 10px">
                    <h4>Thông tin giao hàng</h4>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="note">Lưu ý bắt buộc * :</label>
                                <select name="required_note" class="form-control submit-required">
                                    <option value="CHOTHUHANG">Cho thử hàng</option>
                                    <option value="CHOXEMHANGKHONGTHU">Cho xem hàng không thử</option>
                                    <option value="KHONGCHOXEMHANG">Không cho xem hàng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="">Người trả phí vận chuyển *:</label>
                                <select name="payment_type_id" class="form-control submit-required">
                                    <option value="1" @if($order->fee_bearer == 2) checked @endif>Người bán</option>
                                    <option value="2" @if($order->fee_bearer == 1) checked @endif>Người mua</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="note">Mã giảm giá :</label>
                                <input class="form-control getprice-required" name="coupon" value="" placeholder="Nhập mã giảm giá"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label for="">Khối lượng (gram) *</label>
                                <input type="number" class="form-control getprice-required total_weight submit-required" name="weight" readonly="true" value="{{ $products->sum('weight') }}"/>
                            </div>
                        </div>
                        @php($selectedSize = $products->sortByDesc('size')->first())
                        <div class="col-sm-3" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label for="">Chiều dài (cm) *</label>
                                <input type="number" class="form-control getprice-required total_length submit-required" name="length" readonly="true"  value="{{ $selectedSize['length'] }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label>Chiều cao (cm) *</label>
                                <input type="number" class="form-control getprice-required total_height submit-required" name="height" readonly="true"  value="{{ $selectedSize['height'] }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label>Chiều rộng (cm) *</label>
                                <input type="number" class="form-control getprice-required total-width submit-required" name="width" readonly="true" value="{{ $selectedSize['width'] }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6" style="padding-bottom: 20px">
                            <strong>Tổng tiền thu hộ</strong>
                            <input class="cod_amount form-control currency" name="cod_amount" value="{{ number_format($codAmount) }} đ"/>
                        </div>
                        <div class="col-sm-6" style="padding-bottom: 20px">
                            <strong>Tổng giá trị đơn hàng</strong>
                            <input id="insurance_value" class="form-control currency getprice-required submit-required" name="insurance_value" value="{{ number_format($order->cod_price_statement) }} đ"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="service_id" style="margin-right: 20px">Dịch vụ * :</label>
                                <select name="service_id" class="form-control getprice-required submit-required">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label style="margin-right: 20px">Tổng phí vận chuyển:</label>
                                <input class="form-control currency" value="0" name="fee_total" disabled/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note">Nội dung đơn hàng * :</label>
                        <textarea class="form-control submit-required" placeholder="Enter nội dung" cols="30" rows="2" name="content">{{ $products->implode('note', ', ') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="note">Ghi chú:</label>
                        <textarea class="form-control" placeholder="Enter ghi chú" cols="30" rows="2" name="note" id="ghn_note"></textarea>
                    </div>
                </div>
            </div> 
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h4>Thông tin sản phẩm</h4>
            </div>
            <div class="product-info col-sm-12">
                <table class="table table-bordered">
                    <thead>
                        <tr class="success">
                            <th width="30%">Tên sản phẩm</th>
                            <th>Giá tiền</th>
                            <th>KL</th>
                            <th>KL theo KT</th>
                            <th>Dài</th>
                            <th>Cao</th>
                            <th>Rộng</th>
                            <th>Mã sản phẩm</th>
                            <th>Số lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $key => $product)
                            <tr class="item-size">
                                <td>
                                    <strong>{{ $product['product_name'] }}</strong>
                                    <input type="hidden" name="items[{{$key}}][name]" value="{{ $product['product_name'] }}">
                                </td>
                                <td>
                                    <strong>{{ $product['price'] }}</strong>
                                </td>
                                <td>
                                    <input type="number" class="form-control getprice-required submit-required item-weight" name="items[{{$key}}][weight]" value="{{ $product['weight'] }}">
                                </td>
                                <td class="weight-by-size">    
                                    {{ ceil($product['length'] * $product['width'] * $product['height'] / 6) }}g
                                </td>
                                <td>
                                    <input type="number" class="form-control getprice-required submit-required item-length" name="items[{{$key}}][length]" value="{{ $product['length'] }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control getprice-required submit-required item-height" name="items[{{$key}}][height]" value="{{ $product['height'] }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control getprice-required submit-required item-width" name="items[{{$key}}][width]" value="{{ $product['width'] }}">
                                </td>
                                <td>
                                    <strong>{{ $product['sku'] }}</strong>
                                    <input type="hidden" class="form-control" name="items[{{$key}}][code]" value="{{ $product['sku'] }}"/>
                                </td>
                                <td>
                                    <strong>{{ $product['quantity'] }}</strong>
                                    <input type="hidden" class="form-control" name="items[{{$key}}][quantity]" value="{{ $product['quantity'] }}"/>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="col-sm-12" style="text-align: right;">
            <input type="hidden" name="cod_partner" value="ghn">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success"Lưu/button>
        </div>
    </div>
</form>

<script>
    let ghn5XhrPools = [];
    function ghn5AbortRequest() {
        ghn5XhrPools.map(function(jqXHR) {
            jqXHR.abort();
        });
        ghn5XhrPools = [];
    }

    function getGhn5Services(shopId, fromDistrict, toDistrict)
    {
        return $.ajax({
            type: 'GET',
            url: '{{ route("co.ghn-services") }}',
            data: {
                shop_id: shopId,
                from_district: fromDistrict,
                to_district: toDistrict,
                order_id: '{{ $order->id }}',
                oClass: String.raw`{{ get_class($order) }}`
            },
            beforeSend: function(jqXHR) {
                $('#ghn-5-form .transfer-info select[name="service_id"]').empty();
            },
            dataType: 'JSON',
            success: function(data) {
                for ([id, value] of Object.entries(data)) {
                    $('#ghn-5-form .transfer-info select[name="service_id"]').append("<option value='" + id + "'>" + value + "</option>");
                }
            }
        })
    }

    function ghn5GetPrice(el)
    {
        const formData = $('#ghn-5-form').serializeObject();
        var senderDistrict = $('#ghn-5-form .inventory option:selected').data('district');
        if (el && ['inventory', 'to_district_id'].indexOf(el.attr('name')) !== -1) {
            getGhn5Services(formData.inventory, senderDistrict, formData.to_district_id)
                .then(() => {
                    $('#ghn-5-form .transfer-info select[name="service_id"]').change();
                });
        }
        $.ajax({
            type: 'GET',
            url: '{{ route("co.get-price", ["partner" => 'ghn_5']) }}',
            data: {
                service_id: formData.service_id,
                from_district_id: senderDistrict,
                to_district_id: formData.to_district_id,
                to_ward_code: formData.to_ward_code,
                height: formData.height,
                length: formData.length,
                weight: formData.weight,
                width: formData.width,
                insurance_value:  formData.insurance_value,
                coupon: formData.coupon,
                inventory: formData.inventory,
                order_id: '{{ $order->id }}',
                oClass: String.raw`{{ get_class($order) }}`
            },
            beforeSend: function(jqXHR) {
                ghn5AbortRequest();
                ghn5XhrPools.push(jqXHR);
                $('#ghn-5-form input[name="fee_total"]').val('0 đ');
            },
            success: function(res) {
                $('#ghn-5-form input[name="fee_total"]').val(res.toLocaleString() + ' đ');
            }
        });
    }
$(function() {
    if ($('#ghn-5-form .partner-province').val() && $('#ghn-5-form .partner-district').val()) {
        const formData = $('#ghn-5-form').serializeObject();
        var senderDistrict = $('#ghn-5-form .inventory option:selected').data('district');
        getGhn5Services(formData.inventory, senderDistrict, formData.to_district_id)
            .then(() => {
                if ('{{ @$order->cod_service_id }}') {
                    $('#ghn-5-form .transfer-info select[name="service_id"]').val('{{ $order->cod_service_id }}').change();
                } else {
                    $('#ghn-5-form .transfer-info select[name="service_id"]').change();
                }
                ghn5GetPrice();
            });
    }
    $('#ghn-5-form .getprice-required').change(function() {
        ghn5GetPrice($(this));
    });
});
</script>
