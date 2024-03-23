@extends('la.layouts.print')
@section('content')
<div class="row" style="border: 1px solid #555555; margin-top: 30px">
    <div class="col-xs-12 text-center" style="padding: 0; height: 20px;">
        <div>PHIẾU VẬN CHUYỂN</div>
    </div>
    <div class="col-xs-12" style="font-size: 13px;">
        <div class="row">
            <div class="col-xs-12 text-left">
                {{ $store->name }}
            </div>
            <div class="col-xs-12 text-left">
                Website: {{ $store->website_url }}
            </div>
            <div class="col-xs-12 text-left">
                <div class="row">
                    <div class="col-xs-12">
                        Đ/C: {{ $store->address }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        SĐT: {{ $store->owner->phone }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px">
            @if($transportPartner)
            <div class="col-xs-6 text-left">
                <div>
                    Đơn vị vận chuyển: <strong>{{ $transportPartner->name }}</strong>
                </div>
                <div>
                    SĐT: <strong>{{ $transportPartner->phone }}</strong>
                </div>
                <div>
                    Địa chỉ: <strong>{{ $transportPartner->getFullAddress() }}</strong>
                </div>
            </div>
            @endif
            @if($receiver)
            <div class="col-xs-6 text-left">
                <div>
                    Khách hàng: <strong>{{ $receiver->name }}</strong>
                </div>
                <div>
                    SĐT: <strong>{{ $receiver->phone }}</strong>
                </div>
                <div>
                    Địa chỉ: <strong>{{ $receiver->getFullAddress() }}</strong>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<table class="table table-bordered" style="margin: 5px 0 0 0;">
    <thead>
        <th style="padding: 2px; text-align: center;">STT</th>
        <th width="40%">Sản phẩm</th>
        <th width="3%">SL</th>
        <th>SK</th>
        <th>KL</th>
        <th>Dài</th>
        <th>Rộng</th>
        <th>Cao</th>
        <th>Tổng khối</th>
        <th>Tổng kg</th>
        <th>Giá/đv</th>
        <th style="white-space: nowrap;">Tổng tiền</th>
    </thead>
    <tbody>
        @foreach ($transportProducts as $key => $product)
            <tr>
                <td style="padding: 2px" class="text-center">{{ $key+1 }}</td>
                <td>{{ $product['product_name'] }}</td>
                <td class="text-right">{{ $product['quantity'] }}</td>
                <td class="text-right"> {{ $product['packages'] }}</td>
                <td class="text-right">{{ $product['weight'] }}</td>
                <td class="text-right">{{ $product['length'] }}</td>
                <td class="text-right">{{ $product['width'] }}</td>
                <td class="text-right">{{ $product['height'] }}</td>
                <td class="text-right">{{ $product['total_cubic_meter'] }}</td>
                <td class="text-right"> {{ $product['total_weight'] }}</td>
                <td class="text-right" style="white-space: nowrap;">
                    {{ number_format($product['price']) }} {{ $symbol }}
                </td>
                <td class="text-right" style="white-space: nowrap;">
                    {{ number_format($product['total']) }} {{ $symbol }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2"><strong>Tổng:</strong></td>
            <td class="total-quantity text-danger text-right strong">{{ array_sum(array_column($transportProducts, 'quantity')) }}</td>
            <td class="total-packages text-danger text-right strong">{{ array_sum(array_column($transportProducts, 'packages')) }}</td>
            <td colspan="4"></td>
            <td class="total-cubicmeter text-danger text-right strong">{{ array_sum(array_column($transportProducts, 'total_cubic_meter')) }}</td>
            <td class="total-kilo text-danger text-right strong">{{ array_sum(array_column($transportProducts, 'total_weight')) }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td colspan="2">Thành tiền:</td>
            <td colspan="2" class="text-right">{{ number_format($transport->total) }} {{ $symbol }}</td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td colspan="2">Thanh toán:</td>
            <td colspan="2" class="text-right">{{ number_format($transport->transport_price) }} {{ $symbol }}</td>
        </tr>
    </tbody>
</table>
<div class="row" style="margin-top: 20px">
    <div class="col-xs-4"></div>
    <div class="col-xs-4"></div>
    <div class="col-xs-4 text-center">
        @php
            $createdAt = \Carbon\Carbon::now();
        @endphp
        <small><i>Ngày {{ $createdAt->day }} tháng {{ $createdAt->month }} năm {{ $createdAt->year }}</i></small><br/>
    </div>
    <div class="col-xs-4 text-center">
        Người nhận hàng
    </div>
    <div class="col-xs-4 text-center">
        Đơn vị vận chuyển
    </div>
    <div class="col-xs-4 text-center">
        Nhân viên bán hàng
    </div>
</div>
@endsection
