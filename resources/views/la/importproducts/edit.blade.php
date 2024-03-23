@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/importproducts') }}">ImportProduct</a> :
@endsection
@section("contentheader_description", $importproduct->$view_col)
@section("section", "ImportProducts")
@section("section_url", url(config('laraadmin.adminRoute') . '/importproducts'))
@section("sub_section", "Edit")

@section("htmlheader_title", "ImportProducts Edit : ".$importproduct->$view_col)

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
				{!! Form::model($importproduct, ['route' => [config('laraadmin.adminRoute') . '.importproducts.update', $importproduct->id ], 'method'=>'PUT', 'id' => 'importproduct-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'import_id')
					@la_input($module, 'product_id')
					@la_input($module, 'attrs_id')
					@la_input($module, 'attrs_text')
					@la_input($module, 'note')
					@la_input($module, 'quantity')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/importproducts') }}">Huỷ</a></button>
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
	$("#importproduct-edit-form").validate({
		
	});
});
</script>
@endpush
