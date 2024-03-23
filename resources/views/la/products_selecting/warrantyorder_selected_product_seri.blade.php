@foreach ($products as $product)
@php
    $code = app(\App\Services\Generator::class)->generate(3, true);
@endphp
<tr class="selected-product-seri" data-product-id="{{ $product->id }}">
    <td>
        @if(isset($id))
        <input type="checkbox" class="row" value="{{ $id }}" />
        <input type="hidden" name="series[{{ $code }}][id]" value="{{ $id }}">
        {{ $id }}
        @endif
    </td> 
    <td>
        {{ $product->name }}
        <input type="hidden" name="series[{{ $code }}][product_id]" value="{{ $product->id }}">
    </td>
    <td>
        <div class="form-group">
            <select class="form-control ajax-select choose-seri"
                params="{{ json_encode(['product_id' => $product->id]) }}" 
                name="series[{{ $code }}][seri_id]" value="">
                @if(isset($selected_series) && !empty($selected_series))
                    <option value="{{ $selected_series->id }}" selected>{{ $selected_series->seri_number }}</option>
                @endif
            </select>
        </div>
    </td>
    <td>
        <select class="form-control" name="series[{{ $code }}][error_type]">
            @foreach (\App\Models\WarrantyOrderProductSeri::getAvailableErrorTypes() as $value => $label)
            <option value="{{ $value }}" @if(isset($error_type) && $error_type == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="form-control seri-status" name="series[{{ $code }}][status]">
            @foreach (\App\Models\WarrantyOrderProductSeri::getAvailableStatus() as $value => $label)
            <option value="{{ $value }}" @if(isset($status) && $status == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
        <div class="return-at" style="display: {{ isset($status) && $status == 5 ? 'block' : 'none' }}">
            <label for="" style="margin: 5px 0 0 0;">Ngày trả</label>
            <input class="form-control datetime-picker" name="series[{{ $code }}][return_at]" type="text" value="{{ isset($return_at) && $return_at ?  $return_at->format('d/m/Y H:i:s')  : '' }}" @if((isset($status) && $status != 5) || !isset($status)) disabled="disabled" @endif />
        </div>
    </td>
    <td>
        <textarea class="order-product-note form-control" name="series[{{ $code }}][note]" cols="40" rows="1" placeholder="Ghi chú">{{ isset($note) && $note ? $note : '' }}</textarea>
    </td>
    @if(@$bill_lading)
    <td>
        Mã vận đơn {{ strtoupper($bill_lading->partner) }}: <strong class="text-danger">{{ $bill_lading->order_code }}</strong>
    </td>
    @else
    <td></td>
    @endif
</tr>
@endforeach
