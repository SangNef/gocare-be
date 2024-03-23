
@extends('la.layouts.print')
@section('content')
    <div class="row" style="border: 1px solid #555555; margin-top: 30px">
        <div class="col-xs-12 text-center" style="padding: 0; height: 20px;">
            <div style="display: inline-block; transform: translateX(50px)">
                {{ trans('warranty_order.print') }}
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
            </div>
            <div class="row" style="margin-top: 20px">
                <div class="col-xs-12 text-left">
                    <div style="display: inline-block; margin-right: 20px">
                        Tên khách hàng: {{ $customer->name }}
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
            <th style="width: 5%">Ngày nhận</th>
            <th style="width: 28%">Tên sản phẩm</th>
            <th style="width: 10%">Series</th>
            <th style="width: 8%">Trạng thái</th>
            <th style="width: 5%">Ngày trả</th>
            <th style="width: 40%">Ghi chú</th>
        </thead>
        @foreach ($items as $key => $item)
            <tr>
                <td style="padding: 2px" class="text-center">{{ $key+1 }}</td>
                <td>{{ $item['created_at'] }}</td>
                <td>{{ $item['product_name'] }}</td>
                <td>{{ $item['seri_number'] }}</td>
                <td>{{ $item['status'] }}</td>
                <td>{{ $item['return_at'] }}</td>
                <td>{{ $item['note'] }}</td>
            </tr>
        @endforeach
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
