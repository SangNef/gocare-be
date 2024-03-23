<div class="row bg-gray" id="product-filter">
    <form id="product-filter-form" method="get">
        <div class="col-sm-3">
            <div class="form-group">
                <label for="action">{{ trans('messages.product_category') }} :</label><select
                        class="form-control select2-hidden-accessible"
                        data-placeholder="{{ trans('messages.product_category') }}" rel="select2" name="filter[0][value]"
                        tabindex="-1" aria-hidden="true">
                    <option value="">{{ trans("messages.all") }}</option>
                    @foreach(\App\Models\ProductCategory::all() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="filter[0][field]" value="category_ids"/>
                <input type="hidden" name="filter[0][operation]" value="like"/>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label for="price">Tên :</label>
                <input class="form-control" value="" name="filter[1][value]"/>
                <input type="hidden" name="filter[1][field]" value="name"/>
                <input type="hidden" name="filter[1][operation]" value="like"/>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label for="price">Mã :</label>
                <input class="form-control" value="" name="filter[2][value]"/>
                <input type="hidden" name="filter[2][field]" value="sku"/>
                <input type="hidden" name="filter[2][operation]" value="like"/>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label for="price"><input type="checkbox" name="combo" /> Sản phẩm combo</label>
            </div>
        </div>
    </form>
</div>
<div class="row">
    <div id="product-list">

    </div>
</div>