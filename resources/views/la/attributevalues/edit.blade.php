@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/attributevalues') }}">Thuộc tính sản phẩm</a> :
@endsection
@section("contentheader_description", $attributevalue->$view_col)
@section("section", "Thuộc tính sản phẩm")
@section("section_url", url(config('laraadmin.adminRoute') . '/attributevalues'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Thuộc tính sản phẩm : ".$attributevalue->$view_col)

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
				{!! Form::model($attributevalue, ['route' => [config('laraadmin.adminRoute') . '.attributevalues.update', $attributevalue->id ], 'method'=>'PUT', 'id' => 'attributevalue-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'attribute_id')
					@la_input($module, 'value')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/attributevalues') }}">Huỷ</a></button>
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
	$("#attributevalue-edit-form").validate({
		
	});
});
</script>
@endpush
