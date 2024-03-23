@extends('la.layouts.print')
@section('content')
    <div class="row" style="border: 1px solid #555555; padding: 10px">
        <div class="col-xs-12" style="text-align: center">
            {{ trans('product.export') }}
        </div>
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
        <div class="col-xs-12 text-left" style="margin-top: 20px">
            <div style="display: inline-block; margin-right: 20px">
                Tên khách hàng: {{ $customer->name }}
            </div>
            <div style="display: inline-block">
                SĐT: {{ $customer->phone }}
            </div>
            <div>
                Địa chỉ: {{ $customer->getFullAddress() }}
            </div>
        </div>
    </div>
    <table class="table table-bordered" style="margin-top: 30px">
        
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="20%">Ảnh</th>
                <th width="35%">Tên sản phẩm</th>
                <th width="20%">Đơn giá</th>
                <th width="20%">Trạng thái</th>
            </tr>
        </thead>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product['stt'] }}</td>
                <td><img src="data:image/jpg;base64, {{ $product['featured_image'] }}" alt="{{ $product['name'] }}" style="width: 100%"/></td>
                <td>{{ $product['name'] }}</td>
                <td>{{ number_format($product['price']) }} đ</td>
                <td>{{ $product['status'] }}</td>
            </tr>
        @endforeach 
    </table>
@endsection
