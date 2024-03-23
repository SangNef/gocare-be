<form action="{{ route('co.create-bill', ['id' => $order->id, 'partner' => 'vtp']) }}" method="POST" id="viettelpost-form" class="cod-form">
    {{ csrf_field() }}
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Đẩy đơn hàng lên Viettel Post</h4>
    </div>
    <div class="modal-body">
        <div class="error alert alert-danger" style="display: none"></div>
        <div class="row">
            <div class="col-sm-6">
                <div class="sender-info" style="padding-bottom: 10px">
                    <h4>Thông tin gửi hàng</h4>
                    <div class="form-group">
                        <label for="status">Chọn kho hàng * : </label>
                        <select name="inventory" class="inventory form-control getprice-required submit-required">
                            @foreach($stores as $store)
                            <option 
                                value="{{ $store['group_id'] }}" 
                                data-province-id="{{ $store['province_id'] }}"
                                data-district-id="{{ $store['district_id'] }}"
                            >
                                {{ $store['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="margin-right: 10px">Hình thức thu tiền :</label>
                        <label style="margin-right: 10px"><input type="radio" value="1" name="charge_method" checked>Thu COD</label>
                        <label><input type="radio" value="2" name="charge_method">Tính vào công nợ</label>
                    </div>
                    <div class="form-group">
                        <label for="status">Ngày giao hàng: </label>
                        <input class="form-control datetime-picker" name="DELIVERY_DATE" type="text" value="{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}" />
                    </div>
                </div>
                <div class="receiver-info">
                    <h4>Thông tin khách hàng</h4>
                    <div class="form-group">
                        <input type="checkbox" name="update_customer_info" id="update-customer-info">
                        <label for="update-customer-info">Cập nhật lại thông tin của khách hàng</label>
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Tên khách hàng * :</label>
                                <input type="text" name="RECEIVER_FULLNAME" class="form-control submit-required" placeholder="Nhập tên khách hàng" value="{{ $address->name }}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">SĐT * :</label>
                                <input type="text" name="RECEIVER_PHONE" class="form-control submit-required" placeholder="Nhập số điện thoại" value="{{ $address->phone }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Địa chỉ * :</label>
                                <input type="text" name="RECEIVER_ADDRESS" class="form-control submit-required" placeholder="Nhập địa chỉ" value="{{ $address->address }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Tỉnh/Thành phố * :</label>
                                <select class="partner-province form-control select2 getprice-required submit-required" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="RECEIVER_PROVINCE" tabindex="-1" aria-hidden="true">
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
                                <select class="partner-district form-control select2 getprice-required submit-required" data-placeholder="Enter Quận/Huyện" rel="select2" name="RECEIVER_DISTRICT" tabindex="-1" aria-hidden="true">
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
                                <select class="partner-ward form-control select2 submit-required" data-placeholder="Enter Xã/Phường" rel="select2" name="RECEIVER_WARDS" tabindex="-1" aria-hidden="true">
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
                                <label for="note">Loại sản phẩm :</label>
                                <select name="PRODUCT_TYPE" class="form-control getprice-required">
                                    <option value="HH" selected>Hàng hóa</option>
                                    <option value="TH">Thư</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="">Số kiện hàng *:</label>
                                <input type="number" class="form-control submit-required package-quantity" name="PRODUCT_QUANTITY" min="1" value="{{ $products->sum('quantity') }}"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="note">Mã đơn hàng :</label>
                                <input class="form-control" name="ORDER_NUMBER" readonly value="{{ $order->code }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Loại vận đơn * :</label>
                                <select name="ORDER_PAYMENT" class="form-control">
                                    <option value="1">Không thu tiền</option>
                                    <option value="2">Thu hộ tiền cước và tiền hàng</option>
                                    <option value="3">Thu hộ tiền hàng</option>
                                    <option value="4">Thu hộ tiền cước</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Dịch vụ (Tính cước) * :</label>
                                <select name="ORDER_SERVICE" class="form-control getprice submit-required">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note">Ghi chú :</label>
                        <textarea id="order_note" class="form-control" placeholder="Enter Ghi chú" cols="30" rows="2" name="ORDER_NOTE">{{ $order->note }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <strong>Tổng tiền thu hộ</strong>
                            <input id="MONEY_COLLECTION" class="cod_amount form-control currency getprice-required" name="MONEY_COLLECTION" value="{{ $codAmount }}"/>
                        </div>
                        <div class="col-sm-3" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label>Tổng khối lượng</label>
                                <input class="form-control getprice-required total-weight submit-required" name="PRODUCT_WEIGHT" value="{{ $products->sum('weight') }}" readonly data-default-value="{{ $products->sum('weight') }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label>Chiều cao *:</label>
                                <input class="form-control getprice-required total-height submit-required" name="PRODUCT_HEIGHT" value="{{
                                    $products[0]['height']
                                }}" data-default-value="{{ $products->count() == 1 ? $products[0]['height'] : 0 }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label>Chiều dài *:</label>
                                <input class="form-control getprice-required total-length submit-required" name="PRODUCT_LENGTH" value="{{
                                    $products[0]['length']
                                }}" data-default-value="{{ $products->count() == 1 ? $products[0]['length'] : 0 }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3 form-group" style="padding-bottom: 20px">
                            <div class="form-group">
                                <label>Chiều rộng *:</label>
                                <input class="form-control getprice-required total-width submit-required" name="PRODUCT_WIDTH" value="{{
                                    $products[0]['width']
                                }}" data-default-value="{{ $products->count() == 1 ? $products[0]['width'] : 0 }}"/>
                            </div>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <strong>Tổng tiền hàng</strong>
                            <input id="PRODUCT_PRICE" class="form-control currency getprice-required" name="PRODUCT_PRICE" value="{{ $order->cod_price_statement }}" readonly/>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h4>Thông tin sản phẩm</h4>
                <input type="hidden" name="PRODUCT_NAME" value="{{ $products->implode('product_name', ' + ') }}">
            </div>
            <div class="product-info col-sm-12">
                <table class="table table-bordered">
                    <thead>
                        <tr class="success">
                            <th width="40%">Tên sản phẩm</th>
                            <th>Khối lượng (g) *</th>
                            <th>Giá tiền *</th>
                            <th>Số lượng *</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $key => $product)
                            <tr>
                                <td>
                                    <strong>{{ $product['product_name'] }}</strong>
                                    <input type="hidden" name="LIST_ITEM[{{$key}}][PRODUCT_NAME]" value="{{ $product['product_name'] }}">
                                </td>
                                <td>
                                    <input class="form-control product-weight" name="LIST_ITEM[{{$key}}][PRODUCT_WEIGHT]" value="{{ $product['weight'] }}"/>
                                </td>
                                <td>
                                    <input readonly class="form-control currency" name="LIST_ITEM[{{$key}}][PRODUCT_PRICE]" value="{{ $product['price'] }}"/>
                                </td>
                                <td>
                                    <input readonly class="form-control" name="LIST_ITEM[{{$key}}][PRODUCT_QUANTITY]" value="{{ $product['quantity'] }}"/>
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
            <input type="hidden" name="cod_partner" value="vtp">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success"Lưu/button>
        </div>
    </div>
</form>

<script>
let vtpXhrPools = [];
function vtpAbortRequest() {
    vtpXhrPools.map(function(jqXHR) {
        jqXHR.abort();
    });
    vtpXhrPools = [];
}

function vtpGetPrice() {
    const formData = $('#viettelpost-form').serializeObject();
    let senderProvince = $('#viettelpost-form .inventory option:selected').data('province-id');
    let senderDistrict = $('#viettelpost-form .inventory option:selected').data('district-id');
    $.ajax({
        method: 'GET',
        url: '{{ route("co.get-price", ["partner" => "vtp"]) }}',
        data: {
            MONEY_COLLECTION: formData.MONEY_COLLECTION,
            PRODUCT_PRICE: formData.PRODUCT_PRICE,
            SENDER_PROVINCE: senderProvince,
            SENDER_DISTRICT: senderDistrict,
            PRODUCT_WEIGHT: formData.PRODUCT_WEIGHT,
            PRODUCT_LENGTH: formData.PRODUCT_LENGTH,
            PRODUCT_HEIGHT: formData.PRODUCT_HEIGHT,
            PRODUCT_WIDTH: formData.PRODUCT_WIDTH,
            PRODUCT_TYPE: formData.PRODUCT_TYPE,
            RECEIVER_PROVINCE: formData.RECEIVER_PROVINCE,
            RECEIVER_DISTRICT: formData.RECEIVER_DISTRICT,
            order_id: '{{ $order->id }}',
            oClass: String.raw`{{ get_class($order) }}`
        },
        beforeSend: function(jqXHR) {
            vtpAbortRequest();
            vtpXhrPools.push(jqXHR);
            $('#viettelpost-form .getprice').empty();
            $('.error').empty().hide();
        },
        success: function(res) {
            for (id in res) {
                $('#viettelpost-form .getprice').append("<option value='" + id + "' price='"+ res[id]['price'] +"'>" + res[id]['name'] + "</option>");
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON;
            if (typeof error !== 'undefined') {
                $('.error').text(error.message).show();
            }
        }
    })
}

$(function() {
    $('#viettelpost-form .getprice-required').on('change', function() {
        vtpGetPrice();
    });

    $(document).on('change', '.product-weight', function() {
        let total = 0;
        $('.product-weight').each(function() {
            total += parseInt($(this).val(), 10);
        })
        $('.total-weight').val(total).change();
    });

    $(document).on('change', '.total-weight, .total-width, .total-length, .total-height', function() {
        $(this).attr('data-default-value', $(this).val());
    });

    $(document).on('change', '.package-quantity', function() {
        let quantity = Number($(this).val());
        $('.total-weight, .total-width, .total-length, .total-height').each(function() {
            let value = $(this).data('default-value');
            $(this).val(value * quantity).change();
        });
    });

    if($('#viettelpost-form .partner-province').val() && $('#viettelpost-form .partner-district').val()) {
        vtpGetPrice();
    }
})
</script>
