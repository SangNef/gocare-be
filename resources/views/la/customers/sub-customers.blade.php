<div class="col-md-12">
    <h3>Tổng đơn hàng: {{ number_format($current['number_of_orders']) }}</h3>
    <h3>Doanh số: {{ number_format($current['total_amount']) }}đ</h3>
    <table class="table table-bordered">
        <thead>
            <tr class="success">
                <th>ID</th>
                <th>Tên</th>
                <th>SĐT</th>
                <th>Ngày tạo</th>
                <th>Tổng đơn hàng</th>
                <th>Doanh số</th>
            </tr>
        </thead>
        <tbody>
            @if ($subs->count() > 0)
                @foreach ($subs as $sub)
                    <tr>
                        <td>{{ $sub->id }}</td>
                        <td>{{ $sub->name }}</td>
                        <td>{{ $sub->phone }}</td>
                        <td>{{ $sub->created_at }}</td>
                        <td class="text-right">{{ number_format($sub->number_of_order) }}</td>
                        <td class="text-right">{{ number_format($sub->total_amount) }}đ</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center">Không có dữ liệu phù hợp</td>
                </tr>            
            @endif
            <tr>
                <td colspan="6" class="text-right">{{ $pagination->links() }}</td>
            </tr>
        </tbody>
    </table>
</div>