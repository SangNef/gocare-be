@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/ioseris') }}">IOSeri</a> :
@endsection
@section("contentheader_description", $ioseri->$view_col)
@section("section", "IOSeris")
@section("section_url", url(config('laraadmin.adminRoute') . '/ioseris'))
@section("sub_section", "Edit")

@section("htmlheader_title", "IOSeris Edit : ".$ioseri->$view_col)

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
				{!! Form::model($ioseri, ['route' => [config('laraadmin.adminRoute') . '.ioseris.update', $ioseri->id ], 'method'=>'PUT', 'id' => 'ioseri-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'io_id')
					@la_input($module, 'product_id')
					@la_input($module, 'pseri_id')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/ioseris') }}">Huỷ</a></button>
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
	$("#ioseri-edit-form").validate({
		
	});
});
</script>
@endpush
