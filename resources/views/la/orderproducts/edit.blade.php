@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/orderproducts') }}">OrderProduct</a> :
@endsection
@section("contentheader_description", $orderproduct->$view_col)
@section("section", "OrderProducts")
@section("section_url", url(config('laraadmin.adminRoute') . '/orderproducts'))
@section("sub_section", "Edit")

@section("htmlheader_title", "OrderProducts Edit : ".$orderproduct->$view_col)

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
				{!! Form::model($orderproduct, ['route' => [config('laraadmin.adminRoute') . '.orderproducts.update', $orderproduct->id ], 'method'=>'PUT', 'id' => 'orderproduct-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'order_id')
					@la_input($module, 'product_id')
					@la_input($module, 'quantity')
					@la_input($module, 'price')
					@la_input($module, 'product_type')
					@la_input($module, 'total')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/orderproducts') }}">Huỷ</a></button>
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
	$("#orderproduct-edit-form").validate({
		
	});
});
</script>
@endpush
