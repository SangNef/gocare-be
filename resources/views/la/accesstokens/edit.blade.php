@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/accesstokens') }}">AccessToken</a> :
@endsection
@section("contentheader_description", $accesstoken->$view_col)
@section("section", "AccessTokens")
@section("section_url", url(config('laraadmin.adminRoute') . '/accesstokens'))
@section("sub_section", "Edit")

@section("htmlheader_title", "AccessTokens Edit : ".$accesstoken->$view_col)

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
				{!! Form::model($accesstoken, ['route' => [config('laraadmin.adminRoute') . '.accesstokens.update', $accesstoken->id ], 'method'=>'PUT', 'id' => 'accesstoken-edit-form']) !!}
					@la_input($module, 'name')
					<div class="form-group">
						{!! Form::label('name', 'Api key') !!}
						{{ Form::text('api_key', $accesstoken->api_key, ['class' => 'form-control', 'readonly' => true]) }}
					</div>
					@la_input($module, 'status')
					{{--
					@la_input($module, 'api_key')
					@la_input($module, 'status')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/accesstokens') }}">Huỷ</a></button>
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
	$("#accesstoken-edit-form").validate({
		
	});
});
</script>
@endpush
