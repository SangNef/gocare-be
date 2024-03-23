@extends('la.layouts.print')
@section('content')
<div class="container">
@foreach($items as $item)
    <div class="order-seri row">
        <div class="col-xs-12 qrcode text-center">
            <img src="{{ route('qrcode.warrantyorder', ['id' => $item['id']]) }}"/>
        </div>
        <div class="col-xs-12 order-content">
            <div class="content">
                <span class="key">
                Khách hàng:
                </span>
                <span class="value">
                    {{ $customer->username }}
                </span>
            </div>
            @foreach (collect($item)->only(['order_code', 'id', 'seri_number', 'note']) as $key => $value)
            <div class="content">
                <span class="key">
                {{ trans('warranty_order.'.$key) }}:
                </span>
                <span class="value">
                    {{ $value }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
@endforeach
</div>
<style type="text/css">
    body .wrapper {
        padding: 0 !important;
    }
    @media print {
        html, body {
            margin: 0 !important;
        }
        .order-seri {
            margin: 0 auto;
            page-break-after: always;
        }
        .order-seri .order-content {
            font-size: 10px;
        }
        .order-seri .order-content .value {
            font-weight: bold;
            word-wrap: breal-word;
        }
        .order-seri .qrcode img {
            margin: 0 auto;
            max-height: 60px;
        }
    }
    @page {
        size: 50mm 50mm;
        margin: 0;
    }
</style>
@endsection
