@foreach ($products as $key => $product)
<tr class="selected-product-seri" data-index="{{ $index }}">
    <td>
        {{ $product->name }}<br />
        @foreach($attrs as $attr)
            <small>
                {{ $attr['name'] }}:
                @foreach($attr['values'] as $v)
                    <span class="attribute-value {{ !in_array($v['id'], $selectedAttrs) ? 'hidden' : '' }}" data-id="{{ $v['id'] }}">{{ $v['value'] }}</span>
                @endforeach
            </small>
        @endforeach
    </td>
    <td>
        <div class="form-group row">
            <div class="col-sm-9 selected-seri-plain-text-{{$index}}">
                @if(isset($selected_series) && !empty($selected_series))
                    @if (count($selected_series) <=3)
                        {{ implode(',', $selected_series) }}
                    @else
                        {{ implode(',', array_slice($selected_series, 0, 3)) }}, + thêm {{ count($selected_series)-3 }} seri
                    @endif
                @endif
            </div>
            <div class="col-sm-3">
                <button type="button" data-toggle="modal" data-index="{{ $index }}" data-target="#AddSeri" class="btn btn-xs btn-primary pull-right choose-seri" value="{{ json_encode(['product_id' => $product->id, 'attr_ids' => $selectedAttrs]) }}">Chọn</button>
                <textarea style="display: none" name="products[{{ $index }}][series]" class="selected-series selected-series-{{ $index }}">
                     @if(isset($selected_series) && !empty($selected_series))
                        {{ implode(',', array_keys($selected_series)) }}
                    @endif
                </textarea>
                <input type="hidden" name="products[{{ $index }}][has_series]" value="{{ $product->has_series }}">
            </div>
        </div>
    </td>
    <td>{{ \Carbon\Carbon::now() }}</td>
</tr>
@endforeach
