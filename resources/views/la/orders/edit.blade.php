@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/orders') }}">Đơn hàng</a> :
@endsection
@section("contentheader_description", $order->$view_col)
@section("section", "Đơn hàng")
@section("section_url", url(config('laraadmin.adminRoute') . '/orders'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa đơn hàng : ".$order->$view_col)

@section("main-content")

<div class="alert alert-danger errors" style="display: @if(count($errors) > 0) block @else none @endif">
	<ul>
		@foreach ($errors->all() as $error)
			@if(is_array($error))
				@foreach($error as $mess)
					<li>{{ $mess }}</li>
				@endforeach
			@else
				<li>{{ $error }}</li>
			@endif
		@endforeach
	</ul>
</div>

<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		{!! Form::model($order, ['route' => [config('laraadmin.adminRoute') . '.orders.update', $order->id ], 'method'=>'PUT', 'id' => 'order-add-form']) !!}
		<div class="row">
			<div class="col-sm-12">
				<ul data-toggle="ajax-tab" class="nav nav-tabs profile" role="tablist">
					<li class="active"><a role="tab" data-toggle="tab" class="active" href="#tab-general-info" data-target="#tab-info"><i class="fa fa-bars"></i>{{ trans('messages.general_info') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-payment" data-target="#tab-payment"><i class="fa fa-dollar"></i>{{ trans('order.payment') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-discount" data-target="#tab-discount"><i class="fa fa-dollar"></i>Chiết khấu theo danh mục sản phẩm</a></li>
					<li><a role="tab" data-toggle="tab" href="#tab-extra-info" data-target="#tab-extra-info"><i class="fa fa-info"></i>{{ trans('order.extra_info') }}</a></li>
					<li><a role="tab" data-toggle="tab" href="#order_product_series" data-target="#order_product_series"><i class="fa fa-info"></i>{{ trans('order.order_product_series') }}</a></li>
	{{--				<li><a role="tab" data-toggle="tab" href="#tab-transport" data-target="#tab-transport"><i class="fa fa-truck"></i>{{ trans('order.transport') }}</a></li>--}}
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
										'selectedProducts' => $order->orderProducts->map(function (\App\Models\OrderProduct $orderProduct, $key) use ($order, $existedIndex) {
											return  [
												'note' => $orderProduct->note,
												'products' => [$orderProduct->product],
												'lastestPrice' => $orderProduct->price,
												'n_quantity' => $orderProduct->quantity,
												'w_quantity' => $orderProduct->w_quantity,
												'quantity' => (int) $orderProduct->quantity + (int) $orderProduct->w_quantity,
												'selectedAttrs' => [explode(',', $orderProduct->attr_ids)],
												'existedIndex' => [$existedIndex[$key]],
												'has_combo' => $orderProduct->combo_id,
												'combo' => \App\Models\ProductCombo::find($orderProduct->combo_id),
												'parent_id' => $orderProduct->parent_id,
												'discount_percent' => $orderProduct->discount_percent,
												'dimension' => $orderProduct->dimension,
											];
										}),
										'excludeFilter' => [
										   'filter[6000]' => [
											   'field' => 'status',
											   'value' => 1
										   ]
										],
										'currencyType' => $order->currency_type,
										'sub_type' => $order->sub_type,
										'addMore' => $order instanceof \App\Models\DOrder && $order->isFromFE() ? false : true
									])
								</div>
							</div>
							<div class="col-md-3 col-sm-12">
								<div class="form-group">
									<label for="code">Mã đơn hàng* :</label>
									<input class="form-control" readonly="readonly" name="code" type="text" value="{{ $order->code }}" />
								</div>
								<div class="form-group">
									<label for="code">Ngày tạo* :</label>
									<input class="form-control datepicker" name="created_at" type="text" value="{{ $order->created_at->format('Y/m/d') }}"/>
								</div>
								<div class="form-group">
									<label for="status" style="margin-right: 20px">Khách hàng :</label>
									<select name="customer_id" extra_param="{{ $order->currency_type }}" class="form-control @if (!$order instanceof \App\Models\DOrder || !$order->isFromFE()) ajax-select @endif" model="customer" id="order_customer">
										<option value="{{ $order->customer_id }}" selected>{{ $order->customer->name }}</option>
									</select>
								</div>
								@include('la.orders.add_user_form')
								<div class="form-group" style="display: none">
									<label for="status" style="margin-right: 20px">Loại :</label>
									{!! $order->getSubTypeHTMLFormatted() !!}
									<input type="hidden" name="sub_type" value="{{ $order->sub_type }}" checked>
								</div>
								<div class="form-group">
									<div>
										<label for="status" style="margin-right: 20px">Kiểu :</label>
										{!! $order->getTypeHTMLFormatted() !!}
										<input type="hidden" name="type" value="{{ $order->type }}" checked>
									</div>
								</div>
								<div class="form-group">
									<label for="fee_bearer" style="margin-right: 20px">Chịu phí :</label>
									{!! $order->getFeeBearerHTMLFormatted() !!}
								</div>
								<div class="form-group" style="display: none">
									<label for="fee_bearer" style="margin-right: 20px">Thu phí :</label>
									{!! $order->getPaymentMethodHTMLFormatted() !!}
									<input type="hidden" name="payment_method" value="{{ $order->payment_method }}">
								</div>
								<div class="row">
									<div class="col-sm-4">
										<div class="form-group">
											<label for="status">Phí :</label>
											<input id="order_fee" class="form-control currency text-right" name="fee" value="{{ $order->fee }}"/>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<label for="status">{{ trans('order.discount') }} :</label>
											<input id="order_discount" class="form-control currency text-right" name="discount" value="{{ $order->discount }}"/>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<label for="status">{{ trans('order.discount_percent') }} :</label>
											<input id="order_discount_percent" type="number" class="form-control text-right" name="discount_percent" value="{{ $order->discount_percent }}"/>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label for="status">Trạng thái :</label>
									<select class="form-control" name="status" @cannot('update-status', $order) disabled @endcannot>
										@foreach ($orderStatus as $value => $label)
											<option value="{{ $value }}" @if ($value == $order->status) selected @endif>{{ $label }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label for="status">Duyệt :</label>
									<select class="form-control" name="approve" @cannot('approve-order', $order) disabled @endcannot>
										@foreach ($approve as $value => $label)
											<option value="{{ $value }}" @if ($value == $order->approve) selected @endif>{{ $label }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group" id="order_combo_discount_total" style="display: none">
									<label for="status">Giảm giá theo combo (<span class="text-sm text-warning">Đã trừ vào giá sản phẩm</span>):</label>
									<input class="plain-text form-control text-right" disabled value="0"/>
								</div>
								<div class="form-group" id="order_group_total">
									<label for="status">Giảm giá theo danh mục sản phẩm:</label>
									<input class="plain-text form-control text-right" disabled value="{{ number_format($order->discount_by_cate) }} đ"/>
								</div>
								<div class="form-group">
									<label for="status">Tổng tiền:</label>
									<input id="order_total" class="plain-text form-control currency" type="text" disabled value="{{ $order->total }} đ"/>
								</div>
								<div class="form-group">
									<label for="status">Thanh toán :</label>
									<input class="form-control currency total-paid-amount" type="text" value="{{ $order->paid }} đ" readonly/>
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
								@if(!$transactions->isEmpty())
								@foreach($transactions as $key => $paid)
									<div class="row payment-detail">
										<input type="hidden" name="payment[{{ $key }}][payment_type]" value="{{ $paid->type }}">
										<div class="col-sm-3 col-xs-6">
											<div class="form-group">
												<label for="status">Ngân hàng :</label>
												<select class="form-control ajax-select bank_id" model="banks" name="payment[{{ $key }}][bank_id]" extra_param="{{ $order->currency_type }}" @if($order->status == 2) disabled @endif>
													@if ($paid->bank_id)
														<option value="{{ $paid['bank_id'] }}">{{ implode(' - ', [$paid->bank->name, $paid->bank->branch, $paid->bank->acc_name, $paid->bank->acc_id]) }}</option>
													@endif
												</select>
												<input type="hidden" name="payment[{{ $key }}][transaction_id]" value="{{ $paid->id }}">
											</div>
										</div>
										<div class="col-sm-3 col-xs-6">
											<div class="form-group">
												<label for="status">Mã giao dịch :</label>
												<input class="form-control" name="payment[{{ $key }}][code]" value="{{ $paid->trans_id }}" @if($order->status == 2) disabled @endif/>
											</div>
										</div>
										<div class="col-sm-2 col-xs-4">
											<div class="form-group">
												<label for="status">Số tiền :</label>
												<input class="form-control paid-amount currency" name="payment[{{ $key }}][amount]" value="{{ max($paid->received_amount, $paid->transfered_amount) }}" @if($order->status == 2) disabled @endif/>
											</div>
										</div>
										<div class="col-sm-2 col-xs-3">
											<div class="form-group">
												<label for="status">Phí giao dịch :</label>
												<input class="form-control currency" name="payment[{{ $key }}][fee]" value="{{ $paid->fee }}" @if($order->status == 2) disabled @endif/>
											</div>
										</div>
										<div class="col-sm-1 col-xs-3 p0">
											<div class="form-group">
												<label for="status">Ngày GD:</label>
												<input class="form-control datepicker" name="payment[{{ $key }}][paid_date]" value="{{ $paid->created_at->format('Y/m/d') }}" @if($order->status == 2) disabled @endif/>
											</div>
										</div>
										<div class="col-sm-1 col-xs-1">
											<div class="form-group">
												<label for="status">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
												<button type="button" class="btn btn-success btn-xs add-payment-detail" @if($order->status == 2) disabled @endif><i class="fa fa-plus"></i></button>
												<button type="button" class="btn btn-danger btn-xs remove-payment-detail" @if ($order->status == 2) disabled @endif><i class="fa fa-minus"></i></button>
											</div>
										</div>
									</div>
								@endforeach
								@else
								<div class="row payment-detail">
									<div class="col-sm-3 col-xs-6">
										<div class="form-group">
											<label for="status">Ngân hàng :</label>
											<select class="form-control ajax-select bank_id" model="banks" name="payment[1][bank_id]" extra_param="{{ $order->currency_type }}">
											</select>
										</div>
									</div>
									<div class="col-sm-3 col-xs-6">
										<div class="form-group">
											<label for="status">Mã giao dịch :</label>
											<input class="form-control" name="payment[1][code]" value=""/>
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
								@endif
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="tab-extra-info">
						<div class="row">
							<div class="col-sm-12">
								@la_input($module, 'note')
								<div class="form-group">
									<label for="status">Sửa tổng tiền đơn hàng :</label>
									<input class="form-control currency" name="modify_total_price" value="0" id="modify_total_price"/>
								</div>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane fade in" id="order_product_series">
						<div class="col-sm-12">
							<div class="row">
								<div class="bg-gray" style="padding: 10px; display: flex; align-items: center ">
									<h3 style="margin: 0 50px 0 0;">{{ trans('messages.selected_products') }}</h3>
									<div class="form-group" style="margin-bottom: 0;">
										<label for="order_series_type" style="margin: 0 10px 0 0;">Gán seri :</label>
										@if(!$order->order_series_type || $order instanceof \App\Models\DOrder)
											<label style="margin: 0 10px 0 0;"><input type="radio" value="1" name="order_series_type" @if (!$order->order_series_type || $order->order_series_type == 1) checked @endif>Seri có sẵn</label>
											<label style="margin: 0 10px 0 0;"><input type="radio" value="2" name="order_series_type" @if($order->type == 2) disabled @endif>Tạo seri mới</label>
											<label style="margin: 0;"><input type="radio" value="3" name="order_series_type" @if (@$order->order_series_type == 3) checked @endif>Cập nhật seri sau</label>
										@else
										<span class="label label-warning">{{ \App\Models\Order::getOrderSeriesType()[$order->order_series_type] }}</span>
										<input type="hidden" name="order_series_type" value="{{ $order->order_series_type }}">
										@endif
									</div>
								</div>
							</div>
							@include('la.products_selecting.selected_products_series', [
								'view' => 'la.products_selecting.order_selected_product_seri',
								'selectedProducts' => $order->orderProducts->map(function (\App\Models\OrderProduct $orderProduct, $key) use ($order, $existedIndex) {
									$groupAttribute = \App\Models\ProductGroupAttributeMedia::where('attribute_value_ids', $orderProduct->attr_ids)
										->where('product_id', $orderProduct->product_id)
										->first();
									$selectedSeris = \App\Models\ProductSeri::where('order_id', $order->id)
											->where('product_id', $orderProduct->product_id);
									if ($groupAttribute) {
										$selectedSeris->where('group_attribute_id', $groupAttribute->id);
									}
									return [
										'products' => [$orderProduct->product],
										'selected_series' => $selectedSeris->pluck('seri_number', 'id')
											->toArray(),
										'attrs' => app(\App\Repositories\AttributeValueRepository::class)->getAttrs(explode(',', $orderProduct->attr_ids)),
										'selectedAttrs' => explode(',', $orderProduct->attr_ids),
										'index' => $existedIndex[$key]
									];
								}),
								'order' => $order,
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
					@include('la.orders.tabs.tab_transport', [
						'products' => $transport && $transport->transportOrderProducts->count() > 0
							? $transport->transportOrderProducts
							: $order->products,
						'existedIndex' => $existedIndex,
						'attrs' => $order->orderProducts->map(function (\App\Models\OrderProduct $orderProduct) {
							return app(\App\Repositories\AttributeValueRepository::class)->getAttrs(explode(',', $orderProduct->attr_ids));
						}),
						'selectedAttrs' => $order->orderProducts->map(function (\App\Models\OrderProduct $orderProduct) {
							return explode(',', $orderProduct->attr_ids);
						}),
					])
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
				</div>
				<div class="col-sm-12" style="text-align: right">
					<input type="hidden" name="store_id" value="{{ $order->store_id }}">
					@if($order->isCODOrder())
						@if(!$codOrder)
						@if ($billLadingHtml)
							<button type="button" data-toggle="modal" data-target="#bill_ladding_modal" class="btn btn-success">Vận đơn hàng</button>
						@endif
						<button type="button" data-toggle="modal" data-target="#fake-cod" class="btn btn-primary">
							Fake vận chuyển
						</button>
						@elseif($codOrder)
						<button type="button" data-toggle="modal" data-target="#cod-update-status" class="btn btn-primary">Cập nhật trạng thái đơn hàng</button>
						@endif
					@endif
					@if ($order instanceof  \App\Models\DOrder)
						<input type="hidden" name="d" value="1">
						<button type="submit" name="save_draft" value="1" class="btn btn-success" @if($order->status == 2) disabled @endif>{{ trans('button.save') }}</button>
						<button type="submit" class="btn btn-primary onetime-click" id="create-order-submit" @if($order->status == 2 || !$order->isReadyCreateOrder()) disabled @endif>{{ trans('button.create_from_draft') }}</button>
					@else
						@if($order->status == 7)
							<a type="button" class="btn btn-danger" href="{{ route('order.cancel', ['id' => $order->id]) }}">{{ trans('button.cancel') }}</a>
						@endif
						<button type="submit" class="btn btn-success onetime-click" id="order-submit" @if($order->status == 2) disabled @endif>{{ trans('button.save') }}</button>
					@endif
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@if($order->isCODOrder())
	@if(!$codOrder)
		<div class="modal fade" id="bill_ladding_modal" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-70" role="document">
				<div class="modal-content">
					{!! $billLadingHtml !!}
				</div>
			</div>
		</div>
		<div class="modal fade" id="fake-cod" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form action="{{ route('co.fake-bill', ['orderId' => $order->id]) }}" method="POST">
						{{ csrf_field() }}
						@if ($order instanceof \App\Models\DOrder)
							<input type="hidden" name="d" value="1"/>
						@endif
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Fake vận chuyển</h4>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label style="margin-right: 10px">Đối tác vận chuyển *:</label> 
								<label style="margin-right: 10px">
									<input type="radio" value="vtp" name="partner" checked>
									Viettel Post
								</label>
								<label style="margin-right: 10px">
									<input type="radio" value="ghtk" name="partner">
									GiaoHangTietKiem
								</label>
								<label>
									<input type="radio" value="ghn" name="partner">
									GiaoHangNhanh
								</label>
								<label>
									<input type="radio" value="ghn_5" name="partner">
									GiaoHangNhanh < 5kg
								</label>
								<label>
									<input type="radio" value="other" name="partner" >
									Vận chuyển khác
								</label>
							</div>
							<div class="form-group">
								<label for="">Mã vận đơn *:</label>
								<input type="text" class="form-control" name="order_code" placeholder="Nhập mã vận đơn" required/>
							</div>
							<div class="form-group">
								<label for="">Mã kho </label>
								<select name="store_id" class="form-control" placeholder="Nhập mã kho">
								</select>
							</div>
							<div class="form-group">
								<label for="">Tiền hàng :</label>
								<input type="text" class="form-control currency" name="package_price" 
									value="{{ round($order->total) }}" readonly/>
							</div>
							<div class="form-group">
								<label for="">Tiền COD *:</label>
								<input type="text" class="form-control currency" name="cod_amount" 
									value="{{ round($order->total) }}" required/>
							</div>
							<div class="form-group">
								<label for="">Tiền cước *:</label>
								<input type="text" class="form-control currency" name="fee_amount" value="0" required/>
							</div>
						</div>
						<div class="modal-footer">
							<div style="text-align: right;">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-success"Lưu/button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		@include('la.cod_orders.script', [
			'customer' => $order->customer,
			'codAmount' => $order->total
		])
	@elseif($codOrder)
		{!! $billLadingHtml !!}
	@endif
@endif

@include('la.partials.modals.vnpost_create_sender', [
    'store' => $order->store
])
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
@include('la.orders.script', [
	'selectedSeries' => $order->productSeries->pluck('seri_number')->toArray()
])
@push('styles')
	<link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/datatables.min.css') }}"/>
	<link rel="stylesheet" href="{{ asset('la-assets/plugins/datepicker/datepicker3.css') }}">
@endpush
@push('scripts')

<script src="{{ asset('la-assets/plugins/datatables/datatables.min.js') }}"></script>
<script>
function loadPartnerInventories(partner) {
	let orderId = "{{ $order->id }}";
	let url = "{{ url(config('laraadmin.adminRoute')) . '/cod-orders/partner-inventories/' }}" + partner + "/" + orderId;
	$.ajax({
		url: url,
		type: "GET",
		dataType: "JSON",
		beforeSend: function() {
			$("#fake-cod select[name='store_id']").empty();
		},
		success: function(results) {
			for (id in results) {
				$("#fake-cod select[name='store_id']").append("<option value='" + id + "'>" + results[id] + "</option>");
			}
		}
	});
}

$(function () {
	// @if ($order instanceof \App\Models\DOrder && $order->isFromFE())
	// 	$('#order-add-form input, #order-add-form select, #order-add-form textarea, #order-add-form .remove-selected-product').attr('readonly', 'readonly');
	// 	$('#order-add-form input[type="checkbox"], #order-add-form .remove-selected-product').click(function (e) {
	// 		e.preventDefault();
	// 		return false;
	// 	});
	// @endif
	loadSelectedProductIndex();
	@if($order->isCODOrder() && !$codOrder)
		loadPartnerInventories('vtp');
		$('#fake-cod input[name="partner"]').change(function() {
			let partner = $(this).val();
			loadPartnerInventories(partner);
		});
	@endif
	
	@if(!$transport)
	(function() {
		let orderData = $('#order-add-form').serializeObject();
		let quantityCol = parseInt(orderData.sub_type) == 2 ? 'w_quantity' : 'n_quantity';
		if (typeof orderData.products !== 'undefined') {
			Object.values(orderData.products).map(function(product, id) {
				index = Object.keys(orderData.products)[id];
				$(`.selected-product-transport[data-index=${index}] .quantity, .selected-product-transport[data-index=${index}] .packages`).val(product[quantityCol]);
			});
		}
	})();
	@endif

	scanSeri();
	updateSeriesForm();
	calculateTransport();
	$("#order-add-form").validate({
		
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
    @if (session('auto_submit') == 1)
		$('#create-order-submit').click();
	@endif
});
</script>
@endpush
