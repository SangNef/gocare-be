<div class="row" style="margin-top: 20px">
    <div class="col-md-10 col-md-offset-1">
        <table id="selected_products_series" class="table table-bordered">
            <thead>
                <tr class="success">
                    @foreach($cols as $col)
                    <th style="width: {{ $col['width'] }}%">{{ $col['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if(isset($selectedProducts) && isset($view))
                    @foreach($selectedProducts as $product)
                        @include($view, $product)
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
