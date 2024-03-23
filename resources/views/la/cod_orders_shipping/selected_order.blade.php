@foreach ($cOrders as $cOrder)
@php
    $order = $cOrder->order_type === 'WarrantyOrderProductSeri'
        ? $cOrder->warrantyOrderProductSeriActualOrder()
        : $cOrder->order;
@endphp
<input type="hidden" name="partner_ids[]" value="{{ $cOrder->id }}">
<tr class="selected-order" data-order-id="{{ $cOrder->id }}">
    <td>{{ $cOrder->order_code }}</td>
    <td>{{ $cOrder->customer->name }}</td>
    <td>{{ $cOrder->created_at }}</td>
    <td>{{ @$order->code }}</a></td>
    <td class="text-center">{{ $cOrder->getNumberOfProducts() }}</td>
    <td>
        <input class="form-control currency cod_amount" type="text" name="bill_data[{{ $cOrder->id }}][cod_amount]" value="{{ $cOrder->cod_amount }}">
    </td>
    <td>
        <input class="form-control currency fee_amount" type="text" name="bill_data[{{ $cOrder->id }}][fee_amount]" value="{{ $cOrder->fee_amount }}">
    </td>
</tr>
@endforeach
