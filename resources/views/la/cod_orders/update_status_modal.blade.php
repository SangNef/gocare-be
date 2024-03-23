<div class="modal fade" id="cod-update-status" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('co.update-provider-status', ['type' => $codOrder->partner, 'code' => $codOrder->order_code]) }}" method="POST" id="cod-update-status-form">
                {{ csrf_field() }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Cập nhật trạng thái đơn hàng {{ strtoupper($codOrder->partner) }}</h4>
                </div>
                <div class="modal-body">
                    <h5>Mã đơn hàng: <strong>{{ $codOrder->order_code }}</strong></h5>
                    <div class="form-group">
                        <label for="note">Trạng thái :</label>
                        <select name="status" class="form-control">
                            @foreach($status as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-sm-12" style="text-align: right;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success onetime-click"Lưu/button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>