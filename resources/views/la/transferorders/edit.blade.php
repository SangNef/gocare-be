@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/transferorders') }}">TransferOrder</a> :
@endsection
@section("contentheader_description", $transferorder->$view_col)
@section("section", "TransferOrders")
@section("section_url", url(config('laraadmin.adminRoute') . '/transferorders'))
@section("sub_section", "Edit")

@section("htmlheader_title", "TransferOrders Edit : ".$transferorder->$view_col)

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
				{!! Form::model($transferorder, ['route' => [config('laraadmin.adminRoute') . '.transferorders.update', $transferorder->id ], 'method'=>'PUT', 'id' => 'transferorder-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'code')
					@la_input($module, 'customer_id')
					@la_input($module, 'Người tạo')
					@la_input($module, 'number_of_seris')
					@la_input($module, 'amount')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/transferorders') }}">Huỷ</a></button>
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
	$("#transferorder-edit-form").validate({
		
	});
});
</script>
@endpush
