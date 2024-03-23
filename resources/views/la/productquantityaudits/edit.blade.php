@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/productquantityaudits') }}">ProductQuantityAudit</a> :
@endsection
@section("contentheader_description", $productquantityaudit->$view_col)
@section("section", "ProductQuantityAudits")
@section("section_url", url(config('laraadmin.adminRoute') . '/productquantityaudits'))
@section("sub_section", "Edit")

@section("htmlheader_title", "ProductQuantityAudits Edit : ".$productquantityaudit->$view_col)

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
				{!! Form::model($productquantityaudit, ['route' => [config('laraadmin.adminRoute') . '.productquantityaudits.update', $productquantityaudit->id ], 'method'=>'PUT', 'id' => 'productquantityaudit-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'store_id')
					@la_input($module, 'product_id')
					@la_input($module, 'attrs_id')
					@la_input($module, 'attrs_value')
					@la_input($module, 'amount')
					@la_input($module, 'left')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/productquantityaudits') }}">Huỷ</a></button>
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
	$("#productquantityaudit-edit-form").validate({
		
	});
});
</script>
@endpush
