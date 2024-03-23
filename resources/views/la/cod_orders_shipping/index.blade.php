@extends("la.layouts.app")

@section("contentheader_title", "CODOrdersShipping")
@section("contentheader_description", "CODOrdersShipping listing")
@section("section", "CODOrdersShipping")
@section("sub_section", "Listing")
@section("htmlheader_title", "CODOrdersShipping Listing")

@section("headerElems")
@la_access("CODOrdersShipping", "create")
	@if(request('type'))
    <button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Add Order</button>
    @else
    <a href="{{ url(config('laraadmin.adminRoute') . '/cod-orders-shipping?type=1') }}" class="btn btn-success btn-sm pull-right">Tạo đơn hàng đi</a>
    <a href="{{ url(config('laraadmin.adminRoute') . '/cod-orders-shipping?type=2') }}" class="btn btn-success btn-sm pull-right mr-1">Tạo đơn hàng hoàn</a>
    @endif
@endla_access
@endsection

@section("main-content")

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('la.datatable.index', ['id' => 'shipping-order', 'path' => 'cod_orders_shipping_dt_ajax?type='.request('type'), 'cols' => [
    [
        'title' => 'ID',
        'field' => 'id',
    ],
    [
        'title' => 'Đối tác vận chuyển',
        'field' => 'partner',
    ],
    [
        'title' => 'Trạng thái',
        'field' => 'status',
    ],
    [
        'title' => 'Loại',
        'field' => 'type',
    ],
    [
        'title' => 'Tổng cước',
        'field' => 'total_cod',
    ],
    [
        'title' => 'Tổng phí',
        'field' => 'total_fee',
    ],
    [
        'title' => 'Ngày tạo',
        'field' => 'created_at',
    ],
    [
        'title' => 'Ghi chú',
        'field' => 'note',
    ]
],
    'extraFilter' => 'la.cod_orders_shipping.extra-filter', 
    'filterOptions' => [
    'partner' => [
        'vtp' => 'ViettelPost',
        'ghn' => 'GHN',
    ],
    'type' => $typeList,
    'status' => $statusList,
]])

@la_access("CODOrdersShipping", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-70" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Add Order</h4>
                <div class="alert alert-danger errors" style="display: none">
					<ul>
					</ul>
				</div>
			</div>
			{!! Form::open(['action' => ['LA\CODOrdersShippingController@store', 'type' => request('type')], 'id' => 'shipping-order-form', 'method' => 'POST']) !!}
			<div class="modal-body">
				<div class="box-body">
                    <div class="row">
                        <div class="col-sm-9" id="relation_orders">
                            <div class="form-group">
                                <select
                                    id="search-order" 
                                    class="form-control"
                                    multiple="" value="">
                                </select>
                            </div>
                            <div id="selected_orders">
                                @include('la.cod_orders_shipping.selected_order_list')
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label style="margin-right: 20px">Thao tác :</label>
                                <label style="margin-right: 10px"><input type="radio" name="handle_type" value="1">Thủ công</label>
                                <label><input type="radio" name="handle_type" value="2" checked>Quét mã vạch</label>
                            </div>
                            <div class="form-group">
                                <label style="margin-right: 20px">Đối tác vận chuyển :</label>
                                <label style="margin-right: 10px"><input type="radio" value="vtp" name="partner">Viettel Post</label>
                                <label><input type="radio" value="ghn" name="partner" checked>GHN</label>
                                <label><input type="radio" value="ghn_5" name="partner" checked>GHN < 5kg</label>
                                <label><input type="radio" value="ghtk" name="partner" checked>GHTK</label>
                                <label><input type="radio" value="other" name="partner" checked>Vận chuyển khác</label>
                            </div>
                            <div class="form-group">
                                <label for="status">Trạng thái :</label>
                                <select class="form-control" name="status">
                                    @foreach($statusList as $key => $value)
                                    <option value="{{ $key }}" @if($key == 1) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="note">Ghi chú:</label>
                                <textarea class="form-control tinymce" placeholder="Enter ghi chú" cols="30" rows="10" name="note"></textarea>
                            </div>
                        </div>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				{!! Form::submit( 'Submit', ['class'=>'btn btn-success']) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endla_access

@endsection
@include('la.cod_orders_shipping.script')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
@endpush

@push('scripts')
<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
$(function () {
    $(document).on('click', '.print-order', function (event) {
        url = "{{ url(config('laraadmin.adminRoute')) . 'cod_orders_shipping_dt_ajax?type='.request('type') }}";
		event.preventDefault();
		var el = $(this);
		var iframe = document.createElement('iframe');
		iframe.className='pdfIframe'
		document.body.appendChild(iframe);
		iframe.style.display = 'none';
		iframe.onload = function () {
			setTimeout(function () {
				iframe.focus();
				URL.revokeObjectURL(url)
				document.body.removeChild(iframe)
				el.removeAttr('disabled');
				el.html('IN');
			}, 1);
		};
		iframe.src = $(this).attr('href');
	})
	$("#shipping-order-form").validate({
		
	});

    $('#admin-filter-form .format-bills').change(function(e) {
		let bills = formatShippingBillIds(e.target.value);
        $(this).val(bills);
    });
});
</script>
@endpush
