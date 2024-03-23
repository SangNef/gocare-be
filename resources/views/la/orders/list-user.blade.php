@forelse($customers as $customer)
    <tr>
        <td>{{ $customer->name }}</td>
        <td><span>{{ number_format($customer->discount) . ' đ' }}</span><a data-id="{{ $customer->id }}" class="btn btn-sm edit"><i class="fa fa-pencil"></i></a></td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center">No data</td></tr>
@endforelse
<tr>
    <td colspan="3">
        {{ $customers->links() }}
    </td>
</tr>