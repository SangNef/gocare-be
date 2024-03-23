@if ($products->total() > 0)
	@foreach($products as $product)
		<tr>
			<td>{{ $product->id }}</td>
			<td>{{ $product->sku }}</td>
			<td>{{ $product->name }}</td>
			<td>
				@if ($product->quantity >= 1)
					<span class="label label-success">Còn hàng</span>
				@else
					<span class="label label-danger">Hết hàng</span>
				@endif
			</td>
			<td>{{ number_format($product->quantity) }}</td>
			<td>{{ number_format($product->n_quantity) }}</td>
			<td>{{ number_format($product->w_quantity) }}</td>
			<td>
				<button type="button" data-target="#updateProductMinimum" id="product_{{ $product->id }}" data-toggle="modal" class="btn btn-sm {{ $product->min > 0 ? 'btn-success' : 'btn-danger' }}">{{ $product->min > 0 ? number_format($product->min) : 'Tắt' }}</button>
				<button type="button" data-target="#viewProductQuantity" id="product_quantity_{{ $product->id }}" data-toggle="modal" class="btn btn-sm btn-success">Số lượng chi tiết</button>
				<button type="button" data-target="#viewProductPrice" id="product_price_{{ $product->id }}" data-toggle="modal" class="btn btn-sm btn-primary">Giá</button>
			</td>
		</tr>
	@endforeach
	<tr>
		<td colspan="8">
			{{ $products->appends(array_merge(request()->all(), ['tp' => 1]))->links() }}
		</td>
	</tr>
@else
	<tr>
		<td colspan="8" class="text-center">
			Không có dữ liệu
		</td>
	</tr>
@endif