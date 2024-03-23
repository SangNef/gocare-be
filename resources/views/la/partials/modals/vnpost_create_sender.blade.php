<div class="modal fade" id="VnpostSender" role="dialog" aria-labelledby="VnpostSender">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
			{!! Form::open(['url' => route('store.vnpost-senders.post', ['id' => $store->id]), 'id' => 'VnpostSenderForm']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Tạo địa chỉ gửi hàng</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Tên người gửi * :</label>
                                <input type="text" name="SenderFullname" class="form-control submit-required" placeholder="Nhập tên người gửi" value="">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">SĐT * :</label>
                                <input type="text" name="SenderTel" class="form-control submit-required" placeholder="Nhập số điện thoại" value="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="status" style="margin-right: 20px">Địa chỉ * :</label>
                                <input type="text" name="SenderAddress" class="form-control submit-required" placeholder="Nhập địa chỉ" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Tỉnh/Thành phố * :</label>
                                <select class="sender-province form-control select2-hidden-accessible select2 submit-required" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="SenderProvinceId" tabindex="-1" aria-hidden="true">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district">Quận/Huyện * :</label>
                                <select class="sender-district form-control select2-hidden-accessible select2 submit-required" data-placeholder="Enter Quận/Huyện" rel="select2" name="SenderDistrictId" tabindex="-1" aria-hidden="true">
                                    <option class="selected" value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="province">Xã/Phường * :</label>
                                <select class="sender-ward form-control select2-hidden-accessible select2 submit-required" data-placeholder="Enter Xã/Phường" rel="select2" name="SenderWardId" tabindex="-1" aria-hidden="true">
                                    <option class="selected" value="">Chọn Xã/Phường</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input id="default" type="checkbox" value="1" name="default">
                                <label for="default" style="margin-right: 10px">Địa chỉ mặc định</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div style="text-align: right;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        {!! Form::submit('Submit', ['class'=>'btn btn-success']) !!}
                    </div>
                </div>
			{!! Form::close() !!}
        </div>
    </div>
</div>

@push('scripts')
<script>
const VnpostSenderForm = $('#VnpostSenderForm');
let provinceDeferred, districtDeferred;
const getAddressUrl = codGetAddressUrl.replace(':partner', 'vnpost');

function loadProvince() {
    getShippingPartnerAddress(getAddressUrl, null, 'province')
        .then(function(data) {
            const provinceInput = VnpostSenderForm.find('select.sender-province'); 
            for ([id, value] of Object.entries(data)) {
                provinceInput.append(`<option value="${id}">${value}</option>`);
            }
            provinceInput.change();
        });
}

function createNewSender(callback) {
    VnpostSenderForm.submit(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (checkRequiredInputs(VnpostSenderForm.attr('id'))) {
            const data = {
                _token: "{{ csrf_token() }}",
                ...VnpostSenderForm.serializeObject(),
                SenderProvinceName: VnpostSenderForm.find('select.sender-province option:selected').text(),
                SenderDistrictName: VnpostSenderForm.find('select.sender-district option:selected').text(),
                SenderWardName: VnpostSenderForm.find('select.sender-ward option:selected').text(),
            }
            $.ajax({
                url: VnpostSenderForm.attr('action'),
                data: data,
                type: VnpostSenderForm.attr('method'),
                success: function (data) {
                    VnpostSenderForm.parents('#VnpostSender').modal('hide');
                    VnpostSenderForm[0].reset();
                    if (callback) {
                        callback(data);
                    }
                },
                error: function (data) {
                    alert('There is an error');
                }
            })
        }
    });
}

$(function () {
    loadProvince();
    createNewSender();
    
    VnpostSenderForm.find('select.sender-province').change(function() {
        provinceDeferred = $.Deferred();
        const provinceId = $(this).val();
        VnpostSenderForm.find('select.sender-district, select.sender-ward').find('option').not('.selected').remove();
        getShippingPartnerAddress(getAddressUrl, provinceId, 'district')
            .then(function(data) {
                for ([id, value] of Object.entries(data)) {
                    VnpostSenderForm.find('select.sender-district').append(`<option value="${id}">${value}</option>`);
                }
                provinceDeferred.resolve();
            });
    });

    VnpostSenderForm.find('select.sender-district').change(function() {
        districtDeferred = $.Deferred();
        const districtId = $(this).val();
        VnpostSenderForm.find('select.sender-ward').find('option').not('.selected').remove();
        getShippingPartnerAddress(getAddressUrl, districtId, 'ward')
            .then(function(data) {
                for ([id, value] of Object.entries(data)) {
                    VnpostSenderForm.find('select.sender-ward').append(`<option value="${id}">${value}</option>`);
                }
                districtDeferred.resolve();
            });
    });
})
</script>
@endpush