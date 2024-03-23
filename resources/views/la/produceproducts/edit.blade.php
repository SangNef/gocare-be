@extends("la.layouts.app")

@section("contentheader_title")
	<a href="{{ url(config('laraadmin.adminRoute') . '/produceproducts') }}">ProduceProduct</a> :
@endsection
@section("contentheader_description", $produceproduct->$view_col)
@section("section", "ProduceProducts")
@section("section_url", url(config('laraadmin.adminRoute') . '/produceproducts'))
@section("sub_section", "Edit")

@section("htmlheader_title", "ProduceProducts Edit : ".$produceproduct->$view_col)

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
				{!! Form::model($produceproduct, ['route' => [config('laraadmin.adminRoute') . '.produceproducts.update', $produceproduct->id ], 'method'=>'PUT', 'id' => 'produceproduct-edit-form']) !!}
					@la_form($module)
					
					{{--
					@la_input($module, 'product_id')
					@la_input($module, 'quantity')
					@la_input($module, 'produce_id')
					--}}
                    <br>
					<div class="form-group">
						{!! Form::submit( 'Lưu', ['class'=>'btn btn-success']) !!} <button class="btn btn-default pull-right"><a href="{{ url(config('laraadmin.adminRoute') . '/produceproducts') }}">Huỷ</a></button>
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
	$("#produceproduct-edit-form").validate({
		
	});
});
</script>
@endpush
