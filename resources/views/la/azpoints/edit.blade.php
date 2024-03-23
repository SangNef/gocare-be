@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/azpoints') }}">Azpoint</a> :
@endsection
@section("contentheader_description", $azpoint->$view_col)
@section("section", "Azpoints")
@section("section_url", url(config('laraadmin.adminRoute') . '/azpoints'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Azpoints Edit : ".$azpoint->$view_col)

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
				{!! Form::model($azpoint, ['route' => [config('laraadmin.adminRoute') . '.azpoints.update', $azpoint->id ], 'method'=>'PUT', 'id' => 'azpoint-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'customer_id')
					@la_input($module, 'description')
					@la_input($module, 'balance')
					@la_input($module, 'pseri_id')
					@la_input($module, 'amount')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/azpoints') }}">Huỷ</a></button>
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
	$("#azpoint-edit-form").validate({
		
	});
});
</script>
@endpush
