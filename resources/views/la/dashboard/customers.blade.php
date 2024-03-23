
<div class="box box-success">
	<div class="box-body">
		<table id="example1" class="table table-bordered">
		<thead>
		<tr class="success">
			<th>Người quản lý</th>
			<th>Tài khoản</th>
			<th>Tổng nạp</th>
			<th>Tổng sản lượng</th>
			<th>Tổng nợ</th>
			<th>%</th>
			<th>Ngày nạp</th>
		</tr>
		</thead>
		<tbody>
			@if ($customers->total() > 0)
				@foreach ($customers as $customer)
					<tr>
						<td>{{ $customer->admin_name }}</td>
						<td>{{ $customer->username }}</td>
						<td>{{ number_format($customer->amount) }}đ</td>
						<td>{{ number_format($customer->o_amount) }}đ</td>
						<td>{{ number_format($customer->debt_total) }}đ</td>
						<td>{{ $customer->o_amount > 0 ? round($customer->debt_total/$customer->o_amount, 2)*100 : 0 }}%</td>
						<td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customer->created_at)->format('d/m/Y H:i') }}</td>
					</tr>
				@endforeach
				<tr>
					<td colspan="6" id="customers-pagination">{{ $customers->links() }}</td>
				</tr>
			@else
				<tr>
					<td colspan="6">Không có dữ liệu</td>
				</tr>
			@endif
		</tbody>
		</table>
	</div>
</div>