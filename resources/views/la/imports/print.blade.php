@extends('la.layouts.print')
@section('content')
    <h2 class="text-center">AZPRO ĐƠN ĐẶT HÀNG</h2>
    <div class="row">
        <div class="col-xs-6"><strong>Số đơn</strong></div>
        <div class="col-xs-6">{{ $import->code }}</div>
    </div>
    <div class="row">
        <div class="col-xs-6"><strong>Ngày đặt hàng</strong></div>
        <div class="col-xs-6">{{ \Carbon\Carbon::parse($import->imported_at)->format('d/m/Y') }}</div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>SL đặt</th>
                        <th>SL đã về</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($import->products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <img src="{{$product->product->getFeaturedImagePathByAttrValues(implode(',', $product->attrs_value)) }}" width="150"/>
                                {{ $product->name }} ({{ $product->attr_name }})
                            </td>
                            <td>{{ number_format($product->quantity) }}</td>
                            <td>{{ number_format($product->done) }}</td>
                            <td>{{ $product->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
