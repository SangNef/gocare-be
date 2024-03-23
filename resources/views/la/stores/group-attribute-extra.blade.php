@if ($groups->count() > 0)
<table class="table table-bordered">
    <thead>
        <tr class="success">
            <th rowspan="2" class="text-center">Thuộc tính</th>
            <th colspan="2" class="text-center">Số lượng</th>
        </tr>
        <tr class="success">
            <th class="text-center">Số lượng s/p mới <span class="text-danger">({{ $storeQuantity->n_quantity }})</span></th>
            <th class="text-center">Số lượng s/p bảo hành<span class="text-danger">({{ $storeQuantity->w_quantity }})</span></th>
        </tr>
    </thead>
    <tbody>
        @foreach($groups as $index => $group)
            <tr>
                <td>{{ $group->attribute_value_texts }}</td>
                <td><input @if ($product->has_series)) disabled @endif class="form-control border-none" value="{{ $group->n_quantity }}" name="quantity[{{ $index }}][n_quantity]" /></td>
                <td>
                    <input @if ($product->has_series)) disabled @endif  class="form-control border-none" value="{{ $group->w_quantity }}" name="quantity[{{ $index }}][w_quantity]" />
                    <input @if ($product->has_series)) disabled @endif  type="hidden" value="{{ $group->attribute_value_ids }}" name="quantity[{{ $index }}][attribute_value_ids]" />
                    <input @if ($product->has_series)) disabled @endif  type="hidden" value="{{ $group->attribute_value_texts }}" name="quantity[{{ $index }}][attribute_value_texts]" />
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
    <div class="form-group">
        <label for="role">Số lượng sản phẩm mới:</label>
        <input @if ($product->has_series)) disabled @endif type="number" class="form-control" name="n_quantity" value="{{ $storeQuantity->n_quantity }}">
    </div>
    <div class="form-group">
        <label for="role">Số lượng sản phẩm bảo hành:</label>
        <input @if ($product->has_series)) disabled @endif type="number" class="form-control" name="w_quantity" value="{{ $storeQuantity->w_quantity }}">
    </div>
@endif
@if (!$product->has_series)
    <input type="hidden" name="product_id" value="{{ request('product_id') }}" />
@endif