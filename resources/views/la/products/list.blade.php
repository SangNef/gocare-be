@foreach($products as $product)
<div class="product-item" data-id="{{ $product->id }}">
    <div class="product-quantity">
        <span class="text-danger"><strong>({{ isset(request()->sub_type) && request()->sub_type == 2 ? $product->w_quantity : $product->n_quantity }})</strong></span>
        @if (@$product->pending_quantity) <span class="text-yellow"><strong>({{ @$product->pending_quantity }})</strong></span> @endif
    </div>
    <div class="product-info">
        {!! $product->getFullFeaturedImage(80)  !!}
        <p><strong>{{ $product->sku }}</strong></p>
        <p>{{ $product->name }}</p>
    </div>
    @if (isset($product->validCombos) && $product->validCombos->count() > 0)
        <div class="product-combo text-left" style="width: 100%">
            <p class="text-danger">Khuyến mại</p>
            @foreach($product->validCombos as $combo)
                <label><input value="{{ $combo->id }}" type="checkbox"> <a class="text-sm " target="_blank" href="{{ url(config('laraadmin.adminRoute').'/productcombos/'.$combo->id.'/edit') }}">{{ $combo->note }}</a></label>
            @endforeach
        </div>
    @endif
</div>
@endforeach
<div class="row">
    <div class="col-sm-12">
        {{ $products->links() }}
    </div>
</div>
