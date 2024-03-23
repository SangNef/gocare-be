@extends("la.layouts.app")

@section("contentheader_title", "Đơn hàng")
@section("contentheader_description", "Danh sách đơn hàng")
@section("section", "Đơn hàng")
@section("sub_section", "Danh sách")
@section("htmlheader_title", "Danh sách đơn hàng")

@section("headerElems")
@la_access("Orders", "create")
{{--<button id="modify-price" class="btn btn-warning btn-sm mr5" data-toggle="modal" data-target="#ModifyPrice">Điều chỉnh giá sản phẩm</button>--}}
@if(auth()->user()->isSupperAdminRole())
<button id="update-status" type="status" class="btn btn-warning btn-sm mr5">Cập nhật đơn hàng thành công</button>
{{--<button id="approve-all" type="approve" class="btn btn-warning btn-sm mr5">Duyệt đơn hàng</button>--}}
@endif
<button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#AddModal">Thêm đơn hàng</button>
@endla_access
{{--<button class="btn btn-warning btn-sm pull-right mr-1" data-toggle="modal" data-target="#ChangeProductModal">{{ trans('button.change_product') }}</button>--}}
@endsection
@push('styles')
<style>
	#example1 th:nth-child(1) input,
	#example1 th:nth-child(6) input {
		width: 100% !important;
	}
	/*  */
</style>
@endpush
@section("main-content")

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{!! $error !!}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('la.partials.created_filter', [
	'useExtraFilter' => true,
	'filterCreatedDate' => true,
	'filterDate' => true,
	'restoreState' => true,
	'totals' => ['total_amount' => 'Tổng tiền giao dịch', 'total_output' => 'Sản lượng'],
	'filterColumns' => [],
	'filterOptions' => [
		'status' => $orderStatus,
		'type' => [
			1 => trans('order.type_1'),
			2 => trans('order.type_2'),
		],
		'sub_type' => [
		  	1 => trans('order.sub_type_1'),
		  	2 => trans('order.sub_type_2'),  
		],
		'approve' => $approve,
		'cod_compare_status' => [
			0 => 'Chưa đối soát',
            1 => 'Đã đối soát',
			2 => 'Không vận chuyển'
		]
	],
	'extraForm' => 'la.orders.extra_filter_form',
])
<div class="box box-success">
	<!--<div class="box-header"></div>-->
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
					<th>&nbsp;</th>
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
	<div class="modal-dialog modal-90" role="document">
		{!! Form::open(['action' => 'LA\OrdersController@store', 'id' => 'order-add-form', 'class' => 'order-add-form-1']) !!}
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Thêm đơn hàng</h4>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger errors" style="display: none">
					<ul></ul>
				</div>
				<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
					<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-payment" data-target="#tab-payment"><i class="fa fa-dollar"></i>{{ trans('order.payment') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-discount" data-target="#tab-discount"><i class="fa fa-dollar"></i>Chiết khấu theo danh mục sản phẩm</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-extra-info" data-target="#tab-extra-info"><i class="fa fa-info"></i>{{ trans('order.extra_info') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-series" data-target="#tab-series"><i class="fa fa-ticket"></i>{{ trans('order.order_product_series') }}</a></li>
{{--					<li><a role="tab" data-toggle="tab" href="#tab-transport" data-target="#tab-transport"><i class="fa fa-truck"></i>{{ trans('order.transport') }}</a></li>--}}
				</ul>
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active fade in" id="tab-info">
						<div class="row">
							<div class="col-md-9 col-sm-12">
								<div id="adding-products">
									@include('la.products_selecting.selecting', [
										'selectedView' => 'la.products_selecting.order_selected_product',
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
								<div class="form-group" id="draft_order_input" style="display: none">
									<div class="input-group">
										<label for="status">Đơn nháp :</label>
										<select name="draft_order_id" class="form-control ajax-select" model="draft_order" id="draft_order" data-allow-clear="true">
										</select>
										<div class="input-group-btn">
											<button class="btn btn-primary btn-sm" type="button" id="load-draft-order" disabled="true">
												<i class="fa fa-refresh" aria-hidden="true"></i>
											</button>
										</div>
									</div>
									<small class="text-danger" style="display: none">* Đang nhập sản phẩm từ đơn nháp</small>
								</div>
								<div class="form-group">
									<label for="code">Mã đơn hàng* :</label>
									<input class="form-control" readonly="readonly" name="code" type="text" value="{{ app(\App\Services\Generator::class)->generateOrderCode() }}" />
								</div>
								<div class="form-group">
									<label for="code">Ngày tạo* :</label>
									<input class="form-control datepicker" name="created_at" type="text" value="{{ \Carbon\Carbon::today()->format('Y/m/d') }}" data-date-format="yyyy/mm/dd" />
								</div>
								<div class="form-group">
									<label for="status" style="margin-right: 20px">Khách hàng :</label>
									<select name="customer_id" extra_param="1" class="form-control ajax-select" model="customer" id="order_customer" lookup="order_store">
									</select>
								</div>
								@include('la.orders.add_user_form')
								<div class="form-group" style="display: none">
									<label for="sub_type" style="margin-right: 20px">Loại :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="sub_type" checked>Hàng mới</label>
									<label><input type="radio" value="2" name="sub_type">Hàng bảo hành</label>
								</div>
								<div class="form-group">
									<div>
										<label for="status" style="margin-right: 20px">Kiểu :</label>
										<label style="margin-right: 10px"><input type="radio" value="1" name="type">Nhập</label>
										<label><input type="radio" value="2" name="type" checked>Xuất</label>
									</div>
									<div id="currency_type" style="margin-top: 15px; display: none">
										<label for="currency_type" style="margin-right: 20px">Loại tiền tệ :</label>
										<label style="margin-right: 10px"><input type="radio" value="1" name="currency_type" checked>VNĐ</label>
										<label><input type="radio" value="2" name="currency_type">NDT</label>
									</div>
								</div>
								<div class="form-group">
									<label for="fee_bearer" style="margin-right: 20px">Chịu phí :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="fee_bearer" checked>Người mua</label>
									<label><input type="radio" value="2" name="fee_bearer">Người bán</label>
								</div>
								<div class="form-group" style="display: none">
									<label for="status" style="margin-right: 20px">Thu tiền :</label>
									<label style="margin-right: 10px"><input type="radio" value="1" name="payment_method" checked>Thanh toán sau</label>
									<label><input type="radio" value="2" name="payment_method"> Vận chuyển COD</label>
								</div>
								<div class="form-group">
									<label for="status">Trạng thái :</label>
									<select class="form-control" name="status">
										<option value="1">{{ trans('status.processing') }}</option>
										<option value="2">{{ trans('status.success') }}</option>
										<option value="3">{{ trans('status.refund') }}</option>
										<option value="4">{{ trans('status.cancel') }}</option>
									</select>
								</div>
								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											<label for="status">Phí :</label>
											<input id="order_fee" class="form-control currency text-right" name="fee" value="0"/>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<label for="status">{{ trans('order.discount') }} :</label>
											<input id="order_discount" class="form-control currency text-right" name="discount" value="0"/>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<label for="status">{{ trans('order.discount_percent') }} :</label>
											<input id="order_discount_percent" type="number" class="form-control text-right" name="discount_percent" value="0"/>
										</div>
									</div>
								</div>
								<div class="form-group" id="order_combo_discount_total" style="display: none">
									<label for="status">Giảm giá theo combo (<span class="text-sm text-warning">Đã trừ vào giá sản phẩm</span>):</label>
									<input class="plain-text form-control text-right" disabled value="0"/>
								</div>
								<div class="form-group" id="order_group_total">
									<label for="status">Giảm giá theo danh mục sản phẩm:</label>
									<input class="plain-text form-control text-right" disabled value="0"/>
								</div>
								<div class="form-group">
									<label for="status">Tổng tiền:</label>
									<input id="order_total" class="plain-text form-control text-right" disabled value="0"/>
								</div>
								<div class="form-group">
									<label for="status">Chọn nhanh ngân hàng  :</label>
									<select class="form-control ajax-select bank_id" id="automated-bank" name="automated_bank" model="banks" extra_param="1">
									</select>
								</div>
								<div class="form-group">
									<label for="status">Thanh toán :</label>
									<input class="form-control text-right total-paid-amount" readonly value="0 đ"/>
								</div>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-payment">
						<div class="row">
							<div class="col-sm-12 payment-info">
								<div class="form-group">
									<label for="status">Phương thức thanh toán :</label>
									<input class="form-control" name="payment[method]" disabled value="{{ trans('messages.bank_transfer') }}"/>
								</div>
								<div class="row payment-detail">
									<div class="col-sm-3 col-xs-6">
										<div class="form-group">
											<label for="status">Ngân hàng :</label>
											<select class="form-control ajax-select order-banks bank_id" model="banks" extra_param="1" lookup="order_store" name="payment[1][bank_id]">
											</select>
										</div>
									</div>
									<div class="col-sm-3 col-xs-6">
										<div class="form-group">
											<label for="status">Mã giao dịch :</label>
											<input class="form-control code" name="payment[1][code]" value=""/>
										</div>
									</div>
									<div class="col-sm-2 col-xs-4">
										<div class="form-group">
											<label for="status">Số tiền :</label>
											<input class="form-control paid-amount currency" name="payment[1][amount]" value="0 đ"/>
										</div>
									</div>
									<div class="col-sm-2 col-xs-3">
										<div class="form-group">
											<label for="status">Phí giao dịch :</label>
											<input class="form-control currency" name="payment[1][fee]" value="0"/>
										</div>
									</div>
									<div class="col-sm-1 col-xs-3 p0">
										<div class="form-group">
											<label for="status">Ngày GD:</label>
											<input class="form-control datepicker" name="payment[1][paid_date]" value="{{ \Carbon\Carbon::today()->format('Y/m/d') }}"/>
										</div>
									</div>
									<div class="col-sm-1 col-xs-2">
										<div class="form-group">
											<label for="status">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
											<button type="button" class="btn btn-success btn-xs add-payment-detail"><i class="fa fa-plus"></i></button>
											<button type="button" class="btn btn-danger btn-xs remove-payment-detail" disabled><i class="fa fa-minus"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-discount">
						<div class="row">
							<div class="col-sm-12">
								<table class="table-bordered table">
									<thead>
										<tr class="success">
											<th>STT</th>
											<th>Tên danh mục</th>
											<th>Số lượng</th>
											<th>Tổng tiền</th>
											<th>Giảm giá</th>
											<th>Giảm giá %</th>
											<th>Tổng giảm giá</th>
										</tr>
									</thead>
									<tbody>
									@foreach(\App\Models\ProductCategory::all() as $index => $pCate)
										<tr id="cate_{{ $pCate->id }}">
											<td>{{ $index+1 }}</td>
											<td>{{ $pCate->name }}</td>
											<td class="cate_quantity text-right">0</td>
											<td class="cate_total text-right">0</td>
											<td class="cate_discount text-right">0</td>
											<td class="cate_discount_1 text-right">0</td>
											<td class="cate_total_discount text-right">0</td>
										</tr>
									@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-extra-info">
						<div class="row">
							<div class="col-sm-12">
								@la_input($module, 'note')
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-series">
						<div class="col-sm-12">
							<div class="row">
								<div class="bg-gray" style="padding: 10px; display: flex; align-items: center ">
									<h3 style="margin: 0 50px 0 0;">{{ trans('messages.selected_products') }}</h3>
									<div class="form-group" style="margin-bottom: 0;">
										<label for="order_series_type" style="margin: 0 10px 0 0;">Gán seri :</label>
										<label style="margin: 0 10px 0 0;"><input type="radio" value="1" name="order_series_type" checked>Seri có sẵn</label>
										<label style="margin: 0 10px 0 0;"><input type="radio" value="2" name="order_series_type" disabled>Tạo seri mới</label>
										<label style="margin: 0;"><input type="radio" value="3" name="order_series_type">Cập nhật seri sau</label>
									</div>
								</div>
							</div>
							@include('la.products_selecting.selected_products_series', [
								'cols' => [
									[
										'name' => 'Tên sản phẩm',
										'width' => 20
									],
									[
										'name' => 'Series',
										'width' => 70
									],[
										'name' => 'Ngày tháng',
										'width' => 10
									]
								]
							])
						</div>
					</div>
					@include('la.orders.tabs.tab_transport')
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
					<button type="submit" class="btn btn-success" id="order-submit">Lưu</button>
					<button type="submit" class="btn btn-warning" id="draft-order-submit">{{ trans('button.save_draft') }}</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endla_access
<div class="modal fade" id="ChangeProductModal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{!! Form::open(['url' => route('order.switchProduct.create'), 'id' => 'order-add-form']) !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">{{ trans('button.change_product') }}</h4>
			</div>
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="status" style="margin-right: 20px">{{ trans('messages.customers') }} :</label>
						<select class="form-control ajax-select" model="customer" name="customer_id">

						</select>
					</div>
					<div class="row">
						<div class="col-sm-5">
							<div class="form-group">
								<label for="status" style="margin-right: 20px">{{ trans('messages.products') }} :</label>
								<select class="form-control ajax-select" model="product_for_switch" name="product_id">

								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="status" style="margin-right: 20px">{{ trans('messages.quantity') }} :</label>
								<input class="form-control integer" name="quantity" value="0">
							</div>
						</div>
						<div class="col-sm-4">

							<div class="form-group">
								<label for="status" style="margin-right: 20px">Kiểu :</label>
								<label style="margin-right: 10px"><input type="radio" value="1" name="type" checked>Bảo hành => Mới</label>
								<label><input type="radio" value="2" name="type">Mới => Bảo hành</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label style="margin-right: 10px"><input type="radio" value="1" name="fee_type" checked>Miễn phí</label>
						<label><input type="radio" value="2" name="fee_type">Mất phí</label>
					</div>
					<div class="order-payment" style="display: none">
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label for="status">{{ trans('messages.amount') }} :</label>
									<input class="form-control currency" name="payment[amount]" value="0"/>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<div class="form-group">
										<label style="margin-right: 10px"><input type="radio" value="1" name="transaction_type" checked>Nhận</label>
										<label><input type="radio" value="2" name="transaction_type">Chuyển</label>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label for="status">{{ trans('order.note')  }} :</label>
									<input class="form-control" name="note" value=""/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label for="status">Ngân hàng :</label>
									<select class="form-control ajax-select" model="banks" name="payment[bank_id]">
									</select>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label for="status">Mã giao dịch :</label>
									<input class="form-control" name="payment[code]" value=""/>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label for="status">{{ trans('order.payment_fee') }} :</label>
									<input class="form-control currency" name="payment[fee]" value="0"/>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-success onetime-click">Lưu</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
<div class="modal fade" id="ModifyPrice" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			{!! Form::open(['url' => route('order.modify-price'), 'id' => 'order-add-form']) !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">{{ trans('button.modify_price') }}</h4>
			</div>
			<div class="modal-body">
				<div class="box-body">
					<div class="form-group">
						<label for="status" style="margin-right: 20px">{{ trans('messages.products') }} :</label>
						<select class="form-control ajax-select" model="product_for_switch" name="product_id">

						</select>
					</div>
					<div class="row">
						<div class="col-md-12" id="list-customer">
							<table class="table table-bordered">
								<thead>
									<tr class="success">
										<th width="50%">Khách hàng</th>
										<th width="50%">Giá sản phẩm</th>
									</tr>
								</thead>
								<tbody>
									<tr><td colspan="7" class="text-center">No data</td></tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-success onetime-click">Lưu</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>

<div class="modal fade" id="PaymentResult" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<h3 class="text-danger result"></h3>
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="ProductAttrSelecting" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">

		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Chọn thuộc tính sản phẩm</h4>
				<input type="hidden" id="data-index">
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<div class="col-sm-12" style="text-align: right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
					<button type="button" class="btn btn-success">Lưu</button>
				</div>
			</div>
		</div>
	</div>
</div>
@include('la.orders.tabs.seri-selecting-modal')
@endsection
@include('la.orders.script')
@push('scripts')
<script type="text/javascript" src="/la-assets/js/scanner.js"></script>
<script>
	var url = "{{ url(config('laraadmin.adminRoute')) . '/order_dt_ajax?sessionKey=' . $sessionKey }}";
	if ("{{request('from')}}") {
		url += "&from={{request('from')}}";
	}
	if ("{{request('d')}}") {
		url += "&d={{request('d')}}";
	}
	if ("{{request('cross_store')}}") {
		url += "&cross_store={{request('cross_store')}}";
	}
	if ("{{request('payment_method')}}") {
		url += "&payment_method={{request('payment_method')}}";
	}
$(function () {
	const table = $("#example1").DataTable({
        
	});
	$('#AddModal').on('shown.bs.modal', function () {
		scanSeri();
	});
	$('#PaymentResult').on('shown.bs.modal', function (e) {
		var content = $(e.relatedTarget).attr('data-content');
		$(this).find('.result').html(content);
	});
	$(document).on('click', '.print-order', function (event) {
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

	$(document).on('click', '.print-orders,.print-selected-orders', function (event) {
		let print_type = $(this).attr('class').indexOf('print-selected-orders') !== -1 ? 2 : 1;
		let customerId = $('#filter_bar th.col-customer_id .filter-item').val();
		let category_id = $('select.filter-category-id').val() ? $('select.filter-category-id').val().join(',') : '';
		let el = $(this);
		if (!customerId) {
			alert('Chọn khách hàng');
			setTimeout(function () {
				el.removeAttr('disabled');
				el.html(print_type == 2 ? "In đơn được chọn" : 'IN');
				return;
			}, 1);
		}
		if (customerId) {
			let url = "{{ route('order.print-orders', ['sessionKey' => $sessionKey]) }}";
			if ("{{request('from')}}") {
				url += "&from={{request('from')}}";
			}
			if ("{{request('d')}}") {
				url += "&d={{request('d')}}";
			}
			url += "&customer_id=" + customerId;
			if ($(this).attr('class').indexOf('print-selected-orders') !== -1) {
				url += '&print_type=2';
			}
			let iframe = document.createElement('iframe');
			iframe.className='pdfIframe'
			document.body.appendChild(iframe);
			iframe.style.display = 'none';
			iframe.onload = function () {
				setTimeout(function () {
					iframe.focus();
					URL.revokeObjectURL(url);
					document.body.removeChild(iframe);
					el.removeAttr('disabled');
					el.html(print_type == 2 ? "In đơn được chọn" : 'IN');
				}, 1);
			};
			iframe.src = url;
		}
	})

	$("#order-add-form").validate({
		
	});

	
	$('input[name="fee_type"]').change(function () {
		$('.order-payment').hide();
		if  ($('input[name="fee_type"]:checked').val() == 2) {
			$('.order-payment').show();
		}
	})
	
	$('input[name="sub_type"]').change(function() {
		$('#selected_products').trigger('selecting_product.changed');
		$('.order-selected-product input').trigger('change');
	});

	$('#check-all').change(function (event) {
		if ($(this).prop('checked')) {
			$('input.row').prop('checked', true);
		} else {
			$('input.row').prop('checked', false);
		}
	});

	$('#update-status, #approve-all').click(function() {
		var selected = [];
		var approveUrl = "{{ url(config('laraadmin.adminRoute') . '/approve-order') }}";
		var type = $(this).attr('type');
		$('input.row').each(function () {
			if ($(this).prop('checked')) {
				selected.push($(this).val());
			}
		})
		
		if (selected.length > 0) {
			approveUrl += '?type=' + type + '&ids=' + selected.join(',');
			location.href = approveUrl;
		} else {
			alert('Chọn đơn hàng');
		}
	});

	$('#order-add-form input[name="currency_type"]').change(function() {
		$('#selected_products').trigger('selected_products.reload');
		$('#order-add-form .payment-detail .order-banks').attr('extra_param', $(this).val());
		$('#order_customer').attr('extra_param', $(this).val());
		initAjaxSelect();
	})

	$('input[name="fee_bearer"]').change(function() {
		updateOrderForm();
	});

	function loadCustomer(page = 1)
	{
		var productId = $('#ModifyPrice select[name="product_id"]').val();
		$.ajax({
			url: "{{ route('orderproduct.get-customer') }}?" + 'product_id=' + productId,
			data: {
				page: page
			},
			success: function(data) {
				$('#list-customer table tbody').html(data);
			}
		});
	}

	$(document).on('click', '#ModifyPrice .pagination a', function(e) {
		e.preventDefault();
		loadCustomer(GetURLParameter('page',  $(this).attr('href')));
	});

	$('#ModifyPrice select[name="product_id"]').change(function() {
		loadCustomer();
	});

	$('#list-customer').on('click', '.edit', function() {
		var id = $(this).data('id');
		var currentPrice = $(this).parent().find('span').text();
		currentPrice = Number(currentPrice.replace(/[^0-9.-]+/g,""));
		var input = '<input type="text" name="discount" class="currency" value="'+currentPrice+'"/>';
		$(this).parent().html(input + '<a data-id="'+id+'" class="btn btn-sm save"><i class="fa fa-save"></i></a>');
		initNumberInput();
	});

	$('#list-customer').on('click', '.save', function() {
		var id = $(this).data('id');
		var newPrice = $(this).parent().find('input[name="discount"]').val();
		var url = "{{ route('order.modify-price') }}?id=" + id + "&price=" + newPrice;
		$.ajax({
			url: url
		});
		$(this).parent().html(newPrice + '<a data-id="'+id+'" class="btn btn-sm edit"><i class="fa fa-pencil"></i></a>');
	});

	$('#province').change(function() {
        let id = $(this).find('option:selected').val();
        $('#district,#ward').find('option').not('.selected').remove();
        getAddress(id, 'province', '#district', '{{ route('customer.get-address') }}');
    });

    $('#district').change(function() {
        let id = $(this).find('option:selected').val();
        $('#ward').find('option').not('.selected').remove();
        getAddress(id, 'district', '#ward', '{{ route('customer.get-address') }}');
	});

	$('#order_store').change(function() {
		if (typeof setStoreFromCustomer === 'undefined' || !setStoreFromCustomer) {
			$('.order-banks').val('').change();
			$('#order_customer').val('').change();
		}
		loadProduct();
	});
	$('.order-add-form-1').submit(function (e) {
		var button = $(e.originalEvent.submitter).attr('id');
		e.preventDefault();
		var form = $(this);
		form.find('button[type="submit"]').prop('disabled', true);
		form.find('.alert-danger').hide();
		var url = form.attr('action');
		if (button == 'draft-order-submit') {
			url = '{{ route('order.save-draft') }}';
		}
		$.ajax({
			url: url,
			data: form.serialize(),
			type: 'POST',
			success: function (data) {
				location.reload();
			},
			error: function (data) {
				var errors = data.responseJSON;
				var html = '';
				var existsIndex = [];
				$('.order-selected-product').map(function () {
					existsIndex.push($(this).attr('data-index'));
				})
				var errorFields = Object.keys(errors);
				Object.values(errors).forEach(function (error, index) {
					var extra = '';
					if (errorFields[index].indexOf('products') != -1) {
						var productIndex = errorFields[index].split('.');
						if (productIndex.length > 2) {
							var dataIndex = productIndex[1];
							extra += ' - Dòng ' + (existsIndex.indexOf(dataIndex) + 1);
						}
					}
					html += '<li>' + error.join('<br>') + extra + '</li>';
				});
				form.find('.alert-danger').show();
				form.find('.alert-danger ul').html(html);
				form.find('button[type="submit"]').prop('disabled', false);
				
			}
		})
	})
	$('#admin-filter-form .format-bills').change(function(e) {
		let bills = formatShippingBillIds(e.target.value);
        $(this).val(bills);
    });
});
</script>
@endpush
