@if ($customers->count() > 0)
@foreach($customers as $customer)
	<tr>
		<td>{{ $customer->id }}</td>
		<td>{{ $customer->name }}</td>
		<td>{{ $customer->phone }}</td>
		<td>
			<button class="btn btn-sm btn-danger remove-excluded-customer" value="{{ $customer->id }}"><i class="fa fa-trash"></i></button>
		</td>
	</tr>
@endforeach
@else
	<tr>
		<td colspan="4" class="text-center">Không có dữ liệu</td>
	</tr>
@endif