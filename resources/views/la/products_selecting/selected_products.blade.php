<div class="row">
    <div class="col-sm-12 bg-gray">
        <h3 style="margin: 10px 0">{{ trans('messages.selected_products') }}</h3>
    </div>
</div>
<div class="row" id="selected_products">
    @if(isset($selectedProducts))
        @foreach($selectedProducts as $product)
            @include($selectedView, $product)
        @endforeach
    @endif
</div>