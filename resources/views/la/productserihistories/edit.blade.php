@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/productserihistories') }}">ProductSeriHistory</a> :
@endsection
@section("contentheader_description", $productserihistory->$view_col)
@section("section", "ProductSeriHistories")
@section("section_url", url(config('laraadmin.adminRoute') . '/productserihistories'))
@section("sub_section", "Edit")

@section("htmlheader_title", "ProductSeriHistories Edit : ".$productserihistory->$view_col)

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
				{!! Form::model($productserihistory, ['route' => [config('laraadmin.adminRoute') . '.productserihistories.update', $productserihistory->id ], 'method'=>'PUT', 'id' => 'productserihistory-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'product_seri_id')
					@la_input($module, 'creator_id')
					@la_input($module, 'customer_id')
					@la_input($module, 'transfered_at')
					@la_input($module, 'transfer_order_id')
					@la_input($module, 'price')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/productserihistories') }}">Huỷ</a></button>
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
	$("#productserihistory-edit-form").validate({
		
	});
});
</script>
@endpush
