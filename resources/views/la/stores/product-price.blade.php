<h3>Sản phẩm: {{ $product->name }}</h3>
<h5>Sku: {{ $product->sku }}</h3>
<table class="table table-bordered">
    <thead>
        <tr class="success">
            <th rowspan="2" class="text-center">Nhóm khách hàng</th>
            <th colspan="2" class="text-center">Giá</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupPrices as $index => $group)
            <tr>
                <td>{{ $group->display_name }}</td>
                <td>
                    <input class="form-control border-none" value="{{ $group->discount }}" name="groups[{{ $index }}][price]" />
                    <input type="hidden" value="{{ $group->id }}" name="groups[{{ $index }}][id]" />
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<input type="hidden" name="product_id" value="{{ request('product_id') }}" />