@foreach ($products as $product)
<div class="selected-product col-sm-12" data-id="{{ $product->id }}">
    <div class="card mb-3">
        <div class="row no-gutters">
            <div class="col-md-4">
                {!! $product->getFullFeaturedImage(80)  !!}
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title"><strong>{{ $product->sku }}</strong></h5>
                    <p class="card-text">{{ $product->name }}</p>
                    <div class="row">
                        <div class="col-sm-6"><label>{{ trans('messages.quantity') }}:</label></div>
                        <div class="col-sm-6" style="padding-left: 0"><input class="form-control integer" type="number" name="relation_product[{{ $product->id }}]" value="1"></div>
                    </div>
                    <button type="button" class="remove-selected-product" data-id="{{ $product->id }}"><span aria-hidden="true">X</span></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach