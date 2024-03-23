@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ route('co.index', ['type' => $type]) }}">{{ trans('cod_order.'.$type) }}</a>
@endsection
@section("section", trans('cod_order.'.$type))
@section("section_url", route('co.index', ['type' => $type]))
@section("sub_section", "Edit")

@section("htmlheader_title", "Order Edit: ".$codOrder->order_code)

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
				{!! Form::model($codOrder, ['route' => ['co.update', $type, $codOrder->id ], 'method'=>'PUT', 'id' => 'cod-edit-form']) !!}
                    <h4>Đơn hàng: <strong>{{ $codOrder->order_code }}</strong></h4>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="status">Tiền cước thực tế:</label>
								<input class="form-control currency" name="real_amount" value="{{ $codOrder->real_amount }} đ"/>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="status">Tiền COD:</label>
								<input class="form-control currency" name="cod_amount" value="{{ $codOrder->cod_amount }} đ"/>
							</div>
						</div>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ route('co.index', ['type' => $type]) }}">Huỷ</a></button>
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
	$("#cod-edit-form").validate({
		
	});
});
</script>
@endpush
