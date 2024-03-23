@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/smssents') }}">Smssent</a> :
@endsection
@section("contentheader_description", $smssent->$view_col)
@section("section", "Smssents")
@section("section_url", url(config('laraadmin.adminRoute') . '/smssents'))
@section("sub_section", "Edit")

@section("htmlheader_title", "Smssents Edit : ".$smssent->$view_col)

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
				{!! Form::model($smssent, ['route' => [config('laraadmin.adminRoute') . '.smssents.update', $smssent->id ], 'method'=>'PUT', 'id' => 'smssent-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'mod')
					@la_input($module, 'username')
					@la_input($module, 'phone')
					@la_input($module, 'message')
					@la_input($module, 'result')
					@la_input($module, 'status')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/smssents') }}">Huỷ</a></button>
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
	$("#smssent-edit-form").validate({
		
	});
});
</script>
@endpush
