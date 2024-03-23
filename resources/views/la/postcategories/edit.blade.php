@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/postcategories') }}">Danh mục tin tức</a> :
@endsection
@section("contentheader_description", $postcategory->$view_col)
@section("section", "Danh mục tin tức")
@section("section_url", url(config('laraadmin.adminRoute') . '/postcategories'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa danh mục tin tức : ".$postcategory->$view_col)

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
				{!! Form::model($postcategory, ['route' => [config('laraadmin.adminRoute') . '.postcategories.update', $postcategory->id ], 'method'=>'PUT', 'id' => 'postcategory-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'title')
					@la_input($module, 'status')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/postcategories') }}">Huỷ</a></button>
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
	$("#postcategory-edit-form").validate({
		
	});
});
</script>
@endpush
