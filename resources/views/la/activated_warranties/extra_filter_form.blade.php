<div class="form-group">
    <label>
        Ngày kích hoạt bảo hành:
    </label>
    <div class="row">
        <div class="col-sm-4">
            <input type="text" name="filter[0][value]" placeholder="Từ ngày" class="form-control input-sm datepicker"/>
            <input type="hidden" name="filter[0][field]" value="activated_at"/>
            <input type="hidden" name="filter[0][operation]" value="gt"/>
        </div>
        <div class="col-sm-4">
            <input type="text" name="filter[1][value]"placeholder="Đến ngày" class="form-control input-sm datepicker"/>
            <input type="hidden" name="filter[1][field]" value="activated_at"/>
            <input type="hidden" name="filter[1][operation]" value="lt"/>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-4">
            <label>
                Ngày kích hoạt bảo hành (Tuần):
            </label>
            <select name="filter_week" class="form-control" placeholder="{{ trans('messages.all') }}">
                <option value="" selected>Tất cả</option>
                <option value="{{ \Carbon\Carbon::now()->startOfWeek()->subWeek()->format('Y/m/d') }}">Tuần trước</option>
                <option value="{{ \Carbon\Carbon::now()->startOfWeek()->format('Y/m/d') }}">Tuần này</option>
            </select>
        </div>
    </div>
</div>