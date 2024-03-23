<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <select id="product-attribute" class="form-control ajax-select" model="attribute" multiple>
                @foreach($product->attrs as $pAttribute)
                    <option value="{{ $pAttribute->attribute_id }}" selected>{{ $pAttribute->attr->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-12 mb-2">
        <table class="table table-bordered">
            <thead>
            <tr class="success">
                <th width="25%">{{ trans('product.product_attribute') }}</th>
                <th>{{ trans('product.product_attribute_value') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($product->attrs as $pAttribute)
                <tr>
                    <th>{{ $pAttribute->attr->name }}</th>
                    <th>
                        <div class="form-group">
                            <select class="form-control ajax-select product-attribute-value" id="product-attribute-value-{{ $pAttribute->attribute_id }}" model="attribute_value" extra_param="{{ $pAttribute->attribute_id }}" multiple>
                                @foreach($pAttribute->getValues() as $value)
                                    <option value="{{ $value->id }}" selected>{{ $value->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-12 mb-2">
        <table class="table table-bordered">
            <thead>
            <tr class="success">
                <th width="25%">{{ trans('product.product_group_attribute') }}</th>
                <th>{{ trans('product.media') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($product->attrValues as $attrValue)
                <tr>
                    <th>{{ $attrValue->attribute_value_texts }}</th>
                    <th>
                        <div class="form-group row">
                            @foreach($attrValue->getMedia() as $media)
                                <div class="col-md-2 col-sm-3">
                                    {!! $media !!}
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-link" data-content="{{ $attrValue->id }}" data-toggle="modal" data-target="#add-group-attribute-media">Thêm/Sửa</button>
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>