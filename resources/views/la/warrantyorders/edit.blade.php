@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/warrantyorders') }}">WarrantyOrder</a> :
@endsection
@section("contentheader_description", $warrantyorder->$view_col)
@section("section", "WarrantyOrders")
@section("section_url", url(config('laraadmin.adminRoute') . '/warrantyorders'))
@section("sub_section", "Edit")

@section("htmlheader_title", "WarrantyOrders Edit : ".$warrantyorder->$view_col)

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

<div class="box">
	<div class="box-header">
		
	</div>
	<div class="box-body">
		{!! Form::model($warrantyorder, ['route' => [config('laraadmin.adminRoute') . '.warrantyorders.update', $warrantyorder->id ], 'method'=>'PUT', 'id' => 'warrantyorders-add-form']) !!}
		<div class="row">
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
									'selectedProducts' => $warrantyorder->warrantyOrderProducts->map(function (\App\Models\WarrantyOrderProduct $wop) {
										return  [
										    'note' => $wop->note,
											'products' => [$wop->product],
											'quantity' => $wop->quantity,
										];
									}),
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
							<div class="form-group">
								<label for="code">Mã đơn hàng* :</label>
								<input class="form-control" readonly="readonly" name="code" type="text" value="{{ $warrantyorder->code }}" />
							</div>
							<div class="form-group">
								<label for="code">Ngày tạo* :</label>
								<input class="form-control datepicker" name="created_at" type="text" value="{{ $warrantyorder->created_at->format('Y/m/d') }}"/>
							</div>
							<div class="form-group">
								<label for="status" style="margin-right: 20px">Khách hàng :</label>
								<select name="customer_id" class="form-control ajax-select" model="customer" id="warrantyorder_customer">
									<option value="{{ $warrantyorder->customer_id }}" selected>{{ $warrantyorder->customer->name }}</option>
								</select>
							</div>
							<div class="form-group">
								<label for="status" style="margin-right: 20px">Kiểu :</label>
								{!! $warrantyorder->getTypeHTMLFormatted() !!}
							</div>
							<div class="form-group">
								<label for="currency_type" style="margin-right: 20px">Loại tiền tệ :</label>
								{!! $warrantyorder->getCurrencyHTMLFormatted() !!}
							</div>
							<div class="form-group">
								<label for="status" style="margin-right: 20px">Trạng thái :</label>
								{!! $statusFormatted !!}
							</div>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade in" id="tab-series">
					<div class="col-sm-12">
						<div class="row">
							<div class="bg-gray" style="padding: 10px; display: flex; align-items: center ">
								<h3 style="flex: 1 1; margin: 0 50px 0 0;">{{ trans('messages.selected_products') }}</h3>
								<div style="display: flex; align-items: center">
									<button type="button" id="bill-lading-some" class="btn btn-warning onetime-click" style="margin-right: 20px">
										Vận đơn sản phẩm đã chọn	
									</button>
									@if($codOrder)
										<span style="margin-right: 20px">
											Mã vận đơn {{ strtoupper($codOrder->partner) }}: <strong class="text-danger">{{ $codOrder->order_code }}</strong>
										</span>
									@else
										<button type="button" @if(!$warrantyorder->canCreateBillLadingAllProduct()) disabled @endif id="bill-lading-all" class="btn btn-primary onetime-click" style="margin-right: 20px">
											Vận đơn tất cả sản phẩm
										</button>
									@endif
									<button type="button" id="print-row" class="btn btn-warning onetime-click" style="margin-right: 20px">
										In seri đã chọn
									</button>
									<select class="form-control" id="print-status" style="width: 200px">
										<option value="">Tất cả</option>
										@foreach(\App\Models\WarrantyOrderProductSeri::getAvailableStatus() as $value => $label)
										<option value="{{ $value }}">{{ $label }}</option>
										@endforeach
									</select>
									<button type="button" class="btn btn-primary onetime-click" id="print">In</button>
								</div>
							</div>
						</div>
						<div class="alert alert-danger" style="display: none">
							<ul></ul>
						</div>
						@include('la.products_selecting.selected_products_series', [
							'view' => 'la.products_selecting.warrantyorder_selected_product_seri',
							'selectedProducts' => $selectedProducts,
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
									'width' => 20
								],
								[
									'name' => 'Vận đơn',
									'width' => 10
								]
							]
						])
					</div>
				</div>
			</div>
			<div class="col-sm-12" style="text-align: right">
				<input type="hidden" name="store_id" value="{{ $warrantyorder->store_id }}">
				<button type="submit" class="btn btn-success onetime-click" id="order-submit" @if($warrantyorder->status == 3) disabled @endif>{{ trans('button.save') }}</button>
			</div>
		</div>
		{!! Form::close() !!}
	</div>
</div>

<div class="modal fade" id="bill_ladding_modal" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-70" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>
@endsection

@include('la.warrantyorders.script', [
	'orderId' => $warrantyorder->id
])

@include('la.cod_orders.script', [
	'customer' => $warrantyorder->customer,
	'order' => $warrantyorder
])
@push('scripts')
<script>
$(function () {
    scanSeri();
    orderSeriSelectionIni();

	$("#warrantyorders-add-form").validate({
		
	});

	$('#print').click(function() {
		let el = $(this);
		handlePrint({
			type: 'single',
			id: "{{ $warrantyorder->id }}",
			customer_id: "{{ $warrantyorder->customer_id }}",
			status: $('#print-status').val()
		}, function() {
			el.removeAttr('disabled');
			el.html('In');
		});
	});

	$('#print-row').click(function() {
		let el = $(this);
		let ids = getCheckedValue('.selected-product-seri .row');
		if (ids.length == 0) {
			alert('Chọn seri');
			setTimeout(function() {
				el.removeAttr('disabled');
				el.html('In seri đã chọn');
			});
			return;
		}
		handlePrint({
			type: 'row',
			id: "{{ $warrantyorder->id }}",
			seri_id: ids.join(','),
			customer_id: "{{ $warrantyorder->customer_id }}",
		}, function() {
			el.removeAttr('disabled');
			el.html('In seri đã chọn');
		});
	});
});
</script>
@endpush
