@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/paymenthistories') }}">PaymentHistory</a> :
@endsection
@section("contentheader_description", $paymenthistory->$view_col)
@section("section", "PaymentHistories")
@section("section_url", url(config('laraadmin.adminRoute') . '/paymenthistories'))
@section("sub_section", "Edit")

@section("htmlheader_title", "PaymentHistories Edit : ".$paymenthistory->$view_col)

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
				{!! Form::model($paymenthistory, ['route' => [config('laraadmin.adminRoute') . '.paymenthistories.update', $paymenthistory->id ], 'method'=>'PUT', 'id' => 'paymenthistory-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'provider')
					@la_input($module, 'response')
					@la_input($module, 'message')
					@la_input($module, 'order_id')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/paymenthistories') }}">Huỷ</a></button>
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
	$("#paymenthistory-edit-form").validate({
		
	});
});
</script>
@endpush
