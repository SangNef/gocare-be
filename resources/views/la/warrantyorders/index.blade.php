@extends("la.layouts.app")

@section("contentheader_title", "WarrantyOrders")
@section("contentheader_description", "WarrantyOrders listing")
@section("section", "WarrantyOrders")
@section("sub_section", "Listing")
@section("htmlheader_title", "WarrantyOrders Listing")

@section("headerElems")
@la_access("WarrantyOrders", "create")
	<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm đơn hàng bảo hành</button>
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


@include('la.partials.created_filter', [
	'useExtraFilter' => true,
	'filterCreatedDate' => true,
	'filterDate' => true,
	'restoreState' => true,
	'filterColumns' => [],
	'filterOptions' => [
		'type' => [
			1 => trans('order.type_1')
		],
		'status' => [
			1 => trans('status.received'),
            2 => trans('status.processing'),
			5 => trans('status.success')
		]
	],
	'extraForm' => 'la.warrantyorders.extra_filter_form',
])


<div class="box box-success">
	<div class="box-body">
		<table id="example1" class="table table-bordered">
			<thead>
			<tr class="success">
				@foreach( $listing_cols as $k => $col )
					<th>
						@if ($k === 0)		
						<input type="checkbox" id="check-all" />
						@endif
						@if ($col == 'number_of_products')
						SL
						@else
						{{ $module->fields[$col]['label'] or ucfirst($col) }}
						@endif
					</th>
				@endforeach
				@if($show_actions)
					<th>Actions</th>
				@endif
			</tr>
			</thead>
			<thead id="filter_bar"> 
			<tr class="success">
				@foreach( $listing_cols as $col )
					<th colname="{!! isset($module->fields[$col]) ? $module->fields[$col]['colname'] : $col !!}">{{ $module->fields[$col]['label'] or ucfirst($col) }}</th>
				@endforeach
			</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
</div>


@la_access("Orders", "create")
<div class="modal fade" id="AddModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-70" role="document">
		{!! Form::open(['action' => 'LA\WarrantyOrdersController@store', 'id' => 'warrantyorders-add-form', 'class' => 'order-add-form-1']) !!}
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm đơn hàng bảo hành</h4>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger errors" style="display: none">
					<ul></ul>
				</div>
				<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
					<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-series" data-target="#tab-series"><i class="fa fa-ticket"></i>{{ trans('order.order_product_series') }}</a></li>
				</ul>
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
						<div class="row">
							<div class="col-md-9 col-sm-12">
								<div id="adding-products">
									@include('la.products_selecting.selecting', [
										'selectedView' => 'la.products_selecting.warrantyorder_selected_product',
										'layout' => 'block',
										'productType' => '',
										'excludeFilter' => [
										   'filter[6000]' => [
											   'field' => 'status',
											   'value' => 1
										   ]
										]
									])
								</div>
							</div>
							<div class="col-md-3 col-sm-12">
								@if (!auth()->user()->store_id)
									<div class="form-group">
										<label for="status" style="margin-right: 20px">Kho :</label>
										<select name="store_id" class="form-control ajax-select" model="stores" id="order_store">
										</select>
									</div>
								@endif
								<div class="form-group">
									<label for="code">Mã đơn hàng* :</label>
									<input class="form-control" readonly="readonly" name="code" type="text" value="{{ app(\App\Services\Generator::class)->generateOrderCode('BH') }}" />
								</div>

								<div class="form-group">
									<label for="code">Ngày tạo* :</label>
									<input class="form-control datepicker" name="created_at" type="text" value="{{ \Carbon\Carbon::today()->format('Y/m/d') }}" data-date-format="yyyy/mm/dd" />
								</div>
								<div class="form-group">
									<label for="status" style="margin-right: 20px">Khách hàng :</label>
									<select name="customer_id" class="form-control ajax-select" model="customer" id="order_customer" lookup="order_store">
									</select>
								</div>
								<div class="form-group">
									<label for="status" style="margin-right: 20px">Kiểu :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="type" checked>Nhập</label>
								</div>
								<div class="form-group">
									<label for="currency_type" style="margin-right: 20px">Loại tiền tệ :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="currency_type" checked>VNĐ</label>
								</div>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-series">
						<div class="col-sm-12">
							<div class="row">
								<div class="bg-gray" style="padding: 10px; display: flex; align-items: center ">
									<h3 style="margin: 0 50px 0 0;">{{ trans('messages.selected_products') }}</h3>
								</div>
							</div>
                            @include('la.products_selecting.selected_products_series', [
								'cols' => [
									[
										'name' => 'ID',
										'width' => 5
									],
									[
										'name' => 'Tên sản phẩm',
										'width' => 10
									],
									[
										'name' => 'Series',
										'width' => 15
                                    ],
									[
										'name' => 'Phân loại',
										'width' => 20
									],
                                    [
										'name' => 'Trạng thái',
										'width' => 20
									],
									[
										'name' => 'Ghi chú',
										'width' => 30
									]
								]
							])
                        </div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-success" id="order-submit"Lưu/button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endla_access
@include('la.warrantyorders.multiple-bill-lading-modal')

@endsection

@include('la.warrantyorders.script')
@push('scripts')
<script>
var url = "{{ url(config('laraadmin.adminRoute')) . '/warrantyorder_dt_ajax' }}";
$(function () {
	$("#warrantyorders-add-form").validate({
		
	});

	$('#check-all').change(function (event) {
		if ($(this).prop('checked')) {
			$('input.row:not(:disabled)').prop('checked', true);
		} else {
			$('input.row').prop('checked', false);
		}
	});

	$('#warrantyorders-add-form').submit(function (e) {
		e.preventDefault();
		let form = $(this);
		$.ajax({
			url: form.attr('action'),
			data: form.serialize(),
			type: 'POST',
			beforeSend: function() {
				form.find('button[type="submit"]').prop('disabled', true);
				form.find('.alert-danger').hide();
			},
			success: function (data) {
				location.reload();
			},
			error: function (data) {
				let errors = data.responseJSON;
				let html = '';
				Object.values(errors).forEach(function (error) {
					html += '<li>' + error.join('<br>') + '</li>';
				});
				form.find('.alert-danger').show();
				form.find('.alert-danger ul').html(html);
				form.find('button[type="submit"]').prop('disabled', false);
			}
		})
	})

    $('#AddModal').on('shown.bs.modal', function () {
        scanSeri();
    });

	$(document).on('change', '#filter_bar th.col-customer_id .filter-item', function() {
		if ($(this).val()) {
			$('#print').attr('disabled', false);
		} else {
			$('#print').attr('disabled', true);
		}
	});

	$('#print').click(function(event) {
		let customerId = $('#filter_bar th.col-customer_id .filter-item').val();
		let el = $(this);
		if (!customerId) {
			alert('Chọn khách hàng');
			setTimeout(function () {
				el.removeAttr('disabled');
				el.html('IN');
				return;
            }, 1);
		}
		if (customerId) {
			handlePrint({
				type: "multiple",
				customer_id: customerId,
			}, function() {
				el.removeAttr('disabled');
				el.html('IN');
			});
		}
	});
});
</script>
@endpush
