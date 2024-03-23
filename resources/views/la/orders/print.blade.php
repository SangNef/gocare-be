@extends('la.layouts.print')
@section('content')
    <div class="row" style="border: 1px solid #555555; margin-top: 30px">
        <div class="col-xs-12 text-center" style="padding: 0; height: 20px;">
            <div style="display: inline-block; transform: translateX(50px)">
                {{ trans('order.print_type_' . $order->type) }}
            </div>
            <div style="display: inline-block; float:right; margin-top: 5px;">
                <img width="100px" src="{{ "https://img.vietqr.io/image/vietinbank-100868158318-qr_only.jpg?amount=".$order->subtototaltal."&addInfo=DH%20".$order->code."&accountName=Duong%20My%20Linh" }}">
{{--                <img src="{{ route('qrcode.order', ['id' => $order->id]) }}"/>--}}
                <strong style="display: block; text-align: center">
                    {{ $order->code }}
                </strong>
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
            <th style="padding: 2px; text-align: center;">STT</th>
            <th style="width: 50%">Tên sản phẩm</th>
            <th style="width: 10%">Màu sắc</th>
            <th width="3%">SL</th>
            <th>ĐV</th>
            <th>Đơn giá</th>
            <th>(%)</th>
            <th style="white-space: nowrap;">Thành tiền</th>
            <th>BH</th>
        </thead>
        @foreach ($order->orderProducts as $key => $orderProduct)
            <tr>
                <td style="padding: 2px" class="text-center">{{ $key+1 }}</td>
                <td>{{ $orderProduct->note ? $orderProduct->product->name.' ('.$orderProduct->note.')' : $orderProduct->product->name }}</td>
                <td>{{ $orderProduct->attr_texts }}</td>
                <td class="text-right">{{ number_format($orderProduct->quantity + $orderProduct->w_quantity) }}</td>
                <td class="text-right"> {{ ucfirst($orderProduct->product->unit) }}</td>
                <td class="text-right" style="white-space: nowrap;">
                    {{ number_format($orderProduct->price) }} {{ $symbol }}
                </td>
                <td>{{ $orderProduct->discount_percent }}%</td>
                <td class="text-right"  style="white-space: nowrap;">
                    {{ number_format($orderProduct->total) }} {{ $symbol }}
                </td>
                <td class="text-right"> {{ $orderProduct->product->warranty_period }}T</td>
            </tr>
        @endforeach
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">{{ trans('order.total') }}:</td>
            <td colspan="3" class="text-right">{{ number_format($order->total) }} {{ $symbol }}</td>
        </tr>
        @if (floor($order->fee))
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">{{ trans('order.fee') }}:</td>
            <td colspan="3" class="text-right">{{ number_format($order->fee) }} {{ $symbol }}</td>
        </tr>
        @endif
        @if ($order->discount != 0)
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">{{ trans('order.discount') }}:</td>
            <td colspan="3" class="text-right">{{ number_format($order->discount) }} {{ $symbol }}</td>
        </tr>
        @endif
        @if (floor($order->paid))
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">{{ trans('order.paid_amount') }}:</td>
            <td colspan="3" class="text-right">{{ number_format($order->paid) }} {{ $symbol }}</td>
        </tr>
        @endif
        @if ($order->getOldDebt() != 0)
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">{{ trans('order.old_debt') }}:</td>
            <td colspan="3" class="text-right">{{ number_format($order->getOldDebt()) }} {{ $symbol }}</td>
        </tr>
        @endif
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">{{ trans('order.total_debt') }}:</td>
            <td colspan="3" class="text-right">{{ number_format($order->current_debt) }} {{ $symbol }}</td>
        </tr>
    </table>

    <div class="row">
        <div class="col-xs-6"></div>
        <div class="col-xs-6 text-center">
            @php
                $createdAt = \Carbon\Carbon::parse($order->created_at);
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
