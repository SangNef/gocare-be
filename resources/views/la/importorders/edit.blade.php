@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/importorders') }}">ImportOrder</a> :
@endsection
@section("contentheader_description", $importorder->$view_col)
@section("section", "ImportOrders")
@section("section_url", url(config('laraadmin.adminRoute') . '/importorders'))
@section("sub_section", "Edit")

@section("htmlheader_title", "ImportOrders Edit : ".$importorder->$view_col)

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
				{!! Form::model($importorder, ['route' => [config('laraadmin.adminRoute') . '.importorders.update', $importorder->id ], 'method'=>'PUT', 'id' => 'importorder-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'order_id')
					@la_input($module, 'import_id')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/importorders') }}">Huỷ</a></button>
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
	$("#importorder-edit-form").validate({
		
	});
});
</script>
@endpush
