@extends('la.layouts.app')

@section('htmlheader_title') Tổng quan @endsection
@section('contentheader_title') Tổng quan @endsection
@section('contentheader_description') @endsection

@section('main-content')
<!-- Main content -->
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-light-blue">
                <div class="inner">
                    <h3>{{$data['order']}}</h3>
                    <p>Đơn hàng hôm nay</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="{{ config('laraadmin.adminRoute') . "/orders/?filter[0][value]=" . \Carbon\Carbon::now()->format('Y/m/d') }}" class="small-box-footer">Chi tiết<i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{$data['product']}}</h3>
                    <p>Sản phẩm</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="{{ config('laraadmin.adminRoute') . "/products/"}}" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{$data['post']}}</h3>
                    <p>Bài viết</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="{{ config('laraadmin.adminRoute') . "/posts/"}}" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{$data['ctv']}}</h3>
                    <p>Cộng tác viên</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ config('laraadmin.adminRoute') . "/customers/?type=ctv" }}" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{$data['daily']}}</h3>
                    <p>Đại lý</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ config('laraadmin.adminRoute') . "/customers/?type=daily"}}" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-2 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-olive">
                <div class="inner">
                    <h3>{{$data['khachhang']}}</h3>
                    <p>Khách hàng</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ config('laraadmin.adminRoute') . "/customers/?type=khachhang"}}" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
    </div><!-- /.row -->
    <!-- Main row -->
    @if(!auth()->user()->haveRoleMustBeExcludeFromRoutes())
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs pull-right">
                    <li class="pull-left header"><i class="fa fa-inbox"></i>Báo cáo chi tiết</li>
                    <div class="" style="margin: 3px 8px;">
                        <button type="button" class="btn btn-default float-right" id="daterange-btn">
                            <i class="ion ion-calendar"></i> Chọn thời gian
                            <i class="ion ion-android-arrow-dropdown"></i>
                        </button>
                    </div>
                </ul>
                <div class="tab-content no-padding">
                    <!-- Morris chart - Sales -->
                    <div id="report"></div>
                </div>
            </div><!-- /.nav-tabs-custom -->
        </section><!-- right col -->
    </div><!-- /.row (main row) -->
    @endif
    <div class="row">
        <div class="col-md-6">
            <div class="card dash-product" style="padding: 1px 20px;">
                <div class="card-header border-0">
                    <h3 class="card-title"><a href="{{ config('laraadmin.adminRoute') . "/products/"}}">Sản phẩm hết
                            hàng ({{count($product)}})</a></h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SKU</th>
                                <th>Giá</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($product) && count($product)>0)
                            @foreach($product as $p)
                            <tr>
                                <td>
                                    {!! $p->getFullFeaturedImage(32) !!}
                                    <span style="margin-left: 5px;">{{ $p->name }}</span>
                                </td>
                                <td>{{ $p->sku }}</td>
                                <td>{{ number_format($p->retail_price) }}đ</td>
                                <td>
                                    <a href="{{ config('laraadmin.adminRoute') . "/products/" . $p->id}}" class="text-muted">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">

            <!-- /.card -->

            <div class="card dash-product" style="padding: 1px 20px;">
                <div class="card-header">
                    <h3 class="card-title"><a href="{{ config('laraadmin.adminRoute') . "/orders/"}}">Đơn hàng đang
                            xử lý ({{$data['order_count']}} đơn)</a></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($data['order_list']) && count($data['order_list'])>0) @foreach($data['order_list'] as $k=>$r)
                            <tr>
                                <td>{{$k+1}}.</td>
                                <td>
                                    <a href="{{ config('laraadmin.adminRoute') . "/orders/" . $r->id}}">{{$r->code}}</a>
                                </td>
                                <td>
                                    {{$r->customer->email}}
                                </td>
                                <td><span class="badge bg-info">{{number_format($r->total)}}đ</span></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</section><!-- /.content -->
@endsection

@push('styles')
<!-- Morris chart -->
<link rel="stylesheet" href="{{ asset('la-assets/css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('la-assets/plugins/morris/morris.css') }}">
<!-- jvectormap -->
<link rel="stylesheet" href="{{ asset('la-assets/plugins/jvectormap/jquery-jvectormap-1.2.2.css') }}">
<!-- Date Picker -->
<link rel="stylesheet" href="{{ asset('la-assets/plugins/datepicker/datepicker3.css') }}">
<!-- Daterange picker -->
<link rel="stylesheet" href="{{ asset('la-assets/plugins/daterangepicker/daterangepicker-bs3.css') }}">
<!-- bootstrap wysihtml5 - text editor -->
<link rel="stylesheet" href="{{ asset('la-assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
@endpush


@push('scripts')
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Morris.js charts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="{{ asset('la-assets/plugins/morris/morris.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('la-assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
<!-- jvectormap -->
<script src="{{ asset('la-assets/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
<script src="{{ asset('la-assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('la-assets/plugins/knob/jquery.knob.js') }}"></script>
<!-- daterangepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="{{ asset('la-assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- datepicker -->
<script src="{{ asset('la-assets/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="{{ asset('la-assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('la-assets/plugins/fastclick/fastclick.js') }}"></script>
<!-- dashboard -->
<script src="{{ asset('la-assets/js/pages/dashboard.js') }}"></script>
@endpush

@push('scripts')
<script>
    var xhrPool = [];

    function abortAll() {
        xhrPool.map(function(jqXHR) {
            jqXHR.abort();
        });
        xhrPool = [];
    }

    function loadReport(page, $datetime = null) {
        abortAll();
        $.ajax({
            url: '{{ url(config('
            laraadmin.adminRoute ') . ' / dashboard / report ') }}?' + $datetime,
            beforeSend: function(jqXHR, settings) {
                xhrPool.push(jqXHR);
            },
            success: function(data) {
                $('#report').html(data);
            }
        });
    }

    (function($) {
        loadReport(1);
        $(document).on('click', '#customers-pagination a', function(e) {
            e.preventDefault();
            loadReport($(this).text());
        });
    })(window.jQuery);
    $('#daterange-btn').daterangepicker({
            ranges: {
                'Hôm nay': [moment(), moment()],
                'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Ngày trước': [moment().subtract(6, 'days'), moment()],
                '30 ngày trước': [moment().subtract(29, 'days'), moment()],
                'Tháng này': [moment().startOf('month'), moment().endOf('month')],
                'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
            $date = 'from=' + start.format('YYYY-M-D') + '&to=' + end.format('YYYY-M-D');
            loadReport(1, $date);
        }
    )
</script>
@endpush