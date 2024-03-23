<table class="table table-bordered voucher-products">
    <thead>
        <tr class="success">
            <th class="text-center">STT</th>
            <th class="text-center">SKU</th>
            <th class="text-center">Tên sản phẩm</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        @if ($voucher->getProducts()->count() > 0)
            @foreach ($voucher->getProducts() as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    @if (@$edit)
                        <td>
                            <a class="btn btn-danger voucher-product-item"><i class="fa fa-trash"></i></a>
                            <input type="hidden" name="product_ids[]" value="{{ $product->id }}" />
                        </td>
                    @endif
                </tr>
            @endforeach
        @else
            <tr>
                <td class="text-center" colspan="4">Không có dữ liệu</td>
            </tr>
        @endif
    </tbody>
</table>