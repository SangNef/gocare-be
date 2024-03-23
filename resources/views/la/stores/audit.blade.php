@if ($audits->count() > 0)
@foreach($audits as $audit)
	<tr>
		<td>{{ $audit->order_id }}</td>
		<td>{{ number_format($audit->amount) }}</td>
		<td>{{ number_format($audit->balance) }}</td>
		<td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
	</tr>
@endforeach
	<tr>
		<td colspan="4">{{ $audits->links() }}</td>
	</tr>
@else
	<tr>
		<td colspan="4" class="text-center">Không có dữ liệu</td>
	</tr>
@endif