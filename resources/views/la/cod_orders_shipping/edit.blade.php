@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/cod-orders-shipping?type='.$sOrder->type) }}">COD Orders Shipping list</a> :
@endsection
@section("contentheader_description", $sOrder->$view_col)
@section("section", "cod-orders-shipping")
@section("section_url", url(config('laraadmin.adminRoute') . '/cod-orders-shipping?type='.$sOrder->type))
@section("sub_section", "Edit")

@section("htmlheader_title", "Shipping Orders Edit : ".$sOrder->$view_col)

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
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-danger errors" style="display: none">
					<ul>
					</ul>
				</div>
				{!! Form::model($sOrder, ['route' => [config('laraadmin.adminRoute') . '.cod-orders-shipping.update', $sOrder->id ], 'method'=>'PUT', 'id' => 'shipping-order-form']) !!}					
					<div class="box-body">
						<div class="row">
							<div class="col-sm-9" id="relation_orders">
								<div class="form-group">
									<select id="search-order" class="form-control" multiple="" value="" @if(!$sOrder->canEdit()) disabled @endif>
										@foreach($selectedOrders as $order)
										<option value="{{ $order->order_code }}" selected>{{ $order->order_code }}</option>
										@endforeach
									</select>
								</div>
								<div id="selected_orders">
									@include('la.cod_orders_shipping.selected_order_list', $selectedOrders)
								</div>
							</div>
							<div class="col-sm-3">
								<div class="form-group">
									<label style="margin-right: 20px">Thao tác :</label>
									<label style="margin-right: 10px"><input type="radio" name="handle_type" value="1" @if($sOrder->handle_type == 1) checked @endif>Thủ công</label>
									<label><input type="radio" name="handle_type" value="2" @if($sOrder->handle_type == 2) checked @endif>Quét mã vạch</label>
								</div>
								<div class="form-group">
									<label style="margin-right: 20px">Đối tác vận chuyển :</label>
									{!! $sOrder->getPartnerHTMLFormatted() !!}
									<input type="hidden" name="partner" value="{{ $sOrder->partner }}">
								</div>
								<div class="form-group">
									<label for="status">Trạng thái :</label>
									<select class="form-control" name="status" @if(!$sOrder->canEdit()) disabled @endif>
										@foreach($statusList as $key => $value)
										<option value="{{ $key }}" @if($key == $sOrder->status) selected @endif>{{ $value }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label for="note">Ghi chú:</label>
									<textarea class="form-control tinymce" placeholder="Enter ghi chú" cols="30" rows="10" name="note">
										{!! $sOrder->note !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
                    <br>
					<div class="form-group">
						<button class="btn btn-success" @if(!$sOrder->canEdit()) disabled @endif>Update</button> 
						<button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/cod-orders-shipping?type=') . $sOrder->type }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>
	
@endsection
@include('la.cod_orders_shipping.script', [
	'selectedCodes' => $selectedOrders->pluck('order_code')->toArray()
])

@push('scripts')
<script>
$(function () {
	attachScan();
	$("#shipping-order-form").validate({
		
	});
	
});
</script>
@endpush