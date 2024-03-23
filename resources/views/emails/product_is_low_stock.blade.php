<div class="col-md-9">
    <!-- Message -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Lượng hàng sắp hết trong kho</h3>
        </div>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><b>ID</b></th>
                    <th><b>Mã sản phẩm</b></th>
                    <th><b>Tên sản phẩm</b></th>
                    <th><b>Số lượng</b></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>