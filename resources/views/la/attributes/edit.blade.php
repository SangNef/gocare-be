@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/attributes') }}">Thuộc tính sản phẩm</a> :
@endsection
@section("contentheader_description", $attribute->$view_col)
@section("section", "Thuộc tính sản phẩm")
@section("section_url", url(config('laraadmin.adminRoute') . '/attributes'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa thuộc tính sản phẩm : ".$attribute->$view_col)

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
				{!! Form::model($attribute, ['route' => [config('laraadmin.adminRoute') . '.attributes.update', $attribute->id ], 'method'=>'PUT', 'id' => 'attribute-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'name')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/attributes') }}">Huỷ</a></button>
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
	$("#attribute-edit-form").validate({
		
	});
});
</script>
@endpush
