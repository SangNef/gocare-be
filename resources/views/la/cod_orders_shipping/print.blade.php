@extends('la.layouts.print')
@section('content')
<div class="row" style="border: 1px solid #555555; margin-top: 30px; ">
    <div class="col-xs-12 text-center" style="padding: 0; height: 20px;">
        <div style="display: inline-block; transform: translateX(50px)">
            {{ trans('cod_order_shipping.print_type_' . $sOrder->type) }}
        </div>
        <div style="display: inline-block; float:right; margin-top: 25px; margin-right: 5px">
            <img src="{{ route('qrcode.shipping_order', ['id' => $sOrder->id]) }}"/>
        </div>
    </div>
    <div class="col-xs-12" style="font-size: 13px;">
        <div class="row">
            <div class="col-xs-12 text-left">
                {{ $configs['name'] }}
            </div>
            <div class="col-xs-12 text-left">
                Website: {{ config('app.url') }}
            </div>
            <div class="col-xs-12 text-left">
                <div class="row">
                    <div class="col-xs-12">
                        Đ/C: {{ $configs['address'] }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        SĐT: {{ implode(' - ', array_filter([$configs['sales_phone'], $configs['cs_phone'], $configs['ts_phone']])) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;margin-bottom: 20px;">
            <div class="col-xs-12 text-left">
                <div style="display: inline-block; margin-right: 20px">
                    Đơn vị vận chuyển: {{ trans('cod_order_shipping.partner_' . $sOrder->partner) }}
                </div>
            </div>
        </div>
    </div>
</div>
<table class="table table-bordered" style="margin: 5px 0 0 0;">
    <thead>
        <th>STT</th>
        <th>Tên khách hàng</th>
        <th>Mã vận đơn</th>
        <th>Sản phẩm</th>
        <th>Số lượng</th>
        <th>Tiền thu COD</th>
        <th>Tiền cước</th>
    </thead>
    <tbody>
        @foreach($orders as $key => $order)
            @php
                $rowSpan = $order->row_span;
            @endphp
            @foreach($order->products as $prodKey => $product)
                <tr>
                    @if($prodKey === 0)
                    <td rowspan="{{ $rowSpan }}">{{ ++$key }}</td>
                    <td rowspan="{{ $rowSpan }}">{{ $order->customer_name }}</td>
                    <td rowspan="{{ $rowSpan }}">{{ $order->bill_code }}</td>
                    @endif
                    <td>{{ $product['name'] }}</td>
                    <td class="text-center">{{ $product['quantity'] }}</td>
                    @if($prodKey === 0)
                    <td class="text-right" rowspan="{{ $rowSpan }}">{{ number_format($order->cod_amount) }} đ</td>
                    <td class="text-right" rowspan="{{ $rowSpan }}">{{ number_format($order->fee_amount) }} đ</td>
                    @endif
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2" class="strong">Tổng :</td>
            <td colspan="1" class="text-right total_cod strong">
                <strong>{{ number_format($sOrder->bill_data['total_cod']) }} đ</strong>
            </td>
            <td colspan="1" class="text-right total_fee strong">
            <strong>{{ number_format($sOrder->bill_data['total_fee']) }} đ</strong>
            </td>
        </tr>
    </tfoot>
</table>
<div class="row"> <div class="col-xs-6"></div>
    <div class="col-xs-6 text-center">
        @php
            $createdAt = \Carbon\Carbon::parse($sOrder->created_at);
        @endphp
        <small><i>Ngày {{ $createdAt->day }} tháng {{ $createdAt->month }} năm {{ $createdAt->year }}</i></small><br/>
    </div>
    <div class="col-xs-6 text-center">
        Người nhận hàng
    </div>
    <div class="col-xs-6 text-center">
        Nhân viên bán hàng
    </div>
</div>
@endsection
