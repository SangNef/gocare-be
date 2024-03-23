@extends('la.layouts.print')
@section('content')
    <div class="row" style="border: 1px solid #555555; margin-top: 30px">
        <div class="col-xs-12 text-center" style="padding: 0; height: 20px;">
            <div style="display: inline-block; transform: translateX(50px)">
                {{ trans('order.print_type_' . $order->type) }}
            </div>
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
                <div class="col-xs-12 text-left">
                    @foreach($banks as $bankName => $accs)
                        <div class="row">
                            <div class="col-xs-12">
                                <div style="display: inline-block; margin-right: 10px">
                                    Chủ TK: {{ $bankName }}
                                </div>
                                @foreach ($accs as $acc)
                                    <div style="display: inline-block">
                                        - {{ $acc }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="row" style="margin-top: 20px">
                <div class="col-xs-12 text-left">
                    <div style="display: inline-block; margin-right: 20px">
                        Tên khách hàng: {{ $order->customer->name }}
                    </div>
                     <div style="display: inline-block">
                        SĐT: {{ $customer->phone }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-left">
                    Đ/C: {{ implode('-', array_filter([
    $customer->address,
    $customer->ward,
    $customer->district,
    $customer->province,
])) }}
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered" style="margin: 5px 0 0 0;">
        <thead>
            <tr>
                <th colspan="8" class="text-right">Nợ cũ</th>
                <td colspan="8">{{number_format($orders->first()->current_debt - $orders->first()->amount_charged_to_debt)}} {{ $symbol }}</td>
            </tr>
            <tr>
                <th style="padding: 2px; text-align: center;">STT</th>
                <th>Ngày tạo</th>
                <th style="width: 60%">Tên sản phẩm</th>
                <th>ĐV</th>
                <th width="3%">SL</th>
                <th>Đơn giá</th>
                <th style="white-space: nowrap;">Thành tiền</th>
                <th style="white-space: nowrap;">Thanh toán</th>
                <th style="white-space: nowrap;">Cộng dồn</th>
            </tr>
        </thead>
        @php($count = 1)
        @php($total = 0)
        @foreach($orders as $item)
            @php($rows = count(array_intersect($item->orderProducts->pluck('product_id')->toArray(), $validProducts)))
            @php($first = 1)
            @foreach ($item->orderProducts as $key => $orderProduct)
                @if (in_array($orderProduct->product_id, $validProducts))
                    <tr>
                        <td style="padding: 2px" class="text-center">{{ $count++ }}</td>
                        @if ($first)
                            <td class="text-right" style="vertical-align: middle" rowspan="{{ $rows }}"> {{ $item->created_at->format('d/m/Y') }}</td>
                        @endif
                        <td>{{ $orderProduct->note ? $orderProduct->product->name.' ('.$orderProduct->note.')' : $orderProduct->product->name }}</td>
                        <td class="text-right"> {{ ucfirst($orderProduct->product->unit) }}</td>
                        <td class="text-right">{{ number_format($orderProduct->quantity + $orderProduct->w_quantity) }}</td>
                        <td class="text-right" style="white-space: nowrap;">
                            {{ number_format($orderProduct->price) }} {{ $symbol }}
                        </td>
                        <td class="text-right"  style="white-space: nowrap;">
                            {{ number_format($orderProduct->price * ($orderProduct->quantity + $orderProduct->w_quantity)) }} {{ $symbol }}
                            @php($total += $orderProduct->price * ($orderProduct->quantity + $orderProduct->w_quantity))
                        </td>
                        @if ($first)
                            <td class="text-right" style="white-space: nowrap; vertical-align: middle" rowspan="{{ $rows }}">
                                {{ number_format($item->paid) }} {{ $symbol }}
                            </td>
                            <td class="text-right" style="white-space: nowrap; vertical-align: middle" rowspan="{{ $rows }}">
                                {{ number_format($item->current_debt) }} {{ $symbol }}
                            </td>
                            @php($first = 0)
                        @endif
                    </tr>
                @endif
            @endforeach
        @endforeach
        <tr>
            <td colspan="6" class="text-right" style="white-space: nowrap;">
                Tổng tiền
            </td>
            <td class="text-right"  style="white-space: nowrap;">
                {{ number_format($total) }} {{ $symbol }}
            </td>
            <td class="text-right"></td>
        </tr>
    </table>

    <div class="row">
        <div class="col-xs-6"></div>
        <div class="col-xs-6 text-center">
            @php
                $createdAt = \Carbon\Carbon::now();
            @endphp
            <small><i>Ngày {{ $createdAt->day }} tháng {{ $createdAt->month }} năm {{ $createdAt->year }}</i></small><br/>
        </div>
        <div class="col-xs-6 text-center">
            Khách hàng
        </div>
        <div class="col-xs-6 text-center">
            Nhân viên bán hàng
        </div>
    </div>
@endsection
