@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/transactions') }}">Giao dịch</a> :
@endsection
@section("contentheader_description", $transaction->$view_col)
@section("section", "Giao dịch")
@section("section_url", url(config('laraadmin.adminRoute') . '/transactions'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa giao dịch : ".$transaction->$view_col)

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
			<div class="col-md-8 col-md-offset-2">
				{!! Form::model($transaction, ['route' => [config('laraadmin.adminRoute') . '.transactions.update', $transaction->id ], 'method'=>'PUT', 'id' => 'transaction-edit-form']) !!}
					<div class="form-group">
						<label for="type" class="mr10">Loại :</label>
						@if ($transaction->type == 1)
							<span class="label label-success">Nhận</span>
						@else
							<span class="label label-primary">Chuyển</span>
						@endif
						<input type="hidden" name="type" value="{{ $transaction->type }}">
					</div>
					<div class="form-group">
						<label for="trans_time">Thời gian:</label>
							<input class="form-control datetime-picker" type="text" id="trans_time" name="trans_time" value="{{ $transaction->created_at }}"/>
						</div>
					<div class="form-group">
						<label for="user_id">Người giao dịch*:</label>
						<input class="form-control" type="text" id="user_id" value="{{ auth()->user()->name }}" disabled="disabled"/>
						<input type="hidden" name="user_id" value="{{ auth()->user()->id }}"/>
					</div>
					<div class="form-group">
						<label for="transfer-bank">Ngân hàng :</label>
						<select class="form-control select2-hidden-accessible" required="1" data-placeholder="Enter ngân hàng" rel="select2" name="bank_id" id="bank_id" tabindex="-1" aria-hidden="true" aria-required="true">
								@foreach($banks as $bank)
								<option value="{{ $bank->id }}" @if($bank->id == $transaction->bank_id) selected @endif currency-type="{{ $bank->currency_type }}">{{ $bank->name }}</option>
								@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="customer">Tên khách hàng :</label>
						<select name="customer_id" extra_param="{{ $transaction->bank->currency_type }}" class="form-control ajax-select" model="customer" id="customer_id">
							@if ($transaction->customer_id)
								<option value="{{ $transaction->client->id }}" selected>{{ $transaction->client->name }}</option>
							@endif
						</select>
					</div>
					<div class="form-group">
						<label for="order_id">Mã đơn hàng :</label>
						<select class="form-control ajax-select" id="order_id" model="customer_orders" extra_param="{{ $transaction->customer_id }}" name="order_id">
							@if($transaction->order)
								<option value="{{ $transaction->order_id }}" selected>{{ $transaction->order->code }}</option>
 							@endif
 						</select>
					</div>
					@la_input($module, 'desc')
					@la_input($module, 'trans_id')
					<div class="form-group">
						<label for="amount">Số tiền*:</label>
							<input type="text" value="{{ $transaction->type == 1 ? $transaction->received_amount : $transaction->transfered_amount }}" name="amount" id="amount" class="form-control currency valid" aria-invalid="false"/>
					</div>
					<div class="form-group">
						<label for="amount">Phí:</label>
							<input type="text" value="{{ $transaction->fee }}" name="fee" id="fee" class="form-control currency valid" aria-invalid="false"/>
					</div>
					@la_input($module, 'note')
					<div class="form-group">
						<label for="status">Trạng thái :</label>
						<select class="form-control" required="1" data-placeholder="Enter trạng thái" name="status" id="status" tabindex="-1" aria-hidden="true" aria-required="true" @if($transaction->status == 2) disabled @endif>
							<option value="1" @if($transaction->status == 1) selected @endif>Mới</option>
							<option value="2" @if($transaction->status == 2) selected @endif>Đã duyệt</option>
						</select>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/transactions') }}">Huỷ</a></button>
					</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
	$("#transaction-edit-form").validate({
		
	});

	$('#customer_id').change(function() {
		let customerId = $(this).val();
		$('#order_id').val(null).change().attr('extra_param', customerId);
		initAjaxSelect();
	})

	$('#bank_id').change(function() {
		let selectedBank = $(this).find('option:selected');
		$('#customer_id').attr('extra_param', selectedBank.attr('currency-type'));
		$('#customer_id').val(null).change();
	});
});
</script>
@endpush
