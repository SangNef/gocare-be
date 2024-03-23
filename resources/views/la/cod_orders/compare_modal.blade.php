<div class="modal fade" id="{{ $type }}" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Đối soát</h4>
                <div class="alert alert-danger error-message" style="display: none">
                    
                </div>
            </div>
            <div class="modal-body">
                <div class="stepwizard">
                    <div class="stepwizard-row setup-panel">
                        <div class="stepwizard-step col-xs-4">
                            <a href="#step-1" type="button" class="btn btn-success btn-circle">1</a>
                            <p><small>Tiền COD</small></p>
                        </div>
                        <div class="stepwizard-step col-xs-4"> 
                            <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                            <p><small>Tiền cước</small></p>
                        </div>
                        <div class="stepwizard-step col-xs-4"> 
                            <a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
                            <p><small>Điều chỉnh số dư</small></p>
                        </div>
                    </div>
                </div>
                {!! Form::open(['url' => route('co.update-bank-balance', ['type' => $type]), 'id' => 'order-add-form']) !!}
                    <div class="panel panel-primary setup-content check-bills" id="step-1">
                        <div class="panel-heading">
                            <h3 class="panel-title">Nhập mã vận đơn để lấy tổng tiền COD</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="control-label">Mã vận đơn *</label>
                                <textarea name="cod_bill_ids" id="cod_bill_ids" class="form-control bills" cols="30" rows="7" placeholder="Nhập mã vận đơn"></textarea>
                            </div>
                            <button class="btn btn-primary nextBtn pull-right" type="button">Next</button>
                        </div>
                    </div>
                    
                    <div class="panel panel-primary setup-content check-bills" id="step-2">
                        <div class="panel-heading">
                            <h3 class="panel-title">Nhập mã vận đơn để lấy tổng tiền cước</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="control-label">Mã vận đơn *</label>
                                <textarea name="fee_bill_ids" id="fee_bill_ids" class="form-control bills" cols="30" rows="7" placeholder="Nhập mã vận đơn"></textarea>
                            </div>
                            <button type="button" class="btn btn-default backBtn">Back</button>
                            <button class="btn btn-primary nextBtn pull-right" type="button">Next</button>
                        </div>
                    </div>
                    
                    <div class="panel panel-primary setup-content get-price" id="step-3">
                        <div class="panel-heading">
                            <h3 class="panel-title">Điều chỉnh số dư</h3>
                        </div>
                        <div class="panel-body">
                            <div id="result" class="row" style="margin-bottom: 20px">
                                <div class="col-sm-4">
                                    Tổng COD: <strong class="cod_amount" style="color: red"></strong>
                                </div>
                                <div class="col-sm-4">
                                    Tổng cước: <strong class="real_amount" style="color: red"></strong>
                                </div>
                                <div class="col-sm-4">
                                    Tổng nhận: <strong class="total_amount" style="color: red"></strong>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status">Ngân hàng :</label>
                                <select class="form-control ajax-select" model="banks" name="bank_id" required="required">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Số tiền :</label>
                                <input type="text" class="form-control currency" required="required" name="amount" value="0 đ"/>
                            </div>
                            <button type="button" class="btn btn-default backBtn">Back</button>
                            <button class="btn btn-success submitBtn pull-right" type="submit">Finish!</button>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
