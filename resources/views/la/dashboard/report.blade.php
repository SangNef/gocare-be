<div class="box box-success">
    <div class="box-body">

        <div class="row">
            <div class="col-md-4">
                <!-- Widget: user widget style 1 -->
                <div class="card card-widget widget-user shadow">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-olive">
                        <h3 class="widget-user-username">{{number_format($data['profit']['total'])}}đ</h3>
                        <h5 class="widget-user-desc">Tổng doanh thu</h5>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{number_format($data['profit']['paid'])}}đ</h5>
                                    <span class="description-text">Đã Thanh toán</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{number_format($data['profit']['unpaid'])}}đ</h5>
                                    <span class="description-text">Chưa thanh toán</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4">
                                <div class="description-block">
                                    <h5 class="description-header">{{number_format($data['profit']['count'])}}</h5>
                                    <span class="description-text">Đơn hàng</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
                <!-- /.widget-user -->
            </div>
            <div class="col-md-4">
                <!-- Widget: user widget style 1 -->
                <div class="card card-widget widget-user shadow">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-olive">
                        <h3 class="widget-user-username">{{number_format($data['profit']['paid'])}}đ</h3>
                        <h5 class="widget-user-desc">Đã Thanh toán</h5>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="description-block">
                                    <h5 class="description-header">{{number_format($data['profit']['count']-$data['unpaid'])}}</h5>
                                    <span class="description-text">Đơn hàng</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
                <!-- /.widget-user -->
            </div>
            <div class="col-md-4">
                <!-- Widget: user widget style 1 -->
                <div class="card card-widget widget-user shadow">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-olive">
                        <h3 class="widget-user-username">{{number_format($data['profit']['unpaid'])}}đ</h3>
                        <h5 class="widget-user-desc">Chưa thanh toán</h5>
                    </div>

                    <div class="card-footer">
                        <div class="row">

                            <div class="col-sm-12">
                                <div class="description-block">
                                    <h5 class="description-header">{{number_format($data['unpaid'])}}</h5>
                                    <span class="description-text">Đơn hàng</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
                <!-- /.widget-user -->
            </div>
        </div>
    </div>
</div>