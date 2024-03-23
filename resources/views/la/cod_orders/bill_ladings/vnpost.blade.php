<form action="{{ route('co.create-bill', ['id' => $order->id, 'partner' => 'vnpost']) }}" method="POST" id="vnpost-form" class="cod-form">
    {{ csrf_field() }}
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Đẩy đơn hàng lên VNPost</h4>
    </div>
    <div class="modal-body">
        <div class="error alert alert-danger" style="display: none"></div>
        <div class="row">
            <div class="col-sm-6">
                <div class="sender-info" style="padding-bottom: 10px">
                    <h4>Thông tin gửi hàng</h4>
                    <div class="form-group">
                        <label for="sender-list" style="margin-right: 20px">
                            Chọn người gửi
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#VnpostSender" type="button">Thêm</button>
                        </label>
                        <select id="sender-list" class="form-control submit-required">
                            <option selected disabled>Chọn người gửi</option>
                            @foreach ($senderList as $existSender)
                                <option data-id="{{ $existSender['SenderId'] }}" value="{{ json_encode($existSender) }}">{{ implode(' - ', array_only($existSender, ['SenderFullname', 'SenderTel', 'SenderAddress'])) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="sender" style="display: none">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label for="status" style="margin-right: 20px">Tên người gửi * :</label>
                                    <input type="text" class="form-control" id="SenderFullname" value="" disabled>
                                    <input type="hidden" name="SenderFullname" value="">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="status" style="margin-right: 20px">SĐT * :</label>
                                    <input type="text" id="SenderTel" class="form-control" value="" disabled>
                                    <input type="hidden" name="SenderTel" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="status" style="margin-right: 20px">Địa chỉ * :</label>
                                    <input type="text" id="SenderAddress" value="" class="form-control" disabled>
                                    <input type="hidden" name="SenderAddress" value="">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="province">Tỉnh/Thành phố * :</label>
                                    <input type="text" value="" id="SenderProvinceName" class="form-control" disabled/>
                                    <input type="hidden" value="" name="SenderProvinceId" class="getprice-required"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="province">Quận/Huyện * :</label>
                                    <input type="text" value="" id="SenderDistrictName" class="form-control" disabled/>
                                    <input type="hidden" value="" name="SenderDistrictId" class="getprice-required"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="province">Xã/Phường * :</label>
                                    <input type="text" value="" id="SenderWardName" class="form-control" disabled/>
                                    <input type="hidden" value="" name="SenderWardId"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="margin-right: 10px">Hình thức thu tiền :</label>
                        <label style="margin-right: 10px"><input type="radio" value="1" name="charge_method" checked>Thu COD</label>
                        <label><input type="radio" value="2" name="charge_method">Tính vào công nợ</label>
                    </div>
                </div>
                <div class="receiver-info">
                    <h4>Thông tin khách hàng</h4>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Tên khách hàng * :</label>
                                <input type="text" name="ReceiverFullname" class="form-control submit-required" placeholder="Nhập tên khách hàng" value="{{ $address->name }}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">SĐT * :</label>
                                <input type="text" name="ReceiverTel" class="form-control submit-required" placeholder="Nhập số điện thoại" value="{{ $address->phone }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Địa chỉ * :</label>
                                <input type="text" name="ReceiverAddress" class="form-control submit-required" placeholder="Nhập địa chỉ" value="{{ $address->address }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Tỉnh/Thành phố * :</label>
                                <select class="partner-province form-control select2 getprice-required submit-required" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="ReceiverProvinceId" tabindex="-1" aria-hidden="true">
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
                                <select class="partner-district form-control select2 getprice-required submit-required" data-placeholder="Enter Quận/Huyện" rel="select2" name="ReceiverDistrictId" tabindex="-1" aria-hidden="true">
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
                                <select class="partner-ward form-control select2 submit-required" data-placeholder="Enter Xã/Phường" rel="select2" name="ReceiverWardId" tabindex="-1" aria-hidden="true">
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
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Cho khách xem hàng :</label>
                            <label style="margin-right: 10px"><input type="radio" value="1" name="IsPackageViewable" checked>Có</label>
                            <label><input type="radio" value="0" name="IsPackageViewable">Không</label>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Hình thức thu gom :</label>
                            <label style="margin-right: 10px"><input type="radio" value="1" name="PickupType" checked>Thu gom tận nơi</label>
                            <label><input type="radio" value="2" name="PickupType">Gửi hàng tại bưu cục</label>
                        </div>
                        <div class="col-sm-12" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Người nhận chịu phí vận chuyển :</label>
                            <label style="margin-right: 10px"><input type="radio" class="getprice-required" value="1" name="IsReceiverPayFreight" checked>Có</label>
                            <label><input type="radio" class="getprice-required" value="0" name="IsReceiverPayFreight">Không</label>
                        </div>
                        <div class="col-sm-12 hidden" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Dùng dịch vụ báo phát :</label>
                            <label style="margin-right: 10px"><input type="radio" class="getprice-required" value="1" name="UseBaoPhat" disabled>Có</label>
                            <label><input type="radio" class="getprice-required" value="0" name="UseBaoPhat" checked>Không</label>
                        </div>
                        <div class="col-sm-12 hidden" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Dùng dịch vụ hoá đơn :</label>
                            <label style="margin-right: 10px"><input type="radio" class="getprice-required" value="1" name="UseHoaDon" disabled>Có</label>
                            <label><input type="radio" class="getprice-required" value="0" name="UseHoaDon" checked>Không</label>
                        </div>
                        <div class="col-sm-12 hidden" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Dùng dịch vụ nhắn SMS cho người nhận trước phát :</label>
                            <label style="margin-right: 10px"><input type="radio" class="getprice-required" value="1" name="UseNhanTinSmsNguoiNhanTruocPhat" disabled>Có</label>
                            <label><input type="radio" class="getprice-required" value="0" name="UseNhanTinSmsNguoiNhanTruocPhat" checked>Không</label>
                        </div>
                        <div class="col-sm-12 hidden" style="padding-bottom: 20px">
                            <label style="margin-right: 10px">Dùng dịch vụ nhắn SMS cho người nhận sau phát :</label>
                            <label style="margin-right: 10px"><input type="radio" class="getprice-required" value="1" name="UseNhanTinSmsNguoiNhanSauPhat" disabled>Có</label>
                            <label><input type="radio" class="getprice-required" value="0" name="UseNhanTinSmsNguoiNhanSauPhat" checked>Không</label>
                        </div>
                        <div class="col-sm-6" style="padding-bottom: 20px">
                            <strong>Tổng tiền thu hộ</strong>
                            <input id="CodAmountEvaluation" class="form-control currency getprice-required cod_amount" name="CodAmountEvaluation" value="{{ $codAmount }}"/>
                        </div>
                        <div class="col-sm-6" style="padding-bottom: 20px">
                            <strong>Tổng tiền hàng</strong>
                            <input id="OrderAmountEvaluation" class="form-control currency getprice-required" name="OrderAmountEvaluation" value="{{ $order->cod_price_statement }}"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tổng khối lượng *:</label>
                                <input class="form-control getprice-required submit-required" name="WeightEvaluation" value="{{ $products->sum('weight') }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tổng chiều cao *:</label>
                                <input class="form-control getprice-required submit-required" name="HeightEvaluation" value="{{ $products[0]['height'] }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tổng chiều dài *:</label>
                                <input class="form-control getprice-required submit-required" name="LengthEvaluation" value="{{ $products[0]['length'] }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Tổng chiều rộng *:</label>
                                <input class="form-control getprice-required submit-required" name="WidthEvaluation" value="{{ $products[0]['width'] }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Dịch vụ (Tính cước) * :</label>
                                <select name="ServiceName" class="form-control getprice submit-required">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="note">Mã đơn hàng :</label>
                                <input class="form-control" name="OrderCode" readonly value="{{ $order->code }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note">Nội dung đơn hàng :</label>
                        <textarea class="form-control" placeholder="Enter nội dung đơn hàng" cols="30" rows="2" name="PackageContent" style="white-space: pre-wrap;">{!! $packageContent !!}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="note">Ghi chú :</label>
                        <textarea class="form-control" placeholder="Enter Ghi chú" cols="30" rows="2" name="CustomerNote">{{ $order->note }}</textarea>
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
                            <th width="40%">Tên sản phẩm</th>
                            <th>Khối lượng (g)</th>
                            <th>KL theo KT</th>
                            <th>Chiều dài (cm)</th>
                            <th>Chiều rộng (cm)</th>
                            <th>Chiều cao (cm)</th>
                            <th>Giá tiền</th>
                            <th>Số lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $key => $product)
                            <tr>
                                <td>
                                    <strong>{{ $product['product_name'] }}</strong>
                                </td>
                                <td>
                                    {{ $product['weight'] }}
                                </td>
                                <td class="weight-by-size">
                                    {{ ceil($product['length'] * $product['width'] * $product['height'] / 6) }}g
                                </td>
                                <td>
                                    {{ $product['length'] }}
                                </td>
                                <td>
                                    {{ $product['width'] }}
                                </td>
                                <td>
                                    {{ $product['height'] }}
                                </td>
                                <td>
                                    {{ number_format($product['price']) . ' đ' }}
                                </td>
                                <td>
                                    {{ $product['quantity'] }}
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
            <input type="hidden" name="cod_partner" value="vnpost">
            <input type="hidden" name="quantity" value="{{ $products->sum('quantity')}}">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success"Lưu/button>
        </div>
    </div>
</form>

<script>
let vnpostXhrPools = [];
function vnpostAbortRequest() {
    vnpostXhrPools.map(function(jqXHR) {
        jqXHR.abort();
    });
    vnpostXhrPools = [];
}

function vnpostGetPrice() {
    const formData = $('#vnpost-form').serializeObject();
    $.ajax({
        method: 'GET',
        url: '{{ route("co.get-price", ["partner" => "vnpost"]) }}',
        data: {
            SenderDistrictId: formData.SenderDistrictId,
            SenderProvinceId: formData.SenderProvinceId,
            ReceiverDistrictId: formData.ReceiverDistrictId,
            ReceiverProvinceId: formData.ReceiverProvinceId,
            Weight: formData.WeightEvaluation,
            Width: formData.WidthEvaluation,
            Length: formData.LengthEvaluation,
            Height: formData.HeightEvaluation,
            CodAmount: formData.CodAmountEvaluation,
            IsReceiverPayFreight: formData.IsReceiverPayFreight,
            OrderAmount: formData.OrderAmountEvaluation,
            UseBaoPhat: formData.UseBaoPhat,
            UseHoaDon: formData.UseHoaDon,
            UseNhanTinSmsNguoiNhanTruocPhat: formData.UseNhanTinSmsNguoiNhanTruocPhat,
            UseNhanTinSmsNguoiNhanSauPhat: formData.UseNhanTinSmsNguoiNhanSauPhat,
            order_id: '{{ $order->id }}',
            oClass: String.raw`{{ get_class($order) }}`
        },
        beforeSend: function(jqXHR) {
            vnpostAbortRequest();
            vnpostXhrPools.push(jqXHR);
            $('#vnpost-form .getprice').empty();
            $('.error').empty().hide();
        },
        success: function(res) {
            for (id in res) {
                const selected = id === 'DONG_GIA';
                $('#vnpost-form .getprice').append(`<option value="${id}" ${selected ? "selected" : ""} price="${res[id]['price']}">${res[id]['name']}</option>`);
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

function applySender(senderData) {
    $('#vnpost-form #sender').show();
    for (key in senderData) {
        $(`#vnpost-form #sender input[name="${key}"]`).val(senderData[key]).change();
        $(`#vnpost-form #${key}`).val(senderData[key]).change();
    }
}

$(function() {
    $('#vnpost-form .getprice-required').on('change', function() {
        vnpostGetPrice();
    });

    if(
        $('#vnpost-form .partner-province').val()
        && $('#vnpost-form .partner-district').val()
        && $('#vnpost-form .sender-province').val()
        && $('#vnpost-form .sender-district').val()
    ) {
        vnpostGetPrice();
    }

    createNewSender(function(data) {
        const { SenderFullname, SenderTel, SenderAddress, SenderId } = data;
        const option = new Option(`${SenderFullname} - ${SenderTel} - ${SenderAddress}`, SenderId, true, true);
        $('#vnpost-form #sender-list').append(option);
        applySender(data);
    });

    $(document).on('change', '#sender-list', function() {
        const senderData = JSON.parse($(this).val());
        applySender(senderData);
    });

    @if($shippingSetupInventory)
    const ssi = "{{ $shippingSetupInventory }}";
    $(`#vnpost-form #sender-list option[data-id="${ssi}"]`).prop('selected', true).change();
    @endif
})
</script>
