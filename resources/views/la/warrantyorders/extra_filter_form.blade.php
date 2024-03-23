<div class="form-group">
    <label>
        Ngày trả bảo hành:
    </label>
    <div class="row">
        <div class="col-sm-4">
            <input type="text" name="return_at[from]" placeholder="Từ ngày" class="form-control input-sm datepicker"/>
        </div>
        <div class="col-sm-4">
            <input type="text" name="return_at[to]" placeholder="Đến ngày" class="form-control input-sm datepicker"/>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-4">
            <label>
                Trạng thái seri:
            </label>
            <select name="seri_status" class="form-control input-sm" placeholder="{{ trans('messages.all') }}">
                <option value="" selected>Tất cả</option>
                @foreach (\App\Models\WarrantyOrderProductSeri::getAvailableStatus() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-2" style="margin-top: 24px">
            <button type="button" id="print" type="button" class="btn btn-warning btn-sm onetime-click" style="width: 100%" disabled>
                IN
            </button>
        </div>
        <div class="col-sm-2" style="margin-top: 24px">
            <button type="button" id="bill-lading-multiple" type="button" class="btn btn-warning btn-sm" style="width: 100%">
                Vận đơn
            </button>
        </div>
    </div>
</div>
