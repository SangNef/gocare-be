@if ($observes->count() > 0)
@foreach($observes as $observe)
	<tr>
		<td>{{ $observe->customer->id }}</td>
		<td>{{ $observe->customer->name }}</td>
		<td>{{ $observe->customer->phone }}</td>
		<td>{{ number_format($observe->customer->debt_total) }}</td>
		<td>
			<button class="btn btn-sm btn-danger remove-observer" value="{{ $observe->id }}"><i class="fa fa-trash"></i></button>
			<button class="btn btn-sm btn-primary observer-audit" value="{{ $observe->id }}"><i class="fa fa-eye"></i></button>
		</td>
	</tr>
@endforeach
@else
	<tr>
		<td colspan="5" class="text-center">Không có dữ liệu</td>
	</tr>
@endif