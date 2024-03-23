@foreach($products as $product)
<div class="selected-product col-sm-12 order-selected-product" data-id="{{ $product->id }}">
    <div class="card mb-3">
        <div class="row no-gutters">
            <div class="col-md-4 col-sm-12" style="padding-top: 10px" title="">
                <strong>{{ $product->name }}</strong>
                <input type="hidden" name="products[{{ $product->id }}][product_id]" value="{{ $product->id }}">
            </div>
            <div class="col-md-4 col-sm-12 order-product-input p0">
                <label>
                    Số lượng:
                    <input type="number" class="form-control quantity" name="products[{{ $product->id }}][quantity]" value="{{ isset($quantity) ? $quantity : 1 }}" min="1" data-prev-val="{{ isset($quantity) ? $quantity : 1 }}">
                </label>
            </div>
            <div class="col-md-4 col-sm-12 text-right p0" style="padding-top: 8px">
                <textarea class="order-product-note" name="products[{{ $product->id }}][note]" cols="40" rows="1" placeholder="Ghi chú">{{ isset($note) && $note ? $note : '' }}</textarea>
                <button type="button" class="remove-selected-product btn btn-light text-red" data-id="{{ $product->id }}"><span aria-hidden="true">X</span></button>
            </div>
        </div>
    </div>
</div>
@endforeach
