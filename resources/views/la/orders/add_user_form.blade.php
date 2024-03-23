<div class="form-group">
    <a style="font-weight: bold; text-decoration: underline" id="new-user-button" type="button" data-toggle="collapse" href="#add-new-user" role="button" aria-expanded="false" aria-controls="add-new-user">Thêm khách hàng mới</a>
    <div id="add-new-user" class="collapse" style="margin-top: 10px">
        <div class="form-group">
            <label for="status" style="margin-right: 20px">Tên user * :</label>
            <input type="text" name="customer_username" class="form-control" placeholder="Nhập username">
            <p style="color: red" id="customer-username-error"></p>
        </div>
        <div class="form-group">
            <label for="status" style="margin-right: 20px">Tên khách hàng* :</label>
            <input type="text" name="customer_name" class="form-control" placeholder="Nhập tên khách hàng" required>
            <p style="color: red" id="customer-name-error"></p>
        </div>
        <div class="form-group">
            <label for="status" style="margin-right: 20px">Email * :</label>
            <input type="email" name="customer_email" class="form-control" placeholder="Nhập tên email">
            <p style="color: red" id="customer-email-error"></p>
        </div>
        <div class="form-group">
            <label for="status" style="margin-right: 20px">SĐT * :</label>
            <input type="text" name="customer_phone" class="form-control" placeholder="Nhập số điện thoại">
            <p style="color: red" id="customer-phone-error"></p>
        </div>
        <div class="form-group">
            <label for="status" style="margin-right: 20px">Địa chỉ :</label>
            <input type="text" name="customer_address" class="form-control" placeholder="Nhập địa chỉ">
            <p style="color: red" id="customer-address-error"></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="province">Tỉnh/Thành phố :</label>
                    <select class="form-control select2-hidden-accessible" data-placeholder="Enter Tỉnh/Thành phố" rel="select2" name="customer_province" id="province" tabindex="-1" aria-hidden="true">
                        <option value="">Chọn Tỉnh/Thành phố</option>
                        @foreach($provinces as $province)
                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="district">Quận/Huyện :</label>
                    <select class="form-control select2-hidden-accessible" data-placeholder="Enter Quận/Huyện" rel="select2" name="customer_district" id="district" tabindex="-1" aria-hidden="true">
                        <option class="selected" value="">Chọn Quận/Huyện</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="province">Xã/Phường :</label>
                    <select class="form-control select2-hidden-accessible" data-placeholder="Enter Xã/Phường" rel="select2" name="customer_ward" id="ward" tabindex="-1" aria-hidden="true">
                        <option class="selected" value="">Chọn Xã/Phường</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="status" style="margin-right: 20px">Nhóm * :</label>
            <select name="group_id" class="form-control ajax-select" model="group" id="group_id" lookup="order_store">
            </select>
            <p style="color: red" id="group-id-error"></p>
        </div>
        <div class="form-group">
            <label for="status" style="margin-right: 20px">Nợ trước :</label>
            <input type="text" value="0" name="debt_in_advance" id="debt_in_advance" class="form-control currency valid" aria-invalid="false">
        </div>
        <div>
            <button type="button" class="btn btn-success" id="add-user-button">Thêm</button>
        </div>
    </div>
</div>