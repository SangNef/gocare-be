@extends('la.layouts.print')
@section('content')
    <div class="row" style="border: 1px solid #555555; margin-top: 30px">
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
        </div>
    </div>
    <h2 class="text-center">{{ $produce->description }}</h2>
    <div class="row">
        <div class="col-xs-6"><strong>Tên sản phẩm</strong></div>
        <div class="col-xs-6">{{ $produce->product->name }} @if ($produce->attrs_value) ({{ $produce->attrsName() }}) @endif</div>
    </div>
    <div class="row">
        <div class="col-xs-6"><strong>SKU</strong></div>
        <div class="col-xs-6">{{ $produce->product->sku }}</div>
    </div>
    <div class="row">
        <div class="col-xs-6"><strong>Số lượng</strong></div>
        <div class="col-xs-6">{{ $produce->quantity }}</div>
    </div>
    <div class="row" style="margin-bottom: 25px">
        <div class="col-xs-6"><strong>Trạng thái</strong></div>
        <div class="col-xs-6">{!! $orderStatus->getStatusHTMLFormatted($produce->status) !!}</div>
    </div>
    <div class="row">
        <div class="col-xs-12"><strong>Sản phẩm con cấu thành lên sản phẩm chính</strong></div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>SL</th>
                        <th>Tổng</th>
                        <th>Tồn</th>
                        <th>Còn</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($produce->products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->product->name }} @if ($product->attrs_value) ({{ $product->attrsName() }}) @endif</td>
                            <td>{{ number_format($product->quantity) }}</td>
                            <td>{{ number_format($product->total) }}</td>
                            <td>{{ number_format(@$product->store_quantity) }}</td>
                            <td>{{ number_format(@$product->remain) }}</td>
                            <td>{{ number_format(@$product->amount) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-right">Tổng</td>
                        <td>{{ number_format($produce->products->sum('quantity')) }}</td>
                        <td>{{ number_format($produce->products->sum('total')) }}</td>
                        <td>{{ number_format($produce->products->sum('store_quantity')) }}</td>
                        <td>{{ number_format($produce->products->sum('remain')) }}</td>
                        <td>{{ number_format($produce->products->sum('amount')) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
