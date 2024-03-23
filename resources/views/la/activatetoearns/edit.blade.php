@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/activatetoearns') }}">ActivateToEarn</a> :
@endsection
@section("contentheader_description", $activatetoearn->$view_col)
@section("section", "ActivateToEarns")
@section("section_url", url(config('laraadmin.adminRoute') . '/activatetoearns'))
@section("sub_section", "Edit")

@section("htmlheader_title", "ActivateToEarns Edit : ".$activatetoearn->$view_col)

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
				{!! Form::model($activatetoearn, ['route' => [config('laraadmin.adminRoute') . '.activatetoearns.update', $activatetoearn->id ], 'method'=>'PUT', 'id' => 'activatetoearn-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'product_seri_id')
					@la_input($module, 'order_id')
					@la_input($module, 'name')
					@la_input($module, 'phone')
					@la_input($module, 'amount')
					@la_input($module, 'status')
					@la_input($module, 'activated_at')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/activatetoearns') }}">Huỷ</a></button>
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
	$("#activatetoearn-edit-form").validate({
		
	});
});
</script>
@endpush
