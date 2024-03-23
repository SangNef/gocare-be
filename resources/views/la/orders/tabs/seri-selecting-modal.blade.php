<div class="modal fade" id="AddSeri" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-70" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Chọn seri sản phẩm</h4>
                <input type="hidden" id="data-index">
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 col-xs-12 selecting">
                        <h4>
                            Chọn seri sản phẩm
                            <button type="button" class="btn btn-xs btn-primary pull-right select-seri-button">Chọn</button>
                        </h4>
                        <input type="hidden" name="data-index">
                        <input type="hidden" name="seri_selecting_order_type">
                        <input type="hidden" name="seri_selecting_order_sub_type">
                        <input type="hidden" name="seri_selecting_params">
                        <table class="table table-bordered" width="100%" id="seri-selecting">
                            <thead>
                            <tr class="success">
                                <th data-orderable="false"><input type="checkbox" class="selecting-all" /></th>
                                <th>Mã seri</th>
                                <th>Mã kích hoạt</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="col-md-6 col-xs-12 selected">
                        <h4>
                            Seri sản phẩm đã chọn
                            <button type="button" class="btn btn-xs btn-danger pull-right unselect-seri-button">Xoá</button>
                        </h4>
                        <textarea style="display: none" class="seri_selected"></textarea>
                        <table class="table table-bordered" id="seri-selected"  width="100%">
                            <thead>
                            <tr class="success">
                                <th data-orderable="false"><input type="checkbox" class="selected-all" /></th>
                                <th>Mã seri</th>
                                <th>Mã kích hoạt</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-sm-12" style="text-align: right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success selected-seri">Lưu</button>
                </div>
            </div>
        </div>
    </div>
</div>
