@foreach ($products as $key => $product)
@php
$index = @$existedIndex[$key] ? $existedIndex[$key]  : request('index');
if ($product instanceof \App\Models\TransportOrderProduct) {
    $id = $product->product_id;
    $name = $product->product_name;
    $quantity = $product->quantity;
    $packages = $product->packages;
    $weight = $product->weight;
    $length = $product->length;
    $width = $product->width;
    $height = $product->height;
    $price = $product->price;
} else {
    $id = $product->id;
    $name = $product->name;
    $quantity = 1;
    $packages = 1;
    $weight = $product->weight * 0.001;
    $length = $product->length * 0.01;
    $width = $product->width * 0.01;
    $height = $product->height * 0.01;
    $price = 0;
}
@endphp
    <tr class="selected-product-transport" data-product-id="{{ $id }}" data-index="{{ $index }}">
    <td>
        {{ $name }} <br />
        @foreach(@$attrs[$key] as $attr)
            <small>
                {{ $attr['name'] }}:
                @foreach($attr['values'] as $v)
                    <span class="attribute-value {{ !in_array($v['id'], @$selectedAttrs[$key]) ? 'hidden' : '' }}" data-id="{{ $v['id'] }}">{{ $v['value'] }}</span>
                @endforeach
            </small>
        @endforeach
        <input type="hidden" name="transport[products][{{ $index }}][product_name]" value="{{ $name }}">
        <input type="hidden" name="transport[products][{{ $index }}][product_id]" value="{{ $id }}">
    </td>
    <td>
        <input type="number" class="form-control quantity" name="transport[products][{{ $index }}][quantity]" value="{{ $quantity }}" style="width: 100%">
    </td>
    <td>
        <input type="number" class="form-control packages" name="transport[products][{{ $index }}][packages]" value="{{ $packages }}" style="width: 100%">
    </td>
    <td>
        <input type="number" class="form-control" name="transport[products][{{ $index }}][weight]" value="{{ $weight }}" style="width: 100%">
    </td>
    <td>
        <input type="number" class="form-control" name="transport[products][{{ $index }}][length]" value="{{ $length }}" style="width: 100%">
    </td>
    <td>
        <input type="number" class="form-control" name="transport[products][{{ $index }}][width]" value="{{ $width }}" style="width: 100%">
    </td>
    <td>
        <input type="number" class="form-control" name="transport[products][{{ $index }}][height]" value="{{ $height }}" style="width: 100%">
    </td>
    <td>
        <input type="text" class="form-control total_block" value="0" style="width: 100%" readonly>
    </td>
    <td>
        <input type="text" class="form-control total_weight" value="0" style="width: 100%" readonly>
    </td>
    <td>
        <input class="currency form-control" name="transport[products][{{ $index }}][price]" style="width: 100%" value="{{ $price }}">
    </td>
    <td>
        <input class="currency form-control total" name="transport[products][{{ $index }}][total]" style="width: 100%" value="0" readonly>
    </td>
</tr>
@endforeach