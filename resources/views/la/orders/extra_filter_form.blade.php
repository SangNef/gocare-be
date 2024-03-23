<div class="form-group">
    <div class="row">
        <div class="col-sm-4">
            <label>
                Sản phẩm:
            </label>
            <select name="products[]" multiple class="form-control ajax-select" model="product" placeholder="{{ trans('messages.all') }}">
            </select>
        </div>
        <div class="col-sm-4">
            <label>
                Danh mục sản phẩm:
            </label>
            <select name="pc_ids[]" multiple class="form-control ajax-select filter-category-id" model="productcategory" placeholder="{{ trans('messages.all') }}">
            </select>
        </div>
        <div class="col-sm-1">
            <label>
                &nbsp;
            </label>
            <button class="btn btn-warning btn-xs print-orders onetime-click form-control">IN</button>
        </div>
        <div class="col-sm-3">
            <label>
                &nbsp;
            </label>
            @if (request('from') == 1)
                <button value="2" class="btn btn-warning btn-xs print-selected-orders onetime-click form-control text-small">In đơn được chọn</button>
            @endif
        </div>
    </div>
</div>