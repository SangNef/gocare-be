@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/configs') }}">Config</a> :
@endsection
@section("contentheader_description", $config->$view_col)
@section("section", "Configs")
@section("section_url", url(config('laraadmin.adminRoute') . '/configs'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Configs Edit : ".$config->$view_col)

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
				{!! Form::model($config, ['route' => [config('laraadmin.adminRoute') . '.configs.update', $config->id ], 'method'=>'PUT', 'id' => 'config-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'key')
					@la_input($module, 'value')
					@la_input($module, 'desc')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/configs') }}">Huỷ</a></button>
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
	$("#config-edit-form").validate({
		
	});
});
</script>
@endpush
