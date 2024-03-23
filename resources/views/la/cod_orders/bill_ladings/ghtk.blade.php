<form action="{{ route('co.create-bill', ['id' => $order->id, 'partner' => 'ghtk']) }}" method="POST" id="ghtk-form" class="cod-form">
    {{ csrf_field() }}
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Đẩy đơn hàng lên GHTK</h4>
    </div>
    <div class="modal-body">
        <div class="error alert alert-danger" style="display: none"></div>
        <div class="row">
            <div class="col-sm-7">
                <div class="sender-info" style="padding-bottom: 10px">
                    <h4>Thông tin gửi hàng</h4>
                    <div class="form-group">
                        <label for="status">Chọn kho hàng * : </label>
                        <select name="inventory" class="inventory form-control getprice-required submit-required">
                            @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
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
                                <input type="text" name="name" class="form-control submit-required" colname="name" placeholder="Nhập tên khách hàng" value="{{ $address->name }}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">SĐT * :</label>
                                <input type="text" name="phone" class="form-control submit-required" colname="phone" placeholder="Nhập số điện thoại" value="{{ $address->phone }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Địa chỉ * :</label>
                                <input type="text" name="address" class="form-control getprice-required submit-required" id="address" placeholder="Nhập địa chỉ" value="{{ $address->address }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="partner-province">Tỉnh/Thành phố * :</label>
                                <select id="ghtk_province" class="partner-province form-control select2 getprice-required submit-required" data-placeholder="Chọn Tỉnh/Thành phố" rel="select2" name="province" tabindex="-1" aria-hidden="true">
                                    @foreach($provinces as $key => $value)
                                        <option value="{{ $key }}" @if($address->province == $key) selected="selected" @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ghtk_district">Quận/Huyện * :</label>
                                <select id="ghtk_district" class="partner-district form-control select2 getprice-required submit-required" data-placeholder="Chọn Quận/Huyện" rel="select2" name="district" tabindex="-1" aria-hidden="true">
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
                                <label for="ghtk_ward">Xã/Phường * :</label>
                                <select id="ghtk_ward" class="partner-ward form-control select2 submit-required" data-placeholder="Chọn Xã/Phường" rel="select2" name="ward" tabindex="-1" aria-hidden="true">
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
            <div class="col-sm-5">
                <div class="transfer-info" style="padding-bottom: 10px">
                    <h4>Thông tin giao hàng</h4>
                    <div class="row">
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Hình thức vận chuyển :</label>
                            <label style="margin-right: 10px"><input type="radio" value="road" name="transport" checked>Đường bộ</label>
                            <label><input type="radio" value="fly" name="transport">Đường bay</label>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Freeship :</label> 
                            <label style="margin-right: 10px">
                                <input type="radio" value="0" name="count_fee">
                                Không
                            </label>
                            <label>
                                <input type="radio" value="1" name="count_fee" checked>
                                Có
                            </label>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Hàng dễ vỡ :</label> 
                            <label style="margin-right: 10px">
                                <input type="radio" value="0" name="tag" class="getprice-required" @if (!@$order->cod_tag) checked @endif>
                                Không
                            </label>
                            <label>
                                <input type="radio" value="1" name="tag" class="getprice-required" @if (@$order->cod_tag) checked @endif>
                                Có
                            </label>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <strong>Tổng tiền thu hộ</strong>
                            <input class="cod_amount form-control currency" name="cod_amount" value="{{ number_format($codAmount) }} đ"/>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <strong>Tổng giá trị đơn hàng</strong>
                            <input id="insurance_value" class="form-control currency getprice-required submit-required" name="total" value="{{ number_format($order->cod_price_statement) }} đ"/>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 20px">Tổng phí vận chuyển:</label>
                            <input name="fee_total" class="form-control currency" value="0" disabled/>
                        </div>
                        <div class="col-sm-12">
                            <label for="note">Ghi chú:</label>
                            <textarea class="form-control" placeholder="Enter ghi chú" cols="30" rows="3" name="note" id="ghtk_note"></textarea>
                        </div>
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
                            <th>KL - <span style="color: red">Tối đa 50kg</span></th>
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
                                    <input type="text" class="form-control getprice-required submit-required item-weight" name="items[{{$key}}][weight]" value="{{ $product['weight'] }}">
                                </td>
                                <td class="weight-by-size">
                                    {{ ceil($product['length'] * $product['width'] * $product['height'] / 4000) }}kg
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
                        <tr>
                            <td colspan="9" class="text-danger">* Tổng khối lượng đơn hàng không được vượt quá 50 kg.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="col-sm-12" style="text-align: right;">
            <input type="hidden" name="cod_partner" value="ghtk">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success"Lưu/button>
        </div>
    </div>
</form>

<script>
    var weightUnit = 4000;
    let ghtkXhrPools = [];
    function ghtkAbortRequest() {
        ghtkXhrPools.map(function(jqXHR) {
            jqXHR.abort();
        });
        ghtkXhrPools = [];
    }

    function getPrice()
    {
        const formData = $('#ghtk-form').serializeObject();
        let totalWeight = formData.items.reduce(function(total, product) {
            return total + Number(product.weight);
        }, 0);
        if (formData.province && formData.district) {
            $.ajax({
                type: 'GET',
                url: '{{ route("co.get-price", ["partner" => "ghtk"]) }}',
                data: {
                    pick_address_id: formData.inventory,
                    province: $('#ghtk_province option:selected').text(),
                    district: $('#ghtk_district option:selected').text(),
                    address: formData.address,
                    weight: totalWeight * 1000,
                    value: convertNumberInputValue(formData.total),
                    order_id: '{{ $order->id }}',
                    oClass: String.raw`{{ get_class($order) }}`,
                    products: formData.items,
                    transport: formData.transport,
                    tag: formData.tag,
                },
                beforeSend: function(jqXHR) {
                    ghtkAbortRequest();
                    ghtkXhrPools.push(jqXHR);
                    $('#ghtk-form input[name="fee_total"]').val('0 đ');
                    $('.error').empty().hide();
                },
                success: function(res) {
                    $('#ghtk-form input[name="fee_total"]').val(res.fee.toLocaleString() + ' đ');
                },
                error: function(xhr) {
                    const error = xhr.responseJSON;
                    if (typeof error !== 'undefined') {
                        $('.error').text(error.message).show();
                    }
                }
            });
        }
    }
$(function() {
    $('#ghtk-form').submit((e) => {
        // convert weight to g
        if ($('#warranty-order').length < 1) {
            $('#ghtk-form .item-weight').each(function () { 
                $(this).val($(this).val() * 1000);
            });
        }
    });
    $('#ghtk_province').change(function() {
        let id = $(this).find('option:selected').val();
        $('#ghtk_district option:not(:first),#ghtk_ward option:not(:first)').remove();
        getAddress(id, 'province', '#ghtk_district', '{{ route('customer.get-address') }}');
    });

    $('#ghtk_district').change(function() {
        let id = $(this).find('option:selected').val();
        $('#ghtk_ward option:not(:first)').remove();
        getAddress(id, 'district', '#ghtk_ward', '{{ route('customer.get-address') }}');
    });

    if ($('#ghtk_province').val() && $('#ghtk_district').val())
    {
        getPrice();
    }

    $('#ghtk-form .getprice-required').change(function() {
        getPrice();
    });
});
</script>
