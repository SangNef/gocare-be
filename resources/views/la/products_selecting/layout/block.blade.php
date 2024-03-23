<div class="row" id="relation_products">
    <div class="col-sm-12">
        @include('la.products_selecting.selected_products')
    </div>
    @if (!isset($addMore) || $addMore)
        <div class="col-sm-12">
            @include('la.products_selecting.products')
        </div>
    @endif
</div>
