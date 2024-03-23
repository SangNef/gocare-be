@extends('la.layouts.print')
@section('content')
@php
    $perRow = request()->get('per_row', 3);
    switch ($perRow) {
        case 2:
            if (request('source') != 'produce') {
                $width = 4;
                $size = '25mm';
            } else {
                $width = 6;
                $size = '25mm';
            }
            break;
        case 3: 
            $width = 4;
            $size = '22mm';
            break;
        case 4:
            $width = 3;
            $size = '25mm';
            break;
        default:
            $width = 6;
            $size = '50mm';
            break;
    }
@endphp
@foreach($codes->chunk($perRow) as $code)
@foreach($code as $cd)
    <div class="col-xs-{{$width}} text-center code-row">
        <div class="code">
            <img src="data:image/jpg;base64, {{ $cd->qr_code }}" alt="{{ $cd->seri_number }}" />
            <p class="seri-code">{{ $cd->seri_number }} </p>
            @if($perRow == 2 && request('source') != 'produce')
                <p class="uppercase">{{ $cd->activation_code ?: $cd->getActivationCode() }}</p>
            @endif
        </div>
    </div>
    @endforeach
@endforeach
<style type="text/css">
    body .wrapper {
        padding: 0 !important;
    }
    @if ($width == 6)
        .code-row .code img {
            width:44mm;
        }
        .code-row .code .seri-code {
            padding-top:0;
            margin:0;
            font-size:12px !important;
        }
    @endif
    @if ($perRow == 2)
            .code-row .code img {
        width:14mm !important;
    }
    .code-row .code p {
        padding-top:0;
        margin:0;
        font-size:12px !important;
    }
    @endif
@media print {
        html, body {
            margin: 0 !important;
        }
        .code-row {
            padding-right: 0 !important;
        }
        .code-row .code {
            margin-top: 5px;
        }
        .code-row .code .seri-code {
            font-size: 10px;
            margin: 0 !important;
        }
        @if ($width != 6)
            .code-row .code img {
                max-height: 60px;
            }
        @else 
            .code-row .code img {
                max-height: 44mm;
            }
        @endif

    }
    @page {
        size: 105mm {{ $size }};
        margin: 0;
    }
</style>
@endsection
