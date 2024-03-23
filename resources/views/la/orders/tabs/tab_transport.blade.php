<div role="tabpanel" class="tab-pane fade in" id="tab-transport">
    <div class="col-sm-12">
        <div class="row">
            <div class="bg-gray" style="padding: 10px; display: flex; align-items: center ">
                <h3 style="margin: 0 50px 0 0;">{{ trans('order.transport') }}</h3>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="margin: 0 10px 0 0;">
                        <input type="checkbox" name="use_transport" @if(isset($transport)) checked @endif>
                        Sử dụng vận chuyển
                    </label>
                </div>
                <button type="button" id="print-transport" class="btn btn-primary btn-sm onetime-click">In vận chuyển</button>
            </div>
        </div>
        <div class="tab-content">
            <div class="container">
                <div class="panel infolist">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="error alert alert-danger" style="display: none">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fee" class="col-md-2">Đơn vị vận chuyển :</label>
                            <div class="col-md-10 fvalue" style="width: 30%">
                                <select class="form-control ajax-select" model="customer" name="transport[customer_id]">
                                    @if(isset($transport) && $transport->customer)
                                        <option value="{{$transport->customer_id}}" selected>{{ $transport->customer->username }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="transport_unit" class="col-md-2">Đơn vị :</label>
                            <div class="col-md-10 fvalue">
                                <select class="form-control" id="transport_unit" style="width: 35%" name="transport[unit]">
                                    <option value="cái" @if(isset($transport) && $transport->unit == 'cái') selected @endif>Cái</option>
                                    <option value="kg" @if(isset($transport) && $transport->unit == 'kg') selected @endif>Cân</option>
                                    <option value="khối" @if(isset($transport) && $transport->unit == 'khối') selected @endif>Khối</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px">
                            <div class="col-md-12">
                                <table id="tops_table" class="table table-bordered">
                                    <thead>
                                        <tr class="success">
                                            <th width="12%">Tên sản phẩm</th>
                                            <th>Số lượng</th>
                                            <th>Số kiện</th>
                                            <th>Khối lượng</th>
                                            <th>Dài (m)</th>
                                            <th>Rộng (m)</th>
                                            <th>Cao (m)</th>
                                            <th width="10%">Tổng khối</th>
                                            <th width="10%">Tổng Kg</th>
                                            <th width="12%">Giá/đơn vị</th>
                                            <th width="12%">Tổng tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       @if(isset($products))
                                            @include('la.products_selecting.order_selected_product_transport', $products)
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr class="success">
                                            <td colspan="1"><strong>Tổng:</strong></td>
                                            <td class="total-quantity text-danger strong">0</td>
                                            <td class="total-packages text-danger strong">0</td>
                                            <td colspan="4"></td>
                                            <td class="total-cubicmeter text-danger strong">0</td>
                                            <td class="total-kilo text-danger strong">0</td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr class="success">
                                            <td colspan="8"></td>
                                            <td class="strong">Thành tiền :</td>
                                            <td colspan="2" class="text-right total_cod strong">
                                                <div class="form-group" style="margin-top: 0;">
                                                    <input name="transport[total]" class="currency form-control" value="0" readonly>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="success">
                                            <td colspan="8"></td>
                                            <td class="strong">Thanh toán :</td>
                                            <td colspan="2" class="text-right total_cod strong">
                                                <div class="form-group" style="margin-top: 0;">
                                                    <input name="transport[transport_price]" class="currency form-control" value="{{ isset($transport) ? $transport->transport_price : 0 }}">
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>	
        </div>
    </div>
</div>