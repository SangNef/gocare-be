<tr class="order-row" data-id="{{ $order->id }}">
    <td>{{ $order->code }}</td>
    <td>
        {{ $order->store->name }}
        <div class="form-group">
            <label for="status">Chọn kho hàng : </label>
            <select data-name="inventory" class="inventory form-control getprice-required">
                @foreach($stores as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </td>
    <td>
        <ol style="padding-left: 15px">
            @foreach($products as $product)
                <li>{{ $product['product_name'] . ' x' . $product['quantity'] }}</li>
            @endforeach
        </ol>
        <strong>Tổng khối lượng: <span class="text-danger">{{ $products->sum('weight') }} {{ $partner === "ghtk" ? 'kg' : 'g' }}</span></strong><br/>
        @if ($partner != 'ghtk')
            <strong>Tổng chiều cao: <span class="text-danger">{{ $partner === 'vtp' ? $products[0]['height'] : $products->sum('height') }} cm</span></strong><br/>
            <strong>Tổng chiều dài: <span class="text-danger">{{ $partner === 'vtp' ? $products[0]['length'] : $products->sum('length') }} cm</span></strong><br/>
            <strong>Tổng chiều rộng: <span class="text-danger">{{ $partner === 'vtp' ? $products[0]['width'] : $products->sum('width') }} cm</span></strong><br/>
        @endif
    </td>
    <td>
        <p>Họ tên: {{ $customer->name }}</p>
        <p>SDT: {{ $customer->phone }}</p>
        <p>Địa chỉ: {{ $customer->getFullAddress() }}</p>
    </td>
    <td>
        <div class="form-group">
            <label style="margin-right: 10px"><input type="radio" class="partner" value="vtp" data-name="partner" @if($partner === 'vtp') checked @endif>VTP</label>
            <label style="margin-right: 10px"><input type="radio" class="partner" value="ghn" data-name="partner" @if($partner === 'ghn') checked @endif>GHN</label>
            <label><input type="radio" class="partner" value="ghtk" data-name="partner" @if($partner === 'ghtk') checked @endif>GHTK</label>
        </div>
        @if($partner === 'vtp')
        <div class="form-group">
            <label for="">Số kiện hàng:</label>
            <input type="number" class="form-control getprice-required" data-name="PRODUCT_QUANTITY" min="1" value="{{ $products->sum('quantity') }}"/>
        </div>
        <div class="form-group">
            <label for="status">Loại vận đơn:</label>
            <select data-name="ORDER_PAYMENT" class="form-control getprice-required">
                <option value="1">Không thu tiền</option>
                <option value="2">Thu hộ tiền cước và tiền hàng</option>
                <option value="3">Thu hộ tiền hàng</option>
                <option value="4">Thu hộ tiền cước</option>
            </select>
        </div>
        @elseif($partner === 'ghn')
        <div class="form-group">
            <label for="note">Lưu ý bắt buộc:</label>
            <select data-name="required_note" class="form-control getprice-required">
                <option value="CHOTHUHANG">Cho thử hàng</option>
                <option value="CHOXEMHANGKHONGTHU">Cho xem hàng không thử</option>
                <option value="KHONGCHOXEMHANG">Không cho xem hàng</option>
            </select>
        </div>
         <div class="form-group">
            <label for="">Người trả phí vận chuyển:</label>
            <select data-name="payment_type_id" class="form-control getprice-required">
                <option value="1">Người bán</option>
                <option value="2">Người mua</option>
            </select>
        </div>
        @else
        <div class="form-group">
            <label for="note">Hình thức vận chuyển:</label>
            <select data-name="transport" class="form-control getprice-required">
                <option value="road">Đường bộ</option>
                <option value="fly">Đường bay</option>
            </select>
        </div>
         <div class="form-group">
            <label for="">Freeship:</label>
            <select data-name="count_fee" class="form-control getprice-required">
                <option value="0">Không</option>
                <option value="1">Có</option>
            </select>
        </div>
        @endif
        <div class="form-group">
            @php $serviceFieldName = $partner == 'vtp' ? 'ORDER_SERVICE' : 'service_id' @endphp
            <label for="status" style="margin-right: 20px">Dịch vụ (Tính cước) * :</label>
            <select data-name="{{ $serviceFieldName }}" class="form-control service_id getprice-required">
            </select>
        </div>
    </td>
    <td class="cod-status" style="vertical-align: middle; text-align: center">
    </td>
</tr>
