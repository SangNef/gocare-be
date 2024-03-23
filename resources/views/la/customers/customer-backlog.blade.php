@if($backlogs->count() > 0)
<table class="table table-bordered">
    <thead>
        <tr class="success">
            <th></th>
            <th>Nhập</th>
            <th>Xuất</th>
            <th>Có</th>
            <th>Nợ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($backlogs as $backlog)
            <tr>
                <td>{{ \App\Models\CustomerBacklog::debtTypeName()[$backlog->debt_type] }}</td>
                <td>{{ number_format($backlog->money_in, 2) . $symbol }}</td>
                <td>{{ number_format($backlog->money_out, 2) . $symbol }}</td>
                <td>{{ number_format($backlog->has, 2) . $symbol }}</td>
                <td>{{ number_format($backlog->debt, 2) . $symbol }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
