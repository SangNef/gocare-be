@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/productcategories') }}">Danh mục sản phẩm</a> :
@endsection
@section("contentheader_description", $productcategory->$view_col)
@section("section", "Danh mục sản phẩm")
@section("section_url", url(config('laraadmin.adminRoute') . '/productcategories'))
@section("sub_section", "Sửa")

@section("htmlheader_title", "Sửa danh mục sản phẩm : ".$productcategory->$view_col)

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
				{!! Form::model($productcategory, ['route' => [config('laraadmin.adminRoute') . '.productcategories.update', $productcategory->id ], 'method'=>'PUT', 'id' => 'productcategory-edit-form']) !!}
					@la_form($module)
					<div class="form-group">
						<label style="font-size: 15px" for="use_at_fe">
							<input type="checkbox" id="use_at_fe" name="use_at_fe" style="margin-right: 5px;" @if($productcategory->use_at_fe) checked @endif>
							Sử dụng ở FE
						</label>
					</div>
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/productcategories') }}">Huỷ</a></button>
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
	$("#productcategory-edit-form").validate({
		
	});
});
</script>
@endpush
