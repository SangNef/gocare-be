<div class="row" style="margin-top: 10px">
    <div class="col-md-12">
        <table id="selected_orders_table" class="table table-bordered">
            <thead>
                <tr class="success">
                    <th width="20%">Mã vận đơn</th>
                    <th width="20%">Tên khách hàng</th>
                    <th width="10%">Ngày tạo</th>
                    <th width="15%">Đơn hàng trên hệ thống</th>
                    <th width="5%">SL</th>
                    <th width="15%">Tiền thu COD</th>
                    <th width="15%">Tiền cước</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($selectedOrders))
                    @include('la.cod_orders_shipping.selected_order', ['cOrders' => $selectedOrders])
                @endif
            </tbody>
            <tfoot>
                <tr class="success">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td colspan="2" class="strong">Tổng :</td>
                    <td colspan="1" class="text-right total_cod strong">
                        {{ isset($selectedOrders) ? number_format($selectedOrders->sum('cod_amount')) : 0 }} đ
                    </td>
                    <td colspan="1" class="text-right total_fee strong">
                        {{ isset($selectedOrders) ? number_format($selectedOrders->sum('fee_amount')) : 0 }} đ
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
